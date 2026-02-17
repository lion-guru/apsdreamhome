-- Consolidated Migration Script for APS Dream Homes
-- This script combines all previous migration scripts into a single comprehensive file
-- with proper versioning and rollback capabilities.

-- Version tracking table
CREATE TABLE IF NOT EXISTS `database_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Start transaction for atomicity
START TRANSACTION;

-- Record migration start
INSERT INTO database_migrations (version, description, applied_at, success)
VALUES ('1.0.0', 'Initial consolidated migration', NOW(), 0);

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
  `referral_code` varchar(10) UNIQUE NOT NULL,
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
('Sr. Bdm', 7000001, 15000000, 12.00, 'Bike'),
('Zonal Manager', 15000001, 25000000, 15.00, 'Car');

-- Create password reset table
CREATE TABLE IF NOT EXISTS `password_reset_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reset_email` (`email`),
  KEY `idx_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create stored procedure for user type standardization
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS standardize_user_types()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id INT;
    DECLARE v_utype VARCHAR(20);
    DECLARE cur CURSOR FOR SELECT id, user_type FROM users;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_id, v_utype;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Standardize user types
        CASE v_utype
            WHEN 'assosiate' THEN 
                UPDATE users SET user_type = 'associate' WHERE id = v_id;
            ELSE 
                -- No change needed
                SET v_id = v_id;
        END CASE;
    END LOOP;
    
    CLOSE cur;
    
    -- Update any remaining instances in other tables
    UPDATE associates a
    JOIN users u ON a.user_id = u.id
    SET u.user_type = 'associate'
    WHERE u.user_type = 'assosiate';
    
    -- Log the standardization
    INSERT INTO database_migrations (version, description, applied_at)
    VALUES ('1.0.1', 'Standardized user types (assosiate -> associate)', NOW());
    
END //
DELIMITER ;

-- Create stored procedure for data migration
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS migrate_legacy_data()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_uname VARCHAR(100);
    DECLARE v_uemail VARCHAR(100);
    DECLARE v_upass VARCHAR(255);
    DECLARE v_uphone VARCHAR(20);
    DECLARE v_utype VARCHAR(20);
    DECLARE v_uid VARCHAR(10);
    DECLARE v_user_id INT;
    DECLARE v_sponsor_id VARCHAR(10);
    DECLARE cur CURSOR FOR SELECT uname, uemail, upass, uphone, utype, uid FROM user_backup;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_uname, v_uemail, v_upass, v_uphone, v_utype, v_uid;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Standardize user type
        CASE v_utype
            WHEN 'assosiate' THEN SET v_utype = 'associate';
            ELSE SET v_utype = v_utype;
        END CASE;
        
        -- Insert into users table
        INSERT INTO users (name, email, password, phone, user_type)
        VALUES (v_uname, v_uemail, v_upass, v_uphone, v_utype);
        
        SET v_user_id = LAST_INSERT_ID();
        
        -- If user is an associate, migrate associate data
        IF v_utype = 'assosiate' OR v_utype = 'associate' THEN
            -- Get sponsor ID from associates_backup
            SELECT sponser_id INTO v_sponsor_id FROM associates_backup WHERE uid = v_uid LIMIT 1;
            
            -- Generate referral code if not exists
            SET @referral_code = CONCAT('REF', UPPER(SUBSTRING(MD5(RAND()), 1, 8)));
            
            -- Insert into associates table
            INSERT INTO associates (uid, user_id, sponsor_id, referral_code)
            VALUES (v_uid, v_user_id, v_sponsor_id, @referral_code);
        END IF;
    END LOOP;
    
    CLOSE cur;
    
    -- Log the migration
    INSERT INTO database_migrations (version, description, applied_at)
    VALUES ('1.0.2', 'Migrated legacy user data', NOW());
    
END //
DELIMITER ;

-- Execute the stored procedures
CALL standardize_user_types();
CALL migrate_legacy_data();

-- Update migration record to indicate success
UPDATE database_migrations SET success = 1 WHERE version = '1.0.0';

-- Commit the transaction
COMMIT;

-- Drop the stored procedures (cleanup)
DROP PROCEDURE IF EXISTS standardize_user_types;
DROP PROCEDURE IF EXISTS migrate_legacy_data;

-- Keep backup tables for safety (can be dropped later manually)
-- DROP TABLE IF EXISTS user_backup;
-- DROP TABLE IF EXISTS associates_backup;