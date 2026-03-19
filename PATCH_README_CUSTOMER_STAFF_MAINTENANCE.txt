Bản cập nhật này thêm 3 module admin:
1. Khách hàng
2. Nhân sự / ca làm
3. Thiết bị / bảo trì

Cách áp dụng:
- Copy đè code vào project Laravel hiện tại.
- Chạy php artisan migrate
  hoặc import file database/sql/customer_staff_maintenance_patch.sql
- Đăng nhập admin và kiểm tra menu mới:
  + Khách hàng
  + Nhân sự
  + Ca làm
  + Thiết bị
  + Bảo trì

Module khách hàng:
- Danh sách khách hàng
- Tra cứu theo số điện thoại / email / mã booking
- Lịch sử đặt vé
- Lịch sử thanh toán / hoàn tiền
- Điểm thành viên
- Trạng thái tài khoản

Module nhân sự:
- CRUD nhân sự
- Gán vai trò admin / manager / quầy vé / soát vé / F&B / kỹ thuật
- Tạo ca làm theo ngày
- Phân công nhân sự cho ca

Module thiết bị / bảo trì:
- CRUD thiết bị cho rạp / phòng
- Loại thiết bị: máy chiếu, âm thanh, điều hoà, màn chiếu, ánh sáng, khác
- Tạo yêu cầu bảo trì
- Theo dõi trạng thái và lịch sử sửa chữa
