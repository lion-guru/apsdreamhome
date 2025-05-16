-- User and Associate Separation Migration Script for APS Dream Homes
-- This script updates the database schema to properly separate user and associate data

-- Start transaction for atomicity
START TRANSACTION;

-- Create backup of existing tables
CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user;
CREATE TABLE IF NOT EXISTS associates_backup AS SELECT * FROM associates;

-- Check if users table exists, if not create it
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_type` enum('user','associate','admin') NOT NULL DEFAULT 'user',
  `profile_image` varchar(300) DEFAULT 'default-user.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Check if associates table exists, if not create it with proper structure
CREATE TABLE IF NOT EXISTS `associates` (
  `associate_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(10) UNIQUE NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(10) UNIQUE NOT NULL,
  `level` int(11) DEFAULT 1,
  `total_business` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`associate_id`),
  KEY `idx_associate_uid` (`uid`),
  KEY `idx_associate_sponsor` (`sponsor_id`),
  KEY `idx_associate_referral` (`referral_code`),
  CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create team hierarchy table for tracking relationships if it doesn't exist
CREATE TABLE IF NOT EXISTS `team_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT 'Level in hierarchy (1 for direct sponsor, 2 for sponsor\'s sponsor, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hierarchy_relationship` (`associate_id`, `upline_id`),
  KEY `idx_hierarchy_associate` (`associate_id`),
  KEY `idx_hierarchy_upline` (`upline_id`),
  KEY `idx_hierarchy_level` (`level`),
  KEY `idx_hierarchy_status` (`status`),
  CONSTRAINT `fk_hierarchy_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarchy_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create error logging table if not exists
CREATE TABLE IF NOT EXISTS `migration_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_message` text NOT NULL,
  `affected_uid` varchar(10) DEFAULT NULL,
  `error_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_error_time` (`error_time`),
  KEY `idx_affected_uid` (`affected_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migration procedure for user and associate data
DELIMITER //

-- Drop existing procedure if it exists
DROP PROCEDURE IF EXISTS migrate_user_associate_data //

