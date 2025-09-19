-- Migration: Add API Authentication Tables
-- Version: 1.0.2
-- Created: 2025-05-18 00:44:03

-- Create API keys table
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    permissions TEXT,
    rate_limit INT DEFAULT 100,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,
    UNIQUE KEY (api_key),
    INDEX (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create API request logs table
CREATE TABLE IF NOT EXISTS api_request_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX (api_key_id),
    INDEX (request_time),
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add API permissions to users table if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS api_access BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS api_rate_limit INT DEFAULT 1000;

-- Create sample API key for the first user
INSERT INTO api_keys (user_id, api_key, name, permissions, rate_limit, status, created_at)
SELECT id, '7f4b2a1e9d8c3b6a5f0e7d9c8b3a2f1', 'Admin API Key', '["*"]', 1000, 'active', NOW()
FROM users 
ORDER BY id ASC 
LIMIT 1;

-- Migration verification
SELECT COUNT(*) FROM api_keys;
SELECT COUNT(*) FROM api_request_logs;
