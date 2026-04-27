<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerFeedback;
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

        $serviceSummary = CustomerFeedback::query()
            ->published()
            ->selectRaw('COUNT(*) as total_reviews, AVG(food_rating) as avg_food_rating, AVG(facility_rating) as avg_facility_rating, AVG(staff_rating) as avg_staff_rating')
            ->first();

        $recentFeedbacks = CustomerFeedback::query()
            ->published()
            ->where(function ($query) {
                $query->whereNotNull('overall_comment')
                    ->orWhereNotNull('food_comment')
                    ->orWhereNotNull('facility_comment')
                    ->orWhereNotNull('staff_comment');
            })
            ->latest('id')
            ->limit(6)
            ->get([
                'reviewer_name',
                'food_rating',
                'food_comment',
                'facility_rating',
                'facility_comment',
                'staff_rating',
                'staff_comment',
                'overall_comment',
                'created_at',
            ]);

        return view('frontend.site.cinema', [
            'cinema' => $cinema,
            'screenTypeSummary' => $screenTypeSummary,
            'serviceSummary' => $serviceSummary,
            'recentFeedbacks' => $recentFeedbacks,
        ]);
    }

    public function support(): View
    {
        $faqs = [
            [
                'question' => 'Làm sao để đặt vé nhanh tại FPL Cinema?',
                'answer' => 'Chọn phim, suất chiếu, ghế ngồi, gán loại vé cho từng ghế và hoàn tất thanh toán chuyển khoản QR. Nếu đã có tài khoản thành viên, hệ thống sẽ tự điền bớt thông tin liên hệ và cộng điểm sau khi booking được xác nhận thanh toán.',
            ],
            [
                'question' => 'Tôi có thể tra cứu booking đã đặt ở đâu?',
                'answer' => 'Bạn có thể tra cứu ở mục Tra cứu booking bằng mã booking và số điện thoại/email đã dùng khi đặt, hoặc đăng nhập tài khoản thành viên để xem toàn bộ lịch sử đơn.',
            ],
            [
                'question' => 'Ghế có bị giữ mãi nếu tôi chọn rồi không thanh toán?',
                'answer' => 'Không. Hệ thống giữ ghế theo thời gian thực nhưng booking chờ thanh toán chỉ có hiệu lực trong 2 phút. Hết thời gian này, ghế sẽ tự động nhả để khách khác tiếp tục đặt.',
            ],
            [
                'question' => 'Phim T18 có dùng vé trẻ em được không?',
                'answer' => 'Không. Với phim có phân loại T18, website tự động ẩn và chặn loại vé trẻ em ở bước đặt ghế để đúng quy định độ tuổi.',
            ],
            [
                'question' => 'Website có hỗ trợ hoàn tiền online không?',
                'answer' => 'Hiện website không hỗ trợ tính năng hoàn tiền online. Khách hàng cần kiểm tra kỹ thông tin booking trước khi xác nhận thanh toán.',
            ],
        ];

        $mustHaveItems = [
            'Trang chủ có phim đang chiếu / sắp chiếu / lịch chiếu rõ ràng.',
            'Trang đặt vé có chọn ghế, gán loại vé theo từng ghế, combo riêng, tổng tiền và trạng thái ghế theo thời gian thực.',
            'Trang thanh toán với mã QR chuyển khoản MB Bank, thời gian giữ ghế và trạng thái booking rõ ràng.',
            'Trang đăng nhập / đăng ký / tài khoản thành viên và lịch sử đặt vé.',
            'Trang tra cứu booking cho khách chưa đăng nhập.',
            'Trang tin tức, ưu đãi, thông tin rạp và hỗ trợ khách hàng.',
        ];

        $shouldHaveItems = [
            'FAQ, hướng dẫn check-in bằng QR / vé cứng và đánh giá sau khi xem phim.',
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