CREATE PROCEDURE migrate_user_associate_data()
BEGIN
    -- Declare variables
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_uid VARCHAR(10);
    DECLARE v_name VARCHAR(100);
    DECLARE v_email VARCHAR(100);
    DECLARE v_phone VARCHAR(20);
    DECLARE v_password VARCHAR(255);
    DECLARE v_sponsor_id VARCHAR(10);
    DECLARE v_user_id INT;
    DECLARE v_referral_code VARCHAR(10);
    DECLARE error_msg TEXT DEFAULT '';
    DECLARE v_sqlstate CHAR(5);
    DECLARE v_errno INT;
    DECLARE v_text TEXT;
    
    -- Declare cursor
    DECLARE associate_cursor CURSOR FOR 
        SELECT uid, name, email, phone, password, sponsor_id 
        FROM associates_backup;
    
    -- Declare handlers
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    DECLARE CONTINUE HANDLER FOR 1329 SET v_user_id = NULL;
    DECLARE CONTINUE HANDLER FOR 1062
    BEGIN
        DECLARE v_user_id_str VARCHAR(20);
        DECLARE v_temp_msg TEXT;
        SELECT id INTO v_user_id FROM users WHERE email = v_email LIMIT 1;
        SET v_user_id_str = IFNULL(CAST(v_user_id AS CHAR), 'NULL');
        SET v_temp_msg = CONCAT('Duplicate entry found for email: ', IFNULL(v_email, 'NULL'), '. Using existing user ID: ', v_user_id_str);
        INSERT INTO migration_errors (error_message, affected_uid) VALUES (v_temp_msg, v_uid);
        SET error_msg = '';
    END;

    -- Declare EXIT handler for SQL exceptions
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            v_sqlstate = RETURNED_SQLSTATE,
            v_errno = MYSQL_ERRNO,
            v_text = MESSAGE_TEXT;
        INSERT INTO migration_errors (error_message, affected_uid) 
        VALUES (CONCAT('Error: ', v_errno, ' State: ', v_sqlstate, ' Message: ', v_text), v_uid);
        ROLLBACK;
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = CONCAT('Migration error: ', v_errno, ' - ', v_text);
    END;

    -- Start transaction for data integrity
    START TRANSACTION;

    -- Log migration start
    INSERT INTO migration_errors (error_message, affected_uid) 
    VALUES ('Migration started', NULL);

    -- Declare additional variables for team hierarchy
    DECLARE v_associate_id INT;
    DECLARE v_upline_id INT;
    DECLARE v_level INT;
    DECLARE v_max_level INT DEFAULT 7;
    DECLARE v_current_level INT DEFAULT 1;

    -- Validate backup tables exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.tables 
                  WHERE table_schema = DATABASE() 
                  AND table_name = 'associates_backup') THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'associates_backup table does not exist';
    END IF;

    -- Open cursor and start migration
    OPEN associate_cursor;

    read_loop: LOOP
        FETCH associate_cursor INTO v_uid, v_name, v_email, v_phone, v_password, v_sponsor_id;

        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Check if user already exists with this email
        SELECT id INTO v_user_id FROM users WHERE email = v_email LIMIT 1;

        IF v_user_id IS NULL THEN
            -- Insert new user
            INSERT INTO users (name, email, password, phone, user_type, created_at)
            VALUES (v_name, v_email, v_password, v_phone, 'associate', NOW());

            SET v_user_id = LAST_INSERT_ID();

            -- Log new user creation
            INSERT INTO migration_errors (error_message, affected_uid)
            VALUES (CONCAT('Created new user for associate: ', v_uid), v_uid);
        ELSE
            -- Update existing user to associate type if not already
            UPDATE users SET 
                user_type = 'associate',
                name = COALESCE(v_name, name),
                phone = COALESCE(v_phone, phone),
                updated_at = NOW()
            WHERE id = v_user_id AND user_type != 'associate';

            -- Log user update
            IF ROW_COUNT() > 0 THEN
                INSERT INTO migration_errors (error_message, affected_uid)
                VALUES (CONCAT('Updated existing user to associate type: ', v_uid), v_uid);
            END IF;
        END IF;

        -- Generate referral code
        SET v_referral_code = CONCAT('REF', LPAD(v_user_id, 7, '0'));

        -- Validate data before insertion
        IF v_uid IS NULL OR v_user_id IS NULL THEN
            INSERT INTO migration_errors (error_message, affected_uid)
            VALUES (CONCAT('Invalid data: uid=', IFNULL(v_uid, 'NULL'), ', user_id=', IFNULL(v_user_id, 'NULL')), v_uid);
            ITERATE read_loop;
        END IF;

        -- Insert associate if not exists
        IF NOT EXISTS (SELECT 1 FROM associates WHERE uid = v_uid) THEN
            INSERT INTO associates (uid, user_id, sponsor_id, referral_code, join_date)
            VALUES (v_uid, v_user_id, v_sponsor_id, v_referral_code, NOW());

            -- Log successful migration
            INSERT INTO migration_errors (error_message, affected_uid)
            VALUES (CONCAT('Successfully migrated associate: ', v_uid), v_uid);

            -- Copy additional data from backup if available
            UPDATE associates a
            INNER JOIN associates_backup ab ON ab.uid = a.uid
            SET 
                a.level = ab.level,
                a.total_business = ab.total_business,
                a.current_month_business = ab.current_month_business,
                a.team_business = ab.team_business,
                a.bank_name = ab.bank_name,
                a.account_number = ab.account_number,
                a.ifsc_code = ab.ifsc_code,
                a.bank_micr = ab.bank_micr,
                a.bank_branch = ab.bank_branch,
                a.bank_district = ab.bank_district,
                a.bank_state = ab.bank_state,
                a.account_type = ab.account_type,
                a.pan = ab.pan,
                a.adhaar = ab.adhaar,
                a.nominee_name = ab.nominee_name,
                a.nominee_relation = ab.nominee_relation,
                a.nominee_contact = ab.nominee_contact,
                a.address = ab.address,
                a.date_of_birth = ab.date_of_birth,
                a.is_updated = ab.is_updated,
                a.status = ab.status
            WHERE a.uid = v_uid;
        END IF;
    END LOOP;

    CLOSE associate_cursor;

    -- Build team hierarchy for all associates
    INSERT INTO team_hierarchy (associate_id, upline_id, level)
    SELECT a1.associate_id, a2.associate_id, 1
    FROM associates a1
    INNER JOIN associates a2 ON a1.sponsor_id = a2.uid
    WHERE a1.sponsor_id IS NOT NULL
    ON DUPLICATE KEY UPDATE level = VALUES(level);

    -- Build indirect relationships (up to 7 levels)
    SET v_current_level = 1;

    hierarchy_loop: WHILE v_current_level < v_max_level DO
        BEGIN
            DECLARE CONTINUE HANDLER FOR 1452
            BEGIN
                -- Log foreign key constraint violation
                INSERT INTO migration_errors (error_message, affected_uid)
                VALUES (CONCAT('Foreign key constraint violation at level ', v_current_level + 1), NULL);
                ITERATE hierarchy_loop;
            END;

            INSERT IGNORE INTO team_hierarchy (associate_id, upline_id, level)
            SELECT DISTINCT h1.associate_id, h2.upline_id, v_current_level + 1
            FROM team_hierarchy h1
            JOIN team_hierarchy h2 ON h1.upline_id = h2.associate_id
            WHERE h1.level = v_current_level;
            
            IF ROW_COUNT() = 0 THEN
                LEAVE hierarchy_loop;
            END IF;
            
            -- Log successful level creation
            INSERT INTO migration_errors (error_message, affected_uid)
            VALUES (CONCAT('Successfully built team hierarchy level ', v_current_level + 1), NULL);
            
            SET v_current_level = v_current_level + 1;
        END;
    END WHILE hierarchy_loop;

    -- Log any errors in team hierarchy building
    IF v_current_level = v_max_level THEN
        INSERT INTO migration_errors (error_message, affected_uid)
        VALUES ('Warning: Maximum team hierarchy level reached', NULL);
    END IF;

    -- Log team hierarchy building completion
    INSERT INTO migration_errors (error_message, affected_uid)
    VALUES ('Team hierarchy building completed', NULL);

    -- Log successful completion
    INSERT INTO migration_errors (error_message, affected_uid)
    VALUES ('Migration completed successfully', NULL);

    -- Commit the transaction
    COMMIT;

END //
DELIMITER ;

-- Create trigger for team hierarchy maintenance
DELIMITER //
CREATE TRIGGER IF NOT EXISTS after_associate_insert
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
        JOIN associates a ON th.associate_id = a.associate_id
        WHERE a.uid = NEW.sponsor_id
        AND th.level < 7;
    END IF;
END //
DELIMITER ;

-- Execute the migration procedure
CALL migrate_user_associate_data();

-- Commit the changes
COMMIT;