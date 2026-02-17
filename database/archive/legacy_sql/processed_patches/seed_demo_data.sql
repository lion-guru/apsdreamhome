-- DEMO DATA FOR apsdreamhome

-- Users
INSERT INTO users (first_name, last_name, email, password, phone, role, status) VALUES
('Admin', 'User', 'admin@demo.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000001', 'admin', 'active'),
('Agent', 'Smith', 'agent1@demo.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000002', 'agent', 'active'),
('John', 'Doe', 'john@demo.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000003', 'user', 'active');

-- Properties
INSERT INTO properties (title, description, address, price, bedrooms, bathrooms, area, type, status, owner_id) VALUES
('Luxury Villa', 'A beautiful villa...', '123 Palm Street', 15000000, 5, 4, 3500, 'villa', 'available', 2),
('City Apartment', 'Modern apartment...', '456 City Road', 7000000, 3, 2, 1200, 'apartment', 'sold', 2);

-- Customers
INSERT INTO customers (name, email, phone, address) VALUES
('Rahul Sharma', 'rahul@demo.com', '9000000004', 'Delhi'),
('Priya Singh', 'priya@demo.com', '9000000005', 'Mumbai');

-- Leads
INSERT INTO leads (customer_id, property_id, source, status, notes) VALUES
(1, 1, 'website', 'new', 'Interested in villa'),
(2, 2, 'visit_schedule', 'contacted', 'Requested callback');

-- Bookings
INSERT INTO bookings (user_id, property_id, booking_date, amount, status, customer_id, property_type, installment_plan, created_at, updated_at) VALUES
(1, 1, '2025-05-01', 15000000, 'confirmed', 1, 1, 'Standard', NOW(), NOW()),
(2, 2, '2025-05-02', 7000000, 'pending', 2, 2, 'Premium', NOW(), NOW());

-- Property Visits
INSERT INTO property_visits (customer_id, property_id, lead_id, visit_date, visit_time, status, feedback, rating) VALUES
(1, 1, 1, '2025-05-10', '10:00:00', 'completed', 'Very satisfied', 5),
(2, 2, 2, '2025-05-12', '15:00:00', 'scheduled', '', NULL);

-- Notifications
INSERT INTO notifications (user_id, type, title, message, status, link) VALUES
(1, 'info', 'Welcome!', 'Your account has been created.', 'unread', ''),
(2, 'lead', 'New Lead Assigned', 'A new lead has been assigned to you.', 'unread', 'leads.php');
