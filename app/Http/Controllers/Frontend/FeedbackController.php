<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request, string $booking_code): RedirectResponse
    {
        $booking = Booking::query()
            ->with(['show.movieVersion.movie', 'customer'])
            ->where('booking_code', $booking_code)
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        if (! in_array((string) $booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true)) {
            return back()->with('error', 'Chỉ booking đã thanh toán mới có thể gửi đánh giá.');
        }

        $data = $request->validate([
            'movie_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'movie_comment' => ['nullable', 'string', 'max:2000'],
            'food_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'food_comment' => ['nullable', 'string', 'max:2000'],
            'facility_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facility_comment' => ['nullable', 'string', 'max:2000'],
            'staff_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'staff_comment' => ['nullable', 'string', 'max:2000'],
            'overall_comment' => ['nullable', 'string', 'max:3000'],
        ]);

        $hasAtLeastOneRating = collect([
            $data['movie_rating'] ?? null,
            $data['food_rating'] ?? null,
            $data['facility_rating'] ?? null,
            $data['staff_rating'] ?? null,
        ])->filter(fn ($value) => $value !== null)->isNotEmpty();

        if (! $hasAtLeastOneRating) {
            return back()->with('error', 'Bạn cần chọn ít nhất một hạng mục đánh giá từ 1 đến 5 sao.');
        }

        CustomerFeedback::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            array_merge($data, [
                'booking_id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'movie_id' => $booking->show?->movieVersion?->movie_id,
                'show_id' => $booking->show_id,
                'reviewer_name' => $booking->contact_name,
                'reviewer_email' => $booking->contact_email,
                'status' => 'PUBLISHED',
            ])
        );

        return back()->with('success', 'Cảm ơn bạn! Đánh giá của bạn đã được ghi nhận.');
    }
}
