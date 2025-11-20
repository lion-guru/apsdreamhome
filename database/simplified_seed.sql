-- Simplified seed data for apsdreamhome database
-- This script uses minimal columns that are likely to exist in most tables

-- For properties table
-- Common columns: id, name/property_name, description, address, price
INSERT IGNORE INTO properties (id, name, description, address, price) VALUES
(101, 'Luxury Villa', 'Beautiful luxury villa with garden', 'Delhi Premium Enclave', 15000000),
(102, 'City Apartment', 'Modern apartment in city center', 'Mumbai Heights', 7000000),
(103, 'Suburban House', 'Spacious family home', 'Bangalore Green Valley', 9000000),
(104, 'Beach Property', 'Beachfront luxury home', 'Goa Seaside Lane', 20000000),
(105, 'Penthouse', 'Luxury penthouse with terrace', 'Mumbai Skyline Tower', 25000000);

-- For bookings table
-- Common columns: id, user_id/customer_id, property_id, booking_date, amount
INSERT IGNORE INTO bookings (id, user_id, property_id, booking_date, amount) VALUES
(1, 1, 101, '2025-05-01', 1500000),
(2, 1, 102, '2025-05-02', 700000),
(3, 1, 103, '2025-05-05', 900000);

-- For transactions table
-- Common columns: id, user_id, amount, date
INSERT IGNORE INTO transactions (id, user_id, amount, date) VALUES
(1, 1, 1500000, '2025-05-01'),
(2, 1, 3500000, '2025-05-10'),
(3, 1, 700000, '2025-05-02'),
(4, 1, 1500000, '2025-05-12'),
(5, 1, 900000, '2025-05-05');
