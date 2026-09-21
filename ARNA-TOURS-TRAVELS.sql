-- ============================================================
-- ARNA TOUR & TRAVELS - COMPLETE DATABASE
-- Database: arna_tours_travels
-- Generated from the current project source ZIP
-- ============================================================
-- IMPORTANT:
-- This is a clean/fresh-install SQL file. It recreates the database.
-- It contains the final table structure plus seed data defined by the
-- project migrations. It does NOT contain runtime records created later
-- through the admin panel unless those records were present in source SQL.
-- No default admin password is stored here; create the first admin via
-- admin/setup.php.
-- ============================================================

CREATE DATABASE IF NOT EXISTS arna_tours_travels
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE arna_tours_travels;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS recommendations;
DROP TABLE IF EXISTS route_searches;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS tour_packages;
DROP TABLE IF EXISTS tour_package_categories;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS website_content;
DROP TABLE IF EXISTS website_settings;
DROP TABLE IF EXISTS admin_users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ADMIN USERS
-- ============================================================
CREATE TABLE admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CUSTOMERS
-- ============================================================
CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customers_mobile (mobile_number),
    INDEX idx_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VEHICLES
-- ============================================================
CREATE TABLE vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_name VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(100) NULL,
    registration_number VARCHAR(50) NULL UNIQUE,
    seating_capacity INT UNSIGNED NOT NULL DEFAULT 4,
    rate_per_km DECIMAL(10,2) NOT NULL DEFAULT 0,
    included_km INT UNSIGNED NOT NULL DEFAULT 300,
    price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    status ENUM('AVAILABLE','BOOKED','MAINTENANCE','INACTIVE') NOT NULL DEFAULT 'AVAILABLE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DRIVERS
-- ============================================================
CREATE TABLE drivers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    license_number VARCHAR(80) NULL UNIQUE,
    vehicle_id INT UNSIGNED NULL,
    status ENUM('AVAILABLE','ASSIGNED','OFF_DUTY','INACTIVE') NOT NULL DEFAULT 'AVAILABLE',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_driver_status (status),
    INDEX idx_driver_name (full_name),
    CONSTRAINT fk_driver_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BOOKINGS
