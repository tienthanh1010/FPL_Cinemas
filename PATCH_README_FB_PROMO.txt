BẢN CẬP NHẬT F&B + KHUYẾN MÃI / VOUCHER

1. Copy đè toàn bộ source vào project Laravel của bạn.
2. Chạy migration:
   php artisan migrate

   Hoặc import SQL:
   database/sql/fb_promo_patch.sql

3. Đăng nhập admin và kiểm tra các menu mới:
   - Combo bắp nước
   - Tồn kho F&B
   - Khuyến mãi
   - Voucher

4. Luồng frontend:
   - Màn hình chọn suất đã có thêm chọn combo
   - Có ô nhập voucher
   - Khuyến mãi auto rule sẽ tự áp dụng nếu đủ điều kiện
   - Bán combo sẽ tự tạo booking_products và trừ tồn kho ở KIOSK1
