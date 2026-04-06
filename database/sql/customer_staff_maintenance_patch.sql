ALTER TABLE customers
    ADD COLUMN IF NOT EXISTS account_status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE' AFTER city;

INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'ADMIN', 'Admin', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'ADMIN');
INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'MANAGER', 'Quản lý rạp', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'MANAGER');
INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'TICKET_COUNTER', 'Nhân viên quầy vé', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'TICKET_COUNTER');
INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'TICKET_CHECKIN', 'Nhân viên soát vé', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'TICKET_CHECKIN');
INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'FNB', 'Nhân viên bắp nước', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'FNB');
INSERT INTO roles (code, name, created_at, updated_at)
SELECT 'TECHNICIAN', 'Kỹ thuật / bảo trì', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code = 'TECHNICIAN');
