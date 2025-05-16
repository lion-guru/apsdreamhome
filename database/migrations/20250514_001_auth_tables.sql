-- Authentication and Authorization Tables Migration

-- Users Table (Enhanced)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('0', '1', '2', '3') DEFAULT '1' COMMENT '0:Guest, 1:Customer, 2:Agent, 3:Admin',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expiry INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (email) REFERENCES users(email) ON DELETE CASCADE,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts Tracking Table (for security)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') NOT NULL,
    
    INDEX idx_email (email),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Permissions Table (for fine-grained access control)
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('0', '1', '2', '3') NOT NULL,
    permission VARCHAR(50) NOT NULL,
    
    UNIQUE KEY unique_role_permission (role, permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate initial permissions
INSERT IGNORE INTO user_permissions (role, permission) VALUES
('0', 'view_properties'),
('1', 'book_visit'),
('1', 'view_properties'),
('2', 'manage_properties'),
('2', 'manage_leads'),
('2', 'book_visit'),
('2', 'view_properties'),
('3', 'full_access');

-- Add some sample users (for development/testing)
INSERT IGNORE INTO users 
(first_name, last_name, email, password, phone, role, status) 
VALUES 
('Admin', 'User', 'admin@apsdreamhomes.com', 
 -- Password: AdminPass123! (use password_hash in actual implementation)
 '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$RdyscOEXvhMXDzQIX1EM8Q', 
 '+911234567890', '3', 'active'),
('Agent', 'User', 'agent@apsdreamhomes.com', 
 -- Password: AgentPass123!
 '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$RdyscOEXvhMXDzQIX1EM8Q', 
 '+919876543210', '2', 'active'),
('Customer', 'User', 'customer@apsdreamhomes.com', 
 -- Password: CustomerPass123!
 '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$RdyscOEXvhMXDzQIX1EM8Q', 
 '+918765432109', '1', 'active');

-- Optimize tables
OPTIMIZE TABLE users, password_reset_tokens, login_attempts, user_permissions;
