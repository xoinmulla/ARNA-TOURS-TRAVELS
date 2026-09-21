USE arna_tours_travels;

CREATE TABLE IF NOT EXISTS testimonials (
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

CREATE TABLE IF NOT EXISTS tour_package_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tpc_status_sort (status, sort_order)
);

INSERT INTO tour_package_categories (title, slug, sort_order, status)
SELECT 'Domestic', 'domestic', 1, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM tour_package_categories WHERE slug='domestic');
INSERT INTO tour_package_categories (title, slug, sort_order, status)
SELECT 'International', 'international', 2, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM tour_package_categories WHERE slug='international');
INSERT INTO tour_package_categories (title, slug, sort_order, status)
SELECT 'Dandeli', 'dandeli', 3, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM tour_package_categories WHERE slug='dandeli');

SET @has_category_id := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tour_packages' AND COLUMN_NAME='category_id');
SET @sql := IF(@has_category_id=0, 'ALTER TABLE tour_packages ADD COLUMN category_id INT UNSIGNED NULL AFTER destination', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_category_idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tour_packages' AND INDEX_NAME='idx_tour_packages_category');
SET @sql := IF(@has_category_idx=0, 'ALTER TABLE tour_packages ADD INDEX idx_tour_packages_category(category_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_category_fk := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='tour_packages' AND CONSTRAINT_NAME='fk_tour_packages_category');
SET @sql := IF(@has_category_fk=0, 'ALTER TABLE tour_packages ADD CONSTRAINT fk_tour_packages_category FOREIGN KEY(category_id) REFERENCES tour_package_categories(id) ON DELETE SET NULL ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
