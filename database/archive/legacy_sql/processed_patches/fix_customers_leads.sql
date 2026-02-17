-- Fix missing data for customers and leads/inquiries tables

-- Add customers with correct column structure
-- Based on the database schema memory, customers table has: id, first_name, last_name, email, phone, address, created_at
INSERT IGNORE INTO customers (first_name, last_name, email, phone, address, created_at) VALUES
('Rahul', 'Sharma', 'rahul@example.com', '9876543210', 'Delhi', NOW()),
('Priya', 'Singh', 'priya@example.com', '9876543211', 'Mumbai', NOW()),
('Amit', 'Kumar', 'amit@example.com', '9876543212', 'Bangalore', NOW()),
('Neha', 'Patel', 'neha@example.com', '9876543213', 'Ahmedabad', NOW()),
('Vikram', 'Mehta', 'vikram@example.com', '9876543214', 'Pune', NOW()),
('Anjali', 'Gupta', 'anjali@example.com', '9876543215', 'Hyderabad', NOW()),
('Rajesh', 'Verma', 'rajesh@example.com', '9876543216', 'Chennai', NOW()),
('Sunita', 'Jain', 'sunita@example.com', '9876543217', 'Kolkata', NOW()),
('Deepak', 'Sharma', 'deepak@example.com', '9876543218', 'Jaipur', NOW()),
('Kavita', 'Singh', 'kavita@example.com', '9876543219', 'Lucknow', NOW());

-- Add leads/inquiries with correct property references
-- Based on the database schema memory, leads table has: id, customer_id, property_id, source, status, notes, created_at
INSERT IGNORE INTO leads (customer_id, property_id, source, status, notes, created_at) VALUES
(1, 1, 'website', 'new', 'Interested in luxury villa', NOW()),
(2, 1, 'visit_schedule', 'contacted', 'Requested callback about apartment', NOW()),
(3, 1, 'referral', 'qualified', 'Referred by existing customer', NOW()),
(4, 1, 'direct', 'proposal', 'Direct inquiry at office', NOW()),
(5, 1, 'website', 'negotiation', 'Negotiating price', NOW()),
(6, 1, 'other', 'closed_won', 'Deal finalized', NOW()),
(7, 1, 'website', 'closed_lost', 'Chose competitor property', NOW()),
(8, 1, 'visit_schedule', 'new', 'Scheduled visit next week', NOW()),
(9, 1, 'referral', 'contacted', 'Following up on referral', NOW()),
(10, 1, 'direct', 'qualified', 'Qualified lead with budget approval', NOW());
