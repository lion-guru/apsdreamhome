-- SMS Notification and Configuration Tables Migration

-- SMS Configuration Table
CREATE TABLE IF NOT EXISTS sms_config (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` VARCHAR(255) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate initial SMS configuration
INSERT IGNORE INTO sms_config (`key`, `value`, description) VALUES
('provider', 'twilio', 'Default SMS Provider'),
('max_retry_attempts', '3', 'Maximum SMS Retry Attempts'),
('retry_delay', '300', 'Retry Delay in Seconds');

-- SMS Queue Table
CREATE TABLE IF NOT EXISTS sms_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    provider ENUM('twilio', 'nexmo', 'aws_sns') DEFAULT 'twilio',
    scheduled_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    attempts INT DEFAULT 0,
    next_retry_at DATETIME NULL,
    
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_recipient (recipient)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Templates Table
CREATE TABLE IF NOT EXISTS sms_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    template TEXT NOT NULL,
    variables TEXT COMMENT 'JSON of allowed template variables',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate initial SMS templates
INSERT IGNORE INTO sms_templates 
(name, template, variables) VALUES 
('visit_reminder', 
'Your property visit for {{property_title}} is scheduled on {{visit_date}} at {{visit_time}}. Please confirm or reschedule.', 
'["property_title", "visit_date", "visit_time"]'),

('lead_notification', 
'New lead received for property {{property_title}}. Contact details: {{lead_name}}, {{lead_phone}}', 
'["property_title", "lead_name", "lead_phone"]'),

('account_verification', 
'Your APS Dream Homes verification code is: {{verification_code}}. This code will expire in 15 minutes.', 
'["verification_code"]');

-- User SMS Preferences Table
CREATE TABLE IF NOT EXISTS user_sms_preferences (
    user_id INT PRIMARY KEY,
    enable_sms BOOLEAN DEFAULT TRUE,
    notification_types JSON COMMENT 'JSON array of enabled notification types',
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optimize tables
OPTIMIZE TABLE 
    sms_config, 
    sms_queue, 
    sms_templates, 
    user_sms_preferences;
