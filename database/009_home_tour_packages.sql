USE arna_tours_travels;

-- Homepage Tour Packages section: reuse the editable tour_packages records
-- and add homepage visibility/order controls.
SET
    @has_featured_home := (
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'tour_packages'
            AND COLUMN_NAME = 'featured_home'
    );

SET
    @sql := IF(
        @has_featured_home = 0,
        'ALTER TABLE tour_packages ADD COLUMN featured_home TINYINT(1) NOT NULL DEFAULT 1 AFTER status',
        'SELECT 1'
    );

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

SET
    @has_home_sort := (
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'tour_packages'
            AND COLUMN_NAME = 'home_sort_order'
    );

SET
    @sql := IF(
        @has_home_sort = 0,
        'ALTER TABLE tour_packages ADD COLUMN home_sort_order INT NOT NULL DEFAULT 0 AFTER featured_home',
        'SELECT 1'
    );

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

UPDATE tour_packages SET featured_home = 1 WHERE featured_home IS NULL;

SET
    @has_home_idx := (
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE
            TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'tour_packages'
            AND INDEX_NAME = 'idx_tour_packages_home'
    );

SET
    @sql := IF(
        @has_home_idx = 0,
        'ALTER TABLE tour_packages ADD INDEX idx_tour_packages_home(featured_home, home_sort_order, status)',
        'SELECT 1'
    );

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;