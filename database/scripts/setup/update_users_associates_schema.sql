-- Add address columns to users table
ALTER TABLE users ADD COLUMN address TEXT AFTER phone;
ALTER TABLE users ADD COLUMN city VARCHAR(100) AFTER address;
ALTER TABLE users ADD COLUMN state VARCHAR(100) AFTER city;
ALTER TABLE users ADD COLUMN pincode VARCHAR(20) AFTER state;

-- Add bank details to associates table
ALTER TABLE associates ADD COLUMN bank_name VARCHAR(100) AFTER status;
ALTER TABLE associates ADD COLUMN account_number VARCHAR(50) AFTER bank_name;
ALTER TABLE associates ADD COLUMN ifsc_code VARCHAR(20) AFTER account_number;
ALTER TABLE associates ADD COLUMN branch_name VARCHAR(100) AFTER ifsc_code;
ALTER TABLE associates ADD COLUMN pan_number VARCHAR(20) AFTER branch_name;
ALTER TABLE associates ADD COLUMN account_holder_name VARCHAR(100) AFTER pan_number;

-- Update roles in users table to be consistent
UPDATE users SET role = 'admin' WHERE role = '1';
UPDATE users SET role = 'associate' WHERE role = '2' OR role = 'agent';
UPDATE users SET role = 'customer' WHERE role = '3';
UPDATE users SET role = 'manager' WHERE role = '4';
