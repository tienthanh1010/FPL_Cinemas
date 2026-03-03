<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRating;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $movies = Movie::query()
            ->with('contentRating')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('original_title', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.movies.index', compact('movies', 'q'));
    }

    public function create(): View
    {
        $ratings = ContentRating::orderBy('min_age')->get();
        $movie = new Movie();

        return view('admin.movies.create', compact('movie', 'ratings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();

        Movie::create($data);

        return redirect()->route('admin.movies.index')->with('success', 'Đã tạo phim.');
    }

    public function edit(Movie $movie): View
    {
        $ratings = ContentRating::orderBy('min_age')->get();

        return view('admin.movies.edit', compact('movie', 'ratings'));
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $data = $this->validateData($request);

        $movie->update($data);

        return redirect()->route('admin.movies.index')->with('success', 'Đã cập nhật phim.');
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

    private function validateData(Request $request): array
    {
        return $request->validate([
            'content_rating_id' => ['nullable', 'integer', 'exists:content_ratings,id'],
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'release_date' => ['nullable', 'date'],
            'language_original' => ['nullable', 'string', 'max:32'],
            'synopsis' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'string', 'max:512'],
            'trailer_url' => ['nullable', 'string', 'max:512'],
            'censorship_license_no' => ['nullable', 'string', 'max:64'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);
    }
}
