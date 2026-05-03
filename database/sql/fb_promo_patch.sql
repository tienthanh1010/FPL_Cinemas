ALTER TABLE promotions
    ADD COLUMN IF NOT EXISTS day_of_week TINYINT UNSIGNED NULL AFTER status,
    ADD COLUMN IF NOT EXISTS show_start_from TIME NULL AFTER day_of_week,
    ADD COLUMN IF NOT EXISTS show_start_to TIME NULL AFTER show_start_from,
    ADD COLUMN IF NOT EXISTS customer_scope VARCHAR(16) NULL AFTER show_start_to,
    ADD COLUMN IF NOT EXISTS auto_apply TINYINT(1) NOT NULL DEFAULT 0 AFTER customer_scope,
    ADD COLUMN IF NOT EXISTS coupon_required TINYINT(1) NOT NULL DEFAULT 0 AFTER auto_apply;

INSERT IGNORE INTO product_categories(code, name) VALUES
('POPCORN', 'Bắp nước'),
('COMBO', 'Combo'),
('SNACK', 'Snack');

INSERT IGNORE INTO stock_locations(cinema_id, code, name, location_type, is_active)
SELECT id, 'KIOSK1', 'Quầy F&B chính', 'KIOSK', 1 FROM cinemas;
