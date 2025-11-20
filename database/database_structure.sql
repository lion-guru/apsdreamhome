-- Database Structure Export for apsdreamhome
-- Generated on 2025-03-22 13:45:27

-- --------------------------------------------------------
-- Server version: 10.4.32-MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `apsdreamhome`
--

CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apsdreamhome`;

-- --------------------------------------------------------
-- Drop existing tables
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `about`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `associate_levels`;
DROP TABLE IF EXISTS `associates`;
DROP TABLE IF EXISTS `associates_backup`;
DROP TABLE IF EXISTS `booking_payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `career_applications`;
DROP TABLE IF EXISTS `city`;
DROP TABLE IF EXISTS `commission_transactions`;
DROP TABLE IF EXISTS `contact_backup`;
DROP TABLE IF EXISTS `feedback`;
DROP TABLE IF EXISTS `gata_master`;
DROP TABLE IF EXISTS `images`;
DROP TABLE IF EXISTS `job_applications`;
DROP TABLE IF EXISTS `kissan_master`;
DROP TABLE IF EXISTS `password_reset_temp`;
DROP TABLE IF EXISTS `plot_categories`;
DROP TABLE IF EXISTS `plot_master`;
DROP TABLE IF EXISTS `plots`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `property`;
DROP TABLE IF EXISTS `site_master`;
DROP TABLE IF EXISTS `sponsor_running_no`;
DROP TABLE IF EXISTS `state`;
DROP TABLE IF EXISTS `team_hierarchy`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `user_backup`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table structure
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Table structure for table `about`
-- --------------------------------------------------------

CREATE TABLE `about` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `about` contains data

-- --------------------------------------------------------
-- Table structure for table `admin`
-- --------------------------------------------------------

CREATE TABLE `admin` (
  `aid` int(10) NOT NULL AUTO_INCREMENT,
  `auser` varchar(50) NOT NULL,
  `aemail` varchar(50) NOT NULL CHECK (`aemail` regexp '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]{2,}$'),
  `apass` varchar(255) NOT NULL,
  `adob` date NOT NULL,
  `aphone` varchar(15) NOT NULL CHECK (`aphone` regexp '^[0-9]{10,15}$'),
  PRIMARY KEY (`aid`),
  UNIQUE KEY `aemail` (`aemail`),
  UNIQUE KEY `aemail_2` (`aemail`),
  UNIQUE KEY `unique_admin_email` (`aemail`),
  KEY `idx_email` (`aemail`),
  KEY `idx_admin_user` (`auser`),
  CONSTRAINT `check_email` CHECK (`aemail` regexp '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$'),
  CONSTRAINT `check_phone` CHECK (`aphone` regexp '^[0-9]{10,15}$')
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `admin` contains data

-- --------------------------------------------------------
-- Table structure for table `associate_levels`
-- --------------------------------------------------------

CREATE TABLE `associate_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `min_business` decimal(12,2) NOT NULL,
  `max_business` decimal(12,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `reward_description` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `associate_levels` contains data

-- --------------------------------------------------------
-- Table structure for table `associates`
-- --------------------------------------------------------

CREATE TABLE `associates` (
  `associate_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(10) NOT NULL,
  `level` int(11) DEFAULT 1,
  `total_business` decimal(12,2) DEFAULT 0.00,
  `current_month_business` decimal(12,2) DEFAULT 0.00,
  `team_business` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`associate_id`),
  UNIQUE KEY `uid` (`uid`),
  UNIQUE KEY `referral_code` (`referral_code`),
  KEY `idx_associate_uid` (`uid`),
  KEY `idx_associate_sponsor` (`sponsor_id`),
  KEY `idx_associate_referral` (`referral_code`),
  KEY `fk_associate_user` (`user_id`),
  CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `associates` is empty

-- --------------------------------------------------------
-- Table structure for table `associates_backup`
-- --------------------------------------------------------

