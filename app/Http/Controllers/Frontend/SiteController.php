<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function cinema(): View
    {
        $cinema = current_cinema();
        abort_unless($cinema, 404);

        $cinema->load([
            'auditoriums' => fn ($query) => $query->where('is_active', 1)->orderBy('id'),
        ]);

        $screenTypeSummary = $cinema->auditoriums
            ->groupBy(fn ($auditorium) => strtoupper((string) ($auditorium->screen_type ?: 'STANDARD')))
            ->map(fn ($items) => $items->count())
            ->sortKeys()
            ->all();

        return view('frontend.site.cinema', [
            'cinema' => $cinema,
            'screenTypeSummary' => $screenTypeSummary,
        ]);
    }

    public function support(): View
    {
        $faqs = [
            [
                'question' => 'Làm sao để đặt vé nhanh tại FPL Cinema?',
                'answer' => 'Chọn phim, suất chiếu, ghế ngồi và hoàn tất thanh toán. Nếu đã có tài khoản thành viên, hệ thống sẽ tự điền bớt thông tin liên hệ và cộng điểm khi đơn ở trạng thái thanh toán thành công.',
            ],
            [
                'question' => 'Tôi có thể tra cứu booking đã đặt ở đâu?',
                'answer' => 'Bạn có thể tra cứu ở mục Tra cứu booking bằng mã booking và số điện thoại/email đã dùng khi đặt, hoặc đăng nhập tài khoản thành viên để xem toàn bộ lịch sử đơn.',
            ],
            [
                'question' => 'Điểm thành viên được tính như thế nào?',
                'answer' => 'Mặc định hệ thống đang áp dụng quy đổi 1 điểm cho mỗi 10.000đ thanh toán thành công. Điểm được cộng sau khi đơn được xác nhận thanh toán.',
            ],
            [
                'question' => 'Nếu thanh toán lỗi thì ghế có bị giữ mãi không?',
                'answer' => 'Không. Booking chờ thanh toán có thời hạn giữ ghế. Khi hết hạn hoặc bị hủy, ghế sẽ được nhả lại để khách khác có thể tiếp tục đặt.',
            ],
            [
                'question' => 'Chính sách hủy/hoàn tiền được xử lý thế nào?',
                'answer' => 'Admin có thể xử lý hủy booking và hoàn tiền theo đúng trạng thái payment/refund. Sau khi hoàn tiền thành công, điểm thành viên liên quan tới booking đó cũng được đồng bộ lại.',
            ],
        ];

        $mustHaveItems = [
            'Trang chủ có phim đang chiếu / sắp chiếu / lịch chiếu rõ ràng.',
            'Trang đặt vé có chọn ghế, giá vé, combo, tổng tiền và trạng thái ghế theo thời gian thực.',
            'Trang thanh toán với thông tin đơn, phương thức thanh toán, trạng thái booking rõ ràng.',
            'Trang đăng nhập / đăng ký / tài khoản thành viên và lịch sử đặt vé.',
            'Trang tra cứu booking cho khách chưa đăng nhập.',
            'Trang tin tức, ưu đãi, thông tin rạp và hỗ trợ khách hàng.',
        ];

        $shouldHaveItems = [
            'FAQ, chính sách đổi trả / hoàn tiền, hướng dẫn check-in.',
            'Hiển thị quyền lợi tích điểm ngay tại giao diện khách hàng.',
            'Tin tức / ưu đãi nổi bật trên trang chủ và trong tài khoản thành viên.',
            'Thông tin liên hệ, giờ hoạt động, địa chỉ rạp rõ ràng ở footer và trang riêng.',
        ];

        return view('frontend.site.support', [
            'faqs' => $faqs,
            'mustHaveItems' => $mustHaveItems,
            'shouldHaveItems' => $shouldHaveItems,
        ]);
    }

    public function bookingLookup(Request $request): View
    {
        $booking = null;
        $lookupError = null;
        $lookupCode = trim((string) $request->query('booking_code', ''));
        $lookupContact = trim((string) $request->query('contact', ''));

        if ($lookupCode !== '' || $lookupContact !== '') {
            $data = $request->validate([
                'booking_code' => ['required', 'string', 'max:32'],
                'contact' => ['nullable', 'string', 'max:255'],
            ]);

            $lookupCode = strtoupper(trim((string) $data['booking_code']));
            $lookupContact = trim((string) ($data['contact'] ?? ''));

            $query = Booking::query()
                ->with([
                    'customer.loyaltyAccount.tier',
                    'show.movieVersion.movie.contentRating',
                    'show.auditorium.cinema',
                    'tickets.seat',
                    'bookingProducts.product',
                    'payments.refunds',
                ])
                ->where('booking_code', $lookupCode);

            if ($currentCinemaId = current_cinema_id()) {
                $query->where('cinema_id', $currentCinemaId);
            }

            $booking = $query->first();

            if (! $booking) {
                $lookupError = 'Không tìm thấy booking phù hợp với mã bạn đã nhập.';
            } else {
                $isAuthorized = false;
                $member = member_customer();

                if ($member && (int) $booking->customer_id === (int) $member->id) {
                    $isAuthorized = true;
                }

                if (! $isAuthorized && $lookupContact !== '') {
                    $normalizedContact = mb_strtolower($lookupContact);
                    $isAuthorized = $normalizedContact === mb_strtolower((string) ($booking->contact_phone ?? ''))
                        || $normalizedContact === mb_strtolower((string) ($booking->contact_email ?? ''));
                }

                if (! $isAuthorized) {
                    $booking = null;
                    $lookupError = 'Để xem booking này, hãy nhập đúng số điện thoại hoặc email đã dùng khi đặt vé. Nếu bạn là thành viên sở hữu booking, chỉ cần đăng nhập tài khoản.';
                }
            }
        }

        return view('frontend.site.lookup', [
            'booking' => $booking,
            'lookupCode' => $lookupCode,
            'lookupContact' => $lookupContact,
            'lookupError' => $lookupError,
        ]);
    }
}
