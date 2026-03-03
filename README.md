# Cinema Demo (Laravel 12 + MySQL) — Overlay code

> Bạn sẽ tạo **một project Laravel 12 mới**, rồi **copy toàn bộ thư mục overlay này đè vào** để có:
- Router frontend/admin
- Admin login/logout (session)
- Middleware kiểm tra đăng nhập
- CSRF (Laravel web middleware, form có @csrf)
- Helper: `url_to()`, `redirect_to()`
- Category CRUD (mapped vào bảng `genres` + `is_active`)
- Movie CRUD (poster upload, slug, active/inactive)
- Join query phim theo danh mục (pivot `movie_genres`)
- Lấy suất chiếu theo phim + tạo booking cơ bản
- File SQL import chạy 1 lần (schema + seed demo)

## 1) Tạo project Laravel 12

Mở Terminal trong Laragon (hoặc CMD/Powershell):

```bash
cd C:\laragon\www
composer create-project laravel/laravel cinema
cd cinema
```

## 2) Copy overlay vào project

Giải nén file zip này, sau đó copy **tất cả nội dung** trong `cinema_laravel12_overlay/` vào thư mục project `C:\laragon\www\cinema\` (cho phép overwrite các file trùng tên).

## 3) Cấu hình .env

Mở file `.env` và set MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cinema_demo
DB_USERNAME=root
DB_PASSWORD=
```

## 4) Tạo DB + Import SQL (chạy 1 lần)

- Tạo database `cinema_demo` trong phpMyAdmin / HeidiSQL.
- Import file: `database/sql/cinema_app.sql`

> Lưu ý: file này có `ALTER TABLE ...` nên **hãy import trên DB trống**.

## 5) Chạy project

```bash
php artisan key:generate
php artisan storage:link
php artisan serve
```

Mở:
- Frontend: http://127.0.0.1:8000/
- Admin login: http://127.0.0.1:8000/admin/login

### Tài khoản admin demo
- Email: `admin@local.test`
- Password: `admin123`

## 6) Test checklist theo đúng yêu cầu

### Core + Auth
- `/admin/login` đăng nhập được
- `/admin/logout` đăng xuất được
- Vào `/admin/categories` hoặc `/admin/movies` khi chưa login sẽ bị redirect về login
- CSRF: form POST có `@csrf` (Laravel web middleware)

### Movies & Categories
- CRUD Category (table `genres`, có `is_active`) chạy OK
- CRUD Movie: upload poster (lưu trong `storage/app/public/posters`), slug unique, active/inactive
- Trang chủ `/` hiển thị phim ACTIVE + danh mục
- Click danh mục để lọc phim theo danh mục

### Booking logic
- Vào trang phim → `Xem suất chiếu`
- Chọn showtime + qty → tạo booking (PENDING) + booking_tickets (RESERVED)
- Hệ thống tự chọn ghế trống đầu tiên.

---

## Ghi chú kỹ thuật
- “Category” được ánh xạ vào bảng `genres` (vì schema mẫu dùng `genres` + pivot `movie_genres`).
- “Movie is_active” được map sang cột `status` (ACTIVE/INACTIVE) trong bảng `movies`.

## (Tuỳ chọn) Seed bằng Artisan

Nếu bạn **không muốn import seed trong SQL**, bạn có thể chỉ import phần schema (tự tách file), rồi chạy:

```bash
php artisan db:seed --class=DemoSeeder
```

(Trong gói đã có `database/seeders/DemoSeeder.php`.)
