-- 5. Enhance Contact System with advanced tracking
ALTER TABLE contact
ADD COLUMN category ENUM('inquiry', 'complaint', 'feedback', 'support', 'other') DEFAULT 'inquiry',
ADD COLUMN priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
ADD COLUMN assigned_to INT,
ADD COLUMN resolution_date TIMESTAMP,
ADD COLUMN response_time INT,
ADD COLUMN satisfaction_score INT,
FOREIGN KEY (assigned_to) REFERENCES associates(associate_id);

-- Add new indexes for enhanced performance
ALTER TABLE booking_payments ADD INDEX idx_booking_payment_date (payment_date);
ALTER TABLE associate_performance ADD INDEX idx_performance_month (month_year);
ALTER TABLE contact ADD INDEX idx_priority (priority);
ALTER TABLE contact ADD INDEX idx_category (category);