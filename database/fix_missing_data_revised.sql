-- Fix missing data for customers and leads/inquiries tables

-- Add customers if table is empty
INSERT IGNORE INTO customers (name, email, phone, address, created_at) VALUES
('Rahul Sharma', 'rahul@example.com', '9876543210', 'Delhi', NOW()),
('Priya Singh', 'priya@example.com', '9876543211', 'Mumbai', NOW()),
('Amit Kumar', 'amit@example.com', '9876543212', 'Bangalore', NOW()),
('Neha Patel', 'neha@example.com', '9876543213', 'Ahmedabad', NOW()),
('Vikram Mehta', 'vikram@example.com', '9876543214', 'Pune', NOW()),
('Anjali Gupta', 'anjali@example.com', '9876543215', 'Hyderabad', NOW()),
('Rajesh Verma', 'rajesh@example.com', '9876543216', 'Chennai', NOW()),
('Sunita Jain', 'sunita@example.com', '9876543217', 'Kolkata', NOW()),
('Deepak Sharma', 'deepak@example.com', '9876543218', 'Jaipur', NOW()),
('Kavita Singh', 'kavita@example.com', '9876543219', 'Lucknow', NOW());

-- Check if we have properties, if not create some basic ones
INSERT IGNORE INTO properties (title, description, address, price, bedrooms, bathrooms, area, type, status, owner_id) VALUES
('Luxury Villa', 'Beautiful luxury villa with garden', 'Delhi Premium Enclave', 15000000, 5, 4, 3500, 'villa', 'available', 1),
('City Apartment', 'Modern apartment in city center', 'Mumbai Heights', 7000000, 3, 2, 1200, 'apartment', 'available', 1),
('Suburban House', 'Spacious family home', 'Bangalore Green Valley', 9000000, 4, 3, 2000, 'house', 'available', 1),
('Beach Property', 'Beachfront luxury home', 'Goa Seaside Lane', 20000000, 4, 4, 2800, 'villa', 'available', 1),
('Penthouse', 'Luxury penthouse with terrace', 'Mumbai Skyline Tower', 25000000, 4, 3, 3000, 'penthouse', 'available', 1);

-- Add leads/inquiries with IGNORE to prevent errors
INSERT IGNORE INTO leads (customer_id, property_id, source, status, notes, created_at) VALUES
(1, 1, 'website', 'new', 'Interested in luxury villa', NOW()),
(2, 2, 'visit_schedule', 'contacted', 'Requested callback about apartment', NOW()),
(3, 1, 'referral', 'qualified', 'Referred by existing customer', NOW()),
(4, 2, 'direct', 'proposal', 'Direct inquiry at office', NOW()),
(5, 1, 'website', 'negotiation', 'Negotiating price', NOW()),
(6, 2, 'other', 'closed_won', 'Deal finalized', NOW()),
(7, 1, 'website', 'closed_lost', 'Chose competitor property', NOW()),
(8, 2, 'visit_schedule', 'new', 'Scheduled visit next week', NOW()),
(9, 1, 'referral', 'contacted', 'Following up on referral', NOW()),
(10, 2, 'direct', 'qualified', 'Qualified lead with budget approval', NOW());
