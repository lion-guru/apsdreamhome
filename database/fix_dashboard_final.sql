-- Fix dashboard counts based on actual database schema

-- For the customers table, based on project memory
-- The customers table has: id, name, email, phone, address, created_at
INSERT INTO customers (name, email, phone, address) VALUES
('Rahul Sharma', 'rahul@example.com', '9876543210', 'Delhi'),
('Priya Singh', 'priya@example.com', '9876543211', 'Mumbai'),
('Amit Kumar', 'amit@example.com', '9876543212', 'Bangalore'),
('Neha Patel', 'neha@example.com', '9876543213', 'Ahmedabad'),
('Vikram Mehta', 'vikram@example.com', '9876543214', 'Pune'),
('Anjali Gupta', 'anjali@example.com', '9876543215', 'Hyderabad'),
('Rajesh Verma', 'rajesh@example.com', '9876543216', 'Chennai'),
('Sunita Jain', 'sunita@example.com', '9876543217', 'Kolkata'),
('Deepak Sharma', 'deepak@example.com', '9876543218', 'Jaipur'),
('Kavita Singh', 'kavita@example.com', '9876543219', 'Lucknow');

-- For the leads table, based on project memory
-- The leads table has: id, customer_id, property_id, source, status, notes
INSERT INTO leads (customer_id, property_id, source, status, notes) VALUES
(1, 1, 'website', 'new', 'Interested in luxury villa'),
(2, 1, 'visit_schedule', 'contacted', 'Requested callback about apartment'),
(3, 1, 'referral', 'qualified', 'Referred by existing customer'),
(4, 1, 'direct', 'proposal', 'Direct inquiry at office'),
(5, 1, 'website', 'negotiation', 'Negotiating price'),
(6, 1, 'other', 'closed_won', 'Deal finalized'),
(7, 1, 'website', 'closed_lost', 'Chose competitor property'),
(8, 1, 'visit_schedule', 'new', 'Scheduled visit next week'),
(9, 1, 'referral', 'contacted', 'Following up on referral'),
(10, 1, 'direct', 'qualified', 'Qualified lead with budget approval');
