-- Email Queue and Configuration Tables Migration

-- Email Configuration Table
CREATE TABLE IF NOT EXISTS email_config (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` VARCHAR(255) NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate initial email configuration
INSERT IGNORE INTO email_config (`key`, `value`, description) VALUES
('host', 'smtp.gmail.com', 'SMTP Server Hostname'),
('port', '587', 'SMTP Server Port'),
('encryption', 'tls', 'SMTP Encryption Type'),
('from_email', 'noreply@apsdreamhomes.com', 'Default Sender Email'),
('from_name', 'APS Dream Homes', 'Default Sender Name'),
('reply_to_email', 'support@apsdreamhomes.com', 'Reply-To Email Address'),
('reply_to_name', 'APS Dream Homes Support', 'Reply-To Name');

-- Email Queue Table
CREATE TABLE IF NOT EXISTS email_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    recipient VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    scheduled_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    attempts INT DEFAULT 0,
    
    INDEX idx_status (status),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_recipient (recipient)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Templates Table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables TEXT COMMENT 'JSON of allowed template variables',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate initial email templates
INSERT IGNORE INTO email_templates 
(name, subject, body, variables) VALUES 
('welcome', 'Welcome to APS Dream Homes', 
'<!DOCTYPE html>
<html>
<body>
    <h1>Welcome, {{first_name}}!</h1>
    <p>Thank you for joining APS Dream Homes. Your account has been successfully created.</p>
    <p>Your username is: {{email}}</p>
</body>
</html>', 
'["first_name", "email"]'),

('property_inquiry', 'New Property Inquiry', 
'<!DOCTYPE html>
<html>
<body>
    <h1>New Property Inquiry</h1>
    <p>A new inquiry has been received for property: {{property_title}}</p>
    <p>Inquiry Details:
        <br>Name: {{inquirer_name}}
        <br>Email: {{inquirer_email}}
        <br>Phone: {{inquirer_phone}}
        <br>Message: {{inquiry_message}}
    </p>
</body>
</html>', 
'["property_title", "inquirer_name", "inquirer_email", "inquirer_phone", "inquiry_message"]'),

('visit_confirmation', 'Property Visit Confirmed', 
'<!DOCTYPE html>
<html>
<body>
    <h1>Property Visit Confirmed</h1>
    <p>Your visit to {{property_title}} has been confirmed.</p>
    <p>Details:
        <br>Date: {{visit_date}}
        <br>Time: {{visit_time}}
        <br>Address: {{property_address}}
    </p>
</body>
</html>', 
'["property_title", "visit_date", "visit_time", "property_address"]');

-- Optimize tables
OPTIMIZE TABLE 
    email_config, 
    email_queue, 
    email_templates;
