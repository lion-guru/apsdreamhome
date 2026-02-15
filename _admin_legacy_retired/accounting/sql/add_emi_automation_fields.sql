-- Add reminder fields to emi_installments table
ALTER TABLE emi_installments
ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0,
ADD COLUMN last_reminder_date DATETIME DEFAULT NULL;

-- Add reports table for monthly reports
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    generated_for_month INT NOT NULL,
    generated_for_year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);
