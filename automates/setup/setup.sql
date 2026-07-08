-- Automobiles Management System - Setup SQL (MySQL 8+)
-- Run in phpMyAdmin or MySQL client.

CREATE DATABASE IF NOT EXISTS automates
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE automates;

-- ============
-- Helpers
-- ============

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(90) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id BIGINT UNSIGNED NULL,
  brand VARCHAR(80) NOT NULL,
  model VARCHAR(120) NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  mileage_km INT UNSIGNED NULL,
  fuel_type ENUM('petrol','diesel','hybrid','electric') NOT NULL,
  transmission ENUM('manual','automatic') NOT NULL,
  seating_capacity SMALLINT UNSIGNED NULL,
  horsepower INT UNSIGNED NULL,
  description TEXT NULL,
  status ENUM('active','inactive','sold') NOT NULL DEFAULT 'active',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vehicles_category (category_id),
  CONSTRAINT fk_vehicles_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  image_url TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vehicle_images_vehicle (vehicle_id),
  CONSTRAINT fk_vehicle_images_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_attributes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  attr_key VARCHAR(80) NOT NULL,
  attr_value VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_vehicle_attributes_vehicle (vehicle_id),
  CONSTRAINT fk_vehicle_attributes_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Wishlist & Compare
CREATE TABLE IF NOT EXISTS wishlists (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_wishlists_user_vehicle (user_id, vehicle_id),
  KEY idx_wishlists_vehicle (vehicle_id),
  CONSTRAINT fk_wishlists_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_wishlists_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_comparisons (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vehicle_comparisons_user_vehicle (user_id, vehicle_id),
  KEY idx_vehicle_comparisons_vehicle (vehicle_id),
  CONSTRAINT fk_vehicle_comparisons_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_vehicle_comparisons_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Reviews & Ratings
CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(160) NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_reviews_user_vehicle (user_id, vehicle_id),
  KEY idx_reviews_vehicle (vehicle_id),
  CONSTRAINT fk_reviews_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Bookings & Test Drives
CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  booking_type ENUM('booking','request') NOT NULL DEFAULT 'booking',
  contact_name VARCHAR(120) NOT NULL,
  contact_email VARCHAR(190) NOT NULL,
  contact_phone VARCHAR(40) NULL,
  status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_bookings_user (user_id),
  KEY idx_bookings_vehicle (vehicle_id),
  CONSTRAINT fk_bookings_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS test_drive_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  preferred_datetime DATETIME NULL,
  status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_test_drive_vehicle (vehicle_id),
  KEY idx_test_drive_user (user_id),
  CONSTRAINT fk_test_drive_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_test_drive_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Contact Dealer Requests
CREATE TABLE IF NOT EXISTS dealer_contacts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  vehicle_id BIGINT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_dealer_contacts_user (user_id),
  KEY idx_dealer_contacts_vehicle (vehicle_id),
  CONSTRAINT fk_dealer_contacts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_dealer_contacts_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT NULL,
  type ENUM('booking','test_drive','message','system','sales') NOT NULL DEFAULT 'system',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notifications_user (user_id),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Loan / EMI Calculator history
CREATE TABLE IF NOT EXISTS loan_calculations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  vehicle_id BIGINT UNSIGNED NULL,
  principal DECIMAL(12,2) NOT NULL,
  down_payment DECIMAL(12,2) NOT NULL,
  annual_interest_rate DECIMAL(6,3) NOT NULL,
  tenure_months INT UNSIGNED NOT NULL,
  monthly_emi DECIMAL(12,2) NOT NULL,
  total_payment DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_loan_calc_user (user_id),
  CONSTRAINT fk_loan_calc_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_loan_calc_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Inventory / Inventory snapshots
CREATE TABLE IF NOT EXISTS inventory_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  stock_qty INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inventory_vehicle (vehicle_id),
  CONSTRAINT fk_inventory_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Sales
CREATE TABLE IF NOT EXISTS sales (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  vehicle_id BIGINT UNSIGNED NOT NULL,
  sale_amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  sold_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sales_vehicle (vehicle_id),
  KEY idx_sales_user (user_id),
  CONSTRAINT fk_sales_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sales_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Activity log
CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_user_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(60) NULL,
  entity_id BIGINT UNSIGNED NULL,
  meta JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_activity_actor (actor_user_id),
  CONSTRAINT fk_activity_actor
    FOREIGN KEY (actor_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(120) NOT NULL,
  setting_value LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB;

-- =========
-- Seed data
-- =========

INSERT INTO categories (name, slug) VALUES
  ('SUV','suv'),
  ('Sedan','sedan'),
  ('Truck','truck'),
  ('Luxury','luxury'),
  ('Sports','sports'),
  ('Electric','electric')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Minimal admin seed (password: admin123)
-- Note: password_hash uses bcrypt; since we can’t compute PHP hash here reliably,
-- it’s better to update after first run in a controller.
-- We'll still insert a placeholder hash so schema is valid; app will require reset.

INSERT INTO users (role, full_name, email, password_hash, phone, status)
VALUES (
  'admin',
  'Admin',
  'admin@sutomate.com',
  '$2y$10$nNNU7XE3oH0R5gNFUlqHhOp0QtLC9Do2GXh.EC6FFiIwMJWawOt2K',
  NULL,
  'active'
)
ON DUPLICATE KEY UPDATE role=VALUES(role), full_name=VALUES(full_name), status=VALUES(status);

INSERT INTO settings (setting_key, setting_value) VALUES
  ('system_name', 'Automobile Management System'),
  ('logo_path', ''),
  ('contact_email', 'info@automates.local'),
  ('contact_phone', '+234 800 000 0000'),
  ('contact_address', 'Lagos, Nigeria'),
  ('footer_text', 'Premium dealership platform built for modern car management.'),
  ('homepage_title', 'Discover Your Perfect Ride'),
  ('homepage_subtitle', 'Explore our exclusive collection of premium vehicles handpicked for luxury and performance'),
  ('homepage_about', 'Premium automobile management and dealership platform.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

