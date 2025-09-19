-- Fix dashboard counts for customers with minimal column requirements

-- Add customer data with just name and email (most common fields)
INSERT INTO customers (name, email) VALUES
('Rahul Sharma', 'rahul@example.com'),
('Priya Singh', 'priya@example.com'),
('Amit Kumar', 'amit@example.com'),
('Neha Patel', 'neha@example.com'),
('Vikram Mehta', 'vikram@example.com'),
('Anjali Gupta', 'anjali@example.com'),
('Rajesh Verma', 'rajesh@example.com'),
('Sunita Jain', 'sunita@example.com'),
('Deepak Sharma', 'deepak@example.com'),
('Kavita Singh', 'kavita@example.com');
