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
    private const FORMATS = ['2D','3D','IMAX','4DX','SCREENX','DOLBY'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $versions = MovieVersion::query()
            ->with('movie')
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('movie', fn($q2) => $q2->where('title', 'like', "%{$q}%"))
                    ->orWhere('format', 'like', "%{$q}%")
                    ->orWhere('audio_language', 'like', "%{$q}%")
                    ->orWhere('subtitle_language', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $formats = self::FORMATS;

        return view('admin.movie_versions.index', compact('versions', 'q', 'formats'));
    }

    public function create(): View
    {
        $movieVersion = new MovieVersion();
        $movies = Movie::orderBy('title')->get();
        $formats = self::FORMATS;

        return view('admin.movie_versions.create', compact('movieVersion', 'movies', 'formats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        MovieVersion::create($data);

        return redirect()->route('admin.movie_versions.index')->with('success', 'Đã tạo phiên bản phim.');
    }

    public function edit(MovieVersion $movieVersion): View
    {
        $movies = Movie::orderBy('title')->get();
        $formats = self::FORMATS;

        return view('admin.movie_versions.edit', compact('movieVersion', 'movies', 'formats'));
    }

    public function update(Request $request, MovieVersion $movieVersion): RedirectResponse
    {
        $data = $this->validateData($request, $movieVersion);

        $movieVersion->update($data);

        return redirect()->route('admin.movie_versions.index')->with('success', 'Đã cập nhật phiên bản phim.');
    }

    public function destroy(MovieVersion $movieVersion): RedirectResponse
    {
        try {
            DB::transaction(function () use ($movieVersion) {
                $movieVersion->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá phiên bản phim (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá phiên bản phim.');
    }

    private function validateData(Request $request, ?MovieVersion $movieVersion = null): array
    {
        $data = $request->validate([
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'format' => ['required', Rule::in(self::FORMATS)],
            'audio_language' => ['required', 'string', 'max:32'],
            'subtitle_language' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Enforce unique constraint: (movie_id, format, audio_language, subtitle_language)
        $dup = MovieVersion::query()
            ->where('movie_id', $data['movie_id'])
            ->where('format', $data['format'])
            ->where('audio_language', $data['audio_language'])
            ->where('subtitle_language', $data['subtitle_language'])
            ->when($movieVersion, fn($q) => $q->where('id', '!=', $movieVersion->id))
            ->exists();

        if ($dup) {
            throw ValidationException::withMessages([
                'format' => 'Phiên bản này đã tồn tại (trùng movie + format + audio + sub).',
            ]);
        }

        return $data;
    }
}
