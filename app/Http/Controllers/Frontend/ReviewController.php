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


<div class="box">
    <input type="text" id="voucherInput" placeholder="Nhập mã (VD: GIAM50)">
    <button onclick="applyVoucher()">Áp dụng</button>

    <p id="message"></p>

    <div class="summary" id="summary">
        <p>Giá vé: 120,000 VNĐ</p>
        <p>Giảm: 0 VNĐ</p>
        <p><strong>Tổng: 120,000 VNĐ</strong></p>
    </div>
</div>

<script>
    let ticketPrice = 120000;
    let discount = 0;

    const vouchers = {
        "GIAM50": 0.5,     // giảm 50%
        "SALE10": 0.1,     // giảm 10%
        "VIP100K": 100000  // giảm 100k
    };

    function applyVoucher() {
        const code = document.getElementById("voucherInput").value.toUpperCase();
        const message = document.getElementById("message");

        if (!vouchers[code]) {
            message.innerText = "❌ Mã không hợp lệ";
            message.className = "error";
            discount = 0;
        } else {
            let value = vouchers[code];

            if (value < 1) {
                discount = ticketPrice * value;
            } else {
                discount = value;
            }

            message.innerText = "✅ Áp dụng thành công!";
            message.className = "success";
        }

        updateSummary();
    }

    function updateSummary() {
        let finalPrice = ticketPrice - discount;
        if (finalPrice < 0) finalPrice = 0;

        document.getElementById("summary").innerHTML = `
            <p>Giá vé: ${ticketPrice.toLocaleString()} VNĐ</p>
            <p>Giảm: ${discount.toLocaleString()} VNĐ</p>
            <p><strong>Tổng: ${finalPrice.toLocaleString()} VNĐ</strong></p>
        `;
    }
}
