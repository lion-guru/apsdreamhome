-- Add foreclosure fields to emi_plans table
ALTER TABLE emi_plans
ADD COLUMN foreclosure_date DATETIME NULL,
ADD COLUMN foreclosure_amount DECIMAL(12,2) NULL,
ADD COLUMN foreclosure_payment_id INT NULL,
ADD FOREIGN KEY (foreclosure_payment_id) REFERENCES payments(id);
