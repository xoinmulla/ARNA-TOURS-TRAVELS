CREATE DATABASE IF NOT EXISTS arna_tours_travels
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE arna_tours_travels;


-- ============================================
-- ADMIN USERS
-- ============================================

CREATE TABLE admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================
-- CUSTOMERS
-- ============================================

CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_customers_mobile (mobile_number),
    INDEX idx_name (full_name)
);


-- ============================================
-- BOOKINGS
-- ============================================

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    booking_number VARCHAR(30) NOT NULL UNIQUE,

    customer_id INT UNSIGNED NOT NULL,

    source_location VARCHAR(255) NOT NULL,
    destination_location VARCHAR(255) NOT NULL,

    trip_type ENUM(
        'ROUND_TRIP',
        'DROP'
    ) NOT NULL,

    preferred_date DATE NOT NULL,

    participants INT UNSIGNED NOT NULL DEFAULT 1,

    vehicle_id INT UNSIGNED NULL,
    driver_id INT UNSIGNED NULL,

    status ENUM(
        'NEW',
        'CONFIRMED',
        'IN_PROGRESS',
        'COMPLETED',
        'CANCELLED'
    ) NOT NULL DEFAULT 'NEW',

    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_booking_date (preferred_date),
    INDEX idx_booking_status (status),
    INDEX idx_customer (customer_id)
);


-- ============================================
-- VEHICLES
-- ============================================

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

    status ENUM(
        'AVAILABLE',
        'BOOKED',
        'MAINTENANCE',
        'INACTIVE'
    ) NOT NULL DEFAULT 'AVAILABLE',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================
-- TOUR PACKAGES
-- ============================================

CREATE TABLE tour_package_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tpc_status_sort (status, sort_order)
);

INSERT INTO tour_package_categories (title, slug, sort_order, status) VALUES
('Domestic','domestic',1,'ACTIVE'),
('International','international',2,'ACTIVE'),
('Dandeli','dandeli',3,'ACTIVE');

CREATE TABLE tour_packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    package_name VARCHAR(150) NOT NULL,

    destination VARCHAR(150) NOT NULL,

    category_id INT UNSIGNED NULL,

    duration VARCHAR(100) NULL,

    price DECIMAL(12,2) NULL,

    image VARCHAR(255) NULL,

    description TEXT NULL,

    status ENUM(
        'ACTIVE',
        'INACTIVE'
    ) NOT NULL DEFAULT 'ACTIVE',

    featured_home TINYINT(1) NOT NULL DEFAULT 1,
    home_sort_order INT NOT NULL DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tour_packages_category (category_id),
    CONSTRAINT fk_tour_packages_category FOREIGN KEY(category_id) REFERENCES tour_package_categories(id) ON DELETE SET NULL ON UPDATE CASCADE
);


-- ============================================
-- DESTINATIONS
-- ============================================

CREATE TABLE destinations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    state VARCHAR(100) NULL,

    country VARCHAR(100) DEFAULT 'India',

    latitude DECIMAL(10,8) NULL,

    longitude DECIMAL(11,8) NULL,

    description TEXT NULL,

    image VARCHAR(255) NULL,

    status ENUM(
        'ACTIVE',
        'INACTIVE'
    ) NOT NULL DEFAULT 'ACTIVE',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================
-- WEBSITE SERVICES
-- ============================================

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
);


-- ============================================
-- TESTIMONIALS
-- ============================================

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
);


-- ============================================
-- ROUTE SEARCHES
-- ============================================

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
);


-- ============================================
-- RECOMMENDATIONS
-- ============================================

CREATE TABLE recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    route_search_id BIGINT UNSIGNED NULL,

    place_name VARCHAR(255) NOT NULL,

    recommendation_status ENUM(
        'RECOMMENDED',
        'CAUTION',
        'AVOID'
    ) NOT NULL DEFAULT 'RECOMMENDED',

    detour_distance_km DECIMAL(10,2) NULL,

    detour_time_minutes INT UNSIGNED NULL,

    estimated_visit_minutes INT UNSIGNED NULL,

    reason TEXT NULL,

    navigation_url TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_recommendation_route
        FOREIGN KEY (route_search_id)
        REFERENCES route_searches(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- DRIVERS
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
    INDEX idx_driver_status(status), INDEX idx_driver_name(full_name),
    CONSTRAINT fk_driver_vehicle FOREIGN KEY(vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL ON UPDATE CASCADE
);

ALTER TABLE bookings ADD CONSTRAINT fk_booking_vehicle FOREIGN KEY(vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE bookings ADD CONSTRAINT fk_booking_driver FOREIGN KEY(driver_id) REFERENCES drivers(id) ON DELETE SET NULL ON UPDATE CASCADE;