-- ============================================================
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(30) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NOT NULL,
    source_location VARCHAR(255) NOT NULL,
    destination_location VARCHAR(255) NOT NULL,
    trip_type ENUM('ROUND_TRIP','DROP') NOT NULL,
    preferred_date DATE NOT NULL,
    participants INT UNSIGNED NOT NULL DEFAULT 1,
    vehicle_id INT UNSIGNED NULL,
    driver_id INT UNSIGNED NULL,
    status ENUM('NEW','CONFIRMED','IN_PROGRESS','COMPLETED','CANCELLED') NOT NULL DEFAULT 'NEW',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_booking_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_booking_driver
        FOREIGN KEY (driver_id) REFERENCES drivers(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_booking_date (preferred_date),
    INDEX idx_booking_status (status),
    INDEX idx_customer (customer_id),
    INDEX idx_booking_vehicle (vehicle_id),
    INDEX idx_booking_driver (driver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TOUR PACKAGE CATEGORIES
-- ============================================================
CREATE TABLE tour_package_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tpc_status_sort (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tour_package_categories (title, slug, sort_order, status) VALUES
('Domestic','domestic',1,'ACTIVE'),
('International','international',2,'ACTIVE'),
('Dandeli','dandeli',3,'ACTIVE');

-- ============================================================
-- TOUR PACKAGES
-- ============================================================
CREATE TABLE tour_packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(150) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    category_id INT UNSIGNED NULL,
    duration VARCHAR(100) NULL,
    price DECIMAL(12,2) NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    featured_home TINYINT(1) NOT NULL DEFAULT 1,
    home_sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tour_packages_category (category_id),
    INDEX idx_tour_packages_home (featured_home, home_sort_order, status),
    CONSTRAINT fk_tour_packages_category
        FOREIGN KEY (category_id) REFERENCES tour_package_categories(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DESTINATIONS
-- ============================================================
CREATE TABLE destinations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    state VARCHAR(100) NULL,
    country VARCHAR(100) DEFAULT 'India',
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SERVICES
-- ============================================================
CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    short_description VARCHAR(500) NULL,
    icon VARCHAR(100) NULL,
    image VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_services_status (status),
    INDEX idx_services_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO services (title, short_description, icon, sort_order, status) VALUES
('Taxi & Cab Booking','Comfortable local and outstation taxi services with flexible travel options.','🚕',1,'ACTIVE'),
('Bus Booking','Bus ticket booking support for journeys across major destinations in India.','🚌',2,'ACTIVE'),
('Train Booking','Train booking assistance for convenient intercity and long-distance travel.','🚆',3,'ACTIVE'),
('Flight Booking','Domestic and international flight booking assistance for business and leisure trips.','✈️',4,'ACTIVE'),
('Hotel & Resort Booking','Hotel and resort booking support to complete your journey from travel to stay.','🏨',5,'ACTIVE');

-- ============================================================
-- TESTIMONIALS
-- ============================================================
CREATE TABLE testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_testimonial_status (status),
    INDEX idx_testimonial_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WEBSITE CONTENT / CMS
-- ============================================================
CREATE TABLE website_content (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL DEFAULT 'General',
    content_key VARCHAR(150) NOT NULL,
    content_value LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_website_content_key (content_key),
    INDEX idx_website_content_section (section_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WEBSITE SETTINGS
-- ============================================================
CREATE TABLE website_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(150) NOT NULL,
    setting_value LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_website_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO website_settings (setting_key, setting_value) VALUES
('business_phone', '+91 94800 01511'),
('business_email', ''),
('business_address', 'Hubballi, Karnataka, India'),
('booking_notice', 'Our team will contact you to confirm availability and trip details.');

-- ============================================================
-- ROUTE SEARCHES
-- ============================================================
CREATE TABLE route_searches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_location VARCHAR(255) NOT NULL,
    destination_location VARCHAR(255) NOT NULL,
    travel_date DATE NULL,
    preferences TEXT NULL,
    distance_km DECIMAL(10,2) NULL,
    travel_time_minutes INT UNSIGNED NULL,
    route_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- RECOMMENDATIONS
-- ============================================================
CREATE TABLE recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    route_search_id BIGINT UNSIGNED NULL,
    place_name VARCHAR(255) NOT NULL,
    recommendation_status ENUM('RECOMMENDED','CAUTION','AVOID') NOT NULL DEFAULT 'RECOMMENDED',
    detour_distance_km DECIMAL(10,2) NULL,
    detour_time_minutes INT UNSIGNED NULL,
    estimated_visit_minutes INT UNSIGNED NULL,
    reason TEXT NULL,
    navigation_url TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recommendation_route
        FOREIGN KEY (route_search_id) REFERENCES route_searches(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- OPTIONAL PROJECT SEED FLEET
-- These are the seed records defined by the current project migrations.
-- Admin can edit/remove them later.
-- ============================================================
INSERT INTO vehicles
(vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, image, description, status)
VALUES
('Toyota ETS.','Sedan',4,14.00,300,4200.00,'assets/img/etios.png','Comfortable sedan for city rides, airport transfers and outstation journeys.','AVAILABLE'),
('Swift Dzire.','Sedan',4,13.00,300,3900.00,'assets/img/car-1.png','Practical and comfortable option for everyday travel.','AVAILABLE'),
('Innova Crysta.','Premium MPV',7,20.00,300,6000.00,'assets/img/innova.png','Spacious premium travel for families, groups and long journeys.','AVAILABLE'),
('Maruti Suzuki Ertiga','MPV',7,0.00,300,0.00,'assets/img/ertiga.png','Flexible family and group travel with comfortable seating.','AVAILABLE'),
('Kiya Karan.','MUV',7,16.00,300,4800.00,NULL,'Spacious family and group travel vehicle. Rate: ₹16 × 300 km for 1 day.','AVAILABLE'),
('Tempo Traveller 12+1.','Tempo Traveller',13,22.00,300,6600.00,'assets/img/car-2.png','Group travel option for tours, events and outstation trips.','AVAILABLE'),
('Tempo Traveller 16+1.','Tempo Traveller',17,28.00,300,8400.00,NULL,'Group travel vehicle with 16 passenger seats plus driver. Rate: ₹28 × 300 km for 1 day.','AVAILABLE'),
('Tempo Traveller 20+1.','Tempo Traveller',21,30.00,300,9000.00,NULL,'Large group travel vehicle with 20 passenger seats plus driver. Rate: ₹30 × 300 km for 1 day.','AVAILABLE'),
('Tempo Traveller 25+1.','Tempo Traveller',26,35.00,300,10500.00,NULL,'Large group travel vehicle with 25 passenger seats plus driver. Rate: ₹35 × 300 km for 1 day.','AVAILABLE'),
('Urbania AC.','Urbania AC',17,38.00,300,11400.00,NULL,'Air-conditioned group travel vehicle with 16 passenger seats plus driver. Rate: ₹38 × 300 km for 1 day.','AVAILABLE');

-- ============================================================
-- END
-- ============================================================
