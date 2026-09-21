-- Arna Tour & Travels - Vehicle pricing support
-- Adds editable Rate/KM, Included KM and 1-Day Amount to the admin fleet.

SET @db_name = DATABASE();

SET @has_rate = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vehicles' AND COLUMN_NAME='rate_per_km');
SET @sql = IF(@has_rate=0, 'ALTER TABLE vehicles ADD COLUMN rate_per_km DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER seating_capacity', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_included = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vehicles' AND COLUMN_NAME='included_km');
SET @sql = IF(@has_included=0, 'ALTER TABLE vehicles ADD COLUMN included_km INT UNSIGNED NOT NULL DEFAULT 300 AFTER rate_per_km', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_price = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='vehicles' AND COLUMN_NAME='price_per_day');
SET @sql = IF(@has_price=0, 'ALTER TABLE vehicles ADD COLUMN price_per_day DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER included_km', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Populate the client-provided rates. Existing matching records are updated rather than duplicated.
UPDATE vehicles SET vehicle_name='Toyota ETS.', rate_per_km=14, included_km=300, price_per_day=4200 WHERE vehicle_name IN ('Toyota Etios','Toyota ETS.');
UPDATE vehicles SET vehicle_name='Swift Dzire.', rate_per_km=13, included_km=300, price_per_day=3900 WHERE vehicle_name IN ('Maruti Suzuki Swift Dzire','Swift Dzire.');
UPDATE vehicles SET vehicle_name='Innova Crysta.', rate_per_km=20, included_km=300, price_per_day=6000 WHERE vehicle_name IN ('Toyota Innova Crysta','Innova Crysta.');
UPDATE vehicles SET vehicle_name='Tempo Traveller 12+1.', rate_per_km=22, included_km=300, price_per_day=6600 WHERE vehicle_name IN ('Tempo Traveller','Tempo Traveller 12+1.');

INSERT INTO vehicles (vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, description, status)
SELECT 'Kiya Karan.','MUV',7,16,300,4800,'Spacious family and group travel vehicle. Rate: ₹16 × 300 km for 1 day.','AVAILABLE'
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE vehicle_name='Kiya Karan.');
INSERT INTO vehicles (vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, description, status)
SELECT 'Tempo Traveller 16+1.','Tempo Traveller',17,28,300,8400,'Group travel vehicle with 16 passenger seats plus driver. Rate: ₹28 × 300 km for 1 day.','AVAILABLE'
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE vehicle_name='Tempo Traveller 16+1.');
INSERT INTO vehicles (vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, description, status)
SELECT 'Tempo Traveller 20+1.','Tempo Traveller',21,30,300,9000,'Large group travel vehicle with 20 passenger seats plus driver. Rate: ₹30 × 300 km for 1 day.','AVAILABLE'
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE vehicle_name='Tempo Traveller 20+1.');
INSERT INTO vehicles (vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, description, status)
SELECT 'Tempo Traveller 25+1.','Tempo Traveller',26,35,300,10500,'Large group travel vehicle with 25 passenger seats plus driver. Rate: ₹35 × 300 km for 1 day.','AVAILABLE'
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE vehicle_name='Tempo Traveller 25+1.');
INSERT INTO vehicles (vehicle_name, vehicle_type, seating_capacity, rate_per_km, included_km, price_per_day, description, status)
SELECT 'Urbania AC.','Urbania AC',17,38,300,11400,'Air-conditioned group travel vehicle with 16 passenger seats plus driver. Rate: ₹38 × 300 km for 1 day.','AVAILABLE'
WHERE NOT EXISTS (SELECT 1 FROM vehicles WHERE vehicle_name='Urbania AC.');

-- Verification
SELECT id, vehicle_name, seating_capacity, rate_per_km, included_km, price_per_day, status
FROM vehicles ORDER BY id;
