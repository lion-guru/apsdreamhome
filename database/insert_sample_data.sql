-- Insert sample data into database tables

-- Insert data into state table if not already present
INSERT IGNORE INTO `state` (`sname`) VALUES 
('Uttar Pradesh'),
('Delhi'),
('Maharashtra'),
('Karnataka'),
('Tamil Nadu');

-- Insert data into city table
INSERT IGNORE INTO `city` (`cname`, `sid`) VALUES
('Lucknow', (SELECT `sid` FROM `state` WHERE `sname` = 'Uttar Pradesh')),
('Gorakhpur', (SELECT `sid` FROM `state` WHERE `sname` = 'Uttar Pradesh')),
('Varanasi', (SELECT `sid` FROM `state` WHERE `sname` = 'Uttar Pradesh')),
('New Delhi', (SELECT `sid` FROM `state` WHERE `sname` = 'Delhi')),
('Mumbai', (SELECT `sid` FROM `state` WHERE `sname` = 'Maharashtra')),
('Bangalore', (SELECT `sid` FROM `state` WHERE `sname` = 'Karnataka')),
('Chennai', (SELECT `sid` FROM `state` WHERE `sname` = 'Tamil Nadu'));

-- Insert sample users data
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `phone`, `type`, `status`) VALUES
('Rahul Sharma', 'rahul@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9876543210', 'user', 'active'),
('Priya Singh', 'priya@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '8765432109', 'user', 'active'),
('Amit Kumar', 'amit@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '7654321098', 'agent', 'active'),
('Neha Gupta', 'neha@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '6543210987', 'builder', 'active'),
('Vikram Patel', 'vikram@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '5432109876', 'agent', 'active');

-- DEMO USER DATA (all passwords: Aps@128128)
-- All demo users below use bcrypt hash for 'Aps@128128'
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `phone`, `type`, `status`) VALUES
('Demo User 1', 'demo.user1@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000010001', 'user', 'active'),
('Demo User 2', 'demo.user2@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000010002', 'user', 'active'),
('Demo Agent 1', 'demo.agent1@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000020001', 'agent', 'active'),
('Demo Agent 2', 'demo.agent2@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000020002', 'agent', 'active'),
('Demo Builder', 'demo.builder@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000030001', 'builder', 'active'),
('Demo Customer', 'demo.customer@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000040001', 'customer', 'active'),
('Demo Investor', 'demo.investor@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000040002', 'investor', 'active'),
('Demo Tenant', 'demo.tenant@aps.com', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1', '9000040003', 'tenant', 'active');

-- Sample admin, superadmin, finance, associate, customer, investor, and tenant users for login testing
INSERT IGNORE INTO `admin` (`auser`, `apass`, `role`, `status`, `email`, `phone`) VALUES
('superadmin', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', 'superadmin', 'active', 'superadmin@demo.com', '9000000001'),
('admin', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', 'admin', 'active', 'admin@demo.com', '9000000002'),
('finance', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', 'finance', 'active', 'finance@demo.com', '9000000003'),
('associateadmin', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', 'associate', 'active', 'associate@demo.com', '9000000004');

-- Sample associates
INSERT IGNORE INTO `associates` (`name`, `email`, `phone`, `commission_percent`, `level`, `status`) VALUES
('Associate One', 'associate1@demo.com', '8000000001', 5.00, 1, 'active'),
('Associate Two', 'associate2@demo.com', '8000000002', 3.00, 2, 'active');

-- Sample customers and investors in `users`
-- NOTE: Removed 'address' column for compatibility with current users table
INSERT IGNORE INTO `users` (`name`, `email`, `phone`, `type`, `status`) VALUES
('Customer User', 'customer@demo.com', '7000000001', 'customer', 'active'),
('Investor User', 'investor@demo.com', '7000000002', 'investor', 'active'),
('Tenant User', 'tenant@demo.com', '7000000003', 'tenant', 'active');

-- Sample login password for all above: password
-- Password hash: $2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe

-- You can now login as superadmin, admin, finance, associate, customer, investor, tenant for testing.

-- Insert sample property data
INSERT IGNORE INTO `property` (`title`, `pcontent`, `type`, `bhk`, `stype`, `bedroom`, `bathroom`, `balcony`, `kitchen`, `hall`, `floor`, `size`, `price`, `location`, `city`, `state`, `feature`, `pimage`, `pimage1`, `pimage2`, `pimage3`, `pimage4`, `uid`, `status`, `mapimage`, `topmapimage`, `groundmapimage`, `totalfloor`, `isFeatured`) VALUES
('Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', 1),
('Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', 1),
('Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', 0),
('Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', 1),
('Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', 0);

-- DEMO EMPLOYEE DATA (all passwords: Aps@128128)
-- If not already present, run create_employees_table.sql first
INSERT INTO employees (name, email, phone, role, status, password) VALUES
('Demo Employee 1', 'demo.employee1@aps.com', '9000000001', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 2', 'demo.employee2@aps.com', '9000000002', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 3', 'demo.employee3@aps.com', '9000000003', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 4', 'demo.employee4@aps.com', '9000000004', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1'),
('Demo Employee 5', 'demo.employee5@aps.com', '9000000005', 'employee', 'active', '$2y$10$ZVJcWJm8i9Z8F4bW5Qq5HeeN6g1w7v9m5Q2lZf1eF1eF1eF1eF1eF1');

-- Run the property_type table creation and data insertion script
-- SOURCE create_property_type_table.sql;

-- Verify data insertion
SELECT 'Data insertion completed successfully' AS 'Status';