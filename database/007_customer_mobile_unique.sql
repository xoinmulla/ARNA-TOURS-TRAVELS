USE arna_tours_travels;

-- This migration first consolidates duplicate customer rows by re-pointing bookings to the lowest customer id for each mobile number.
-- Review duplicate customer records before production use if customer profiles contain additional custom data.
UPDATE bookings b
JOIN customers c ON c.id = b.customer_id
JOIN (SELECT mobile_number, MIN(id) AS keep_id FROM customers GROUP BY mobile_number) k
  ON k.mobile_number = c.mobile_number
SET b.customer_id = k.keep_id
WHERE c.id <> k.keep_id;

DELETE c FROM customers c
JOIN (SELECT mobile_number, MIN(id) AS keep_id FROM customers GROUP BY mobile_number) k
  ON k.mobile_number = c.mobile_number
WHERE c.id <> k.keep_id;

SET @has_unique := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='customers' AND INDEX_NAME='uq_customers_mobile' AND NON_UNIQUE=0);
SET @sql := IF(@has_unique=0, 'ALTER TABLE customers ADD UNIQUE KEY uq_customers_mobile (mobile_number)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
