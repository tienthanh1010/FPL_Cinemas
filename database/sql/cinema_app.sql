-- =========================================================
-- Cinema Chain Management Database (Vietnam) - MySQL 8.0+
-- Target: Laravel 12 + Laragon + MySQL
-- Author: ChatGPT
-- Notes:
--   - All money amounts are stored as BIGINT in VND (minor units). Example: 120000 = 120.000đ
--   - Show times are stored as DATETIME in local time (Asia/Ho_Chi_Minh).
--   - Engine: InnoDB, Charset: utf8mb4
--   - Concurrency:
--       seat_holds.active_lock and booking_tickets.active_lock are GENERATED columns (indexed)
--       so only one active lock per (show, seat) at a time.
--       Expire holds by setting status='EXPIRED' when expires_at < NOW().
-- =========================================================

/*!40101 SET NAMES utf8mb4 */;
/*!40101 SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */;

-- Optional (uncomment if you want to create a dedicated database)
-- CREATE DATABASE IF NOT EXISTS cinema_vn
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;
-- USE cinema_vn;

-- =========================================================
-- 01) Master: Chains, Cinemas, Auditoriums, Seats
-- =========================================================

CREATE TABLE IF NOT EXISTS cinema_chains (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  chain_code VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  legal_name VARCHAR(255) NULL,
  tax_code VARCHAR(32) NULL,
  hotline VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  website VARCHAR(255) NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_chain_public_id (public_id),
  UNIQUE KEY uq_chain_code (chain_code),
  CONSTRAINT chk_chain_status CHECK (status IN ('ACTIVE','INACTIVE'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cinemas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  chain_id BIGINT UNSIGNED NOT NULL,
  cinema_code VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Ho_Chi_Minh',
  address_line VARCHAR(255) NULL,
  ward VARCHAR(128) NULL,
  district VARCHAR(128) NULL,
  province VARCHAR(128) NULL,
  country_code CHAR(2) NOT NULL DEFAULT 'VN',
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  opening_hours JSON NULL, -- e.g. {"mon":"09:00-23:00",...}
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cinema_public_id (public_id),
  UNIQUE KEY uq_cinema_code (cinema_code),
  KEY idx_cinema_chain (chain_id),
  KEY idx_cinema_location (province, district),
  CONSTRAINT fk_cinema_chain FOREIGN KEY (chain_id) REFERENCES cinema_chains(id),
  CONSTRAINT chk_cinema_status CHECK (status IN ('ACTIVE','INACTIVE'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoriums (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  cinema_id BIGINT UNSIGNED NOT NULL,
  auditorium_code VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  screen_type VARCHAR(32) NOT NULL DEFAULT 'STANDARD', -- STANDARD / IMAX / DOLBY / 4DX
  seat_map_version INT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_auditorium_public_id (public_id),
  UNIQUE KEY uq_auditorium_code_per_cinema (cinema_id, auditorium_code),
  KEY idx_auditorium_cinema (cinema_id),
  CONSTRAINT fk_auditorium_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_auditorium_screen CHECK (screen_type IN ('STANDARD','IMAX','DOLBY','4DX','SCREENX','GOLDCLASS'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seat_types (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- REGULAR / VIP / COUPLE / SWEETBOX ...
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seat_type_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seats (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  auditorium_id BIGINT UNSIGNED NOT NULL,
  seat_type_id BIGINT UNSIGNED NOT NULL,
  seat_code VARCHAR(16) NOT NULL,     -- e.g. A01
  row_label VARCHAR(8) NOT NULL,      -- e.g. A
  col_number INT UNSIGNED NOT NULL,   -- e.g. 1
  x INT UNSIGNED NULL,                -- optional for seat map UI
  y INT UNSIGNED NULL,                -- optional for seat map UI
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seat_code_per_auditorium (auditorium_id, seat_code),
  KEY idx_seat_auditorium (auditorium_id),
  KEY idx_seat_type (seat_type_id),
  CONSTRAINT fk_seat_auditorium FOREIGN KEY (auditorium_id) REFERENCES auditoriums(id),
  CONSTRAINT fk_seat_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seat blocks: lock seats (broken/maintenance/VIP reserved) for a time range
CREATE TABLE IF NOT EXISTS seat_blocks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  auditorium_id BIGINT UNSIGNED NOT NULL,
  seat_id BIGINT UNSIGNED NULL, -- null => block whole auditorium (rare)
  reason VARCHAR(255) NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_seat_block_auditorium (auditorium_id, start_at, end_at),
  KEY idx_seat_block_seat (seat_id, start_at, end_at),
  CONSTRAINT fk_seat_block_auditorium FOREIGN KEY (auditorium_id) REFERENCES auditoriums(id),
  CONSTRAINT fk_seat_block_seat FOREIGN KEY (seat_id) REFERENCES seats(id),
  CONSTRAINT chk_seat_block_time CHECK (end_at > start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 02) Content: Movies, Genres, Ratings, People, Versions
-- =========================================================

CREATE TABLE IF NOT EXISTS content_ratings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(8) NOT NULL, -- VN: P, K, T13, T16, T18, C
  name VARCHAR(64) NOT NULL,
  min_age INT UNSIGNED NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rating_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  content_rating_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  original_title VARCHAR(255) NULL,
  duration_minutes INT UNSIGNED NOT NULL,
  release_date DATE NULL,
  language_original VARCHAR(32) NULL,
  synopsis TEXT NULL,
  poster_url VARCHAR(512) NULL,
  trailer_url VARCHAR(512) NULL,
  censorship_license_no VARCHAR(64) NULL, -- optional: giấy phép phổ biến
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE', -- ACTIVE/INACTIVE
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_movie_public_id (public_id),
  KEY idx_movie_title (title),
  KEY idx_movie_release (release_date),
  KEY idx_movie_rating (content_rating_id),
  CONSTRAINT fk_movie_rating FOREIGN KEY (content_rating_id) REFERENCES content_ratings(id),
  CONSTRAINT chk_movie_status CHECK (status IN ('ACTIVE','INACTIVE'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS genres (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_genre_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movie_genres (
  movie_id BIGINT UNSIGNED NOT NULL,
  genre_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (movie_id, genre_id),
  CONSTRAINT fk_movie_genre_movie FOREIGN KEY (movie_id) REFERENCES movies(id),
  CONSTRAINT fk_movie_genre_genre FOREIGN KEY (genre_id) REFERENCES genres(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS people (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  dob DATE NULL,
  country_code CHAR(2) NULL,
  bio TEXT NULL,
  avatar_url VARCHAR(512) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_person_public_id (public_id),
  KEY idx_person_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- cast/crew relationship
CREATE TABLE IF NOT EXISTS movie_people (
  movie_id BIGINT UNSIGNED NOT NULL,
  person_id BIGINT UNSIGNED NOT NULL,
  role_type VARCHAR(32) NOT NULL, -- ACTOR/DIRECTOR/PRODUCER/WRITER...
  character_name VARCHAR(255) NULL, -- for actors
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (movie_id, person_id, role_type),
  KEY idx_movie_people_person (person_id),
  CONSTRAINT fk_movie_people_movie FOREIGN KEY (movie_id) REFERENCES movies(id),
  CONSTRAINT fk_movie_people_person FOREIGN KEY (person_id) REFERENCES people(id),
  CONSTRAINT chk_movie_people_role CHECK (role_type IN ('ACTOR','DIRECTOR','PRODUCER','WRITER','COMPOSER','CINEMATOGRAPHER','EDITOR','OTHER'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movie versions: 2D/3D/IMAX + audio/subtitle
CREATE TABLE IF NOT EXISTS movie_versions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  movie_id BIGINT UNSIGNED NOT NULL,
  format VARCHAR(16) NOT NULL DEFAULT '2D', -- 2D/3D/IMAX/4DX/SCREENX
  audio_language VARCHAR(32) NOT NULL DEFAULT 'VI', -- VI/EN/KO/JA...
  subtitle_language VARCHAR(32) NULL,               -- VI/EN...
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_movie_version_unique (movie_id, format, audio_language, subtitle_language),
  KEY idx_movie_version_movie (movie_id),
  CONSTRAINT fk_movie_version_movie FOREIGN KEY (movie_id) REFERENCES movies(id),
  CONSTRAINT chk_movie_format CHECK (format IN ('2D','3D','IMAX','4DX','SCREENX','DOLBY'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 03) Scheduling: Shows + Status History
-- =========================================================

CREATE TABLE IF NOT EXISTS shows (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  auditorium_id BIGINT UNSIGNED NOT NULL,
  movie_version_id BIGINT UNSIGNED NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  on_sale_from DATETIME NULL,
  on_sale_until DATETIME NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'SCHEDULED',
  created_by BIGINT UNSIGNED NULL, -- optional: staff_id / user_id in your auth system
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_show_public_id (public_id),
  KEY idx_show_time (start_time, end_time),
  KEY idx_show_auditorium_time (auditorium_id, start_time),
  KEY idx_show_movie_time (movie_version_id, start_time),
  CONSTRAINT fk_show_auditorium FOREIGN KEY (auditorium_id) REFERENCES auditoriums(id),
  CONSTRAINT fk_show_movie_version FOREIGN KEY (movie_version_id) REFERENCES movie_versions(id),
  CONSTRAINT chk_show_time CHECK (end_time > start_time),
  CONSTRAINT chk_show_status CHECK (status IN ('SCHEDULED','ON_SALE','SOLD_OUT','CANCELLED','ENDED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS show_status_histories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  show_id BIGINT UNSIGNED NOT NULL,
  from_status VARCHAR(16) NULL,
  to_status VARCHAR(16) NOT NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  changed_by BIGINT UNSIGNED NULL,
  note VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_show_status_history_show (show_id, changed_at),
  CONSTRAINT fk_show_status_history_show FOREIGN KEY (show_id) REFERENCES shows(id),
  CONSTRAINT chk_show_status_history CHECK (to_status IN ('SCHEDULED','ON_SALE','SOLD_OUT','CANCELLED','ENDED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 04) Pricing: Ticket Types + Show Prices + (Optional) Pricing Profiles
-- =========================================================

CREATE TABLE IF NOT EXISTS ticket_types (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- ADULT / STUDENT / CHILD / SENIOR / MEMBER ...
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ticket_type_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Snapshot prices per show (recommended)
CREATE TABLE IF NOT EXISTS show_prices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  show_id BIGINT UNSIGNED NOT NULL,
  seat_type_id BIGINT UNSIGNED NOT NULL,
  ticket_type_id BIGINT UNSIGNED NOT NULL,
  price_amount BIGINT UNSIGNED NOT NULL, -- VND
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_show_price_unique (show_id, seat_type_id, ticket_type_id),
  KEY idx_show_price_show (show_id),
  KEY idx_show_price_seat_type (seat_type_id),
  KEY idx_show_price_ticket_type (ticket_type_id),
  CONSTRAINT fk_show_price_show FOREIGN KEY (show_id) REFERENCES shows(id),
  CONSTRAINT fk_show_price_seat_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id),
  CONSTRAINT fk_show_price_ticket_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Pricing profile & rules (for auto-generating show_prices)
CREATE TABLE IF NOT EXISTS pricing_profiles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cinema_id BIGINT UNSIGNED NULL, -- null => global
  code VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pricing_profile_code (code),
  KEY idx_pricing_profile_cinema (cinema_id),
  CONSTRAINT fk_pricing_profile_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pricing_rules (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pricing_profile_id BIGINT UNSIGNED NOT NULL,
  day_of_week TINYINT UNSIGNED NULL, -- 1=Mon .. 7=Sun (Laravel: isoWeekday)
  start_time TIME NULL,              -- time window inside a day
  end_time TIME NULL,
  seat_type_id BIGINT UNSIGNED NOT NULL,
  ticket_type_id BIGINT UNSIGNED NOT NULL,
  price_amount BIGINT UNSIGNED NOT NULL,
  priority INT UNSIGNED NOT NULL DEFAULT 100, -- smaller = higher priority
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pricing_rule_profile (pricing_profile_id, is_active, priority),
  KEY idx_pricing_rule_dow (day_of_week),
  CONSTRAINT fk_pricing_rule_profile FOREIGN KEY (pricing_profile_id) REFERENCES pricing_profiles(id),
  CONSTRAINT fk_pricing_rule_seat_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id),
  CONSTRAINT fk_pricing_rule_ticket_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id),
  CONSTRAINT chk_pricing_rule_dow CHECK (day_of_week IS NULL OR day_of_week BETWEEN 1 AND 7),
  CONSTRAINT chk_pricing_rule_time CHECK (
    (start_time IS NULL AND end_time IS NULL) OR (end_time > start_time)
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 05) Customers + Loyalty
-- =========================================================

CREATE TABLE IF NOT EXISTS customers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  dob DATE NULL,
  gender VARCHAR(16) NULL,
  city VARCHAR(128) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_customer_public_id (public_id),
  UNIQUE KEY uq_customer_phone (phone),
  UNIQUE KEY uq_customer_email (email),
  KEY idx_customer_name (full_name),
  CONSTRAINT chk_customer_gender CHECK (gender IS NULL OR gender IN ('MALE','FEMALE','OTHER'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_tiers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- SILVER/GOLD/PLATINUM...
  name VARCHAR(255) NOT NULL,
  min_points BIGINT UNSIGNED NOT NULL DEFAULT 0,
  benefits JSON NULL, -- e.g. {"ticket_discount_percent":5}
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_loyalty_tier_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_accounts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id BIGINT UNSIGNED NOT NULL,
  tier_id BIGINT UNSIGNED NULL,
  points_balance BIGINT NOT NULL DEFAULT 0,
  lifetime_points BIGINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_loyalty_account_customer (customer_id),
  KEY idx_loyalty_account_tier (tier_id),
  CONSTRAINT fk_loyalty_account_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_loyalty_account_tier FOREIGN KEY (tier_id) REFERENCES loyalty_tiers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS loyalty_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  loyalty_account_id BIGINT UNSIGNED NOT NULL,
  txn_type VARCHAR(16) NOT NULL, -- EARN/REDEEM/ADJUST
  points BIGINT NOT NULL,
  reference_type VARCHAR(32) NULL, -- BOOKING/PROMO/ADMIN...
  reference_id BIGINT UNSIGNED NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_loyalty_txn_account (loyalty_account_id, created_at),
  CONSTRAINT fk_loyalty_txn_account FOREIGN KEY (loyalty_account_id) REFERENCES loyalty_accounts(id),
  CONSTRAINT chk_loyalty_txn_type CHECK (txn_type IN ('EARN','REDEEM','ADJUST'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 06) Sales: Channels, Bookings, Seat Holds, Tickets, Payments, Refunds
-- =========================================================

CREATE TABLE IF NOT EXISTS sales_channels (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- WEB/APP/COUNTER/PARTNER
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sales_channel_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  booking_code VARCHAR(32) NOT NULL, -- human friendly code
  show_id BIGINT UNSIGNED NOT NULL,
  cinema_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NULL,
  sales_channel_id BIGINT UNSIGNED NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'PENDING',
  contact_name VARCHAR(255) NULL,
  contact_phone VARCHAR(32) NULL,
  contact_email VARCHAR(255) NULL,
  subtotal_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  total_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  paid_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  notes VARCHAR(255) NULL,
  expires_at DATETIME NULL, -- for PENDING booking auto-expire
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_booking_public_id (public_id),
  UNIQUE KEY uq_booking_code (booking_code),
  KEY idx_booking_show (show_id),
  KEY idx_booking_cinema (cinema_id),
  KEY idx_booking_customer (customer_id),
  KEY idx_booking_status_created (status, created_at),
  CONSTRAINT fk_booking_show FOREIGN KEY (show_id) REFERENCES shows(id),
  CONSTRAINT fk_booking_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT fk_booking_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_booking_channel FOREIGN KEY (sales_channel_id) REFERENCES sales_channels(id),
  CONSTRAINT chk_booking_status CHECK (status IN ('PENDING','PAID','CANCELLED','REFUNDED','EXPIRED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seat holds prevent double-selection during checkout
CREATE TABLE IF NOT EXISTS seat_holds (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  show_id BIGINT UNSIGNED NOT NULL,
  seat_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NULL,
  hold_token CHAR(64) NOT NULL, -- random token for client session
  status VARCHAR(16) NOT NULL DEFAULT 'HELD',
  expires_at DATETIME NOT NULL,
  active_lock TINYINT GENERATED ALWAYS AS (
    CASE WHEN status IN ('HELD','CONFIRMED') THEN 1 ELSE NULL END
  ) STORED,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seat_hold_token (hold_token),
  UNIQUE KEY uq_seat_hold_active (show_id, seat_id, active_lock),
  KEY idx_seat_hold_show (show_id, expires_at),
  KEY idx_seat_hold_customer (customer_id),
  CONSTRAINT fk_seat_hold_show FOREIGN KEY (show_id) REFERENCES shows(id),
  CONSTRAINT fk_seat_hold_seat FOREIGN KEY (seat_id) REFERENCES seats(id),
  CONSTRAINT fk_seat_hold_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT chk_seat_hold_status CHECK (status IN ('HELD','CONFIRMED','EXPIRED','CANCELLED')),
  CONSTRAINT chk_seat_hold_expires CHECK (expires_at > '2000-01-01 00:00:00')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Booking tickets (line-items by seat)
CREATE TABLE IF NOT EXISTS booking_tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT UNSIGNED NOT NULL,
  show_id BIGINT UNSIGNED NOT NULL,
  seat_id BIGINT UNSIGNED NOT NULL,
  ticket_type_id BIGINT UNSIGNED NOT NULL,
  seat_type_id BIGINT UNSIGNED NOT NULL,
  unit_price_amount BIGINT UNSIGNED NOT NULL,
  discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  final_price_amount BIGINT UNSIGNED NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'RESERVED',
  active_lock TINYINT GENERATED ALWAYS AS (
    CASE WHEN status IN ('RESERVED','ISSUED') THEN 1 ELSE NULL END
  ) STORED,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_booking_ticket_active (show_id, seat_id, active_lock),
  KEY idx_booking_ticket_booking (booking_id),
  KEY idx_booking_ticket_show (show_id),
  KEY idx_booking_ticket_seat (seat_id),
  KEY idx_booking_ticket_type (ticket_type_id),
  CONSTRAINT fk_booking_ticket_booking FOREIGN KEY (booking_id) REFERENCES bookings(id),
  CONSTRAINT fk_booking_ticket_show FOREIGN KEY (show_id) REFERENCES shows(id),
  CONSTRAINT fk_booking_ticket_seat FOREIGN KEY (seat_id) REFERENCES seats(id),
  CONSTRAINT fk_booking_ticket_ticket_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id),
  CONSTRAINT fk_booking_ticket_seat_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id),
  CONSTRAINT chk_booking_ticket_status CHECK (status IN ('RESERVED','ISSUED','CANCELLED','REFUNDED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Issued tickets (QR/Barcode)
CREATE TABLE IF NOT EXISTS tickets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_ticket_id BIGINT UNSIGNED NOT NULL,
  ticket_code VARCHAR(64) NOT NULL,
  qr_payload VARCHAR(512) NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ISSUED',
  issued_at DATETIME NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ticket_code (ticket_code),
  UNIQUE KEY uq_ticket_booking_ticket (booking_ticket_id),
  KEY idx_ticket_status (status),
  CONSTRAINT fk_ticket_booking_ticket FOREIGN KEY (booking_ticket_id) REFERENCES booking_tickets(id),
  CONSTRAINT chk_ticket_status CHECK (status IN ('ISSUED','USED','VOID','REFUNDED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT UNSIGNED NOT NULL,
  provider VARCHAR(32) NOT NULL, -- MOMO/VNPAY/ZALOPAY/CASH/CARD
  method VARCHAR(32) NOT NULL,   -- EWALLET/BANK_TRANSFER/CARD/CASH
  status VARCHAR(16) NOT NULL DEFAULT 'INITIATED',
  amount BIGINT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  external_txn_ref VARCHAR(128) NULL,
  request_payload JSON NULL,
  response_payload JSON NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payment_booking (booking_id),
  KEY idx_payment_status_created (status, created_at),
  KEY idx_payment_external_ref (external_txn_ref),
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id) REFERENCES bookings(id),
  CONSTRAINT chk_payment_status CHECK (status IN ('INITIATED','AUTHORIZED','CAPTURED','FAILED','CANCELLED','REFUNDED')),
  CONSTRAINT chk_payment_method CHECK (method IN ('EWALLET','BANK_TRANSFER','CARD','CASH'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refunds (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  payment_id BIGINT UNSIGNED NOT NULL,
  amount BIGINT UNSIGNED NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'PENDING',
  reason VARCHAR(255) NULL,
  external_ref VARCHAR(128) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_refund_payment (payment_id),
  KEY idx_refund_status (status),
  CONSTRAINT fk_refund_payment FOREIGN KEY (payment_id) REFERENCES payments(id),
  CONSTRAINT chk_refund_status CHECK (status IN ('PENDING','SUCCESS','FAILED','CANCELLED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 07) Promotions & Coupons
-- =========================================================

CREATE TABLE IF NOT EXISTS promotions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NULL,
  promo_type VARCHAR(16) NOT NULL, -- PERCENT/FIXED
  discount_value BIGINT UNSIGNED NOT NULL, -- percent (0-100) if PERCENT else amount VND if FIXED
  max_discount_amount BIGINT UNSIGNED NULL,
  min_order_amount BIGINT UNSIGNED NULL,
  applies_to VARCHAR(16) NOT NULL DEFAULT 'ORDER', -- ORDER/TICKET/PRODUCT
  is_stackable TINYINT(1) NOT NULL DEFAULT 0,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  usage_limit_total INT UNSIGNED NULL,
  usage_limit_per_customer INT UNSIGNED NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_promo_code (code),
  KEY idx_promo_time (start_at, end_at),
  CONSTRAINT chk_promo_type CHECK (promo_type IN ('PERCENT','FIXED')),
  CONSTRAINT chk_promo_applies CHECK (applies_to IN ('ORDER','TICKET','PRODUCT')),
  CONSTRAINT chk_promo_status CHECK (status IN ('ACTIVE','INACTIVE')),
  CONSTRAINT chk_promo_time CHECK (end_at > start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_cinemas (
  promotion_id BIGINT UNSIGNED NOT NULL,
  cinema_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (promotion_id, cinema_id),
  CONSTRAINT fk_promo_cinema_promo FOREIGN KEY (promotion_id) REFERENCES promotions(id),
  CONSTRAINT fk_promo_cinema_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_movies (
  promotion_id BIGINT UNSIGNED NOT NULL,
  movie_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (promotion_id, movie_id),
  CONSTRAINT fk_promo_movie_promo FOREIGN KEY (promotion_id) REFERENCES promotions(id),
  CONSTRAINT fk_promo_movie_movie FOREIGN KEY (movie_id) REFERENCES movies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  promotion_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(64) NOT NULL,
  customer_id BIGINT UNSIGNED NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ISSUED',
  issued_at DATETIME NULL,
  redeemed_at DATETIME NULL,
  expires_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_coupon_code (code),
  KEY idx_coupon_promo (promotion_id),
  KEY idx_coupon_customer (customer_id),
  CONSTRAINT fk_coupon_promo FOREIGN KEY (promotion_id) REFERENCES promotions(id),
  CONSTRAINT fk_coupon_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT chk_coupon_status CHECK (status IN ('ISSUED','REDEEMED','EXPIRED','CANCELLED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applied discounts at booking level
CREATE TABLE IF NOT EXISTS booking_discounts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT UNSIGNED NOT NULL,
  promotion_id BIGINT UNSIGNED NULL,
  coupon_id BIGINT UNSIGNED NULL,
  applied_to VARCHAR(16) NOT NULL DEFAULT 'ORDER', -- ORDER/TICKET/PRODUCT
  discount_amount BIGINT UNSIGNED NOT NULL,
  metadata JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_booking_discount_booking (booking_id),
  CONSTRAINT fk_booking_discount_booking FOREIGN KEY (booking_id) REFERENCES bookings(id),
  CONSTRAINT fk_booking_discount_promo FOREIGN KEY (promotion_id) REFERENCES promotions(id),
  CONSTRAINT fk_booking_discount_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id),
  CONSTRAINT chk_booking_discount_applied CHECK (applied_to IN ('ORDER','TICKET','PRODUCT'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 08) Concessions: Products + Booking Products
-- =========================================================

CREATE TABLE IF NOT EXISTS product_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- POPCORN/DRINK/COMBO...
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_product_category_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  sku VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  unit VARCHAR(32) NOT NULL DEFAULT 'ITEM', -- ITEM/CUP/BOX...
  is_combo TINYINT(1) NOT NULL DEFAULT 0,
  attributes JSON NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_product_public_id (public_id),
  UNIQUE KEY uq_product_sku (sku),
  KEY idx_product_category (category_id),
  CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES product_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- price can be global (cinema_id null) or per cinema
CREATE TABLE IF NOT EXISTS product_prices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  cinema_id BIGINT UNSIGNED NULL,
  price_amount BIGINT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  effective_from DATETIME NOT NULL,
  effective_to DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_product_price_product (product_id, is_active),
  KEY idx_product_price_cinema (cinema_id, is_active),
  KEY idx_product_price_time (effective_from, effective_to),
  CONSTRAINT fk_product_price_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT fk_product_price_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_product_price_time CHECK (effective_to IS NULL OR effective_to > effective_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products sold with booking (concession order)
CREATE TABLE IF NOT EXISTS booking_products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty INT UNSIGNED NOT NULL,
  unit_price_amount BIGINT UNSIGNED NOT NULL,
  discount_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  final_amount BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_booking_product_booking (booking_id),
  KEY idx_booking_product_product (product_id),
  CONSTRAINT fk_booking_product_booking FOREIGN KEY (booking_id) REFERENCES bookings(id),
  CONSTRAINT fk_booking_product_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT chk_booking_product_qty CHECK (qty > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 09) Inventory: Stock Locations, Balances, Movements, Suppliers, Purchase Orders
-- =========================================================

CREATE TABLE IF NOT EXISTS stock_locations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cinema_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(32) NOT NULL, -- WH1/KIOSK1...
  name VARCHAR(255) NOT NULL,
  location_type VARCHAR(16) NOT NULL DEFAULT 'WAREHOUSE', -- WAREHOUSE/KIOSK
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_stock_location_code_per_cinema (cinema_id, code),
  KEY idx_stock_location_cinema (cinema_id),
  CONSTRAINT fk_stock_location_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_stock_location_type CHECK (location_type IN ('WAREHOUSE','KIOSK'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_balances (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  stock_location_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty_on_hand INT NOT NULL DEFAULT 0,
  reorder_level INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inventory_balance_unique (stock_location_id, product_id),
  KEY idx_inventory_balance_product (product_id),
  CONSTRAINT fk_inventory_balance_location FOREIGN KEY (stock_location_id) REFERENCES stock_locations(id),
  CONSTRAINT fk_inventory_balance_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_movements (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  stock_location_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  movement_type VARCHAR(16) NOT NULL, -- IN/OUT/ADJUST/TRANSFER
  qty_delta INT NOT NULL, -- positive/negative
  unit_cost_amount BIGINT UNSIGNED NULL, -- optional VND cost for IN
  reference_type VARCHAR(32) NULL, -- PURCHASE_ORDER/BOOKING/ADJUSTMENT...
  reference_id BIGINT UNSIGNED NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_stock_movement_location (stock_location_id, created_at),
  KEY idx_stock_movement_product (product_id, created_at),
  CONSTRAINT fk_stock_movement_location FOREIGN KEY (stock_location_id) REFERENCES stock_locations(id),
  CONSTRAINT fk_stock_movement_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT chk_stock_movement_type CHECK (movement_type IN ('IN','OUT','ADJUST','TRANSFER'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suppliers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  name VARCHAR(255) NOT NULL,
  tax_code VARCHAR(32) NULL,
  phone VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  address_line VARCHAR(255) NULL,
  ward VARCHAR(128) NULL,
  district VARCHAR(128) NULL,
  province VARCHAR(128) NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_supplier_public_id (public_id),
  KEY idx_supplier_name (name),
  CONSTRAINT chk_supplier_status CHECK (status IN ('ACTIVE','INACTIVE'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  cinema_id BIGINT UNSIGNED NOT NULL,
  po_code VARCHAR(32) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'DRAFT', -- DRAFT/ORDERED/PARTIALLY_RECEIVED/RECEIVED/CANCELLED
  ordered_at DATETIME NULL,
  received_at DATETIME NULL,
  total_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'VND',
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_po_public_id (public_id),
  UNIQUE KEY uq_po_code (po_code),
  KEY idx_po_supplier (supplier_id),
  KEY idx_po_cinema (cinema_id),
  CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
  CONSTRAINT fk_po_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_po_status CHECK (status IN ('DRAFT','ORDERED','PARTIALLY_RECEIVED','RECEIVED','CANCELLED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purchase_order_lines (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  qty_ordered INT UNSIGNED NOT NULL,
  qty_received INT UNSIGNED NOT NULL DEFAULT 0,
  unit_cost_amount BIGINT UNSIGNED NOT NULL,
  line_amount BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_po_line_po (purchase_order_id),
  KEY idx_po_line_product (product_id),
  CONSTRAINT fk_po_line_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id),
  CONSTRAINT fk_po_line_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT chk_po_line_qty CHECK (qty_ordered > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 10) Staff & Operations (optional)
-- =========================================================

CREATE TABLE IF NOT EXISTS staff (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(26) NOT NULL,
  cinema_id BIGINT UNSIGNED NOT NULL,
  staff_code VARCHAR(32) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(32) NULL,
  email VARCHAR(255) NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  hired_at DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_staff_public_id (public_id),
  UNIQUE KEY uq_staff_code_per_cinema (cinema_id, staff_code),
  KEY idx_staff_cinema (cinema_id),
  CONSTRAINT fk_staff_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_staff_status CHECK (status IN ('ACTIVE','INACTIVE','SUSPENDED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(32) NOT NULL, -- MANAGER/CASHIER/USHE R/PROJECTION...
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_role_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_roles (
  staff_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (staff_id, role_id),
  CONSTRAINT fk_staff_roles_staff FOREIGN KEY (staff_id) REFERENCES staff(id),
  CONSTRAINT fk_staff_roles_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS staff_shifts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cinema_id BIGINT UNSIGNED NOT NULL,
  shift_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_shift_cinema_date (cinema_id, shift_date),
  CONSTRAINT fk_shift_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT chk_shift_time CHECK (end_time > start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shift_assignments (
  shift_id BIGINT UNSIGNED NOT NULL,
  staff_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (shift_id, staff_id),
  CONSTRAINT fk_shift_assignment_shift FOREIGN KEY (shift_id) REFERENCES staff_shifts(id),
  CONSTRAINT fk_shift_assignment_staff FOREIGN KEY (staff_id) REFERENCES staff(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS equipment (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cinema_id BIGINT UNSIGNED NOT NULL,
  auditorium_id BIGINT UNSIGNED NULL,
  code VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  equipment_type VARCHAR(32) NOT NULL, -- PROJECTOR/SOUND/SCREEN/SEAT/OTHER
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  installed_at DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_equipment_code_per_cinema (cinema_id, code),
  KEY idx_equipment_cinema (cinema_id),
  KEY idx_equipment_auditorium (auditorium_id),
  CONSTRAINT fk_equipment_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT fk_equipment_auditorium FOREIGN KEY (auditorium_id) REFERENCES auditoriums(id),
  CONSTRAINT chk_equipment_type CHECK (equipment_type IN ('PROJECTOR','SOUND','SCREEN','SEAT','HVAC','LIGHTING','OTHER')),
  CONSTRAINT chk_equipment_status CHECK (status IN ('ACTIVE','INACTIVE','MAINTENANCE','RETIRED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS maintenance_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cinema_id BIGINT UNSIGNED NOT NULL,
  auditorium_id BIGINT UNSIGNED NULL,
  equipment_id BIGINT UNSIGNED NULL,
  requested_by BIGINT UNSIGNED NULL, -- staff_id
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  priority VARCHAR(16) NOT NULL DEFAULT 'MEDIUM',
  status VARCHAR(16) NOT NULL DEFAULT 'OPEN',
  opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_maintenance_status (status, opened_at),
  KEY idx_maintenance_cinema (cinema_id),
  CONSTRAINT fk_maintenance_cinema FOREIGN KEY (cinema_id) REFERENCES cinemas(id),
  CONSTRAINT fk_maintenance_auditorium FOREIGN KEY (auditorium_id) REFERENCES auditoriums(id),
  CONSTRAINT fk_maintenance_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id),
  CONSTRAINT fk_maintenance_staff FOREIGN KEY (requested_by) REFERENCES staff(id),
  CONSTRAINT chk_maintenance_priority CHECK (priority IN ('LOW','MEDIUM','HIGH','URGENT')),
  CONSTRAINT chk_maintenance_status CHECK (status IN ('OPEN','IN_PROGRESS','DONE','CANCELLED'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 11) Audit Log (generic)
-- =========================================================

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_type VARCHAR(32) NULL, -- STAFF/USER/SYSTEM
  actor_id BIGINT UNSIGNED NULL,
  action VARCHAR(64) NOT NULL, -- e.g. BOOKING_CREATED
  entity_type VARCHAR(64) NULL, -- e.g. BOOKING
  entity_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  meta JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_action_time (action, created_at),
  KEY idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 12) Seed basics (optional)
-- =========================================================

-- Seat types
INSERT IGNORE INTO seat_types (id, code, name) VALUES
  (1,'REGULAR','Ghế thường'),
  (2,'VIP','Ghế VIP'),
  (3,'COUPLE','Ghế đôi'),
  (4,'SWEETBOX','Sweetbox');

-- Content ratings (Vietnam)
INSERT IGNORE INTO content_ratings (id, code, name, min_age) VALUES
  (1,'P','Phổ biến mọi lứa tuổi',0),
  (2,'K','Dưới 13 tuổi phải có người giám hộ',0),
  (3,'T13','Từ đủ 13 tuổi',13),
  (4,'T16','Từ đủ 16 tuổi',16),
  (5,'T18','Từ đủ 18 tuổi',18),
  (6,'C','Cấm phổ biến',99);

-- Ticket types
INSERT IGNORE INTO ticket_types (id, code, name) VALUES
  (1,'ADULT','Người lớn'),
  (2,'STUDENT','Học sinh / Sinh viên'),
  (3,'CHILD','Trẻ em'),
  (4,'SENIOR','Người cao tuổi'),
  (5,'MEMBER','Thành viên');

-- Sales channels
INSERT IGNORE INTO sales_channels (id, code, name) VALUES
  (1,'WEB','Website'),
  (2,'APP','Mobile App'),
  (3,'COUNTER','Quầy vé'),
  (4,'PARTNER','Đối tác');

-- Loyalty tiers
INSERT IGNORE INTO loyalty_tiers (id, code, name, min_points) VALUES
  (1,'SILVER','Silver',0),
  (2,'GOLD','Gold',5000),
  (3,'PLATINUM','Platinum',15000);



-- =========================================================
-- 13) APP EXTENSIONS for Laravel demo (admins, categories active, movie slug, demo data)
--     IMPORTANT: Run on a fresh/empty database (import once).
-- =========================================================



-- Add missing columns used by the demo admin UI
ALTER TABLE movies ADD COLUMN slug VARCHAR(255) NULL AFTER title;
CREATE UNIQUE INDEX uq_movies_slug ON movies(slug);

ALTER TABLE genres ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER name;

-- Admin accounts (simple session auth)
CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo chain / cinema / auditorium
INSERT IGNORE INTO cinema_chains (id, public_id, chain_code, name, status)
VALUES (1, '01J2DEMOCHAIN00000000000000', 'DEMO', 'Demo Cinema Chain', 'ACTIVE');

INSERT IGNORE INTO cinemas (id, public_id, chain_id, cinema_code, name, timezone, province, district, status)
VALUES (1, '01J2DEMOCINEMA0000000000000', 1, 'HCM1', 'Demo Cinema - HCM', 'Asia/Ho_Chi_Minh', 'TP.HCM', 'Quận 1', 'ACTIVE');

INSERT IGNORE INTO auditoriums (id, public_id, cinema_id, auditorium_code, name, screen_type, seat_map_version, is_active)
VALUES (1, '01J2DEMOROOM00000000000000', 1, 'R1', 'Phòng 1', 'STANDARD', 1, 1);

-- Demo seats (A01-A10, B01-B10, C01-C10)
INSERT IGNORE INTO seats (auditorium_id, seat_type_id, seat_code, row_label, col_number, is_active)
SELECT 1, 1, CONCAT('A', LPAD(n, 2, '0')), 'A', n, 1 FROM (
  SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
  UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) t;

INSERT IGNORE INTO seats (auditorium_id, seat_type_id, seat_code, row_label, col_number, is_active)
SELECT 1, 1, CONCAT('B', LPAD(n, 2, '0')), 'B', n, 1 FROM (
  SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
  UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) t;

INSERT IGNORE INTO seats (auditorium_id, seat_type_id, seat_code, row_label, col_number, is_active)
SELECT 1, 1, CONCAT('C', LPAD(n, 2, '0')), 'C', n, 1 FROM (
  SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
  UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) t;

-- Demo categories (genres)
INSERT IGNORE INTO genres (id, code, name, is_active) VALUES
  (1,'ACTION','Hành động',1),
  (2,'COMEDY','Hài',1),
  (3,'HORROR','Kinh dị',1),
  (4,'ROMANCE','Tình cảm',1);

-- Demo movies
INSERT IGNORE INTO movies (id, public_id, content_rating_id, title, slug, duration_minutes, release_date, synopsis, poster_url, status)
VALUES
  (1,'01J2MOVIE000000000000000001',3,'Rượt Đuổi Trong Đêm','ruot-duoi-trong-dem',110,'2026-02-14','Một cuộc rượt đuổi nghẹt thở giữa lòng thành phố.','/storage/posters/demo1.jpg','ACTIVE'),
  (2,'01J2MOVIE000000000000000002',1,'Cười Xuyên Việt','cuoi-xuyen-viet',95,'2026-01-20','Hành trình hài hước xuyên Việt của nhóm bạn.','/storage/posters/demo2.jpg','ACTIVE'),
  (3,'01J2MOVIE000000000000000003',5,'Nhà Có Ma','nha-co-ma',105,'2026-03-01','Khi ngôi nhà cũ thức giấc, mọi thứ thay đổi.','/storage/posters/demo3.jpg','ACTIVE');

-- Movie ↔ Category mapping (movie_genres)
INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES
  (1,1),
  (2,2),
  (3,3),
  (1,4);

-- Movie versions (1 per movie)
INSERT IGNORE INTO movie_versions (id, movie_id, format, audio_language, subtitle_language, notes) VALUES
  (1,1,'2D','VI','VI','2D lồng tiếng Việt'),
  (2,2,'2D','VI','VI','2D lồng tiếng Việt'),
  (3,3,'2D','VI','VI','2D lồng tiếng Việt');

-- Demo showtimes (shows)
INSERT IGNORE INTO shows (id, public_id, auditorium_id, movie_version_id, start_time, end_time, on_sale_from, status)
VALUES
  (1,'01J2SHOW0000000000000000001',1,1,'2026-03-05 18:30:00','2026-03-05 20:20:00','2026-03-02 09:00:00','ON_SALE'),
  (2,'01J2SHOW0000000000000000002',1,1,'2026-03-06 20:00:00','2026-03-06 21:50:00','2026-03-02 09:00:00','ON_SALE'),
  (3,'01J2SHOW0000000000000000003',1,2,'2026-03-05 16:00:00','2026-03-05 17:35:00','2026-03-02 09:00:00','ON_SALE'),
  (4,'01J2SHOW0000000000000000004',1,3,'2026-03-07 21:00:00','2026-03-07 22:45:00','2026-03-02 09:00:00','ON_SALE');

-- Snapshot pricing per show (regular seat + adult)
INSERT IGNORE INTO show_prices (show_id, seat_type_id, ticket_type_id, price_amount, currency, is_active) VALUES
  (1,1,1,120000,'VND',1),
  (2,1,1,120000,'VND',1),
  (3,1,1,100000,'VND',1),
  (4,1,1,130000,'VND',1);

-- Demo admin user
-- email: admin@local.test
-- password: admin123
INSERT IGNORE INTO admins (id, name, email, password, is_active)
VALUES (1, 'Super Admin', 'admin@local.test', '$2y$10$PSXu8sBFa/3GimW0bPckQ.Wq1GMVNS0ZA3AyipUp2.V9j2jg.Fqd2', 1);

