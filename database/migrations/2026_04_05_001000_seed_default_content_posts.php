<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_posts')) {
            return;
        }

        if (DB::table('content_posts')->count() > 0) {
            return;
        }

        $now = now();

        DB::table('content_posts')->insert([
            [
                'type' => 'NEWS',
                'title' => 'FPL Cinema chính thức vận hành theo mô hình một rạp duy nhất',
                'slug' => 'fpl-cinema-mot-rap-duy-nhat',
                'excerpt' => 'Hệ thống đặt vé, lịch chiếu, chăm sóc thành viên và vận hành admin đã được chuẩn hóa theo mô hình một rạp duy nhất: FPL Cinema.',
                'content' => "FPL Cinema đã hoàn tất việc tinh gọn toàn bộ hệ thống về mô hình một rạp duy nhất.\n\nĐiều này giúp trải nghiệm người dùng mạch lạc hơn: khách hàng chỉ cần tập trung vào một địa điểm, lịch chiếu rõ ràng hơn, luồng booking - thanh toán - tích điểm cũng được đồng bộ chặt chẽ hơn.",
                'cover_image_url' => null,
                'badge_label' => 'Thông báo hệ thống',
                'status' => 'PUBLISHED',
                'is_featured' => 1,
                'published_at' => $now,
                'starts_at' => null,
                'ends_at' => null,
                'sort_order' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'NEWS',
                'title' => 'Tài khoản thành viên nay có thể theo dõi lịch sử đặt vé và điểm thưởng',
                'slug' => 'tai-khoan-thanh-vien-theo-doi-lich-su-dat-ve',
                'excerpt' => 'Người dùng đã đăng ký tài khoản có thể xem lại booking, số điểm hiện có và tổng chi tiêu ngay trong trang tài khoản.',
                'content' => "Trang tài khoản thành viên mới giúp khách hàng theo dõi các booking đã đặt, trạng thái thanh toán, số điểm đang có và hạng thành viên.\n\nĐiểm thưởng được đồng bộ sau mỗi booking thanh toán thành công.",
                'cover_image_url' => null,
                'badge_label' => 'Thành viên',
                'status' => 'PUBLISHED',
                'is_featured' => 0,
                'published_at' => $now->copy()->subDay(),
                'starts_at' => null,
                'ends_at' => null,
                'sort_order' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'OFFER',
                'title' => 'Ưu đãi thành viên: tích điểm cho mọi booking thanh toán thành công',
                'slug' => 'uu-dai-thanh-vien-tich-diem-booking',
                'excerpt' => 'Mặc định hệ thống áp dụng quy đổi 1 điểm cho mỗi 10.000đ thanh toán thành công tại FPL Cinema.',
                'content' => "Khi bạn đặt vé và thanh toán thành công, hệ thống sẽ tự động cộng điểm vào tài khoản thành viên.\n\nTỷ lệ mặc định hiện tại: 1 điểm / 10.000đ. Admin có thể điều chỉnh tỷ lệ này trong file cấu hình môi trường.",
                'cover_image_url' => null,
                'badge_label' => 'Tích điểm',
                'status' => 'PUBLISHED',
                'is_featured' => 1,
                'published_at' => $now,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMonths(6),
                'sort_order' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'OFFER',
                'title' => 'Mua vé online nhanh hơn với tra cứu booking và thanh toán tiếp tục',
                'slug' => 'tra-cuu-booking-va-thanh-toan-tiep-tuc',
                'excerpt' => 'Khách hàng có thể tra cứu booking bằng mã đơn và tiếp tục thanh toán nếu booking còn hiệu lực.',
                'content' => "FPL Cinema đã bổ sung tính năng tra cứu booking ngay trên giao diện người dùng.\n\nNếu booking đang chờ thanh toán và vẫn còn hiệu lực, khách hàng có thể quay lại thanh toán tiếp mà không cần thao tác lại toàn bộ quy trình.",
                'cover_image_url' => null,
                'badge_label' => 'Trải nghiệm mới',
                'status' => 'PUBLISHED',
                'is_featured' => 0,
                'published_at' => $now->copy()->subHours(12),
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMonths(6),
                'sort_order' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('content_posts')) {
            return;
        }

        DB::table('content_posts')
            ->whereIn('slug', [
                'fpl-cinema-mot-rap-duy-nhat',
                'tai-khoan-thanh-vien-theo-doi-lich-su-dat-ve',
                'uu-dai-thanh-vien-tich-diem-booking',
                'tra-cuu-booking-va-thanh-toan-tiep-tuc',
            ])
            ->delete();
    }
};
