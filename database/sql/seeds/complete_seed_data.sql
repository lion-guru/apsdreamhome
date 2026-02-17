-- Complete seed data for apsdreamhome database
-- This script will populate all major tables with realistic demo data

-- Properties (if not already populated)
INSERT IGNORE INTO properties (id, title, description, address, price, bedrooms, bathrooms, area, type, status) VALUES
(101, 'Luxury Villa', 'Beautiful luxury villa with garden and pool', 'Delhi Premium Enclave, Sector 15', 15000000, 5, 4, 3500, 'villa', 'available'),
(102, 'City Apartment', 'Modern apartment in city center', 'Mumbai Heights, Bandra West', 7000000, 3, 2, 1200, 'apartment', 'available'),
(103, 'Suburban House', 'Spacious family home in quiet neighborhood', 'Bangalore Green Valley, Whitefield', 9000000, 4, 3, 2000, 'house', 'available'),
(104, 'Beach Property', 'Beachfront luxury home with amazing views', 'Goa Seaside Lane, Candolim', 20000000, 4, 4, 2800, 'villa', 'available'),
(105, 'Penthouse', 'Luxury penthouse with terrace and city views', 'Mumbai Skyline Tower, Worli', 25000000, 4, 3, 3000, 'penthouse', 'available');

-- Users (if not already populated)
INSERT IGNORE INTO users (id, name, email, password, phone, type, status) VALUES
(1, 'Admin User', 'admin@apsdreamhome.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000001', 'admin', 'active'),
(2, 'Agent Smith', 'agent@apsdreamhome.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000002', 'agent', 'active'),
(3, 'John Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000003', 'user', 'active');

-- Customers (already created with minimal structure)
-- We'll add more if needed based on dashboard requirements

-- Leads (already created with minimal structure)
-- We'll add more if needed based on dashboard requirements

-- Bookings (if not already populated)
INSERT IGNORE INTO bookings (id, user_id, property_id, booking_date, amount, status) VALUES
(1, 3, 101, '2025-05-01', 1500000, 'confirmed'),
(2, 3, 102, '2025-05-02', 700000, 'pending'),
(3, 3, 103, '2025-05-05', 900000, 'confirmed');

-- Transactions (if not already populated)
INSERT IGNORE INTO transactions (id, user_id, amount, date) VALUES
(1, 3, 1500000, '2025-05-01 10:15:30'),
(2, 3, 3500000, '2025-05-10 14:30:00'),
(3, 3, 700000, '2025-05-02 11:45:20'),
(4, 3, 1500000, '2025-05-12 09:20:15'),
(5, 3, 900000, '2025-05-05 16:10:45');

-- Feedback (if table exists)
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  property_id INT,
  rating INT,
  feedback_text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO feedback (user_id, property_id, rating, feedback_text, created_at) VALUES
(3, 101, 5, 'Excellent property with amazing amenities. The villa exceeded our expectations.', '2025-05-16 14:30:00'),
(3, 102, 4, 'Great apartment in a convenient location. Modern amenities and good value.', '2025-05-16 15:45:00');

-- Gallery (if table exists)
CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  property_id INT,
  image_url VARCHAR(255),
  caption VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO gallery (property_id, image_url, caption, uploaded_at) VALUES
(101, 'images/properties/villa1.jpg', 'Front view of luxury villa', '2025-05-10 10:00:00'),
(101, 'images/properties/villa2.jpg', 'Swimming pool area', '2025-05-10 10:05:00'),
(102, 'images/properties/apartment1.jpg', 'Modern living room', '2025-05-10 11:00:00'),
(102, 'images/properties/apartment2.jpg', 'Kitchen with premium appliances', '2025-05-10 11:05:00');

-- Testimonials (if table exists)
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  testimonial_text TEXT,
  rating INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO testimonials (user_id, testimonial_text, rating, created_at) VALUES
(3, 'I found my dream home through APS Dream Home! The entire process was smooth and professional.', 5, '2025-05-15 16:30:00'),
(3, 'Great experience working with the APS Dream Home team. They understood our requirements perfectly.', 5, '2025-05-16 14:45:00');

-- Property Visits (if table exists)
CREATE TABLE IF NOT EXISTS property_visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  property_id INT,
  visit_date DATE,
  visit_time TIME,
  status ENUM('scheduled', 'completed', 'cancelled', 'no-show') DEFAULT 'scheduled',
  feedback TEXT,
  rating INT
);

INSERT IGNORE INTO property_visits (user_id, property_id, visit_date, visit_time, status, feedback, rating) VALUES
(3, 101, '2025-05-20', '14:00:00', 'scheduled', NULL, NULL),
(3, 102, '2025-05-21', '11:00:00', 'scheduled', NULL, NULL),
(3, 103, '2025-05-15', '10:00:00', 'completed', 'Property was exactly as described. Very satisfied.', 5),
(3, 104, '2025-05-16', '15:00:00', 'cancelled', 'Had a scheduling conflict', NULL);

-- Notifications (if table exists)
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  type VARCHAR(50),
  title VARCHAR(255),
  message TEXT,
  status ENUM('read', 'unread') DEFAULT 'unread',
  link VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO notifications (user_id, type, title, message, status, link, created_at) VALUES
(1, 'info', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', 'dashboard.php', NOW()),
(2, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from John Doe.', 'unread', 'leads.php?id=1', NOW()),
(2, 'visit', 'Visit Scheduled: City Apartment', 'John Doe has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread', 'visits.php?id=2', NOW()),
(1, 'lead', 'Lead Status Updated: City Apartment', 'The lead for City Apartment has been updated to contacted.', 'read', 'leads.php?id=2', DATE_SUB(NOW(), INTERVAL 1 DAY));
