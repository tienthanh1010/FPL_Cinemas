<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MovieVersionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $versions = MovieVersion::query()
            ->with('movie')
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('movie', fn ($q2) => $q2->where('title', 'like', "%{$q}%"))
                    ->orWhere('format', 'like', "%{$q}%")
                    ->orWhere('audio_language', 'like', "%{$q}%")
                    ->orWhere('subtitle_language', 'like', "%{$q}%")
                    ->orWhere('notes', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.movie_versions.index', [
            'versions' => $versions,
            'q' => $q,
            'formatOptions' => admin_movie_format_options(),
            'languageOptions' => admin_language_options(),
        ]);
    }

    public function create(): View
    {
        $movieVersion = new MovieVersion();

        return view('admin.movie_versions.create', $this->formData($movieVersion));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

$movieVersion = MovieVersion::create($data);

        return redirect()->route('admin.movie_versions.show', $movieVersion)->with('success', 'Đã tạo phiên bản phim.');
    }

    public function show(MovieVersion $movieVersion): View
    {
        $movieVersion->load(['movie', 'shows.auditorium']);

        return view('admin.movie_versions.show', compact('movieVersion'));
    }

    public function edit(MovieVersion $movieVersion): View
    {
        return view('admin.movie_versions.edit', $this->formData($movieVersion));
    }

    public function update(Request $request, MovieVersion $movieVersion): RedirectResponse
    {
        $data = $this->validateData($request, $movieVersion);

        $movieVersion->update($data);

        return redirect()->route('admin.movie_versions.show', $movieVersion)->with('success', 'Đã cập nhật phiên bản phim.');
    }

    public function destroy(MovieVersion $movieVersion): RedirectResponse
    {
        try {
            DB::transaction(function () use ($movieVersion) {
                $movieVersion->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá phiên bản phim (có thể đang được tham chiếu bởi suất chiếu).');
        }

        return back()->with('success', 'Đã xoá phiên bản phim.');
    }

    private function formData(MovieVersion $movieVersion): array
    {
        return [
            'movieVersion' => $movieVersion,
            'movies' => Movie::orderBy('title')->get(),
            'formatOptions' => admin_movie_format_options(),
            'languageOptions' => admin_language_options(),
        ];
    }

    private function validateData(Request $request, ?MovieVersion $movieVersion = null): array
    {
        $languageKeys = array_keys(admin_language_options());
        $formatKeys = array_keys(admin_movie_format_options());

        $data = $request->validate([
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'format' => ['required', Rule::in($formatKeys)],
            'audio_language' => ['required', Rule::in($languageKeys)],
            'subtitle_language' => ['nullable', Rule::in($languageKeys)],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'audio_language.in' => 'Ngôn ngữ audio phải được chọn từ danh sách có sẵn.',
            'subtitle_language.in' => 'Ngôn ngữ phụ đề phải được chọn từ danh sách có sẵn.',
        ]);

        $data['subtitle_language'] = $this->nullableString($data['subtitle_language'] ?? null);
        $data['notes'] = $this->nullableString($data['notes'] ?? null);

        $duplicateExists = MovieVersion::query()
            ->where('movie_id', $data['movie_id'])
            ->where('format', $data['format'])
            ->where('audio_language', $data['audio_language'])
            ->where('subtitle_language', $data['subtitle_language'])
            ->when($movieVersion, fn ($query) => $query->where('id', '!=', $movieVersion->id))
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'format' => 'Phiên bản này đã tồn tại (trùng phim, định dạng, audio và phụ đề).',
            ]);
        }

        return $data;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }
}
