-- Create sms_logs table
CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('success', 'error') NOT NULL,
    error_message TEXT NULL,
    attempt INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sms_logs_status (status),
    INDEX idx_sms_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add SMS configuration to site_settings if not exists
INSERT IGNORE INTO site_settings (setting_name, setting_value, created_at)
VALUES 
    ('sms_gateway', 'twilio', NOW()),
    ('sms_api_key', '', NOW()),
    ('sms_api_secret', '', NOW()),
    ('sms_from_number', '', NOW());
