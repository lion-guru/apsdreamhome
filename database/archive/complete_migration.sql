-- Complete Database Migration Script for APS Dream Homes
-- This script updates the database schema to support the new user and associates structure
-- while maintaining compatibility with existing code

-- Backup existing data
CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user;
CREATE TABLE IF NOT EXISTS associates_backup AS SELECT * FROM associates;

-- Drop tables with foreign key constraints first (if they exist)
DROP TABLE IF EXISTS team_hierarchy;
DROP TABLE IF EXISTS commission_transactions;
DROP TABLE IF EXISTS contact;
DROP TABLE IF EXISTS associate_performance;
DROP TABLE IF EXISTS associates;
DROP TABLE IF EXISTS referrals;

-- Create new users table with enhanced security and features
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('user','agent','builder','associate','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(300) DEFAULT 'default-user.png',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_email` (`email`),
  KEY `idx_user_status` (`status`),
  KEY `idx_user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create associates table with proper foreign key relationships
CREATE TABLE IF NOT EXISTS `associates` (
  `associate_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(10) UNIQUE NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(15) UNIQUE NOT NULL,
  `level` int(11) DEFAULT 1,
  `total_business` decimal(12,2) DEFAULT 0.00,
  `current_month_business` decimal(12,2) DEFAULT 0.00,
  `team_business` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`associate_id`),
  KEY `idx_associate_uid` (`uid`),
  KEY `idx_associate_sponsor` (`sponsor_id`),
  KEY `idx_associate_referral` (`referral_code`),
  CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create associate levels table for commission structure
CREATE TABLE IF NOT EXISTS `associate_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `min_business` decimal(12,2) NOT NULL,
  `max_business` decimal(12,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `reward_description` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create team hierarchy table for tracking relationships
CREATE TABLE IF NOT EXISTS `team_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT 'Level in hierarchy (1 for direct sponsor, 2 for sponsor\'s sponsor, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hierarchy_associate` (`associate_id`),
  KEY `idx_hierarchy_upline` (`upline_id`),
  KEY `idx_hierarchy_level` (`level`),
  CONSTRAINT `fk_hierarchy_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarchy_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create commission transactions table
CREATE TABLE IF NOT EXISTS `commission_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `business_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `level_difference_amount` decimal(10,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`transaction_id`),
  KEY `idx_commission_associate` (`associate_id`),
  KEY `idx_commission_booking` (`booking_id`),
  KEY `idx_commission_upline` (`upline_id`),
  KEY `idx_commission_date` (`transaction_date`),
  CONSTRAINT `fk_commission_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commission_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default associate levels
INSERT INTO `associate_levels` (`level_name`, `min_business`, `max_business`, `commission_percentage`, `reward_description`) VALUES
('Associate', 0, 1000000, 5.00, 'Mobile'),
('Sr. Associate', 1000001, 3500000, 7.00, 'Tablet'),
('Bdm', 3500001, 7000000, 10.00, 'Laptop'),
('Sr. Bdm', 7000001, 15000000, 12.00, 'Domestic/Foreign Tour'),
('Vice President', 15000001, 30000000, 15.00, 'Pulsar Bike'),
('President', 30000001, 50000000, 18.00, 'Bullet'),
('Site Manager', 50000001, 999999999, 20.00, 'Car');

-- Migration procedure for user data
DELIMITER //
CREATE PROCEDURE migrate_user_data()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_uid INT;
    DECLARE v_uname VARCHAR(100);
    DECLARE v_uemail VARCHAR(100);
    DECLARE v_uphone VARCHAR(20);
    DECLARE v_upass VARCHAR(50);
    DECLARE v_utype VARCHAR(50);
    DECLARE v_uimage VARCHAR(300);
    DECLARE v_new_user_id INT;
    DECLARE v_referral_code VARCHAR(10);
    
    -- Cursor for user table
    DECLARE user_cursor CURSOR FOR 
        SELECT uid, uname, uemail, uphone, upass, utype, uimage 
        FROM user_backup;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Start transaction for data integrity
    START TRANSACTION;
    
    OPEN user_cursor;
    
    read_loop: LOOP
        FETCH user_cursor INTO v_uid, v_uname, v_uemail, v_uphone, v_upass, v_utype, v_uimage;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insert into new users table
        INSERT INTO users (name, email, phone, password, user_type, profile_image, created_at)
        VALUES (v_uname, v_uemail, v_uphone, 
                -- Use original password for now, will need manual update later
                v_upass, 
                -- Convert user type if needed
                CASE 
                    WHEN v_utype = 'assosiate' THEN 'associate'
                    ELSE v_utype
                END,
                v_uimage, NOW());
        
        SET v_new_user_id = LAST_INSERT_ID();
        
        -- Generate referral code (simple implementation)
        SET v_referral_code = CONCAT('REF', LPAD(v_new_user_id, 7, '0'));
        
        -- If user is an associate, add to associates table
        IF v_utype = 'assosiate' OR v_utype = 'associate' THEN
            INSERT INTO associates (uid, user_id, referral_code)
            VALUES (CONCAT('A', LPAD(v_uid, 9, '0')), v_new_user_id, v_referral_code);
        END IF;
    END LOOP;
    
    CLOSE user_cursor;
    
    -- Commit the transaction
    COMMIT;
    
 END //
DELIMITER ;

-- Create trigger for team hierarchy maintenance
DELIMITER //
CREATE TRIGGER after_associate_insert
AFTER INSERT ON associates
FOR EACH ROW
BEGIN
    -- Insert direct relationship
    IF NEW.sponsor_id IS NOT NULL THEN
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        SELECT NEW.associate_id, associate_id, 1
        FROM associates
        WHERE uid = NEW.sponsor_id;
        
        -- Insert indirect relationships (up to 7 levels)
        INSERT INTO team_hierarchy (associate_id, upline_id, level)
        SELECT NEW.associate_id, th.upline_id, th.level + 1
        FROM team_hierarchy th
        WHERE th.associate_id = (SELECT associate_id FROM associates WHERE uid = NEW.sponsor_id)
        AND th.level < 7;
    END IF;
END //
DELIMITER ;

-- Add indexes for performance
CREATE INDEX idx_user_name ON users(name);
CREATE INDEX idx_user_phone ON users(phone);

-- Modify existing contact table to support associate assignment
ALTER TABLE contact
ADD COLUMN category ENUM('inquiry', 'complaint', 'feedback', 'support', 'other') DEFAULT 'inquiry',
ADD COLUMN priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
ADD COLUMN assigned_to INT,
ADD COLUMN resolution_date TIMESTAMP,
ADD COLUMN response_time INT,
ADD COLUMN satisfaction_score INT,
ADD CONSTRAINT fk_contact_associate FOREIGN KEY (assigned_to) REFERENCES associates(associate_id) ON DELETE SET NULL;

-- Note: After running this migration, you'll need to update your application code
-- to use the new table structure and field names.

-- To execute the migration:
-- 1. First backup your database
-- 2. Run this script
-- 3. Execute the migration procedure: CALL migrate_user_data();
-- 4. Update your application code to use the new table structure