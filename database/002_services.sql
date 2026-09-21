USE arna_tours_travels;

-- Public travel services used by the website.
CREATE TABLE IF NOT EXISTS services (
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

INSERT INTO services (title, short_description, icon, sort_order, status)
SELECT 'Taxi & Cab Booking', 'Comfortable local and outstation taxi services with flexible travel options.', '🚕', 1, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM services WHERE title = 'Taxi & Cab Booking');
INSERT INTO services (title, short_description, icon, sort_order, status)
SELECT 'Bus Booking', 'Bus ticket booking support for journeys across major destinations in India.', '🚌', 2, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM services WHERE title = 'Bus Booking');
INSERT INTO services (title, short_description, icon, sort_order, status)
SELECT 'Train Booking', 'Train booking assistance for convenient intercity and long-distance travel.', '🚆', 3, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM services WHERE title = 'Train Booking');
INSERT INTO services (title, short_description, icon, sort_order, status)
SELECT 'Flight Booking', 'Domestic and international flight booking assistance for business and leisure trips.', '✈️', 4, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM services WHERE title = 'Flight Booking');
INSERT INTO services (title, short_description, icon, sort_order, status)
SELECT 'Hotel & Resort Booking', 'Hotel and resort booking support to complete your journey from travel to stay.', '🏨', 5, 'ACTIVE'
WHERE NOT EXISTS (SELECT 1 FROM services WHERE title = 'Hotel & Resort Booking');
