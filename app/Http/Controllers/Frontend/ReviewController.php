<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Movie $movie): RedirectResponse
    {
        abort_if($movie->status !== 'ACTIVE', 404);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1500'],
        ]);

        Review::create($data + [
            'movie_id' => $movie->id,
            'is_approved' => true,
        ]);

        return redirect()
            ->route('movies.showtimes', $movie)
            ->with('success', 'Cảm ơn bạn đã gửi đánh giá cho bộ phim.');
    }
}
