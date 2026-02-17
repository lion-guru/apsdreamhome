-- Security Enhancement Migration Script

-- Add status column to admin table if not exists
ALTER TABLE admin 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'locked', 'inactive') DEFAULT 'active';

-- Create login attempts tracking table
CREATE TABLE IF NOT EXISTS login_attempts (
    username VARCHAR(100) PRIMARY KEY,
    attempts INT DEFAULT 0,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES admin(auser) ON DELETE CASCADE
);

-- Create security event logging table
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    username VARCHAR(100),
    ip_address VARCHAR(45),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create password history table to prevent password reuse
CREATE TABLE IF NOT EXISTS password_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES admin(auser) ON DELETE CASCADE
);

-- Add indexes for performance
CREATE INDEX idx_security_logs_username ON security_logs(username);
CREATE INDEX idx_security_logs_event_type ON security_logs(event_type);
CREATE INDEX idx_password_history_username ON password_history(username);

-- Trigger to log password changes
DELIMITER //
CREATE TRIGGER log_password_change 
AFTER UPDATE ON admin
FOR EACH ROW
BEGIN
    IF OLD.apass != NEW.apass THEN
        INSERT INTO password_history (username, password_hash) 
        VALUES (NEW.auser, NEW.apass);
    END IF;
END;//
DELIMITER ;

-- Initial data for notification templates related to security
INSERT IGNORE INTO notification_templates (
    type, 
    title_template, 
    message_template
) VALUES 
(
    'security_alert', 
    'Security Alert for {username}', 
    'Suspicious activity detected on your account. {details}'
),
(
    'account_locked', 
    'Account Locked - {username}', 
    'Your account has been temporarily locked due to multiple failed login attempts.'
),
(
    'password_reset', 
    'Password Reset Confirmation', 
    'Your password was recently reset. If this was not you, please contact support immediately.'
);
