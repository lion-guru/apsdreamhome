-- Migration: Add/Update User Preferences Table
-- Version: 1.0.1
-- Created: 2025-05-25 18:30:00

-- Create table if it doesn't exist (without foreign key first to avoid dependency issues)
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(100) NOT NULL,
    preference_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_preference (user_id, preference_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add created_at if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'user_preferences';
SET @columnname = 'created_at';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) = 0,
    'ALTER TABLE user_preferences ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    'SELECT 1'
));

PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add updated_at if it doesn't exist
SET @columnname = 'updated_at';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) = 0,
    'ALTER TABLE user_preferences ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    'SELECT 1'
));

PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add unique index if it doesn't exist
SET @indexname = 'unique_user_preference';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) = 0,
    'ALTER TABLE user_preferences ADD UNIQUE INDEX unique_user_preference (user_id, preference_key)',
    'SELECT 1'
));

PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key if it doesn't exist
SET @constraint_name = 'user_preferences_ibfk_1';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND CONSTRAINT_NAME = @constraint_name
    ) = 0,
    'ALTER TABLE user_preferences ADD CONSTRAINT user_preferences_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE',
    'SELECT 1'
));

PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add default preferences for existing users
-- First check if role column exists
SET @roleColumnExists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'role'
);

-- Add dashboard layout for admins/agents if role column exists
SET @sql = IF(@roleColumnExists > 0,
    "INSERT INTO user_preferences (user_id, preference_key, preference_value)
    SELECT id, 'dashboard_layout', '{\"widgets\":[\"recent_properties\",\"leads\",\"visits\",\"revenue\"]}' 
    FROM users 
    WHERE role = 'admin' OR role = 'agent'",
    "SELECT 'Skipping admin/agent dashboard preferences - role column not found' AS message"
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add notification preferences for all users
INSERT IGNORE INTO user_preferences (user_id, preference_key, preference_value)
SELECT id, 'notification_preferences', '{"email":true,"in_app":true,"sms":false}' 
FROM users
WHERE id NOT IN (
    SELECT user_id 
    FROM user_preferences 
    WHERE preference_key = 'notification_preferences'
);

-- Add index for faster lookups
CREATE INDEX idx_user_preferences_key ON user_preferences(preference_key);

-- Migration verification
-- Add SELECT queries to verify the migration was successful
SELECT COUNT(*) FROM user_preferences;
SELECT COUNT(DISTINCT user_id) FROM user_preferences;
