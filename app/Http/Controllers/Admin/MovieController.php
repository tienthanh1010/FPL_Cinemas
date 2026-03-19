<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContentRating;
use App\Models\Movie;
use App\Models\MovieVersion;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovieController extends Controller
{
    private const STATUSES = [
        'ACTIVE' => 'Đang hiển thị',
        'INACTIVE' => 'Tạm ẩn',
    ];

    private const TRAILER_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtu.be',
        'vimeo.com',
        'www.vimeo.com',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $movies = Movie::query()
            ->with(['contentRating', 'genres', 'directorCredits', 'versions'])
            ->withCount('versions')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('title', 'like', "%{$q}%")
                        ->orWhere('original_title', 'like', "%{$q}%")
                        ->orWhere('language_original', 'like', "%{$q}%")
                        ->orWhereHas('genres', fn ($genreQuery) => $genreQuery->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('credits', fn ($personQuery) => $personQuery->where('full_name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.movies.index', [
            'movies' => $movies,
            'q' => $q,
            'statusOptions' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        $movie = new Movie();

        return view('admin.movies.create', $this->formData($movie));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        $movie = null;

        DB::transaction(function () use ($payload, &$movie) {
            $movie = Movie::create(array_merge($payload['movie'], [
                'public_id' => (string) Str::ulid(),
            ]));

            $this->syncGenres($movie, $payload['genre_ids']);
            $this->syncCredits($movie, $payload['credits']);
            $this->syncVersions($movie, $payload['versions']);
        });

        return redirect()->route('admin.movies.show', $movie)->with('success', 'Đã tạo phim với đầy đủ dữ liệu liên kết.');
    }

    public function show(Movie $movie): View
    {
        $movie->load(['contentRating', 'genres', 'directorCredits', 'castCredits', 'versions', 'versions.shows.auditorium']);

        return view('admin.movies.show', compact('movie'));
    }

    public function edit(Movie $movie): View
    {
        $movie->load(['genres', 'credits', 'versions']);

        return view('admin.movies.edit', $this->formData($movie));
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $payload = $this->validatePayload($request, $movie);

        DB::transaction(function () use ($movie, $payload) {
            $movie->update($payload['movie']);
            $this->syncGenres($movie, $payload['genre_ids']);
            $this->syncCredits($movie, $payload['credits']);
            $this->syncVersions($movie, $payload['versions']);
        });

        return redirect()->route('admin.movies.show', $movie)->with('success', 'Đã cập nhật phim.');
    }

    public function destroy(Movie $movie): RedirectResponse
    {
        try {
            DB::transaction(function () use ($movie) {
                $movie->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá phim (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá phim.');
    }

    private function formData(Movie $movie): array
    {
        $movie->loadMissing(['contentRating', 'genres', 'credits', 'versions']);

        $creditNames = [
            'DIRECTOR' => $this->implodeRoleNames($movie, 'DIRECTOR'),
            'WRITER' => $this->implodeRoleNames($movie, 'WRITER'),
            'CAST' => $this->implodeRoleNames($movie, 'CAST'),
        ];

        $versionRows = $movie->versions
            ->map(fn (MovieVersion $version) => [
                'id' => $version->id,
                'format' => $version->format,
                'audio_language' => $version->audio_language,
                'subtitle_language' => $version->subtitle_language,
                'notes' => $version->notes,
            ])
            ->values()
            ->all();

        if (empty($versionRows)) {
            $versionRows = [[
                'id' => null,
                'format' => '2D',
                'audio_language' => $movie->language_original ?: 'VI',
                'subtitle_language' => null,
                'notes' => null,
            ]];
        }

        return [
            'movie' => $movie,
            'ratings' => ContentRating::orderBy('min_age')->get(),
            'genres' => Category::orderBy('name')->get(),
            'statusOptions' => self::STATUSES,
            'languageOptions' => admin_language_options(),
            'formatOptions' => admin_movie_format_options(),
            'selectedGenreIds' => old('genre_ids', $movie->genres->pluck('id')->all()),
            'creditValues' => [
                'DIRECTOR' => old('credit_director_names', $creditNames['DIRECTOR']),
                'WRITER' => old('credit_writer_names', $creditNames['WRITER']),
                'CAST' => old('credit_cast_names', $creditNames['CAST']),
            ],
            'versionRows' => old('versions', $versionRows),
        ];
    }

    private function validatePayload(Request $request, ?Movie $movie = null): array
    {
        $languageKeys = array_keys(admin_language_options());
        $formatKeys = array_keys(admin_movie_format_options());

        $validator = Validator::make($request->all(), [
            'content_rating_id' => ['nullable', 'integer', 'exists:content_ratings,id'],
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'release_date' => ['nullable', 'date'],
            'language_original' => ['required', Rule::in($languageKeys)],
            'synopsis' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'url', 'max:512'],
            'trailer_url' => ['nullable', 'url', 'max:512'],
            'censorship_license_no' => ['nullable', 'string', 'max:64'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'credit_director_names' => ['nullable', 'string', 'max:1000'],
            'credit_writer_names' => ['nullable', 'string', 'max:1000'],
            'credit_cast_names' => ['nullable', 'string', 'max:3000'],
            'versions' => ['nullable', 'array'],
            'versions.*.id' => ['nullable', 'integer'],
            'versions.*.format' => ['nullable', Rule::in($formatKeys)],
            'versions.*.audio_language' => ['nullable', Rule::in($languageKeys)],
            'versions.*.subtitle_language' => ['nullable', Rule::in($languageKeys)],
            'versions.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'language_original.in' => 'Ngôn ngữ gốc phải được chọn từ danh sách có sẵn.',
            'poster_url.url' => 'Poster phải là một đường dẫn hợp lệ.',
            'trailer_url.url' => 'Trailer phải là một đường dẫn hợp lệ.',
        ]);

        $validator->after(function ($validator) use ($request, $movie) {
            $posterUrl = $this->nullableString($request->input('poster_url'));
            $trailerUrl = $this->nullableString($request->input('trailer_url'));

            if ($posterUrl !== null && ! $this->looksLikeImageUrl($posterUrl)) {
                $validator->errors()->add('poster_url', 'Poster URL chỉ chấp nhận link ảnh trực tiếp (.jpg, .jpeg, .png, .webp, .avif).');
            }

            if ($trailerUrl !== null && ! $this->looksLikeSupportedTrailerUrl($trailerUrl)) {
                $validator->errors()->add('trailer_url', 'Trailer URL chỉ chấp nhận YouTube hoặc Vimeo để tránh nhập link linh tinh.');
            }

            $versions = $this->normalizeVersions($request->input('versions', []), $request->input('language_original', 'VI'));
            $seen = [];

            foreach ($versions as $index => $version) {
                $humanIndex = $index + 1;

                if ($version['format'] === '' || $version['audio_language'] === '') {
                    $validator->errors()->add("versions.$index.format", "Phiên bản #{$humanIndex} cần đủ định dạng và ngôn ngữ audio.");
                    continue;
                }

                if ($movie !== null && $version['id'] !== null) {
                    $belongsToMovie = $movie->versions()->whereKey($version['id'])->exists();
                    if (! $belongsToMovie) {
                        $validator->errors()->add("versions.$index.id", 'Phiên bản phim không thuộc bộ phim hiện tại.');
                    }
                }

                $duplicateKey = implode('|', [
                    $version['format'],
                    $version['audio_language'],
                    $version['subtitle_language'] ?? '',
                ]);

                if (isset($seen[$duplicateKey])) {
                    $validator->errors()->add("versions.$index.format", 'Danh sách phiên bản đang bị trùng định dạng + audio + subtitle.');
                }

                $seen[$duplicateKey] = true;
            }
        });

        $movieData = $validator->validate();

        $versions = $this->normalizeVersions($request->input('versions', []), $movieData['language_original']);
        if ($versions->isEmpty()) {
            $versions = collect([[
                'id' => null,
                'format' => '2D',
                'audio_language' => $movieData['language_original'],
                'subtitle_language' => null,
                'notes' => null,
            ]]);
        }

        return [
            'movie' => [
                'content_rating_id' => $movieData['content_rating_id'] ?? null,
                'title' => trim((string) $movieData['title']),
                'original_title' => $this->nullableString($movieData['original_title'] ?? null),
                'duration_minutes' => (int) $movieData['duration_minutes'],
                'release_date' => $movieData['release_date'] ?? null,
                'language_original' => $movieData['language_original'],
                'synopsis' => $this->nullableString($movieData['synopsis'] ?? null),
                'poster_url' => $this->nullableString($movieData['poster_url'] ?? null),
                'trailer_url' => $this->normalizeTrailerUrl($movieData['trailer_url'] ?? null),
                'censorship_license_no' => $this->nullableString($movieData['censorship_license_no'] ?? null),
                'status' => $movieData['status'],
            ],
            'genre_ids' => array_values(array_unique(array_map('intval', $movieData['genre_ids'] ?? []))),
            'credits' => [
                'DIRECTOR' => $this->parsePeopleNames($movieData['credit_director_names'] ?? ''),
                'WRITER' => $this->parsePeopleNames($movieData['credit_writer_names'] ?? ''),
                'CAST' => $this->parsePeopleNames($movieData['credit_cast_names'] ?? ''),
            ],
            'versions' => $versions,
        ];
    }

    private function syncGenres(Movie $movie, array $genreIds): void
    {
        $movie->genres()->sync($genreIds);
    }

    private function syncCredits(Movie $movie, array $credits): void
    {
        DB::table('movie_people')->where('movie_id', $movie->id)->delete();

        $rows = [];
        foreach ($credits as $role => $names) {
            foreach (array_values($names) as $index => $name) {
                $person = Person::firstOrCreate(
                    ['full_name' => $name],
                    ['public_id' => (string) Str::ulid()]
                );

                $rows[] = [
                    'movie_id' => $movie->id,
                    'person_id' => $person->id,
                    'role_type' => $role,
                    'character_name' => null,
                    'sort_order' => $index + 1,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('movie_people')->insert($rows);
        }
    }

    private function syncVersions(Movie $movie, Collection $versions): void
    {
        foreach ($versions as $version) {
            $payload = [
                'movie_id' => $movie->id,
                'format' => $version['format'],
                'audio_language' => $version['audio_language'],
                'subtitle_language' => $version['subtitle_language'],
                'notes' => $version['notes'],
            ];

            if ($version['id'] !== null) {
                $movie->versions()->whereKey($version['id'])->update($payload);
                continue;
            }

            $exists = $movie->versions()
                ->where('format', $payload['format'])
                ->where('audio_language', $payload['audio_language'])
                ->where('subtitle_language', $payload['subtitle_language'])
                ->exists();

            if (! $exists) {
                $movie->versions()->create($payload);
            }
        }
    }

    private function normalizeVersions(array $rows, string $fallbackLanguage): Collection
    {
        return collect($rows)
            ->map(function ($row) use ($fallbackLanguage) {
                $format = strtoupper(trim((string) ($row['format'] ?? '')));
                $rawAudioLanguage = strtoupper(trim((string) ($row['audio_language'] ?? '')));
                $subtitleLanguage = strtoupper(trim((string) ($row['subtitle_language'] ?? '')));
                $notes = $this->nullableString($row['notes'] ?? null);
                $id = blank($row['id'] ?? null) ? null : (int) $row['id'];
                $hasMeaningfulData = $id !== null || $format !== '' || $rawAudioLanguage !== '' || $subtitleLanguage !== '' || $notes !== null;

                return [
                    'id' => $id,
                    'format' => $format,
                    'audio_language' => $hasMeaningfulData ? ($rawAudioLanguage === '' ? strtoupper($fallbackLanguage) : $rawAudioLanguage) : '',
                    'subtitle_language' => $subtitleLanguage === '' ? null : $subtitleLanguage,
                    'notes' => $notes,
                ];
            })
            ->filter(function ($row) {
                return $row['id'] !== null
                    || $row['format'] !== ''
                    || $row['audio_language'] !== ''
                    || $row['subtitle_language'] !== null
                    || $row['notes'] !== null;
            })
            ->values();
    }

    private function parsePeopleNames(string $value): array
    {
        $parts = preg_split('/[\r\n,;|]+/u', $value) ?: [];

        return collect($parts)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item, 'UTF-8'))
            ->values()
            ->all();
    }

    private function implodeRoleNames(Movie $movie, string $role): string
    {
        return $movie->credits
            ->filter(fn ($person) => ($person->pivot->role_type ?? null) === $role)
            ->pluck('full_name')
            ->implode(', ');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }

    private function looksLikeImageUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        foreach (self::IMAGE_EXTENSIONS as $extension) {
            if (str_ends_with($path, ".{$extension}")) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeSupportedTrailerUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, self::TRAILER_HOSTS, true);
    }

    private function normalizeTrailerUrl(?string $url): ?string
    {
        $url = $this->nullableString($url);
        if ($url === null) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) && ! empty($query['v'])) {
            return 'https://www.youtube.com/watch?v=' . $query['v'];
        }

        if ($host === 'youtu.be') {
            $videoId = trim((string) parse_url($url, PHP_URL_PATH), '/');
            if ($videoId !== '') {
                return 'https://www.youtube.com/watch?v=' . $videoId;
            }
        }

        return $url;
    }
}