CREATE TABLE `associates_backup` (
  `associate_id` int(11) NOT NULL DEFAULT 0,
  `uid` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `associates_backup` is empty

-- --------------------------------------------------------
-- Table structure for table `booking_payments`
-- --------------------------------------------------------

CREATE TABLE `booking_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `booking_id` (`booking_id`),
  KEY `idx_booking_payment_date` (`payment_date`),
  CONSTRAINT `booking_payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `booking_payments` is empty

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `property_type` int(11) NOT NULL,
  `installment_plan` varchar(255) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `next_payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `property_location` varchar(100) DEFAULT NULL,
  `payment_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_history`)),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `idx_customer_name` (`customer_name`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_booking_date` (`booking_date`),
  KEY `idx_booking_customer_name` (`customer_name`),
  KEY `idx_booking_dates` (`booking_date`,`next_payment_date`),
  KEY `idx_booking_status` (`status`,`payment_status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `bookings` contains data

-- --------------------------------------------------------
-- Table structure for table `career_applications`
-- --------------------------------------------------------

CREATE TABLE `career_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_career_email` (`email`),
  KEY `idx_career_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `career_applications` contains data

-- --------------------------------------------------------
-- Table structure for table `city`
-- --------------------------------------------------------

CREATE TABLE `city` (
  `cid` int(50) NOT NULL AUTO_INCREMENT,
  `cname` varchar(100) NOT NULL,
  `sid` int(50) NOT NULL,
  PRIMARY KEY (`cid`),
  KEY `fk_state_city` (`sid`),
  CONSTRAINT `fk_state_city` FOREIGN KEY (`sid`) REFERENCES `state` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `city` contains data

-- --------------------------------------------------------
-- Table structure for table `commission_transactions`
-- --------------------------------------------------------

CREATE TABLE `commission_transactions` (
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

-- Table `commission_transactions` is empty

-- --------------------------------------------------------
-- Table structure for table `contact_backup`
-- --------------------------------------------------------

CREATE TABLE `contact_backup` (
  `cid` int(50) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `contact_backup` contains data

-- --------------------------------------------------------
-- Table structure for table `feedback`
-- --------------------------------------------------------

CREATE TABLE `feedback` (
  `fid` int(50) NOT NULL AUTO_INCREMENT,
  `uid` int(50) NOT NULL,
  `fdescription` varchar(300) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `feedback` contains data

-- --------------------------------------------------------
-- Table structure for table `gata_master`
-- --------------------------------------------------------

CREATE TABLE `gata_master` (
  `gata_id` int(11) NOT NULL,
  `site_id` int(25) NOT NULL,
  `gata_no` varchar(50) NOT NULL,
  `area` float NOT NULL,
  `available_area` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `gata_master` contains data

-- --------------------------------------------------------
-- Table structure for table `images`
-- --------------------------------------------------------

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `title` varchar(100) NOT NULL,
  `image` longblob DEFAULT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `images` contains data

-- --------------------------------------------------------
-- Table structure for table `job_applications`
-- --------------------------------------------------------

CREATE TABLE `job_applications` (
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `job_applications` contains data

-- --------------------------------------------------------
-- Table structure for table `kissan_master`
-- --------------------------------------------------------

CREATE TABLE `kissan_master` (
  `kissan_id` int(25) NOT NULL,
  `site_id` int(25) NOT NULL,
  `gata_a` int(25) NOT NULL,
  `gata_b` int(25) DEFAULT NULL,
  `gata_c` int(25) DEFAULT NULL,
  `gata_d` int(25) DEFAULT NULL,
  `area_gata_a` float NOT NULL DEFAULT 0,
  `area_gata_b` float DEFAULT 0,
  `area_gata_c` float DEFAULT 0,
  `area_gata_d` float DEFAULT 0,
  `k_name` varchar(200) NOT NULL,
  `k_adhaar` int(12) NOT NULL,
  `area` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `kissan_master` contains data

-- --------------------------------------------------------
-- Table structure for table `password_reset_temp`
-- --------------------------------------------------------

CREATE TABLE `password_reset_temp` (
  `email` varchar(250) NOT NULL,
  `key` varchar(250) NOT NULL,
  `expDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `password_reset_temp` is empty

-- --------------------------------------------------------
-- Table structure for table `plot_categories`
-- --------------------------------------------------------

CREATE TABLE `plot_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `plot_categories` contains data

-- --------------------------------------------------------
-- Table structure for table `plot_master`
-- --------------------------------------------------------

CREATE TABLE `plot_master` (
  `plot_id` int(25) NOT NULL,
  `site_id` int(25) NOT NULL,
  `gata_a` int(25) NOT NULL,
  `gata_b` int(25) DEFAULT NULL,
  `gata_c` int(25) DEFAULT NULL,
  `gata_d` int(25) DEFAULT NULL,
  `area_gata_a` float NOT NULL DEFAULT 0,
  `area_gata_b` float DEFAULT 0,
  `area_gata_c` float DEFAULT 0,
  `area_gata_d` float DEFAULT 0,
  `plot_no` varchar(200) NOT NULL,
  `area` float NOT NULL,
  `available_area` float NOT NULL,
  `plot_dimension` varchar(100) DEFAULT NULL,
  `plot_facing` varchar(100) DEFAULT NULL,
  `plot_price` float DEFAULT NULL,
  `plot_status` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `plot_master` contains data

-- --------------------------------------------------------
-- Table structure for table `plots`
-- --------------------------------------------------------

CREATE TABLE `plots` (
  `id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `block_code` varchar(255) NOT NULL,
  `plot_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `breadth` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  `total_size` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `plots` contains data

-- --------------------------------------------------------
-- Table structure for table `projects`
-- --------------------------------------------------------

CREATE TABLE `projects` (
  `bid` int(11) NOT NULL,
  `builder_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `projects` contains data

-- --------------------------------------------------------
-- Table structure for table `property`
-- --------------------------------------------------------

CREATE TABLE `property` (
  `pid` int(50) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `pcontent` longtext NOT NULL,
  `type` varchar(100) NOT NULL,
  `bhk` varchar(50) NOT NULL,
  `stype` varchar(100) NOT NULL,
  `bedroom` int(50) NOT NULL,
  `bathroom` int(50) NOT NULL,
  `balcony` int(50) NOT NULL,
  `kitchen` int(50) NOT NULL,
  `hall` int(50) NOT NULL,
  `floor` varchar(50) NOT NULL,
  `size` int(50) NOT NULL,
  `price` int(50) NOT NULL,
  `location` varchar(200) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `feature` longtext NOT NULL,
  `pimage` varchar(300) NOT NULL,
  `pimage1` varchar(300) NOT NULL,
  `pimage2` varchar(300) NOT NULL,
  `pimage3` varchar(300) NOT NULL,
  `pimage4` varchar(300) NOT NULL,
  `uid` int(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `mapimage` varchar(300) NOT NULL,
  `topmapimage` varchar(300) NOT NULL,
  `groundmapimage` varchar(300) NOT NULL,
  `totalfloor` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `isFeatured` int(11) DEFAULT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `property` contains data

-- --------------------------------------------------------
-- Table structure for table `site_master`
-- --------------------------------------------------------

CREATE TABLE `site_master` (
  `site_id` int(10) NOT NULL,
  `site_name` varchar(200) NOT NULL,
  `district` varchar(100) NOT NULL,
  `tehsil` varchar(200) NOT NULL,
  `gram` varchar(300) NOT NULL,
  `area` float NOT NULL,
  `available_area` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Table `site_master` contains data

-- --------------------------------------------------------
-- Table structure for table `sponsor_running_no`
-- --------------------------------------------------------

CREATE TABLE `sponsor_running_no` (
  `current_no` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `sponsor_running_no` contains data

-- --------------------------------------------------------
-- Table structure for table `state`
-- --------------------------------------------------------

CREATE TABLE `state` (
  `sid` int(50) NOT NULL AUTO_INCREMENT,
  `sname` varchar(100) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `state` contains data

-- --------------------------------------------------------
-- Table structure for table `team_hierarchy`
-- --------------------------------------------------------

CREATE TABLE `team_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT 'Level in hierarchy (1 for direct sponsor, 2 for sponsor''s sponsor, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hierarchy_associate` (`associate_id`),
  KEY `idx_hierarchy_upline` (`upline_id`),
  KEY `idx_hierarchy_level` (`level`),
  CONSTRAINT `fk_hierarchy_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hierarchy_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `team_hierarchy` is empty

-- --------------------------------------------------------
-- Table structure for table `user`
-- --------------------------------------------------------

CREATE TABLE `user` (
  `uid` int(50) NOT NULL AUTO_INCREMENT,
  `sponsor_id` varchar(100) DEFAULT NULL,
  `sponsored_by` varchar(100) DEFAULT NULL,
  `uname` varchar(100) NOT NULL,
  `uemail` varchar(100) NOT NULL,
  `uphone` varchar(20) NOT NULL,
  `upass` varchar(50) NOT NULL,
  `utype` varchar(100) DEFAULT NULL,
  `uimage` varchar(300) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(11) DEFAULT NULL,
  `bank_micr` varchar(100) DEFAULT NULL,
  `bank_branch` varchar(200) DEFAULT NULL,
  `bank_district` varchar(200) DEFAULT NULL,
  `bank_state` varchar(200) DEFAULT NULL,
  `account_type` enum('savings','current','fixed') DEFAULT 'savings',
  `pan` varchar(10) DEFAULT NULL,
  `adhaar` int(12) DEFAULT NULL,
  `nominee_name` varchar(100) DEFAULT NULL,
  `nominee_relation` varchar(50) DEFAULT NULL,
  `nominee_contact` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `join_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_updated` varchar(1) NOT NULL,
  `job_role` varchar(50) NOT NULL DEFAULT 'Associate',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `sponsor_id` (`sponsor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `user` contains data

-- --------------------------------------------------------
-- Table structure for table `user_backup`
-- --------------------------------------------------------

CREATE TABLE `user_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `utype` enum('user','agent','builder') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `user_backup` contains data

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `utype` enum('user','agent','builder') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_email` (`email`),
  KEY `idx_user_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table `users` contains data

-- --------------------------------------------------------
-- Stored procedures and functions
-- --------------------------------------------------------

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `migrate_user_data_with_password_handling`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_uid INT;
    DECLARE v_uname VARCHAR(100);
    DECLARE v_uemail VARCHAR(100);
    DECLARE v_uphone VARCHAR(20);
    DECLARE v_upass VARCHAR(255); -- Increased length to accommodate bcrypt hashes
    DECLARE v_utype VARCHAR(50);
    DECLARE v_uimage VARCHAR(300);
    DECLARE v_new_user_id INT;
    DECLARE v_referral_code VARCHAR(10);
    DECLARE user_backup_exists INT;
    DECLARE v_temp_password VARCHAR(20);
    
    -- Declare cursor and handler before any executable statements
    DECLARE user_cursor CURSOR FOR 
        SELECT uid, uname, uemail, uphone, upass, utype, IFNULL(uimage, 'default-user.png') 
        FROM user_backup;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Check if user_backup table exists
    SELECT COUNT(*) INTO user_backup_exists 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'user_backup';
    
    -- Only proceed with migration if backup exists
    IF user_backup_exists > 0 THEN
        -- Determine the column names in user_backup
        SET @has_uid = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_backup' 
                        AND column_name = 'uid');
        
        SET @has_uname = (SELECT COUNT(*) FROM information_schema.columns 
                        WHERE table_schema = DATABASE() 
                        AND table_name = 'user_backup' 
                        AND column_name = 'uname');
        
        -- Adjust cursor based on available columns
        IF @has_uid > 0 AND @has_uname > 0 THEN
            
            -- Start transaction for data integrity
            START TRANSACTION;
            
            -- Create a temporary table to store migration results for reporting
            CREATE TEMPORARY TABLE IF NOT EXISTS migration_results (
                email VARCHAR(100),
                status VARCHAR(50),
                message VARCHAR(255)
            );
            
            OPEN user_cursor;
            
            read_loop: LOOP
                FETCH user_cursor INTO v_uid, v_uname, v_uemail, v_uphone, v_upass, v_utype, v_uimage;
                
                IF done THEN
                    LEAVE read_loop;
                END IF;
                
                -- Check password format to determine if it needs conversion
                -- Most legacy systems use MD5 or SHA1 (32 or 40 chars)
                IF LENGTH(v_upass) = 32 OR LENGTH(v_upass) = 40 THEN
                    -- For passwords that are already hashed but not in bcrypt format
                    -- We can't convert directly, so we'll set a temporary password
                    -- and flag the account for password reset
                    SET v_temp_password = CONCAT('Temp', FLOOR(RAND() * 1000000));
                    
                    -- Insert into new users table with temporary password
                    INSERT INTO users (name, email, phone, password, user_type, profile_image, created_at, status)
                    VALUES (v_uname, v_uemail, v_uphone, 
                            -- Use PHP password_hash equivalent in MySQL (if available)
                            -- Otherwise, store with a flag for reset
                            CONCAT('RESET_REQUIRED:', v_upass), 
                            -- Convert user type if needed
                            CASE 
                                WHEN v_utype = 'assosiate' THEN 'associate'
                                ELSE v_utype
                            END,
                            v_uimage, NOW(), 'inactive');
                    
                    -- Log the migration status
                    INSERT INTO migration_results (email, status, message)
                    VALUES (v_uemail, 'password_reset_required', 'Legacy password format detected, reset required');
                ELSE
                    -- For passwords that might be in plaintext or already in bcrypt format
                    INSERT INTO users (name, email, phone, password, user_type, profile_image, created_at)
                    VALUES (v_uname, v_uemail, v_uphone, 
                            -- Use original password
                            v_upass, 
                            -- Convert user type if needed
                            CASE 
                                WHEN v_utype = 'assosiate' THEN 'associate'
                                ELSE v_utype
                            END,
                            v_uimage, NOW());
                    
                    -- Log the migration status
                    INSERT INTO migration_results (email, status, message)
                    VALUES (v_uemail, 'migrated', 'User migrated successfully');
                END IF;
                
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
            
            -- Output migration results
            SELECT * FROM migration_results;
            
            -- Drop temporary table
            DROP TEMPORARY TABLE IF EXISTS migration_results;
            
            -- Commit the transaction
            COMMIT;
            
            -- Output summary
            SELECT 'Migration completed. Some accounts may require password reset.' AS message;
        ELSE
            -- Handle case where column names are different
            SELECT 'User backup table has different column structure than expected' AS message;
        END IF;
    ELSE
        SELECT 'No user backup table found, skipping migration' AS message;
    END IF;
 END//
DELIMITER ;

DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_migrated_passwords`()
BEGIN
    -- Create a temporary table to store users needing password reset
    CREATE TEMPORARY TABLE users_to_reset AS
    SELECT id, email 
    FROM users 
    WHERE password LIKE 'RESET_REQUIRED:%';
    
    -- Output the list of users needing password reset
    SELECT * FROM users_to_reset;
    
    -- Drop temporary table
    DROP TEMPORARY TABLE IF EXISTS users_to_reset;
    
    -- Output instructions
    SELECT 'To complete migration, send password reset emails to these users' AS next_steps;
END//
DELIMITER ;

-- --------------------------------------------------------
-- End of database structure export
-- --------------------------------------------------------
