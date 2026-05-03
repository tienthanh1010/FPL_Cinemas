-- Cập nhật thông tin liên hệ cho mô hình một rạp FPL Cinema.
-- Dùng file này nếu bạn muốn cập nhật thủ công bằng MySQL thay vì chạy php artisan migrate.

UPDATE cinemas
SET phone = '0393312307',
    email = 'kientr2307@gmail.com',
    updated_at = NOW()
WHERE name = 'FPL Cinema' OR id = 1;

UPDATE cinema_chains
SET hotline = '0393312307',
    email = 'kientr2307@gmail.com',
    updated_at = NOW()
WHERE name = 'FPL Cinema' OR id = 1;
