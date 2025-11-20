-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 01:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apsdreamhomes`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `migrate_user_data_with_password_handling` ()   BEGIN
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
 END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_migrated_passwords` ()   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `about`
--

CREATE TABLE `about` (
  `id` int(10) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about`
--

INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
(10, 'About Us', '...your existing content...', 'condos-pool.png');

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `type` enum('call','email','meeting','task','note') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aid` int(10) NOT NULL,
  `auser` varchar(50) NOT NULL,
  `aemail` varchar(50) NOT NULL,
  `apass` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `adob` date NOT NULL,
  `aphone` varchar(15) NOT NULL CHECK (`aphone` regexp '^[0-9]{10,15}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aid`, `auser`, `aemail`, `apass`, `role`, `status`, `adob`, `aphone`) VALUES
(14, 'admin', 'abhay3007@live.com', '$2y$10$Y6NLTnc3Wq8V5qXR6q1MceBl6X5QltEobQ/RKDnMU/Sw.TeMvyy9e', 'admin', 'active', '2025-04-25', '07007444842');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sales` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_chatbot_interactions`
--

CREATE TABLE `ai_chatbot_interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `query` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `satisfaction_score` decimal(2,1) DEFAULT NULL,
  `response_time` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `associates`
--

CREATE TABLE `associates` (
  `associate_id` int(11) NOT NULL,
  `uid` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(50) NOT NULL,
  `level` int(11) DEFAULT 1,
  `total_business` decimal(12,2) DEFAULT 0.00,
  `current_month_business` decimal(12,2) DEFAULT 0.00,
  `team_business` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_level_id` int(11) DEFAULT 1,
  `reward_earned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associates`
--

INSERT INTO `associates` (`associate_id`, `uid`, `user_id`, `sponsor_id`, `referral_code`, `level`, `total_business`, `current_month_business`, `team_business`, `created_at`, `updated_at`, `current_level_id`, `reward_earned`) VALUES
(1, 'APS000001', 14, NULL, 'UQL52ET1', 1, 0.00, 0.00, 0.00, '2025-03-27 20:21:30', '2025-03-27 20:21:30', 1, 0),
(2, 'APS000002', 32, 'APS000001', 'REF0DBBED17', 1, 0.00, 0.00, 0.00, '2025-03-29 20:57:10', '2025-03-29 20:57:10', 1, 0),
(3, 'APS000003', 33, 'APS000001', 'REFE55F7B50', 1, 0.00, 0.00, 0.00, '2025-04-01 18:05:40', '2025-04-01 18:05:40', 1, 0),
(4, 'APS000004', 34, 'APS000002', 'REF5D96203A', 1, 0.00, 0.00, 0.00, '2025-04-02 19:30:27', '2025-04-02 19:30:27', 1, 0),
(5, 'APS000005', 35, 'APS000002', 'REFCDED7006', 1, 0.00, 0.00, 0.00, '2025-04-02 19:32:32', '2025-04-02 19:32:32', 1, 0),
(6, 'APS000006', 36, 'APS000004', 'REF6CAAF528', 1, 0.00, 0.00, 0.00, '2025-04-02 19:34:06', '2025-04-02 19:34:06', 1, 0),
(7, 'APS000007', 37, 'APS000006', 'REF611BD726', 1, 0.00, 0.00, 0.00, '2025-04-02 19:35:00', '2025-04-02 19:35:00', 1, 0);

--
-- Triggers `associates`
--
DELIMITER $$
CREATE TRIGGER `after_associate_insert` AFTER INSERT ON `associates` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `associates_backup`
--

CREATE TABLE `associates_backup` (
  `associate_id` int(11) NOT NULL DEFAULT 0,
  `uid` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sponsor_id` varchar(10) DEFAULT NULL,
  `referral_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `associate_levels`
--

CREATE TABLE `associate_levels` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `min_business` decimal(12,2) NOT NULL,
  `max_business` decimal(12,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `reward_description` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `commission_rate` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `associate_levels`
--

INSERT INTO `associate_levels` (`level_id`, `level_name`, `min_business`, `max_business`, `commission_percentage`, `reward_description`, `created_at`, `updated_at`, `commission_rate`) VALUES
(1, 'Associate', 0.00, 1000000.00, 5.00, 'Mobile', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(2, 'Sr. Associate', 1000001.00, 3500000.00, 7.00, 'Tablet', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(3, 'Bdm', 3500001.00, 7000000.00, 10.00, 'Laptop', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(4, 'Sr. Bdm', 7000001.00, 15000000.00, 12.00, 'Domestic/Foreign Tour', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(5, 'Vice President', 15000001.00, 30000000.00, 15.00, 'Pulsar Bike', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(6, 'President', 30000001.00, 50000000.00, 18.00, 'Bullet', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(7, 'Site Manager', 50000001.00, 999999999.00, 20.00, 'Car', '2025-03-21 06:07:02', '2025-03-21 06:07:02', 0.00),
(44, 'Sr. Associate', 1000001.00, 3500000.00, 7.00, 'Tablet', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00),
(45, 'Bdm', 3500001.00, 7000000.00, 10.00, 'Laptop', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00),
(46, 'Sr. Bdm', 7000001.00, 15000000.00, 12.00, 'Domestic/Foreign Tour', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00),
(47, 'Vice President', 15000001.00, 30000000.00, 15.00, 'Pulsar Bike', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00),
(48, 'President', 30000001.00, 50000000.00, 18.00, 'Bullet', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00),
(49, 'Site Manager', 50000001.00, 999999999.00, 20.00, 'Car', '2025-04-18 11:37:15', '2025-04-18 11:37:15', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `changes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `id`, `customer_name`, `property_type`, `installment_plan`, `booking_date`, `status`, `payment_status`, `amount`, `paid_amount`, `next_payment_date`, `notes`, `customer_email`, `customer_phone`, `property_id`, `property_location`, `payment_history`, `last_updated`, `deleted_at`) VALUES
(1, 1, 'gfggf', 1, '36', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL),
(2, 2, 'hffhfhv', 1, '25', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL),
(3, 1, 'gfggf', 1, '36', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL),
(4, 2, 'hffhfhv', 1, '25', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL),
(5, 1, 'gfggf', 1, '36', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL),
(6, 2, 'hffhfhv', 1, '25', '2025-03-19 16:15:14', 'pending', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-19 16:34:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_payments`
--

CREATE TABLE `booking_payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `campaign_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` enum('planned','active','completed','cancelled') NOT NULL DEFAULT 'planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `expected_revenue` decimal(12,2) DEFAULT NULL,
  `actual_cost` decimal(12,2) DEFAULT NULL,
  `actual_revenue` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaign_members`
--

CREATE TABLE `campaign_members` (
  `member_id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `status` enum('sent','opened','clicked','responded','converted','unsubscribed') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_applications`
--

CREATE TABLE `career_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `career_applications`
--

INSERT INTO `career_applications` (`id`, `name`, `phone`, `email`, `file_name`, `file_type`, `file_size`, `comments`, `created_at`, `file_data`) VALUES
(1, 'abhay kumar singh', '7007444842', 'techguruabhay@gmail.com', 'vinay.doc', 'application/msword', 44544, 'hhvhv', '2024-08-28 22:19:28', ''),
(2, 'abhay kumar singh', '7007444842', 'techguruabhay@gmail.com', 'vinay.doc', 'application/msword', 44544, 'hhvhv', '2024-08-28 22:20:52', ''),
(3, 'abhay kumar singh', '07007444842', 'techguruabhay@gmail.com', 'Document.docx', 'application/vnd.openxmlformats-officedocument.word', 131360, 'yf', '2024-08-28 22:25:49', ''),
(4, 'fhgcg', '7665878765', 'user126@example.com', 'vinay.doc', 'application/msword', 44544, 'hfyfh', '2024-08-28 22:30:07', ''),
(5, 'Abhay Singh', '7007444842', 'techguruabhay@gmail.com', 'BSPHCL Registration Form.pdf', 'application/pdf', 75441, 'Yvvh', '2024-08-29 05:42:12', ''),
(6, 'abhay singh', '7007444842', 'ghjhjghghv@gmail.com', '56fc6462-60ea-48af-8e5f-9e5111e703cf-bihar-domicile-cert.pdf', 'application/pdf', 61986, 'Thjcfcjfg', '2024-08-29 08:36:21', ''),
(7, 'fhgcg', '7665878765', 'user129@example.com', 'vinay.doc', 'application/msword', 44544, 'hfyfh', '2024-08-29 13:25:58', ''),
(8, 'fhgcg', '7665878765', 'user130@example.com', 'Document.docx', 'application/vnd.openxmlformats-officedocument.word', 131360, 'hfyfh', '2024-08-29 13:26:38', ''),
(9, 'fhgcg', '7665878765', 'user131@example.com', 'mannu chudhri.doc', 'application/msword', 45056, 'hyhgub', '2024-08-29 13:27:48', ''),
(10, 'fhgcg', '7665878765', 'user132@example.com', 'mannu chudhri.doc', 'application/msword', 45056, 'hyhgub', '2024-08-29 14:11:43', '');

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `cid` int(50) NOT NULL,
  `cname` varchar(100) NOT NULL,
  `sid` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `city`
--

INSERT INTO `city` (`cid`, `cname`, `sid`) VALUES
(9, 'Olisphis', 3),
(10, 'Alegas', 2),
(11, 'Floson', 2),
(12, 'Ulmore', 7),
(13, 'Awrerton', 15),
(14, 'New Delhi', 19),
(15, 'Mumbai', 20),
(16, 'Bangalore', 21),
(17, 'Chennai', 22);

-- --------------------------------------------------------

--
-- Table structure for table `commission_transactions`
--

CREATE TABLE `commission_transactions` (
  `transaction_id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `business_amount` decimal(12,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(4,2) NOT NULL,
  `level_difference_amount` decimal(10,2) DEFAULT 0.00,
  `upline_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communications`
--

CREATE TABLE `communications` (
  `communication_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `type` enum('email','call','sms','meeting','other') NOT NULL,
  `direction` enum('inbound','outbound') NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `communication_date` datetime NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User who made/received the communication',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `components`
--

CREATE TABLE `components` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `content` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_backup`
--

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

--
-- Dumping data for table `contact_backup`
--

INSERT INTO `contact_backup` (`cid`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(7, 'codeastro', 'asda@asd.com', '8888885454', 'codeastro.com', 'asdasdasd', 'new', '2025-03-19 11:53:05'),
(8, 'Davis Tackett', 'davis.tackett@gmail.com', '7804720593', 'Hi apsdreamhomes.com Webmaster.', 'Looking for a Great Job? \r\n75% of resumes arenâ€™t even seen by hiring managers!  \r\n \r\nIs your resume keyword rich and ATS ready? \r\n \r\nFind out with our FREE consultation with a certified, trained resume writing. \r\nSend your resume to resumes@razored', 'new', '2025-03-19 11:53:05'),
(9, 'Amber Demko', 'amber.demko@yahoo.com', '238553317', 'Hi apsdreamhomes.com Admin!', 'WANTED: Partnerships & Agents for Global E-commerce Firm\r\n\r\n4U2 Inc., a premier E-commerce , Sourcing Brokerage firm, is actively seeking partnerships and collaboration with manufacturers and wholesalers for agricultural, commercial, and residential ', 'new', '2025-03-19 11:53:05'),
(10, 'India Coffman', 'india.coffman@googlemail.com', '0393 0171594', 'To the apsdreamhomes.com Webmaster!', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com', 'new', '2025-03-19 11:53:05'),
(11, 'Matthew Williams', 'futurosalesco@gmail.com', '740192981', 'Boost Your Social Media with 35,000 Engaging Reels', 'Hello,\r\n\r\nBoost your social media presence with our collection of 35,000 engaging Reels. Crafted for attention and engagement, these Reels are perfect for all major platforms.\r\n\r\nReady to elevate your social media game? Cliick here: https://thedigita', 'new', '2025-03-19 11:53:05'),
(12, 'CompanyRegistar.org', 'daniella.kean36@gmail.com', '285357317', 'Your online property apsdreamhomes.com is listed in only a few directories.', 'Dear Sir/Madam \r\n\r\nI see your domain is only listed in 12 out of 2398 directories\r\n\r\nThis will substantially impact your page rank, the more directories your company is listed in, locally or globally, the greater your back links you have and the bett', 'new', '2025-03-19 11:53:05'),
(13, 'Ravi Chery', 'evelyne.chery@hotmail.com', '3554963290', 'Why You are not in Googles search first Page?', 'Hi,\r\nMy name is Ravi, owner of Webomaze Australia. You have finally found an SEO Company that GETS RESULTS. The proof is my 24,800+ reviews out of which 98.6% are 5-STAR REVIEWS.\r\n I recently grew my clientâ€™s organic search traffic  with high googl', 'new', '2025-03-19 11:53:05'),
(14, 'Oman Kashiwagi', 'precious.kashiwagi62@gmail.com', '03.33.63.67.70', 'Need Business Capital Funding?', 'Hello,\r\n\r\nSecuring the necessary funding to fuel growth and bring ideas to life is one of the most significant challenges for startups and established businesses. At our company, we specialize in providing customized financing solutions for both star', 'new', '2025-03-19 11:53:05'),
(15, 'Ralf Walkom', 'ralf.walkom23@gmail.com', '648425339', 'LeadsMax.biz shutting down', 'Hello,\r\n\r\nIt is with sad regret that after 12 years, LeadsMax.biz is shutting down.\r\n\r\nWe have made all our databases available on our website.\r\n\r\n25 Million companies\r\n527 Million People\r\n\r\nLeadsMax.biz', 'new', '2025-03-19 11:53:05'),
(16, 'Matthew Williams', 'futurosalesco@gmail.com', '491075894', 'Custom Unlimited Lifetime APIs', 'Hey there,\r\n\r\nI know what youâ€™re thinking â€“ â€œThis sounds amazing, but is it really for me?â€ Letâ€™s tackle those doubts head-on.\r\n\r\nConcern 1: â€œIâ€™m not tech-savvy enough to manage custom APIs.â€\r\nOur APIs are designed to be user-friendly', 'new', '2025-03-19 11:53:05'),
(17, 'Rod Alford', 'alford.rod@msn.com', '(12) 7659-5141', 'Hi apsdreamhomes.com Owner!', 'Work From Home With This 100% FREE Training..., I Promise...You Will Never Look Back\r\n$500+ per day, TRUE -100% Free Training, go here:\r\n\r\nezwayto1000aday.com', 'new', '2025-03-19 11:53:05'),
(18, 'Heather Rawson', 'rawson.heather@gmail.com', '249901171', 'To the apsdreamhomes.com Admin.', 'Have you seen a great feature or an entire website design that you love and wish that you could have for your business?\r\n\r\nWe can make it happen and at wholesale rates.\r\n\r\nWhy pay $50+ per hour for web development work, \r\nwhen you can get higher qual', 'new', '2025-03-19 11:53:05'),
(19, 'Oman Ryder', 'ryder.deandre@outlook.com', '(03) 6241 2589', 'Flexible Funding to Drive Your Business Forward', 'Hello,\r\n\r\nSecuring the funding to drive growth and realize ideas is a major challenge for startups and established businesses alike. At Cateus Investment Company (CIC), we specialize in tailored financing solutions to meet these needs.\r\n\r\nWe offer:\r\n', 'new', '2025-03-19 11:53:05'),
(20, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Maximize Your Reach: Want to get your ad seen just like this one? I can help you reach countless others! Email me below to learn more about our services and start spreading your message effectively.\r\n\r\nPhil Stewart\r\nEmail: zg7qh8@gomail2.xyz\r\nSkype: ', 'new', '2025-03-19 11:53:05'),
(21, 'Sheena Brownlee', 'sheena.brownlee@hotmail.com', '225707945', 'Hello apsdreamhomes.com Administrator.', 'WANTED: Partnerships & Agents for Global E-commerce Firm\r\n\r\n4U2 Inc., a premier E-commerce , Sourcing Brokerage firm, is actively seeking partnerships and collaboration with manufacturers and wholesalers for agricultural, commercial, and residential ', 'new', '2025-03-19 11:53:05'),
(22, 'Lavada Percy', 'percy.lavada2@hotmail.com', '613666860', 'â€‹â€‹Need More Free Time In Your Business?', 'Did you know that you can use this hidden blueprint that makes it super fast & super easy to scale sales for your website apsdreamhomes.com without you having to do anything? revolutionary blueprint leverages automations to allow you to work infinite', 'new', '2025-03-19 11:53:05'),
(23, 'Fabien Deschamps', 'morrismi1@outlook.com', '(07) 4069 6279', 'Hi apsdreamhomes.com Owner.', 'Hi there!\r\n\r\nAre you looking to maximize the impact of your YouTube videos? Introducing YTCopyCat, our innovative SaaS tool that converts your YouTube videos into various high-quality written content using advanced AI.\r\nWhat Can Our Tool Do for You?\r', 'new', '2025-03-19 11:53:05'),
(24, 'Kerrie McVeigh', 'kerrie.mcveigh55@gmail.com', '3236086452', 'To the apsdreamhomes.com Owner!', 'Do you have a list of website updates that you want to deploy but hate having to pay the INSANE prices to get it done?\r\n\r\nWhy pay $50+ per hour for web development work, \r\nwhen you can get higher quality results AT LESS THAN HALF THE COST? \r\n\r\nWe are', 'new', '2025-03-19 11:53:05'),
(25, 'Ross Steinman', 'morrismi1@outlook.com', '079 79 35 14', 'Hello apsdreamhomes.com Administrator.', 'Dear apsdreamhomes.com owner or manager, \r\n\r\nCut your business or personal credit cards and loan payments in half. eliminate interest and reduce your debt by 50%. 100% guaranteed. The average customer saves $56,228 in unnecessary interest plus princi', 'new', '2025-03-19 11:53:05'),
(26, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Hi, would you like your ad to reach millions of contact forms just like this one did? Check out my site below for more details.\r\n\r\nhttp://kmb4zc.contactblasting.xyz', 'new', '2025-03-19 11:53:05'),
(27, 'Sadie Hefner', 'hefner.sadie@googlemail.com', '079 2040 2402', 'Hi apsdreamhomes.com Owner.', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com', 'new', '2025-03-19 11:53:05'),
(28, 'Rogelio Edelson', 'rogelio.edelson@gmail.com', '7067311379', 'Photo Video Remote controlled Drones', 'Find your perfect drone at http://cameracrazydrones.shop! Experience the most recent types and special discounts with fantastic discounts. Go to our site right away and get huge savings!  --> http://cameracrazydrones.shop\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r', 'new', '2025-03-19 11:53:05'),
(29, 'Sharron Ayres', 'ayres.sharron@gmail.com', '267171848', 'Sky Shots', 'Discover the top drones at unbeatable rates! Check out http://cameracrazydrones.shop today to seize big deals on top-rated UAVs. Hurry!\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nThis is an email promotion for the best Drones in the wor', 'new', '2025-03-19 11:53:05'),
(30, 'Nadya Alison', 'creatify64@gmail.com', '2077460146', 'I will take care !', 'Hi ,\r\n\r\nI will help you develop your brand and digital marketing strategies over all your social media accounts\r\n\r\n\r\nchat with me now to discusee further ===> https://shorturl.at/NF2Nj\r\n\r\nRegards', 'new', '2025-03-19 11:53:05'),
(31, 'Georgiana Steadman', 'steadman.georgiana@msn.com', '418-802-4099', 'Hello apsdreamhomes.com Admin!', 'Hi,\r\n\r\nAre you looking for a quick and hassle-free business loan?\r\n\r\nWe offer a variety of options to suit your needs, including Expansion loans, startup loans, heavy equipment loans, home loans, real estate development loans, construction loans, wor', 'new', '2025-03-19 11:53:05'),
(32, 'Hilario Rhyne', 'rhyne.hilario@gmail.com', '0650 114 46 66', 'To the apsdreamhomes.com Owner.', 'bills are crazy expensive ? Celebrities never pay retail, and you shouldnâ€™t either! Take our quick quiz to learn how to cut your mobile bill down to just $20. \r\n?\r\n\r\nTake the survey and win an IPhone 16 before itâ€™s released!!!\r\nhttps://mailchi.mp', 'new', '2025-03-19 11:53:05'),
(33, 'lucky', 'lucky@gmail.com', '7878989878', 'luuuu', 'kyyyy', 'new', '2025-03-19 11:53:05'),
(34, 'Hunter Brobst', 'hunter.brobst@gmail.com', '0368 5087819', 'Dear apsdreamhomes.com Admin!', 'Advantages of hiring a Developer:\r\n\r\nSpecialized Expertise\r\nTailored Customization and Control\r\nTime and Cost Efficiency\r\nCustom Plugin Development\r\nSEO Optimization\r\nOngoing Support and Maintenance\r\nSeamless Integration and Migration\r\nScalability fo', 'new', '2025-03-19 11:53:05'),
(35, 'Liz Kaminski', 'kaminski.brett@gmail.com', '763-236-1819', 'Re: Live Chat Agents for Hire', 'Hi apsdreamhomes.com Owner,\r\n\r\nStruggling with Live Chat Issues?\r\n\r\nSlow response times and missed chats can create a stressful environment for your team and frustrate your customers.\r\n\r\nWhat If It Gets Worse?\r\n\r\nAs these issues pile up, you risk dam', 'new', '2025-03-19 11:53:05'),
(36, 'Lavern Reeves', 'reeves.lavern@gmail.com', '450 4758', 'Hello apsdreamhomes.com Admin!', 'Advantages of hiring a Developer:\r\n\r\nSpecialized Expertise\r\nTailored Customization and Control\r\nTime and Cost Efficiency\r\nCustom Plugin Development\r\nSEO Optimization\r\nOngoing Support and Maintenance\r\nSeamless Integration and Migration\r\nScalability fo', 'new', '2025-03-19 11:53:05'),
(37, 'Arnette Landseer', 'landseer.arnette@gmail.com', '4197942', 'Hello apsdreamhomes.com Webmaster.', ' Running a business is not easy.\r\nEspecially when the cost of nearly everything continues to skyrocket.\r\n\r\nCut yourself a break from the stress of rising expenses with a working capital solution that will provide you the relief needed to get through ', 'new', '2025-03-19 11:53:05'),
(38, 'Vicky Johnson', 'johnson.vicky@msn.com', '2484865997', 'Hi apsdreamhomes.com Owner!', 'Are you concerned that your current website that was built a few years back,is no longer a good representation of your company ?\r\n\r\nWhy pay $50+ per hour for web development work, \r\nwhen you can get higher quality results AT LESS THAN HALF THE COST? ', 'new', '2025-03-19 11:53:05'),
(39, 'Karol', 'info@butterfield.bangeshop.com', '125941185', '404 Not Found', 'Hello, \r\n\r\nI hope this email finds you well. I wanted to let you know about our new BANGE backpacks and sling bags that just released.\r\n\r\nBange is perfect for students, professionals and travelers. The backpacks and sling bags feature a built-in USB ', 'new', '2025-03-19 11:53:05'),
(40, 'Samantha Curtsinger', 'loan.farm@proton.me', '(02) 6122 3412', 'To the apsdreamhomes.com Admin!', 'Hi,\r\n\r\nAre you seeking a fast and reliable business loan to fuel your growth?\r\n\r\nAt Fund Crowns Limited, we understand that every business is unique. Thatâ€™s why we offer a diverse range of financing options tailored to meet your specific needs, inc', 'new', '2025-03-19 11:53:05'),
(41, 'Latashia Pugh', 'latashia.pugh@gmail.com', '99037572', 'Hello apsdreamhomes.com Webmaster!', 'Important: \r\nYour Vetted Business listing is no longer visible because the annual verification email that we sent, was returned to us as â€œundeliverableâ€.\r\n\r\nIf the returned email was in error, you can add or update your email & listing info using', 'new', '2025-03-19 11:53:05'),
(42, 'Susan Karsh', 'concepcion.mcadams18@gmail.com', '531531190', 'Quick question', 'Hello,\r\n\r\nWould you be interested in dropping up to 2 pounds a week without hitting the gym?\r\n\r\nMost people donâ€™t believe this is possible, but it is, because it has been working for me and I never gain the weight back.\r\n\r\nI work for Elebands and w', 'new', '2025-03-19 11:53:05'),
(43, 'abhay kumar singh', 'techguruabhay@gmail.com', '7007444842', 'jjh', 'hghvb', 'new', '2025-03-19 11:53:05'),
(44, 'Jesenia Wainscott', 'jesenia.wainscott@msn.com', '078 7308 6390', 'Is apsdreamhomes.com your site?', 'Hey  \r\n\r\nNot sure how much money apsdreamhomes.com  is making, but selling digital products is on fire.\r\n\r\nThe transaction value in the Digital Commerce market is projected to reach US$7.63 trillion in 2024. (Statistica.)\r\nThere is a simple 2-step me', 'new', '2025-03-19 11:53:05'),
(45, 'Mark Carbine', 'markwints39@gmail.com', '734-555-7893', 'beating you?', 'Hi My name is Mark.  I did a google search and I noticed that apsdreamhomes.com has fewer reviews and positive ratings than other businesses I would say are similar to you.  \r\n\r\nFrom what I see I think your actually pretty awesome and should be getti', 'new', '2025-03-19 11:53:05'),
(46, 'Amelia Brown', 'ameliabrown12784@gmail.com', '30797473', 'Youtube Promotion: Grow your subscribers by 700 each month', 'Hi there,\r\n\r\nWe run a Youtube growth service, where we can increase your subscriber count safely and practically. \r\n\r\n- Guaranteed: We guarantee to gain you 700-1500 new subscribers each month.\r\n- Real, human subscribers who subscribe because they ar', 'new', '2025-03-19 11:53:05'),
(47, 'Brigitte Bequette', 'bequette.brigitte@gmail.com', '(08) 8254 0854', 'Hi apsdreamhomes.com Admin.', 'Still looking to get your WordPress website done, fixed, or completed? Reach out to us at e.solus@gmail.com & Prices starts @ $99!', 'new', '2025-03-19 11:53:05'),
(48, 'Rosaline Marzano', 'marzano.rosaline@gmail.com', '7736050606', 'Hi apsdreamhomes.com Administrator!', ' Running a business is not easy.\r\nEspecially when the cost of nearly everything continues to skyrocket.\r\n\r\nCut yourself a break from the stress of rising expenses with a working capital solution that will provide you the relief needed to get through ', 'new', '2025-03-19 11:53:05'),
(49, 'Sheena', 'sheena@apsdreamhomes.com', '46500877', 'Sheena Plante', 'New Multifunction Anti-theft Waterproof Sling Bag\r\n\r\nThe best ever SUPER Sling Bag: Drop-proof/Anti-theft/Scratch-resistant/USB Charging\r\n\r\n50% OFF for the next 24 Hours ONLY + FREE Worldwide Shipping for a LIMITED time\r\n\r\nBuy now: https://xbags.shop', 'new', '2025-03-19 11:53:05'),
(50, 'Ngan Buckley', 'ngan.buckley@gmail.com', '06-57389971', 'Dear apsdreamhomes.com Webmaster.', 'Hey! I wanted to share something cool with you! If youâ€™re tired of the same old wireless plans that promise savings but donâ€™t deliver, you should check out Roccstar Wireless. Theyâ€™re shaking things up with no-nonsense plans that have everything', 'new', '2025-03-19 11:53:05'),
(51, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Congratulations! By reading this, you’ve proven that contact form blasting captures attention. The biggest challenge in advertising is getting your ad seen, and you’ve just done that with my ad and I can do it with yours too! Now, let’s blast your ad', 'new', '2025-03-19 11:53:05'),
(52, 'Jada Darell', 'jadadarell@gmail.com', '30216226', 'TikTok Promotion: Grow your followers by 400 each month', 'Hi there,\r\n\r\nWe run a TikTok growth service, which can increase your number of followers safely and practically.\r\n\r\nWe aim to gain you 400-1200+ real human followers per month, with all actions safe as they are made manually (no bots).\r\n\r\nIf you are ', 'new', '2025-03-19 11:53:05'),
(53, 'Chloe Pfeifer', 'pfeifer.chloe@gmail.com', '27622233', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(54, 'Daniela Whitmer', 'daniela.whitmer66@gmail.com', '3008741292', 'Hi apsdreamhomes.com Administrator!', 'Are you struggling to get your website updated to the way you need it ?\r\n\r\nTired of paying $50+ per hour just for a few tweaks?\r\n\r\nWe are a FULL SERVICE, USA managed web development agency with wholesale pricing.\r\n\r\nNo job too big or small. Test us o', 'new', '2025-03-19 11:53:05'),
(55, 'Edythe Sigler', 'sigler.edythe@gmail.com', '159301951', 'Hello apsdreamhomes.com Webmaster!', 'FINANCIAL INSTRUMENT AND GLOBAL FUNDING\r\n\r\nWe at WEALTHY CREDIT LIMITED HONG KONG -For all your financial instruments -BG/SBLC/PPP ... Also monetization and NON RECOURSE cash loan\r\n10% for any referrals and also Intermediaries/Consultants/Brokers can', 'new', '2025-03-19 11:53:05'),
(56, 'Sammie Trott', 'sammie.trott@msn.com', '7888867965', 'Dear apsdreamhomes.com Webmaster!', 'Are you worried that you wonâ€™t make payroll this month?\r\n\r\nAre rising business expenses and inflation stressing you out?\r\n\r\nLetâ€™s remove the stress & give you some breathing room. \r\n\r\nGet a friendly & no obligation business working capital quote ', 'new', '2025-03-19 11:53:05'),
(57, 'Aracely Armytage', 'aracely.armytage@msn.com', '06-33734301', 'Hi apsdreamhomes.com Webmaster.', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com\r\n\r\nStruggling to rank on Google? Our SEO experts can help. Contact es.olus@gmail.com\r\n\r\n', 'new', '2025-03-19 11:53:05'),
(58, 'Samuel', 'samuel@maple.thawking.shop', '102675518', '404 Not Found', 'Hi \r\n \r\nDefrost frozen foods in minutes safely and naturally with our THAW KINGâ„¢. \r\n\r\n50% OFF for the next 24 Hours ONLY + FREE Worldwide Shipping for a LIMITED \r\n\r\nBuy now: https://thawking.shop\r\n \r\nBest Wishes, \r\n \r\nSamuel', 'new', '2025-03-19 11:53:05'),
(59, 'Nadya Newport', 'newport.florence@gmail.com', '494102225', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(60, 'Isabelle Snowball', 'isabelle.snowball@gmail.com', '6648105019', 'To the apsdreamhomes.com Admin.', 'If you think the BBB is helping you build trust and credibility with potential customers using their antiquated and questionable business tacticsâ€¦â€¦Think again. \r\n\r\nThe BBB has been called out for fraud, pay to play schemes and multiple states att', 'new', '2025-03-19 11:53:05'),
(61, 'Leonel Lang', 'lang.leonel19@googlemail.com', '4570403', 'Hello apsdreamhomes.com Owner.', '\r\nTURN ANY VIDEO INTO A VIDEO GAME QUICKLY\r\n\r\nGAMIFICATION is better than ChatGPT\r\n\r\nGamify your video for FREE if you have over 5,000 views. ($789 Value)\r\n\r\nViewers give 100% attention, watch the whole video, and give you their email.\r\n\r\nSend us you', 'new', '2025-03-19 11:53:05'),
(62, 'Lester Amess', 'amess.lester@gmail.com', '94030559', 'Hello apsdreamhomes.com Admin.', 'Running a small business is not easy.\r\n\r\nEspecially when the cost of nearly everything continues to skyrocket.\r\n\r\nCut yourself a break from the stress of rising expenses with a Working Capital Solution that will provide you the relief needed to get t', 'new', '2025-03-19 11:53:05'),
(63, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Need a way to get millions of people to view your website economically?\r\n Get Info http://vqtuxa.get-fast-results-with-contactformblasting.xyz', 'new', '2025-03-19 11:53:05'),
(64, 'Brian And Dee Noyes', 'lynwood.noyes@yahoo.com', '7804969052', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(65, 'Foster Dawes', 'foster.dawes53@googlemail.com', '3693296829', 'Hello apsdreamhomes.com Administrator!', 'Are you worried that you wonâ€™t make payroll this month?\r\n\r\nAre rising business expenses and inflation stressing you out?\r\n\r\nLetâ€™s remove the stress & give you some breathing room. \r\n\r\nGet a friendly & no obligation business working capital quote ', 'new', '2025-03-19 11:53:05'),
(66, 'Ravi Christiansen', 'christiansen.lacy@yahoo.com', '6785291299', 'Why You are not in Googles search first Page?', 'Hi,\r\nMy name is Ravi, owner of Webomaze Australia. You have finally found an SEO Company that GETS RESULTS. The proof is my 24,800+ reviews out of which 98.6% are 5-STAR REVIEWS.\r\n I recently grew my clientâ€™s organic search traffic  with high googl', 'new', '2025-03-19 11:53:05'),
(67, 'Felica Flockhart', 'felica.flockhart@gmail.com', '8321191673', 'Hi apsdreamhomes.com Administrator.', 'Tired of the BBB and their antiquated 1950â€s business model?\r\n\r\nItâ€™s funny how many business owners loathe the BBB, but still use them because they think they are the only game in town.\r\n\r\nThey are notâ€¦.. \r\n\r\nConsumers donâ€™t care about the â€', 'new', '2025-03-19 11:53:05'),
(68, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Need a way to get millions of people to follow your website without high costs?\r\n More Info: http://v2fync.contact-form-marketing.club', 'new', '2025-03-19 11:53:05'),
(69, 'Rochell Papst', 'rochell.papst16@gmail.com', '3548209082', 'To the apsdreamhomes.com Admin.', 'Good People + Smart Processes + Working Capital = The Recipe for Business Success.\r\n\r\nBut most small business owners put enough thought into the Capital needed to scale and grow their business.\r\n\r\nIf you have the right people & the business processes', 'new', '2025-03-19 11:53:05'),
(70, 'Vasily Kichigin Williams', 'williams.leora16@yahoo.com', '727549424', 'I will be your social media content manager', 'Hi,\r\n\r\nHey! My name is Vasily Kichigin, and I am excited to connect with you , Since 2015, I have worked with over 14,000 customers and completed more than 26,000 orders to help growth and mange socil Media accounts for \r\nMy clients , I am super pass', 'new', '2025-03-19 11:53:05'),
(71, 'Elba', 'info@apsdreamhomes.com', '695677606', 'Elba Plain', 'New Multifunction Waterproof Backpack\r\n\r\nThe best ever SUPER Backpack: Drop-proof/Scratch-resistant/USB Charging/Large capacity storage\r\n\r\n50% OFF for the next 24 Hours ONLY + FREE Worldwide Shipping for a LIMITED time\r\n\r\nBuy now: https://thebackpack', 'new', '2025-03-19 11:53:05'),
(72, 'Merissa Clamp', 'clamp.merissa@gmail.com', '6888880616', 'To the apsdreamhomes.com Owner.', 'Looking to expand your business? I can get you a loan for less than 2% interest rate. Email me here for details:  jpark9000z@gmail.com \r\nThanks\r\nJoseph', 'new', '2025-03-19 11:53:05'),
(73, 'Ladonna Sauer', 'ladonna.sauer@yahoo.com', '47360221', 'Use EVERY major AI for apsdreamhomes.com, Lifetime Unlimited Usage Deal', 'Unlock all these top-tier AI apps from a single, user-friendly dashboard.\r\n\r\nChatGPT 4.0 \r\nGemini Pro \r\nDALLÂ·E 3 \r\nLeonardo AI \r\nMicrosoft Copilot Pro \r\nMeta Llama 3 \r\nStable Diff XL s \r\nPaLM 2 \r\n\r\nEliminate your AI subscriptions, Save thousands of ', 'new', '2025-03-19 11:53:05'),
(74, 'Ronny Hackett', 'hackett.bertha91@yahoo.com', '9469729919', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(75, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Need a way to get your ad read by millions without breaking the bank?\r\n If youâ€™re interested in learning more about how this works, reach out to me using the contact info below.\r\n\r\nRegards,\r\nGeorgiana Poindexter\r\nEmail: Georgiana.Poindexter@morebiz', 'new', '2025-03-19 11:53:05'),
(76, 'Tosha Harker', 'harker.tosha@gmail.com', '618782554', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(77, 'Oman Male', 'precious.male@gmail.com', '034203 22 91', 'Tailored Financing Solutions for Your Business Growth', 'Hello,\r\n\r\nAt Cateus Investment Company (CIC), we recognize that securing the right funding is essential for businesses at all stages, from startups to well-established enterprises. \r\nTo support your growth, we offer flexible financing solutions speci', 'new', '2025-03-19 11:53:05'),
(78, 'Lottie Thompson', 'lottiethompson497@gmail.com', '7831512924', 'Facebook Promotion: Grow your followers by 400 each month', 'Hello,\r\n\r\nWe run a Facebook growth service, which can increase your number of followers safely and practically.\r\n\r\nWe aim to gain you 400+ real human followers per month, with all actions safe as they are made manually (no bots).\r\n\r\nOur price is just', 'new', '2025-03-19 11:53:05'),
(79, 'Verlene Thiessen', 'verlene.thiessen@gmail.com', '951-445-8807', 'To the apsdreamhomes.com Administrator!', 'Unlock Stress-Free Website Managementâ€”Just $25/Month\r\n\r\nExperience worry-free web hosting with our fully managed service designed to keep your website secure, fast, and always up-to-date. For only $25/month, we handle everythingâ€”WordPress core, P', 'new', '2025-03-19 11:53:05'),
(80, 'Benedict Acosta', 'allenjeremy183@gmail.com', '02.68.49.83.30', 'Opportunity for Businesses Outside the USA.', 'Do you own and operate a business outside the USA? My name is Jeremy\r\nAllen from BNF Investments LLC, a Florida based Investment Company.\r\nWe are expanding our operations outside the USA hence; we are actively\r\nlooking for serious business owners ope', 'new', '2025-03-19 11:53:05'),
(81, 'Jeanett Feint', 'feint.jeanett@hotmail.com', '695774400', 'Improve SEO Strategy with a Free Backlink Review', 'Claim your free backlink analysis now!\r\n\r\nhttps://aluzzion.com/go/free-backlink-analysis-tool-for-seo\r\n\r\nDive into a detailed SEO backlink analysis now to enhance your digital strategy! Itâ€™s like checking the backbone of your online presence for st', 'new', '2025-03-19 11:53:05'),
(82, 'Ravi Rossi', 'rossi.willy@yahoo.com', '97470082', 'Why You are not in Googles search first Page?', 'Hi,\r\nMy name is Ravi, owner of Webomaze Australia. You have finally found an SEO Company that GETS RESULTS. The proof is my 24,800+ reviews out of which 98.6% are 5-STAR REVIEWS.\r\n I recently grew my clientâ€™s organic search traffic  with high googl', 'new', '2025-03-19 11:53:05'),
(83, 'Lazaro Magill', 'magill.lazaro@gmail.com', '(08) 9027 1148', 'Hello apsdreamhomes.com Owner!', 'We understand that as a financial institution, you value reliable and trustworthy partners. At First Asia Finance International Limited, we offer the best financial solutions for companies, businesses and individuals seeking loans.\r\n\r\nWe have a varie', 'new', '2025-03-19 11:53:05'),
(84, 'Juana Cross', 'juana.cross@hotmail.com', '407-435-2634', 'Your FREE 1-Month Premium Trial Awaits â€“ Claim It Now!', 'Hi again apsdreamhomes.com Owner,\r\n\r\nWeâ€™re thrilled to announce the launch of the all-new Leader CRM, and as a valued user, you and your team are invited to enjoy a free one-month trial of our premium subscriptionâ€”no credit card required.\r\n\r\nThis', 'new', '2025-03-19 11:53:05'),
(85, 'Everett Mingay', 'everett.mingay@msn.com', '041 944 60 65', 'Your FREE 1-Month Premium Trial Awaits â€“ Claim It Now!', 'Hi again apsdreamhomes.com Owner,\r\n\r\nWeâ€™re thrilled to announce the launch of the all-new Leader CRM, and as a valued user, you and your team are invited to enjoy a free one-month trial of our premium subscriptionâ€”no credit card required.\r\n\r\nThis', 'new', '2025-03-19 11:53:05'),
(86, 'Ashely Bothwell', 'ashely.bothwell99@msn.com', '50-23-45-54', 'Hello apsdreamhomes.com Owner!', '\r\nWe understand that as a financial institution, that people value reliable and trustworthy partners. At First Asia Finance International Limited, we offer the best financial solutions for companies, businesses and individuals seeking loans.\r\n\r\nWe ha', 'new', '2025-03-19 11:53:05'),
(87, 'Zoey Lee', 'singletary.lavina@msn.com', '42521072', 'Revitalize your website without breaking the bank!', 'Hello\r\n\r\nWant professional services at an affordable price? Fiverr has a extensive selection of budget-friendly freelancersâ€”priced from $5! From graphic design, marketing, web development, or additional service, Fiverr has expert professionals eage', 'new', '2025-03-19 11:53:05'),
(88, 'Ronny Baeza', 'johnson.baeza@yahoo.com', '6341523959', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(89, 'Matthew Williams', 'futurosalesco@gmail.com', '134198390', 'Why Creators Stay Broke (And How You Can Fix It)', 'Hey there,  \r\n\r\nMost creators are one paycheck away from panic. They hustle brand deals, chase ad revenue, and hope algorithms donâ€™t kill their reach overnight.  \r\n\r\nHereâ€™s the truth: Theyâ€™re building someone elseâ€™s empire, not their own. Com', 'new', '2025-03-19 11:53:05'),
(90, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', '??', 'Looking to boost traffic to your video or website economically?\r\n Get in touch with me through the info provided below if youâ€™d like to know more about how I can help.\r\n\r\nRegards,\r\nAngus Joshua\r\nEmail: Angus.Joshua@morebiz.my\r\nWebsite: http://ww7cf', 'new', '2025-03-19 11:53:05'),
(91, 'Mathew Dickerman', 'dickerman.lorrie@gmail.com', '427 7208', 'Hi apsdreamhomes.com Administrator.', 'Hello\r\n\r\nI hope this message finds you well. My name is Mathew Lundgren, and I am a Research Assistant in the Research and Development Department at Newton Laboratories Pro Ltd, a leading biopharmaceutical company based in London, England. I am reach', 'new', '2025-03-19 11:53:05'),
(92, 'Ellen', 'apsdreamhomes.com@hotmail.com', '665987125', 'Real Estate PHP', 'Hello \r\n\r\nLooking for the perfect gift that will genuinely enchant? Our Enchanted Shining Roseâ„¢ combines beauty and magic in one beautiful gift. With its gentle light and shimmering rose adorned with tiny twinkling lights, itâ€™s something that bri', 'new', '2025-03-19 11:53:05'),
(93, 'Marlene Figueroa', 'morrismi1@outlook.com', '51 294 20 09', 'Assets/payment handler', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(94, 'Mckenzie Boulton', 'mckenzie.boulton@yahoo.com', '078 4363 4296', 'To the apsdreamhomes.com Admin!', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com\r\n\r\nStruggling to rank on Google? Our SEO experts can help. Contact es.olus@gmail.com', 'new', '2025-03-19 11:53:05'),
(95, 'Lucienne Brandon', 'brandon.lucienne@outlook.com', '079 0639 9234', 'Hi apsdreamhomes.com Owner!', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com\r\n\r\nStruggling to rank on Google? Our SEO experts can help. Contact es.olus@gmail.com', 'new', '2025-03-19 11:53:05'),
(96, 'Shane Gowins', 'shane.gowins@googlemail.com', '3959689420', 'To the apsdreamhomes.com Administrator!', 'Hi! Based on what we found on your website, you could be missing out on tens of thousands in annual tax credits. Our software scans thousands of credits to see which you qualify forâ€”and the best part? We only get paid if we secure savings for you. ', 'new', '2025-03-19 11:53:05'),
(97, 'Adrienne Liles', 'adrienne.liles@hotmail.com', '7739735695', 'Use EVERY major AI for apsdreamhomes.com, Lifetime Unlimited Usage Deal', 'Access all the premium AI apps from one, user-friendly dashboard.\r\n\r\nChatGPT 4.0 \r\nGemini Pro \r\nDALLÂ·E 3 \r\nLeonardo AI \r\nMicrosoft Copilot Pro \r\nMeta Llama 3 \r\nStable Diff XL s \r\nPaLM 2 \r\n\r\nCancel your AI subscriptions, Save thousands of  $$$$.\r\n\r\nU', 'new', '2025-03-19 11:53:05'),
(98, 'Ronny Kirby', 'kirby.prince@googlemail.com', '193171578', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(99, 'Sammie Derrington', 'oskaroliver2023@outlook.com', '(07) 4087 4727', 'Dear apsdreamhomes.com Admin.', 'My name is James Broderick, and I am an attorney at Broderick & Associates LLP based in Canada. I am reaching out to discuss matters concerning your late relative payable on death.\r\n\r\nPlease feel free to contact me at your earliest convenience at the', 'new', '2025-03-19 11:53:05'),
(100, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', 'seeking information', 'Need a way to get millions of people to engage with your website on a budget?\r\n Let me know if youâ€™d like more informationâ€”my contact info is listed below.\r\n\r\nRegards,\r\nStanley Stainforth\r\nEmail: Stanley.Stainforth@morebiz.my\r\nWebsite: http://fub', 'new', '2025-03-19 11:53:05'),
(101, 'Ola', 'info@apsdreamhomes.com', '5023543945', 'Ola Mcclellan', 'New Multifunction Waterproof Backpack\r\n\r\nThe best ever SUPER Backpack: Drop-proof/Scratch-resistant/USB Charging/Large capacity storage\r\n\r\n50% OFF for the next 24 Hours ONLY + FREE Worldwide Shipping for a LIMITED time\r\n\r\nBuy now: https://thebackpack', 'new', '2025-03-19 11:53:05'),
(102, 'Tandy Morrice', 'morrice.tandy@gmail.com', '538980269', 'Use EVERY major AI for apsdreamhomes.com, Lifetime Unlimited Usage Deal', 'Access all these top-tier AI apps from a single, easy-to-use dashboard.\r\n\r\nChatGPT 4.0 \r\nGemini Pro \r\nDALLÂ·E 3 \r\nLeonardo AI \r\nMicrosoft Copilot Pro \r\nMeta Llama 3 \r\nStable Diff XL s \r\nPaLM 2 \r\n\r\nCancel your AI subscriptions, Save thousands of Dolla', 'new', '2025-03-19 11:53:05'),
(103, 'Ronny Glenelg', 'glenelg.janet95@yahoo.com', '610298060', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(104, 'Brian And Dee Mello', 'mello.jewel@yahoo.com', '42479841', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(105, ' Crawford', 'carbone.leoma@gmail.com', '1173676764', 'New Message', 'Hi, this is a friendly reminder that this is the last chance for USA based businesses to file their claim to receive compensation from the Visa/Mastercard $5.5 Billion Dollar settlement. You could potentially receive tens of thousands, hundreds of th', 'new', '2025-03-19 11:53:05'),
(106, 'Hudson Schlenker', 'hudson.schlenker@gmail.com', '021 638 21 32', 'Your FREE 1-Month Premium Trial Awaits â€“ Claim It Now!', 'Hi again user_name,\r\n\r\nWeâ€™re thrilled to announce the launch of the all-new Leader CRM, and as a valued user, you and your team are invited to enjoy a free one-month trial of our premium subscriptionâ€”no credit card required.\r\n\r\nThis latest versio', 'new', '2025-03-19 11:53:05'),
(107, 'Ardita J Pither', 'pither.luz44@gmail.com', '3746534490', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(108, 'Matthew Williams', 'futuresalesco@gmail.com', '21839827', 'Ready to Drive Tens of Thousands of Buyers to Your Website a Day?', 'Hi there,  \r\n\r\nI wanted to share something powerful with youâ€”our revolutionary product, TrafficFuse. \r\n\r\nThis isnâ€™t just any traffic tool. With TrafficFuse, youâ€™ll have access to tens of thousands of visitors, views, and clicks daily to your we', 'new', '2025-03-19 11:53:05'),
(109, 'Carri Rylah', 'rylah.carri@googlemail.com', '0676 344 71 02', 'Container House at Factory Price', 'Rick here from Container Speedy House Co., Ltd, we are the factory producing modular container houses from China. It is a pleasure to introduce you our container houses for office, accommodation, hotel, school and camping house etc. \r\n\r\nPlease contac', 'new', '2025-03-19 11:53:05'),
(110, 'Millard Criswell', 'criswell.millard@gmail.com', '436 1927', 'Container House at Factory Price', 'Rick here from Container Speedy House Co., Ltd, we are the factory producing modular container houses from China. It is a pleasure to introduce you our container houses for office, accommodation, hotel, school and camping house etc. \r\n\r\nPlease contac', 'new', '2025-03-19 11:53:05'),
(111, 'Megan Collier', 'megan.collier77@gmail.com', '4893091', 'Use EVERY major AI for apsdreamhomes.com, Lifetime Unlimited Usage Deal', 'Unlock all these top-tier AI apps from one, easy-to-use dashboard.\r\n\r\nChatGPT 4.0 \r\nGemini Pro \r\nDALLÂ·E 3 \r\nLeonardo AI \r\nMicrosoft Copilot Pro \r\nMeta Llama 3 \r\nStable Diff XL s \r\nPaLM 2 \r\n\r\nCancel your AI subscriptions, Save thousands of Dollars.\r\n', 'new', '2025-03-19 11:53:05'),
(112, 'Ardita J Bogart', 'bogart.issac@gmail.com', '267445668', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(113, 'Jonathan Moss', 'info@prboostcolab.com', '12345678', 'Get free visibility for your brand with a Benzinga article', 'Hey there,  \r\n\r\nI hope youâ€™re great. I think your business deserves more exposure.\r\n\r\nWeâ€™re currently offering a free article on Benzinga, which attracts 14M+ visitorsâ€”a great way to build credibility for your company and boost exposure at no c', 'new', '2025-03-19 11:53:05'),
(114, 'Phil Stewart', 'noreplyhere@aol.com', '342-123-4456', 'need information', 'Affiliate marketers, take charge! Contact form advertising bypasses all the restrictive rules of traditional ad platforms. Send your ads to millions of websites with no bans, no limits, and no extra costs.\r\n\r\nInterested? Get in touch via the contact ', 'new', '2025-03-19 11:53:05'),
(115, 'Jayne Seese', 'seese.jayne@gmail.com', '01.22.59.46.21', 'Hi apsdreamhomes.com Webmaster!', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(116, 'Shalanda Chute', 'chute.shalanda@gmail.com', '0382 3571264', 'To the apsdreamhomes.com Admin!', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(117, 'Sheila Verge', 'sheila.verge@gmail.com', '4119527256', 'Use EVERY major AI for apsdreamhomes.com, Lifetime Unlimited Usage Deal', 'Access all the premium AI apps from one, user-friendly dashboard.\r\n\r\nChatGPT 4.0 \r\nGemini Pro \r\nDALLÂ·E 3 \r\nLeonardo AI \r\nMicrosoft Copilot Pro \r\nMeta Llama 3 \r\nStable Diff XL s \r\nPaLM 2 \r\n\r\nCancel your AI subscriptions, Save thousands of Dollars.\r\n\r', 'new', '2025-03-19 11:53:05'),
(118, 'Maxwell Lemieux', 'lemieux.maxwell@gmail.com', '337156968', 'questioning', 'Hi, this is a friendly reminder that if your business accepted Visa/Mastercard between 2004 and 2019, you may be eligible to participate in the Visa/Mastercard class action settlement, which has set aside $5.54 Billion for businesses like yours. The ', 'new', '2025-03-19 11:53:05'),
(119, 'Brad Treasure', 'brad.treasure@gmail.com', '907-277-9301', 'Hello apsdreamhomes.com Administrator!', 'Let Us Take a Few Things Off Your Plate (Plus Black Friday Savings)\r\n\r\nUnlike your current host, Best Website goes above and beyond to support your business by including valuable extras as part of our fully managed WordPress hosting service, such as ', 'new', '2025-03-19 11:53:05'),
(120, 'Demetrius Feetham', 'feetham.demetrius@gmail.com', '323-813-4655', 'Dear apsdreamhomes.com Owner!', 'Let Us Take a Few Things Off Your Plate (Plus Black Friday Savings)\r\n\r\nUnlike your current host, Best Website goes above and beyond to support your business by including valuable extras as part of our fully managed WordPress hosting service, such as ', 'new', '2025-03-19 11:53:05'),
(121, 'Nadya Ginder', 'efren.ginder28@gmail.com', '225002624', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(122, 'Kristi Golden', 'morrismi1@outlook.com', '483 22 644', 'Payment/deposits handler. ', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(123, 'Graig Escamilla', 'morrismi1@outlook.com', '02682 21 71 94', 'Payment/deposits handler. ', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(124, 'Ardita J Demarest', 'lavada.demarest@gmail.com', '6809369517', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(125, 'Leandra Rowell', 'leandra.rowell@gmail.com', '918435836', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(126, 'Ardita J Brewster', 'brewster.whitney@yahoo.com', '42381405', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(127, 'Gilbert Buggy', 'oskaroliver2023@outlook.com', '214-353-0032', 'Hello apsdreamhomes.com Admin.', 'My name is James Broderick, and I am an attorney at Broderick & Associates LLP based in Canada. I am reaching out to discuss matters concerning your late relative payable on death sum of Eleven Million Eight Hundred Thousand, Twenty United States Dol', 'new', '2025-03-19 11:53:05'),
(128, 'Willis Jacobson', 'oskaroliver2023@outlook.com', '06-29925228', 'Dear apsdreamhomes.com Admin.', 'My name is James Broderick, and I am an attorney at Broderick & Associates LLP based in Canada. I am reaching out to discuss matters concerning your late relative payable on death sum of Eleven Million Eight Hundred Thousand, Twenty United States Dol', 'new', '2025-03-19 11:53:05'),
(129, 'Nadya Enriquez', 'abigail.enriquez@msn.com', '211009622', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(130, 'Brian And Dee Lambert', 'lambert.cyrus@yahoo.com', '4158450', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(131, 'Alphonse Bueno', 'alphonse.bueno@gmail.com', '699808872', 'To the apsdreamhomes.com Admin!', 'Ready to save on gas and so much more? With our membership, starting at just $20/month, youâ€™ll enjoy global savings designed for your lifestyle! í ¼í¼Ÿ\r\ní ½í³² https://kristi.savingshighwayglobal.com/?page=membership\r\n\r\nSmart savings are just a cli', 'new', '2025-03-19 11:53:05'),
(132, 'Nadya Barela', 'barela.darwin@yahoo.com', '6084040863', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(133, 'Brian And Dee Ramsey', 'chase.ramsey@gmail.com', '7783191080', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(134, 'Ardita J Hopman', 'hopman.bertie@gmail.com', '3236387635', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(135, 'Allie Teal', 'teal.allie@yahoo.com', '635350256', 'Make $10k+ a month', 'Hi! \r\n\r\nIs your website not making the sales that it should? \r\n\r\nWe build highly branded shopify stores.  \r\n\r\nBe your own boss, you want a BUSINESS, not a website only! \r\nThe best value for your money (17k happy clients) \r\nWe provide you with lifetim', 'new', '2025-03-19 11:53:05'),
(136, 'Sterling Burchett', 'burchett.sterling@gmail.com', '5006888795', 'looking for clarification', 'Struggling to gain visibility? We deliver your ad text to millions of website contact forms at one flat rate. Guaranteed visibility without the hassle of per-click charges. Grow your business now.\r\n\r\n Feel free to contact me using the details below i', 'new', '2025-03-19 11:53:05'),
(137, 'Nadya Lansford', 'linette.lansford@gmail.com', '3013754817', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05');
INSERT INTO `contact_backup` (`cid`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(138, 'Ronny Swadling', 'swadling.reinaldo@msn.com', '669081992', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(139, 'Brian And Dee Roney', 'earle.roney@yahoo.com', '7939826336', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(140, 'Max Elisha', 'elisha.max@gmail.com', '475825399', 'Dear apsdreamhomes.com Owner.', 'Hi\r\n\r\nDo you use Google Maps for finding companies / suppliers / clients?\r\n\r\nWe grabbed all 25 million companies from google maps, including addresses, Industries, Phones, Emails, Websites, Lat/Long, many more..\r\n\r\nGet it today for $4.99 \r\n\r\nhttps://', 'new', '2025-03-19 11:53:05'),
(141, 'Nadya Fossett', 'emanuel.fossett@outlook.com', '6763816407', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(142, 'Nadya Penington', 'penington.dianna@gmail.com', '3642415789', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(143, 'Brian And Dee Carls', 'margo.carls@gmail.com', '2686824664', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(144, 'Amelia Brown', 'ameliabrown5822@gmail.com', '5108871549', 'YouTube Promotion: Grow your subscribers by 700-1500 each month', 'Hi there,\r\n\r\nWe run a Youtube growth service, where we can increase your subscriber count safely and practically. \r\n\r\n- Guaranteed: We guarantee to gain you 700-1500 new subscribers each month.\r\n- Real, human subscribers who subscribe because they ar', 'new', '2025-03-19 11:53:05'),
(145, 'Erick Beckman', 'morrismi1@outlook.com', '562-387-2268', 'Payment/deposits handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(146, 'Isla Zepeda', 'morrismi1@outlook.com', '034605 27 96', 'Payment/deposits handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(147, 'Miguel Reynolds', 'miguel.reynolds@gmail.com', '079 1172 7448', 'To the apsdreamhomes.com Owner!', 'Are you still looking at getting your website done/ completed? Contact e.solus@gmail.com\r\n\r\nStruggling to rank on Google? Our SEO experts can help. Contact es.olus@gmail.com', 'new', '2025-03-19 11:53:05'),
(148, 'Layne Buring', 'buring.layne@gmail.com', '3178886650', 'inquiry', 'Need capital to grow your business? DAC offers quick funding with minimal paperwork. Click here! http://lgrgd4.dac-capital.top/  ', 'new', '2025-03-19 11:53:05'),
(149, 'Alena Scantlebury', 'scantlebury.alena@gmail.com', '0493 29 48 10', 'Hi apsdreamhomes.com Webmaster!', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(150, 'Rhonda Crume', 'rhonda.crume@gmail.com', '53 325 17 31', 'Dear apsdreamhomes.com Administrator!', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(151, 'Vasily Kichigin Lain', 'kristen.lain@googlemail.com', '343815458', 'I will be your social media content manager', 'Hi,\r\n\r\nHey! My name is Vasily Kichigin, and I am excited to connect with you , Since 2015, I have worked with over 14,000 customers and completed more than 26,000 orders to help growth and mange socil Media accounts for \r\nMy clients , I am super pass', 'new', '2025-03-19 11:53:05'),
(152, 'Cathern Scribner', 'cathern.scribner@gmail.com', '5423534391', 'Android App for apsdreamhomes.com', 'Want a mobile App for apsdreamhomes.com for $15?\r\n\r\nCome join checkout our Christmas special and get your App now\r\n\r\nhttps://zundee.click/?affid=affiliateking&url=apsdreamhomes.com', 'new', '2025-03-19 11:53:05'),
(153, 'Nadya Warfe', 'roger.warfe@gmail.com', '621888663', 'Get Your AI Chatbot, Website, or App â€“ Tailored Just for You!', 'Hi ,\r\n\r\n\r\nI will help to build chatbot with the power of AI & help you to integrate in your desired platform (mobile app, website, desktop app).\r\n I have 3+ years of experience in building multiple chatbots. You can always check our portfolio. \r\nBelo', 'new', '2025-03-19 11:53:05'),
(154, 'Beulah Demko', 'beulah.demko@msn.com', '306-222-3838', 'From Someday to Success', 'Do you have a project youâ€™ve been dreaming of completing for years? A book you want to write or publish? A business youâ€™re ready to start or grow? Imagine having your own dedicated team to help you get it across the finish line.\r\nAt WCD Marketing', 'new', '2025-03-19 11:53:05'),
(155, 'Brian And Dee Locklear', 'merri.locklear@yahoo.com', '6569622233', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(156, 'Lucas Finn', 'finn.lucas@gmail.com', '(07) 3658 9337', 'From Someday to Success', 'Do you have a project youâ€™ve been dreaming of completing for years? A book you want to write or publish? A business youâ€™re ready to start or grow? Imagine having your own dedicated team to help you get it across the finish line.\r\nAt WCD Marketing', 'new', '2025-03-19 11:53:05'),
(157, 'Ronny Colls', 'colls.cole61@gmail.com', '599648177', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(158, 'Bennie Sunderland', 'sunderland.bennie@outlook.com', '040 92 51 51', 'To the apsdreamhomes.com Owner!', 'Struggling to rank on Google? Our high-quality backlink services will push your site to the top. Trusted by businesses worldwide! Start now and watch your traffic soar! \r\n\r\nWe Create 1200 Backlinks for you in Just $12\r\n\r\n> Boost Google Ranking\r\nGet H', 'new', '2025-03-19 11:53:05'),
(159, 'Isla Haffner', 'haffner.isla@gmail.com', '0911 83 66 88', 'Dear apsdreamhomes.com Admin!', 'Struggling to rank on Google? Our high-quality backlink services will push your site to the top. Trusted by businesses worldwide! Start now and watch your traffic soar! \r\n\r\nWe Create 1200 Backlinks for you in Just $12\r\n\r\n> Boost Google Ranking\r\nGet H', 'new', '2025-03-19 11:53:05'),
(160, 'Helaine Dunn', 'helaine.dunn@msn.com', '9726674393', 'inquiring', 'Make your marketing budget work harder. For a flat rate, weâ€™ll send your ad text to millions of website contact forms. Itâ€™s cost-effective, reliable, and guarantees your message is read.\r\n\r\n Interested? Get in touch via the contact details below.', 'new', '2025-03-19 11:53:05'),
(161, 'Omar Farley', 'farley.miguel3@googlemail.com', '2604552832', 'Thank you Elon Musk!', 'Hi ,\r\nPlease Stop use emails for Marketing to reach your clients , use X (Twetter) instead , you can use this tool to send bulk X Dm  and forget landing in spam or waste you emails for no reply \r\n\r\nLet me show  this tool how it works let start Free D', 'new', '2025-03-19 11:53:05'),
(162, 'Ronny Laurantus', 'leonor.laurantus12@gmail.com', '3636171457', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(163, 'Glory Banda', 'charlesrosariosa@gmail.com', '6507471150', 'Get Responsivee Website Design', 'We will create a custom website design tailored to your specific business needs,\r\nwhich are of the highest quality at affordable prices.\r\n\r\nFREE BASIC MOCK-UP DESIGN BEFORE WORK \r\n\r\nJust message me at charlesrosariosa@gmail.com\r\n\r\nServices Offered by', 'new', '2025-03-19 11:53:05'),
(164, 'Ardita J Muskett', 'muskett.jami29@gmail.com', '539306873', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(165, 'Francine Poninski', 'francine.poninski@gmail.com', '4802212', 'Supercharge Your Online Presence with Mintsuite â€“ Limited Time Offer!', 'Hi there,\r\n\r\nWeâ€™re excited to introduce Mintsuite, the ultimate platform to enhance your online presence and drive results. Mintsuite empowers you to create stunning websites, manage social media like a pro, and generate traffic effortlessly.\r\n\r\nCr', 'new', '2025-03-19 11:53:05'),
(166, 'Myrna Lenk', 'myrna.lenk83@gmail.com', '31-97-70-20', 'To the apsdreamhomes.com Admin.', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(167, 'Meghan Johansen', 'johansen.meghan99@yahoo.com', '(08) 9095 2360', 'To the apsdreamhomes.com Admin.', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(168, 'Omar Petchy', 'petchy.ira72@googlemail.com', '7147589676', 'How to send bulk X  ( twitter) for Free', 'Hi ,\r\nStart Send bulk X (twitter) Dm 500 dm /day Free  in Simple 3 step system\r\n\r\n1- sign up & create  your account  with this link : https://shorturl.at/EYhaw\r\n2-Tell the tool about your self\r\n3-connect your X account \r\n\r\nAnd happy bulk sending \r\n', 'new', '2025-03-19 11:53:05'),
(169, 'Ronny Blum', 'blum.milagro@gmail.com', '7701240702', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(170, 'Clarence Mathieu', 'morrismi1@outlook.com', '02.47.77.64.53', 'Payment/deposits handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(171, 'Andres Lim', 'morrismi1@outlook.com', '01.92.99.35.32', 'Assets/payment handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining financial record. This position is only for candidates based in the U', 'new', '2025-03-19 11:53:05'),
(172, 'Ardita J Keysor', 'keysor.bettye@gmail.com', '47285708', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(173, 'Mathew Baldwin', 'baldwin.leonida@gmail.com', '0323 4469347', 'Dear apsdreamhomes.com Administrator!', 'Hello\r\n\r\nI hope this message finds you well. My name is Mathew, and I am a Research Assistant in the Research and Development Department one of the leading biopharmaceutical company based in London, England. I am reaching out to explore a potential p', 'new', '2025-03-19 11:53:05'),
(174, 'Matt Dickey', 'dickey.marcy5@yahoo.com', '(16) 3414-6841', 'Hi apsdreamhomes.com Webmaster.', 'Hello\r\n\r\nI hope this message finds you well. My name is Mathew, and I am a Research Assistant in the Research and Development Department one of the leading biopharmaceutical company based in London, England. I am reaching out to explore a potential p', 'new', '2025-03-19 11:53:05'),
(175, 'Catherine Ferreira', 'catherine.ferreira@gmail.com', '2977865469', 'i have a question', 'Make your marketing budget work harder. For a flat rate, weâ€™ll send your ad text to millions of website contact forms. Itâ€™s cost-effective, reliable, and guarantees your message is read.\r\n\r\n Letâ€™s discuss how I can helpâ€”find my contact info b', 'new', '2025-03-19 11:53:05'),
(176, 'Jamaal Olivares', 'jamaal.olivares@gmail.com', '721596926', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(177, 'Odessa Foote', 'odessa.foote@gmail.com', '7888128014', 'Hi apsdreamhomes.com Admin!', 'Hey from Zundee!\r\n\r\nHappy new year to you!\r\n\r\nWe have made all our databases / client lists available to companies. \r\n\r\nCompanies, People, Job Titles, Phones, Emails, you name it!\r\n\r\nVisit us: https://zundee.click', 'new', '2025-03-19 11:53:05'),
(178, 'Thurman Tirado', 'thurman.tirado@msn.com', '4426283', 'Looking for More Clients? Become Part of Our Expanding Business Platform Right Away!', '\r\n\r\nConnect with Clients Faster â€“ Get Featured in Our Professional Registry\r\n\r\nHello,\r\n\r\nOur Business Platform is created to enable you to engage with prospects faster.\r\n\r\nBy signing up, your brand will be featured among trusted experts in your spe', 'new', '2025-03-19 11:53:05'),
(179, 'Ellen Smith', 'mathew.muecke@hotmail.com', '(332) 222-4058', 'Hi apsdreamhomes.com Administrator.', 'Impact Explainers specializes in high-quality 2D/3D animation, CGI/VFX, and motion graphics. With a team of 60+ experts and cutting-edge technology, we deliver exceptional results and fast turnarounds. \r\nUse animation to enhance sales, marketing, bus', 'new', '2025-03-19 11:53:05'),
(180, 'Ellen Smith', 'primm.chelsea@yahoo.com', '(332) 222-4058', 'To the apsdreamhomes.com Admin.', 'Impact Explainers specializes in high-quality 2D/3D animation, CGI/VFX, and motion graphics. With a team of 60+ experts and cutting-edge technology, we deliver exceptional results and fast turnarounds. \r\nUse animation to enhance sales, marketing, bus', 'new', '2025-03-19 11:53:05'),
(181, 'Jackson Witzel', 'witzel.jackson2@gmail.com', '7704774616', 'Maximize Your Marketing Reach â€“ Join in Our Directory Now!', '\r\n\r\nReach Your Customers Quickly â€“ Showcase Your Brand in Our Services Platform\r\n\r\nHello,\r\n\r\nOur Professional Registry is created to allow you to connect with clients more efficiently.\r\n\r\nBy registering, your business will be listed among industry ', 'new', '2025-03-19 11:53:05'),
(182, 'Ronny Felts', 'sondra.felts77@hotmail.com', '5202961982', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(183, 'Princess Veal', 'kinggreensony@gmail.com', '672152849', 'New Lead', 'We have a ton of free leads available for you at https://kinggreensony.com/', 'new', '2025-03-19 11:53:05'),
(184, 'Henry Wright', 'f.as.t.e.a.syb.usinesslo.ans.in.fo@gmail.com', '526692161', 'Your business, your terms needs', 'Hi there,\r\n\r\nCould your business benefit from additional working capital or flexible financing solutions?\r\n\r\nWe specialize in providing fast, hassle-free funding with options tailored to meet your unique needs.\r\n\r\nExplore our offerings:\r\n - Term Loan', 'new', '2025-03-19 11:53:05'),
(185, 'Josette Hendricks', 'josette.hendricks75@outlook.com', '6043038624', 'This just came upâ€”need your help!', 'Drowning in Work? Deadlines, tasks, and endless content creation. What if you could cut the stress and get professional results in minutes? AI tools to help you: \r\n**Create videos  \r\n**Generate content  \r\n**Convert text to speech  \r\nAll while saving ', 'new', '2025-03-19 11:53:05'),
(186, 'Brian And Dee Lajoie', 'hal.lajoie8@googlemail.com', '351274985', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(187, 'Ronny Laird', 'mikayla.laird@gmail.com', '668488157', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(188, 'Isabelle Haddon', 'isabelle.haddon55@msn.com', '7850515882', 'I have a question', 'Hi, this is a friendly reminder that this is the last chance for USA based businesses to file their claim to receive potential compensation owed to you from the Visa/Mastercard $5.5 Billion Dollar settlement. You can learn more here: https://visascar', 'new', '2025-03-19 11:53:05'),
(189, 'Ardita J Manley', 'manley.sharron@yahoo.com', '395515069', 'I will setup,optimize and do pinterest marketing, pins and boards', 'Hi,\r\nMy name is RArdita Ji, I have the skill to create a professional Pinterest presence for you. Your updated account will showcase your product, service or posts to millions of users searching for experts, boards & pins in your niche.\r\n\r\nClick here', 'new', '2025-03-19 11:53:05'),
(190, 'SEO specialist Reidy', 'reidy.miguel@msn.com', '1877235330', 'Skyrocket Your Google Rankings í ½íº€ Top-Notch Backlinks Await!', 'Tired of being buried in search results?\r\nThe solution is here!\r\n\r\nUnlock the power of premium monthly SEO backlinks designed to catapult your website to the top of Google. \r\nThis proven service is your secret weapon for:\r\n\r\n\r\nâœ¨ Boosting your searc', 'new', '2025-03-19 11:53:05'),
(191, 'Rosaline Gregor', 'rosaline.gregor@gmail.com', '7989483937', 'i have an inquiry', 'Transform your business reach with our ad-blasting service. For one flat rate, we send your message to millions of website contact forms. No per click costs - just pure results. Try it now!\r\n\r\n Letâ€™s connectâ€”contact me using the information provi', 'new', '2025-03-19 11:53:05'),
(192, 'Fredric Karn', 'karn.fredric43@gmail.com', '594086098', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(193, 'Taylah Hagelthorn', 'social@roccstarwireless.com', '95664919', 'seeking information', 'Make the switch and save : Unlimited Wireless for $19.99 â€“ Celebrity-Backed & Hassle-Free\r\n\r\nRoccstar Wireless\r\nroccstarwireless.com\r\n\r\nROCCSTAR Wireless, the ultimate mobile experience backed by celebrities and tailored for YOU.\r\n\r\n\r\nHereâ€™s what', 'new', '2025-03-19 11:53:05'),
(194, 'Janine Marriott', 'janine.marriott@gmail.com', '21-97-35-04', 'To the apsdreamhomes.com Administrator.', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(195, 'Kimber Upshaw', 'kimber.upshaw@msn.com', '0470-5971517', 'Dear apsdreamhomes.com Owner!', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(196, 'Brian And Dee Bojorquez', 'bojorquez.joey90@outlook.com', '698842786', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(197, 'Pintrest Marketer Nazario', 'nazario.cathleen@msn.com', '246577107', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(198, 'Michael Cook', 'info@unisoft.com', '2661739670', 'Official Microsoft Partner Offering Best Prices', 'Greetings,\r\n\r\nA quick query: Did you know you may be paying more than necessary for Microsoft products?\r\n\r\nI am asking because we provide authentic Microsoft software, helping you save money on top products, with a ongoing warranty and quick delivery', 'new', '2025-03-19 11:53:05'),
(199, 'Giselle Cox', 'bus.ine.ssfundingfo.rg.ro.wth@gmail.com', '2315696458', 'Funding Solutions Without Hurdles', 'Greetings,\r\n\r\nI wanted to tell you about a selection of funding solutions tailored to help businesses like yours grow and succeed. Whether youâ€™re looking to expand, improve equipment, or just boost cash flow, we offer customized financing options t', 'new', '2025-03-19 11:53:05'),
(200, 'Caroline Warf', 'morrismi1@outlook.com', '0312 3728140', 'Payment/deposits handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining our financial record. This position is only for USA citizens and cand', 'new', '2025-03-19 11:53:05'),
(201, 'Quentin Hiatt', 'morrismi1@outlook.com', '0345 6479304', 'Payment/deposits handler.', 'A remote job opportunity for a Law Firm, the role of a Payment/Deposit Handler. This position involves managing payments and deposits, ensuring accurate processing, and maintaining our financial record. This position is only for USA citizens and cand', 'new', '2025-03-19 11:53:05'),
(202, 'Milan Chapdelaine', 'chapdelaine.milan@gmail.com', '0304-3967089', 'Hello apsdreamhomes.com Admin.', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(203, 'Taren Doherty', 'doherty.taren@gmail.com', '03.79.05.27.97', 'To the apsdreamhomes.com Admin.', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\n\r\nMy hourly rate is $8\r\n\r\n\r\nMy expertise includes: \r\n\r\nWebsite design - c', 'new', '2025-03-19 11:53:05'),
(204, 'Linwood Hopley', 'linwood.hopley@msn.com', '6999487114', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > https://tinyurl.com/3ckxfu2c\r\nSee you there', 'new', '2025-03-19 11:53:05'),
(205, 'Pintrest Marketer Sample', 'sample.wade@gmail.com', '144283392', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(206, 'Mark Rogers', 'mark@reachoutcapital.com', '725-696-1589', 'Quick question', 'Need working capital? Check what you qualify for instantlyâ€”no credit check, \r\n\r\nno documents, no sales calls. Itâ€™s free and fully automated. \r\n\r\nTry now: reachoutcapital.com/approval\r\n\r\n\r\n\r\n\r\n', 'new', '2025-03-19 11:53:05'),
(207, 'Lauren Mann', 'e.m.powering.smallbu.s.iness.25@gmail.com', '183415082', 'Flexible Funding to Support Your Business', 'Hi,\r\n\r\nRunning a business is full of opportunities and challenges, and having the right resources at the right time can make all the difference. Whether youâ€™re preparing for growth, managing seasonal expenses, or responding to an unexpected need, h', 'new', '2025-03-19 11:53:05'),
(208, 'Pintrest Marketer Burroughs', 'burroughs.daisy@gmail.com', '6608477024', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(209, 'Jess Bendrodt', 'bendrodt.jess@msn.com', '370673008', 'Free leads on us!', 'Hey\r\n\r\nCome get your free leads on us!\r\n\r\nhttps://pcxleads.com/apsdreamhomes.com', 'new', '2025-03-19 11:53:05'),
(210, 'Emily Thompson', 'emily@reachoutcapital.biz', '725-605-4747', 'Hello,', 'Tired of dealing with slow, outdated loan approvals? Experience the future of\r\n\r\nworking capitalâ€”instant, hassle-free, and completely automated. \r\n\r\nSee what you qualify: reachoutcapital.com/approval\r\n\r\n\r\n', 'new', '2025-03-19 11:53:05'),
(211, 'Veta Flanery', 'flanery.veta@gmail.com', '81-66-75-89', 'Thank You! Save 3&% on SEO With Transparent Link Building', 'SAVE up to 37% on SEO! FREE Onsite SEO + TRANSPARENT link building at COST PRICE with just a 20% fee. Result: Expect a 214% increase in Sales on long term! Contact us now at eso.lus@gmail.com', 'new', '2025-03-19 11:53:05'),
(212, 'Krystle Mendes', 'mendes.krystle@gmail.com', '0734-3791055', 'Thank You! Save 3&% on SEO With Transparent Link Building', 'SAVE up to 37% on SEO! FREE Onsite SEO + TRANSPARENT link building at COST PRICE with just a 20% fee. Result: Expect a 214% increase in Sales on long term! Contact us now at eso.lus@gmail.com', 'new', '2025-03-19 11:53:05'),
(213, 'Vasily Kichigin Swafford', 'jonas.swafford@outlook.com', '6055340126', 'Instagram Professional and Modern content  !', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(214, 'Elizabeth Perry', 'i.sl.nds.a.les@gmail.com', '684869747', 'Learn about the clean-energy future of floating islands.', 'Looking for a exciting, green adventure on the water? Our Inflatable Cruising Island, launching on crowdfunding this February, is your gateway to zero-emission fun. Itâ€™s more than just a floating islandâ€”itâ€™s a leap into the future of water recr', 'new', '2025-03-19 11:53:05'),
(215, 'Sue Irving', 'sue.irving@gmail.com', '3450850526', 'investigation', 'Struggling to gain visibility? We deliver your ad text to millions of website contact forms at one flat rate. Guaranteed visibility without the hassle of per-click charges. Grow your business now.\r\n\r\n Reach out to me at my contact info below if youâ€', 'new', '2025-03-19 11:53:05'),
(216, 'Ella Williams', 'ella@reachoutcapital.com', '770-742-8023', 'Something to consider', 'Looking for quick working capital? Get approved instantlyâ€”no credit checks, no\r\n\r\npaperwork, just a smooth, easy process. \r\n\r\nSee what you qualify for today: reachoutcapital.com/approval \r\n\r\n', 'new', '2025-03-19 11:53:05'),
(217, 'Pintrest Marketer Ramer', 'ramer.regina94@msn.com', '474598361', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(218, 'Noel Neagle', 'noel.neagle@outlook.com', '682045292', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > \r\n\r\nhttps://shorturl.at/C2Nl9\r\n\r\nSee you th', 'new', '2025-03-19 11:53:05'),
(219, 'Brian And Dee Tavares', 'tavares.ronald@hotmail.com', '690986977', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(220, 'Brenda McCash', 'daganoy172@faxico.com', '414966490', '404 Not Found', 'Hey! \r\n\r\nLooking for long lost friends? Employees working at a certain company? Clients for your company ? Come try out our people and company search for free!\r\n\r\nhttps://marketingfff.com/Apsdreamhomes', 'new', '2025-03-19 11:53:05'),
(221, 'Claudine Small', 'info@globalwidepr.com', '12345678', 'Claim Your Complimentary Digital Journal Feature Right Away', 'Hi,\r\n\r\nYour brand deserves to stand out, and weâ€™re here to help.\r\n\r\nAt Global Wide PR, we specialize in connecting businesses with top media platforms to increase visibility and credibility. As a gesture to get started, weâ€™re offering a free arti', 'new', '2025-03-19 11:53:05'),
(222, 'Heike Brush', 'waviceg761@ixhale.com', '46875170', 'Ever Wondered How to Get Google to Send You Traffic?', 'Hi there,\r\n\r\nThereâ€™s a little-known trick that could make Google your best friend in just minutes. \r\n\r\nIf youâ€™ve ever wanted more free traffic for your offers, this might be what youâ€™re looking for https://marketingeee.com', 'new', '2025-03-19 11:53:05'),
(223, 'Pintrest Marketer Trenwith', 'trenwith.danny@gmail.com', '6502674556', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(224, 'Pintrest Marketer Northey', 'northey.gertie@outlook.com', '51167564', 'Are you Happy with Your Instagram account ?', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(225, 'Genesis Wiltshire', 'wiltshire.genesis@gmail.com', '3569453106', 'Somethingâ€™s not rightâ€”can you check?', 'If you print important documents on WATERPROOF NON-TEAR SCRATCH-PROOF paper,\r\nyou SAVE TREES.\r\nWe produce WORLDâ€™S TOUGHEST PAPERS for LASER PRINTING.\r\nThese are LIFE LONG DURABLE. No Trees cut. Made from recyclable polyester pulp.\r\nPrint certificat', 'new', '2025-03-19 11:53:05'),
(226, 'Fredric', 'info@vo.bangeshop.com', '7326488922', 'Real Estate PHP', 'Morning, \r\n\r\nI hope this email finds you well. I wanted to let you know about our new BANGE backpacks and sling bags that just released.\r\n\r\nBange is perfect for students, professionals and travelers. The backpacks and sling bags feature a built-in US', 'new', '2025-03-19 11:53:05'),
(227, 'Emily Thompson', 'emily@reachoutcapital.biz', '725-605-4747', 'Hello,', 'Tired of dealing with slow, outdated loan approvals? Experience the future of\r\n\r\nworking capitalâ€”instant, hassle-free, and completely automated. \r\n\r\nSee what you qualify: reachoutcapital.com/approval\r\n\r\n\r\n', 'new', '2025-03-19 11:53:05'),
(228, 'Claudine Mckenzie', 'info@globalwidepr.com', '12345678', 'Ready to Stand Out? Free Digital Journal Brand Feature Opportunity', 'Hi,\r\n\r\nYour brand deserves to stand out, and weâ€™re here to help.\r\n\r\nAt Global Wide PR, we specialize in connecting businesses with top media platforms to increase visibility and credibility. As a gesture to get started, weâ€™re offering a free arti', 'new', '2025-03-19 11:53:05'),
(229, 'Pintrest Marketer Vivier', 'vivier.yvette@msn.com', '7071472849', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(230, 'Kendall Poe', 'poe.kendall@googlemail.com', '6643300482', 'Help me figure this out?', 'Looking for fast and easy content creation? Try these 3 Amazing AI Tools: \r\n**Create professional videos  \r\n**Generate content effortlessly  \r\n**Convert text to speech seamlessly  \r\nTake your content to the next level today! http://3amazingaitools.to', 'new', '2025-03-19 11:53:05'),
(231, 'Felix Munn', 'felix.munn@gmail.com', '6505200917', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > \r\n\r\nhttps://shorturl.at/C2Nl9\r\n\r\nSee you th', 'new', '2025-03-19 11:53:05'),
(232, 'Ara Sumsuma', 'ara.sumsuma@outlook.com', '261068948', '10k Visitors for $1', 'Hey!\r\n\r\nWe have an abundance of internet traffic from our blogs, youtube channels, websites and instagram accounts!\r\n\r\nGet 10k visitors for $1\r\n\r\nWe send you traffic based on your niche and country!\r\n\r\nhttps://pcxleads.com', 'new', '2025-03-19 11:53:05'),
(233, 'Rigoberto Putilin', 'rigoberto.putilin@msn.com', '4491977162', 'Product Videos', 'We provide a free promo video for your company which you can use to post on your Instagram, Facebook, TikTok, or any other platform! No commitments, see what we can do!\r\n\r\nhttps://forms.gle/5rRw2SsYPzUVnzd89', 'new', '2025-03-19 11:53:05'),
(234, 'Emily Thompson', 'emily@reachoutcapital.com', '915-308-1515', 'Hey there!', 'Frustrated with slow and outdated loan approvals? \r\n\r\nStep into the future of working capitalâ€”fast, easy, and fully automated.\r\n\r\nFind out what you qualify for in less than 30 seconds at:  reachoutcapital.com/approval\r\n\r\n\r\n\r\nDisclosure: This is a p', 'new', '2025-03-19 11:53:05'),
(235, 'Ella Williams', 'ella@reachoutcapital.com', '915-308-1515', 'Good Day!', 'In need of fast working capital? \r\n\r\nGet approved in secondsâ€”no credit checks, no paperwork, \r\n\r\njust a hassle-free experience.\r\n\r\n\r\n\r\nFind out what you qualify for today: reachoutcapital.com/approval\r\n\r\n\r\n\r\n\r\nDisclosure: This is a paid advertiseme', 'new', '2025-03-19 11:53:05'),
(236, 'Felicity Sauncho', 'felicitysauncho02@gmail.com', '7275162474', 'Youtube Promotion: Grow your subscribers by 700 each month', 'Hi there,\r\n\r\nWe run a Youtube growth service, where we can increase your subscriber count safely and practically. \r\n\r\n- Guaranteed: We guarantee to gain you 700-1500 new subscribers each month.\r\n- Real, human subscribers who subscribe because they ar', 'new', '2025-03-19 11:53:05'),
(237, 'Brock Steigrad', 'brock.steigrad29@outlook.com', '66 445 59 41', 'Hi apsdreamhomes.com Webmaster!', 'Unlock Your Business Potential with Proven Marketing & Investor Relations Strategies\r\nAre you looking to amplify your brandâ€™s visibility, attract new investors + strengthen your market presence? With over 35 years of expertise in Investor Relations', 'new', '2025-03-19 11:53:05'),
(238, 'Jerilyn Beeson', 'beeson.jerilyn@gmail.com', '8583914351', 'Hi apsdreamhomes.com Administrator!', 'Unlock Your Business Potential with Proven Marketing & Investor Relations Strategies\r\nAre you looking to amplify your brandâ€™s visibility, attract new investors + strengthen your market presence? With over 35 years of expertise in Investor Relations', 'new', '2025-03-19 11:53:05'),
(239, 'Lucretia Chatfield', 'chatfield.lucretia@outlook.com', '06-25861310', 'Hello apsdreamhomes.com Admin.', 'Still looking to get your WordPress website done, fixed, or completed? Reach out to us at e.solus@gmail.com', 'new', '2025-03-19 11:53:05'),
(240, 'Kevin Barber', 'algeranoff.matthew@googlemail.com', '655984220', 'Day 1: Why Most Marketing Fails (And How to Make Yours Succeed)', 'Hi Apsdreamhomes,\r\n\r\nMost business owners pour money into marketing that doesnâ€™t work. They run ads, post on social media, and hope for the bestâ€”only to be disappointed by the results. \r\n\r\nThe problem? Theyâ€™re relying on vague branding tactics ', 'new', '2025-03-19 11:53:05'),
(241, 'Jill Hollars', 'jill.hollars59@hotmail.com', '070 4703 3520', 'Hello apsdreamhomes.com Owner!', 'Get More 5-Star Reviews & Rank Higher on Google! Contact us at reviewsninjas@gmail.com\r\n\r\nStruggling to get reviews? 92% of customers check them before choosing a business, and more 5-star reviews mean higher Google rankingsâ€”driving more leads and ', 'new', '2025-03-19 11:53:05'),
(242, 'Almeda Tang', 'almeda.tang@yahoo.com', '9255682320', 'Hello apsdreamhomes.com Admin!', 'Get More 5-Star Reviews & Rank Higher on Google! Contact us at reviewsninjas@gmail.com\r\n\r\nStruggling to get reviews? 92% of customers check them before choosing a business, and more 5-star reviews mean higher Google rankingsâ€”driving more leads and ', 'new', '2025-03-19 11:53:05'),
(243, 'Agnes Kahn', 'agnes.kahn@gmail.com', '01.30.01.46.05', 'Hello apsdreamhomes.com Owner!', '127 Queen St W, Apt 332  \r\nToronto, ON M5H 4G1, Canada  \r\nTel: +1 437 888 3824  \r\nEmail: tomlawman@opayq.com\r\n       tomlawman@edny.net \r\n\r\nToronto, February 24, 2025  \r\n\r\nDear Sir/Madam,\r\n\r\nI am Attorney Tom Lawman, principal partner at Tom Lawman L', 'new', '2025-03-19 11:53:05'),
(244, 'Minna Arthur', 'minna.arthur@msn.com', '567546926', 'Hi apsdreamhomes.com Owner!', '127 Queen St W, Apt 332  \r\nToronto, ON M5H 4G1, Canada  \r\nTel: +1 437 888 3824  \r\nEmail: tomlawman@opayq.com\r\n       tomlawman@edny.net \r\n\r\nToronto, February 24, 2025  \r\n\r\nDear Sir/Madam,\r\n\r\nI am Attorney Tom Lawman, principal partner at Tom Lawman L', 'new', '2025-03-19 11:53:05'),
(245, 'Cassandra Gibson', 'cassandra.gibson@gmail.com', '858-949-6606', 'To the apsdreamhomes.com Administrator.', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\nMy hourly rate is $8\r\n\r\nMy expertise includes:\r\n\r\nWebsite design - custom', 'new', '2025-03-19 11:53:05'),
(246, 'Pintrest Marketer Rumpf', 'rumpf.dylan53@googlemail.com', '7825975795', 'Are you Happy with Your Instagram account ?', 'Social media marketing boosts your business by capturing your target audience with contemporary, stylish content. \r\n\r\nLet me do for you Modern designs for Instagram, Facebook post design, Twitter, LinkedIn, Pinterest, TikTok, Shopify, and your websit', 'new', '2025-03-19 11:53:05'),
(247, 'Lida Lund', 'lund.lida@yahoo.com', '4875974018', 'Hello apsdreamhomes.com Owner!', 'Hi,\r\n\r\nI am a senior web developer, highly skilled and with 10+ years of collective web design and development experience, I work in one of the best web development company.\r\n\r\nMy hourly rate is $8\r\n\r\nMy expertise includes:\r\n\r\nWebsite design - custom', 'new', '2025-03-19 11:53:05'),
(248, 'Jayrn Marques', 'freddie.waddy@gmail.com', '3882177223', '[1]: The Game-Changer for Digital Marketers', 'Hi Apsdreamhomes,\r\n\r\nIn todayâ€™s competitive world of digital marketing, finding tools and systems that can help streamline the process while maximizing efficiency is essential. \r\n\r\nOne tool that has recently been gaining attention among savvy marke', 'new', '2025-03-19 11:53:05'),
(249, 'Lucinda McRae', 'lucinda.mcrae57@gmail.com', '0348 9486067', 'Hello apsdreamhomes.com Owner.', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(250, 'Francesca Abner', 'francesca.abner@msn.com', '6605010611', 'Dear apsdreamhomes.com Owner.', 'We improve MOZ  Domain authority 30+ in 15 Days its help to improve google rank, improve your website SEO, and you get traffic from google \r\n\r\nDA - 0 to 30 - (Only $29) - Yes, Limited time !!\r\n\r\n>> 100% Guarantee \r\n>> Improve Ranking \r\n>> White Hat P', 'new', '2025-03-19 11:53:05'),
(251, 'Pintrest Marketer Mullawirraburka', 'mullawirraburka.chance55@gmail.com', '296775787', 'Unlock Pinterestâ€™s Power for Your Brand!', 'Want to drive traffic & skyrocket your sales? \r\ní ½íº€\r\nLet a Pinterest marketing expert handle the hard work!\r\n\r\n\r\nâœ” Tailwind setup & optimization\r\n\r\nâœ” Expert Pinterest management\r\n\r\nâœ” Proven strategies for growth\r\n\r\n\r\ní ½í±‰ Boost Your Pinter', 'new', '2025-03-19 11:53:05'),
(252, 'Brian And Dee Heinz', 'charli.heinz@yahoo.com', '7976324075', 'Do you Have Tiktok account?', 'The TikTok social media platform has seen explosive growth over the last two years. It now has 500\r\nmillion users that are desperate for fun and exciting content and this is a massive opportunity for you\r\nto promote your business.\r\n\r\nI can help you t', 'new', '2025-03-19 11:53:05'),
(253, 'Cheryl Parker', 'cherylp@nextdayworkingcapital.com', '725-867-2209', 'Just a Thoughtâ€”Could This Help Your Business?', 'Business Funding on Your Termsâ€”No Credit Check, No Hassle.\r\n\r\nGet fast, flexible working capital without the usual roadblocks. \r\n\r\nInstant approvals, next-day funding, and no paperwork required. \r\n\r\nCheck your eligibility in 30 secondsâ€”100% free!', 'new', '2025-03-19 11:53:05'),
(254, 'Shavonne Huish', 'shavonne.huish@gmail.com', '3210916515', 'questioning', 'Are you tired of expensive and ineffective marketing strategies? Our service sends your ad text to millions of website contact forms at a flat rate. No extra costs. Your message will be read and noticed.\r\n\r\n Letâ€™s connectâ€”contact me using the inf', 'new', '2025-03-19 11:53:05'),
(255, 'Kevin Murphy', 'kevinm@nextdayworkingcapital.com', '725-867-2209', 'You Might Want to See This', 'Looking for working capital? See what you qualify for in 30 secondsâ€”no credit check, no paperwork, no sales calls. \r\n\r\nInstant approvals, next-day funding. Itâ€™s fast, free, and fully automated.\r\n\r\nGet started now: www.nextdayworkingcapital.com/ap', 'new', '2025-03-19 11:53:05'),
(256, 'Caridad Dicks', 'caridad.dicks@hotmail.com', '630978471', 'question', 'Boost Your Business with Google Reviews! â­\r\n\r\nLooking to grow your online reputation and attract more customers? Google reviews build trust, improve your search ranking, and make your business stand out. Positive reviews can be the deciding factor ', 'new', '2025-03-19 11:53:05'),
(257, 'Beverly Daws', 'daws.beverly@gmail.com', '485868249', 'Be Part of the Best in Our Professional Registry â€“ Increase Your Exposure!', '\r\n\r\nBoost Your Marketing Impact â€“ Get Listed in Our Directory Right Away!\r\n\r\nHello,\r\n\r\nReady to amplify your visibility? Our Services Directory is the ideal solution to increase your brand exposure and reach customers in need of your expertise.\r\n\r\n', 'new', '2025-03-19 11:53:05'),
(258, 'Philip Dalgleish', 'dalgleish.philip47@hotmail.com', '4168817875', 'Do you have enough  customers?', 'Do you need targeted Customers emails and phone numbers , so I am here to help you check out  my Fiverr 5 stares profile serving over 880 happy customers\r\n contact me here and tell me what you need  ===== > \r\n\r\nhttps://shorturl.at/C2Nl9\r\n\r\nSee you th', 'new', '2025-03-19 11:53:05'),
(259, 'Cheryl Parker', 'cherylp@nextdayworkingcapital.com', '725-867-2209', 'Just a Thoughtâ€”Could This Help Your Business?', 'Business Funding on Your Termsâ€”No Credit Check, No Hassle.\r\n\r\nGet fast, flexible working capital without the usual roadblocks. \r\n\r\nInstant approvals, next-day funding, and no paperwork required. \r\n\r\nCheck your eligibility in 30 secondsâ€”100% free!', 'new', '2025-03-19 11:53:05'),
(260, 'Claudine Hernandez', 'info@globalwidepr.com', '12345678', 'Take the Leap with a Complimentary Digital Journal Feature', 'Hi,\r\n\r\nYour brand deserves to stand out, and weâ€™re here to help.\r\n\r\nAt Global Wide PR, we specialize in connecting businesses with top media platforms to increase visibility and credibility. As a gesture to get started, weâ€™re offering a free arti', 'new', '2025-03-19 11:53:05'),
(261, 'Kevin Murphy', 'kevinm@nextdayworkingcapital.com', '725-867-2209', 'You Might Want to See This', 'Looking for working capital? See what you qualify for in 30 secondsâ€”no credit check, no paperwork, no sales calls. \r\n\r\nInstant approvals, next-day funding. Itâ€™s fast, free, and fully automated.\r\n\r\nGet started now: www.nextdayworkingcapital.com/ap', 'new', '2025-03-19 11:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `content_backups`
--

CREATE TABLE `content_backups` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `fid` int(50) NOT NULL,
  `uid` int(50) NOT NULL,
  `fdescription` varchar(300) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`fid`, `uid`, `fdescription`, `status`, `date`) VALUES
(7, 28, 'This is a demo feedback in order to use set it as Testimonial for the site. Just a simply dummy text rather than using lorem ipsum text lines.', 1, '2022-07-23 16:07:08'),
(8, 33, 'This is great. This is just great. Hmmm, just a dummy text for users feedback.', 1, '2022-07-23 21:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gata_master`
--

CREATE TABLE `gata_master` (
  `gata_id` int(11) NOT NULL,
  `site_id` int(25) NOT NULL,
  `gata_no` varchar(50) NOT NULL,
  `area` float NOT NULL,
  `available_area` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `gata_master`
--

INSERT INTO `gata_master` (`gata_id`, `site_id`, `gata_no`, `area`, `available_area`) VALUES
(1, 1, '513', 10464, 6464),
(2, 1, '500', 91124, 91124),
(3, 1, '508', 27032, 5112),
(4, 1, '509', 18312, 17812),
(5, 1, '510', 12208, 11708),
(6, 1, '511', 10464, 10464),
(7, 1, '512', 8720, 8720),
(8, 1, '513', 8720, 8720),
(9, 1, '564/628', 10464, 5464),
(10, 1, '565', 85020, 85020),
(11, 1, '579', 54500, 54500),
(12, 1, '563', 26160, 26160),
(13, 1, '561', 79352, 79352),
(14, 1, '499', 21364, 21364),
(15, 1, '514', 33136, 33136),
(16, 1, '556', 30520, 30520),
(17, 1, '562', 26160, 26160),
(18, 1, '566', 81532, 81532),
(19, 2, '1206', 20989, 12989),
(20, 2, '1207', 16040, 14040),
(21, 2, '1208', 13132, 10132),
(22, 2, '1375', 16145, 16145),
(23, 2, '1372', 5271, 5271),
(24, 2, '1373', 40369, 40369),
(25, 2, '1374', 13455, 13455),
(26, 2, '1357', 95379, 95379),
(27, 2, '1293', 23788, 23788),
(28, 2, '1266', 2583, 2583),
(29, 2, '1266', 22500, 22500),
(30, 2, '1267', 5702, 5702),
(31, 2, '1268', 5166, 5166),
(32, 2, '1211à¤•', 8720, 8720),
(33, 2, '1260', 19594, 19594),
(34, 2, '1260', 19593, 19593),
(1, 1, '513', 10464, 6464),
(2, 1, '500', 91124, 91124),
(3, 1, '508', 27032, 5112),
(4, 1, '509', 18312, 17812),
(5, 1, '510', 12208, 11708),
(6, 1, '511', 10464, 10464),
(7, 1, '512', 8720, 8720),
(8, 1, '513', 8720, 8720),
(9, 1, '564/628', 10464, 5464),
(10, 1, '565', 85020, 85020),
(11, 1, '579', 54500, 54500),
(12, 1, '563', 26160, 26160),
(13, 1, '561', 79352, 79352),
(14, 1, '499', 21364, 21364),
(15, 1, '514', 33136, 33136),
(16, 1, '556', 30520, 30520),
(17, 1, '562', 26160, 26160),
(18, 1, '566', 81532, 81532),
(19, 2, '1206', 20989, 12989),
(20, 2, '1207', 16040, 14040),
(21, 2, '1208', 13132, 10132),
(22, 2, '1375', 16145, 16145),
(23, 2, '1372', 5271, 5271),
(24, 2, '1373', 40369, 40369),
(25, 2, '1374', 13455, 13455),
(26, 2, '1357', 95379, 95379),
(27, 2, '1293', 23788, 23788),
(28, 2, '1266', 2583, 2583),
(29, 2, '1266', 22500, 22500),
(30, 2, '1267', 5702, 5702),
(31, 2, '1268', 5166, 5166),
(32, 2, '1211à¤•', 8720, 8720),
(33, 2, '1260', 19594, 19594),
(34, 2, '1260', 19593, 19593),
(1, 1, '513', 10464, 6464),
(2, 1, '500', 91124, 91124),
(3, 1, '508', 27032, 5112),
(4, 1, '509', 18312, 17812),
(5, 1, '510', 12208, 11708),
(6, 1, '511', 10464, 10464),
(7, 1, '512', 8720, 8720),
(8, 1, '513', 8720, 8720),
(9, 1, '564/628', 10464, 5464),
(10, 1, '565', 85020, 85020),
(11, 1, '579', 54500, 54500),
(12, 1, '563', 26160, 26160),
(13, 1, '561', 79352, 79352),
(14, 1, '499', 21364, 21364),
(15, 1, '514', 33136, 33136),
(16, 1, '556', 30520, 30520),
(17, 1, '562', 26160, 26160),
(18, 1, '566', 81532, 81532),
(19, 2, '1206', 20989, 12989),
(20, 2, '1207', 16040, 14040),
(21, 2, '1208', 13132, 10132),
(22, 2, '1375', 16145, 16145),
(23, 2, '1372', 5271, 5271),
(24, 2, '1373', 40369, 40369),
(25, 2, '1374', 13455, 13455),
(26, 2, '1357', 95379, 95379),
(27, 2, '1293', 23788, 23788),
(28, 2, '1266', 2583, 2583),
(29, 2, '1266', 22500, 22500),
(30, 2, '1267', 5702, 5702),
(31, 2, '1268', 5166, 5166),
(32, 2, '1211à¤•', 8720, 8720),
(33, 2, '1260', 19594, 19594),
(34, 2, '1260', 19593, 19593);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `title` varchar(100) NOT NULL,
  `image` longblob DEFAULT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `content`, `title`, `image`, `type`) VALUES
(4, '<p>get this offer</p>', 'offer', 0x6d6f746972616d2e6a7067, ''),
(5, '', 'aa', 0x6170732064657369676e2e6a7067, 'legal'),
(6, '', 'aaa', 0x6170732e6a7067, 'legal'),
(10, '<p>suryoday</p>', 'suryoday', 0x70726f6a6563745f32303233303430315f323032333032332d30312e706e67, 'suryoday'),
(15, '<p>certification of incorporation</p>', 'legal', 0x636f692e6a7067, 'legal'),
(16, '<p>director</p>', 'legal', 0x414c4c4f544d454e54204f46204449524543544f52204944454e54494649434154494f4e204e554d424552202844494e29202831295f706167652d303030312e6a7067, 'legal'),
(4, '<p>get this offer</p>', 'offer', 0x6d6f746972616d2e6a7067, ''),
(5, '', 'aa', 0x6170732064657369676e2e6a7067, 'legal'),
(6, '', 'aaa', 0x6170732e6a7067, 'legal'),
(10, '<p>suryoday</p>', 'suryoday', 0x70726f6a6563745f32303233303430315f323032333032332d30312e706e67, 'suryoday'),
(15, '<p>certification of incorporation</p>', 'legal', 0x636f692e6a7067, 'legal'),
(16, '<p>director</p>', 'legal', 0x414c4c4f544d454e54204f46204449524543544f52204944454e54494649434154494f4e204e554d424552202844494e29202831295f706167652d303030312e6a7067, 'legal'),
(4, '<p>get this offer</p>', 'offer', 0x6d6f746972616d2e6a7067, ''),
(5, '', 'aa', 0x6170732064657369676e2e6a7067, 'legal'),
(6, '', 'aaa', 0x6170732e6a7067, 'legal'),
(10, '<p>suryoday</p>', 'suryoday', 0x70726f6a6563745f32303233303430315f323032333032332d30312e706e67, 'suryoday'),
(15, '<p>certification of incorporation</p>', 'legal', 0x636f692e6a7067, 'legal'),
(16, '<p>director</p>', 'legal', 0x414c4c4f544d454e54204f46204449524543544f52204944454e54494649434154494f4e204e554d424552202844494e29202831295f706167652d303030312e6a7067, 'legal');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`name`, `phone`, `email`, `message`, `file_path`) VALUES
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
('djdj', 'hqdqh', 'hwdqh', 'hdqhdComments', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
('abhay kumar singh', '07007444842', 'aps@gmail.com', 'Commtrielents', ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, ''),
(NULL, NULL, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `kissan_master`
--

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

--
-- Dumping data for table `kissan_master`
--

INSERT INTO `kissan_master` (`kissan_id`, `site_id`, `gata_a`, `gata_b`, `gata_c`, `gata_d`, `area_gata_a`, `area_gata_b`, `area_gata_c`, `area_gata_d`, `k_name`, `k_adhaar`, `area`) VALUES
(1, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'ramfal maurya', 2147483647, 5232),
(2, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'SitaRam Maurya', 2147483647, 5232),
(3, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Ravindar Maurya', 2147483647, 5232),
(4, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Shivshankar Maurya', 2147483647, 5232),
(8, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Heera', 2147483647, 8022),
(9, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Mewalal', 2147483647, 9068),
(10, 2, 25, 0, 0, 0, 1682, 0, 0, 0, 'Rambilash', 2147483647, 1682),
(1, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'ramfal maurya', 2147483647, 5232),
(2, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'SitaRam Maurya', 2147483647, 5232),
(3, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Ravindar Maurya', 2147483647, 5232),
(4, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Shivshankar Maurya', 2147483647, 5232),
(8, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Heera', 2147483647, 8022),
(9, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Mewalal', 2147483647, 9068),
(10, 2, 25, 0, 0, 0, 1682, 0, 0, 0, 'Rambilash', 2147483647, 1682),
(1, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'ramfal maurya', 2147483647, 5232),
(2, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'SitaRam Maurya', 2147483647, 5232),
(3, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Ravindar Maurya', 2147483647, 5232),
(4, 2, 19, 0, 0, 0, 5232, 0, 0, 0, 'Shivshankar Maurya', 2147483647, 5232),
(8, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Heera', 2147483647, 8022),
(9, 2, 23, 24, 0, 0, 1046, 8022, 0, 0, 'Mewalal', 2147483647, 9068),
(10, 2, 25, 0, 0, 0, 1682, 0, 0, 0, 'Rambilash', 2147483647, 1682);

-- --------------------------------------------------------

--
-- Table structure for table `layout_templates`
--

CREATE TABLE `layout_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `lead_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL COMMENT 'Where the lead came from (website, referral, etc.)',
  `status` enum('new','contacted','qualified','unqualified') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL COMMENT 'User ID of the agent/associate assigned to this lead',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `size` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migration_errors`
--

CREATE TABLE `migration_errors` (
  `id` int(11) NOT NULL,
  `error_message` text NOT NULL,
  `affected_uid` varchar(10) DEFAULT NULL,
  `error_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `opportunity_id` int(11) NOT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stage` enum('prospecting','qualification','needs_analysis','proposal','negotiation','closed_won','closed_lost') NOT NULL DEFAULT 'prospecting',
  `probability` int(3) DEFAULT 0 COMMENT 'Probability of closing (0-100%)',
  `expected_close_date` date DEFAULT NULL,
  `actual_close_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `property_interest` int(11) DEFAULT NULL COMMENT 'Property ID the customer is interested in',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `layout` varchar(50) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_temp`
--

CREATE TABLE `password_reset_temp` (
  `email` varchar(250) NOT NULL,
  `key` varchar(250) NOT NULL,
  `expDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

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

--
-- Dumping data for table `plots`
--

INSERT INTO `plots` (`id`, `project_name`, `block_code`, `plot_id`, `status`, `breadth`, `length`, `total_size`, `description`) VALUES
(1, 'suryoday colony', 'B', 'B-56', 'available', 25, 40, 1000, 'Intermittent Plot West Phase'),
(2, 'Raghunat Nagri', 'A', 'A506A', 'booked', 25, 40, 1000, 'Intermittent Plot North Phase'),
(3, 'Raghunath nagri', 'B', 'B109', 'available', 25, 40, 1000, 'Intermittent Plot East Phase'),
(4, 'Raghunath nagri', 'B', 'B108', 'sold', 16, 50, 800, 'Intermittent Plot East Phase'),
(5, 'suryoday colony', 'B', 'B107A', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(6, 'suryoday colony', 'B', 'B107B', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(7, 'suryoday colony', 'B', 'B78', 'sold', 25, 40, 1000, 'Intermittent Plot West Phase'),
(8, 'suryoday colony', 'B', 'B110', 'booked', 25, 40, 1000, 'Intermittent Plot East Phase'),
(9, 'suryoday colony', 'A', 'A4', 'sold', 40, 100, 2400, 'Intermittent Plot East Phase'),
(10, 'suryoday colony', 'A', 'A44', 'available', 160, 50, 8000, 'Intermittent Plot West Phase'),
(1, 'suryoday colony', 'B', 'B-56', 'available', 25, 40, 1000, 'Intermittent Plot West Phase'),
(2, 'Raghunat Nagri', 'A', 'A506A', 'booked', 25, 40, 1000, 'Intermittent Plot North Phase'),
(3, 'Raghunath nagri', 'B', 'B109', 'available', 25, 40, 1000, 'Intermittent Plot East Phase'),
(4, 'Raghunath nagri', 'B', 'B108', 'sold', 16, 50, 800, 'Intermittent Plot East Phase'),
(5, 'suryoday colony', 'B', 'B107A', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(6, 'suryoday colony', 'B', 'B107B', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(7, 'suryoday colony', 'B', 'B78', 'sold', 25, 40, 1000, 'Intermittent Plot West Phase'),
(8, 'suryoday colony', 'B', 'B110', 'booked', 25, 40, 1000, 'Intermittent Plot East Phase'),
(9, 'suryoday colony', 'A', 'A4', 'sold', 40, 100, 2400, 'Intermittent Plot East Phase'),
(10, 'suryoday colony', 'A', 'A44', 'available', 160, 50, 8000, 'Intermittent Plot West Phase'),
(1, 'suryoday colony', 'B', 'B-56', 'available', 25, 40, 1000, 'Intermittent Plot West Phase'),
(2, 'Raghunat Nagri', 'A', 'A506A', 'booked', 25, 40, 1000, 'Intermittent Plot North Phase'),
(3, 'Raghunath nagri', 'B', 'B109', 'available', 25, 40, 1000, 'Intermittent Plot East Phase'),
(4, 'Raghunath nagri', 'B', 'B108', 'sold', 16, 50, 800, 'Intermittent Plot East Phase'),
(5, 'suryoday colony', 'B', 'B107A', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(6, 'suryoday colony', 'B', 'B107B', 'sold', 20, 30, 600, 'Intermittent Plot East Phase'),
(7, 'suryoday colony', 'B', 'B78', 'sold', 25, 40, 1000, 'Intermittent Plot West Phase'),
(8, 'suryoday colony', 'B', 'B110', 'booked', 25, 40, 1000, 'Intermittent Plot East Phase'),
(9, 'suryoday colony', 'A', 'A4', 'sold', 40, 100, 2400, 'Intermittent Plot East Phase'),
(10, 'suryoday colony', 'A', 'A44', 'available', 160, 50, 8000, 'Intermittent Plot West Phase');

-- --------------------------------------------------------

--
-- Table structure for table `plot_categories`
--

CREATE TABLE `plot_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `plot_categories`
--

INSERT INTO `plot_categories` (`id`, `category_name`) VALUES
(1, 'ALL'),
(2, 'Available'),
(3, 'Booked'),
(4, 'Hold'),
(5, 'Sold Out'),
(1, 'ALL'),
(2, 'Available'),
(3, 'Booked'),
(4, 'Hold'),
(5, 'Sold Out'),
(1, 'ALL'),
(2, 'Available'),
(3, 'Booked'),
(4, 'Hold'),
(5, 'Sold Out');

-- --------------------------------------------------------

--
-- Table structure for table `plot_master`
--

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

--
-- Dumping data for table `plot_master`
--

INSERT INTO `plot_master` (`plot_id`, `site_id`, `gata_a`, `gata_b`, `gata_c`, `gata_d`, `area_gata_a`, `area_gata_b`, `area_gata_c`, `area_gata_d`, `plot_no`, `area`, `available_area`, `plot_dimension`, `plot_facing`, `plot_price`, `plot_status`) VALUES
(1, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C6', 1500, 0, '30x50', 'North', 1200, 'Available'),
(2, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C7', 1500, 0, NULL, NULL, NULL, NULL),
(3, 1, 3, NULL, NULL, NULL, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(4, 1, 3, NULL, NULL, NULL, 4800, 0, 0, 0, 'A1-A5', 4800, 0, NULL, NULL, NULL, NULL),
(5, 1, 3, NULL, NULL, NULL, 1920, 0, 0, 0, 'C1', 1920, 0, NULL, NULL, NULL, NULL),
(6, 1, 3, NULL, NULL, NULL, 2000, 0, 0, 0, 'C2', 2000, 0, NULL, NULL, NULL, NULL),
(7, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A7', 1000, 0, NULL, NULL, NULL, NULL),
(8, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A8', 1000, 0, NULL, NULL, NULL, NULL),
(9, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A9', 1000, 0, NULL, NULL, NULL, NULL),
(10, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A10', 1000, 0, NULL, NULL, NULL, NULL),
(11, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A11', 1000, 0, NULL, NULL, NULL, NULL),
(12, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A12', 1000, 0, NULL, NULL, NULL, NULL),
(13, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'CL10', 1000, 0, NULL, NULL, NULL, NULL),
(14, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C13', 1500, 0, NULL, NULL, NULL, NULL),
(15, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C14', 1500, 0, NULL, NULL, NULL, NULL),
(16, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'B31', 1000, 0, NULL, NULL, NULL, NULL),
(18, 1, 4, 5, 0, 0, 500, 500, 0, 0, 'A18', 1000, 0, NULL, NULL, NULL, NULL),
(19, 1, 3, 0, 0, 0, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(21, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A1', 1000, 0, NULL, NULL, NULL, NULL),
(22, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A19', 1000, 0, NULL, NULL, NULL, NULL),
(23, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A20', 1000, 0, NULL, NULL, NULL, NULL),
(24, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A21', 1000, 0, NULL, NULL, NULL, NULL),
(25, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A22', 1000, 0, NULL, NULL, NULL, NULL),
(26, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A37', 1000, 0, NULL, NULL, NULL, NULL),
(27, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B1', 1000, 0, NULL, NULL, NULL, NULL),
(28, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B2', 1000, 0, NULL, NULL, NULL, NULL),
(29, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B19', 1000, 0, NULL, NULL, NULL, NULL),
(30, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B20', 1000, 0, NULL, NULL, NULL, NULL),
(31, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B21', 1000, 0, NULL, NULL, NULL, NULL),
(32, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A24', 1000, 0, NULL, NULL, NULL, NULL),
(33, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A25', 1000, 0, '20x50', 'East', 750, 'Available'),
(34, 1, 1, 0, 0, 0, 1000, 0, 0, 0, 'A2', 1000, 0, '20x50', 'East', 850, 'Available'),
(1, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C6', 1500, 0, '30x50', 'North', 1200, 'Available'),
(2, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C7', 1500, 0, NULL, NULL, NULL, NULL),
(3, 1, 3, NULL, NULL, NULL, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(4, 1, 3, NULL, NULL, NULL, 4800, 0, 0, 0, 'A1-A5', 4800, 0, NULL, NULL, NULL, NULL),
(5, 1, 3, NULL, NULL, NULL, 1920, 0, 0, 0, 'C1', 1920, 0, NULL, NULL, NULL, NULL),
(6, 1, 3, NULL, NULL, NULL, 2000, 0, 0, 0, 'C2', 2000, 0, NULL, NULL, NULL, NULL),
(7, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A7', 1000, 0, NULL, NULL, NULL, NULL),
(8, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A8', 1000, 0, NULL, NULL, NULL, NULL),
(9, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A9', 1000, 0, NULL, NULL, NULL, NULL),
(10, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A10', 1000, 0, NULL, NULL, NULL, NULL),
(11, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A11', 1000, 0, NULL, NULL, NULL, NULL),
(12, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A12', 1000, 0, NULL, NULL, NULL, NULL),
(13, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'CL10', 1000, 0, NULL, NULL, NULL, NULL),
(14, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C13', 1500, 0, NULL, NULL, NULL, NULL),
(15, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C14', 1500, 0, NULL, NULL, NULL, NULL),
(16, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'B31', 1000, 0, NULL, NULL, NULL, NULL),
(18, 1, 4, 5, 0, 0, 500, 500, 0, 0, 'A18', 1000, 0, NULL, NULL, NULL, NULL),
(19, 1, 3, 0, 0, 0, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(21, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A1', 1000, 0, NULL, NULL, NULL, NULL),
(22, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A19', 1000, 0, NULL, NULL, NULL, NULL),
(23, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A20', 1000, 0, NULL, NULL, NULL, NULL),
(24, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A21', 1000, 0, NULL, NULL, NULL, NULL),
(25, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A22', 1000, 0, NULL, NULL, NULL, NULL),
(26, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A37', 1000, 0, NULL, NULL, NULL, NULL),
(27, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B1', 1000, 0, NULL, NULL, NULL, NULL),
(28, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B2', 1000, 0, NULL, NULL, NULL, NULL),
(29, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B19', 1000, 0, NULL, NULL, NULL, NULL),
(30, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B20', 1000, 0, NULL, NULL, NULL, NULL),
(31, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B21', 1000, 0, NULL, NULL, NULL, NULL),
(32, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A24', 1000, 0, NULL, NULL, NULL, NULL),
(33, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A25', 1000, 0, '20x50', 'East', 750, 'Available'),
(34, 1, 1, 0, 0, 0, 1000, 0, 0, 0, 'A2', 1000, 0, '20x50', 'East', 850, 'Available'),
(1, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C6', 1500, 0, '30x50', 'North', 1200, 'Available'),
(2, 1, 1, NULL, NULL, NULL, 1500, 0, 0, 0, 'C7', 1500, 0, NULL, NULL, NULL, NULL),
(3, 1, 3, NULL, NULL, NULL, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(4, 1, 3, NULL, NULL, NULL, 4800, 0, 0, 0, 'A1-A5', 4800, 0, NULL, NULL, NULL, NULL),
(5, 1, 3, NULL, NULL, NULL, 1920, 0, 0, 0, 'C1', 1920, 0, NULL, NULL, NULL, NULL),
(6, 1, 3, NULL, NULL, NULL, 2000, 0, 0, 0, 'C2', 2000, 0, NULL, NULL, NULL, NULL),
(7, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A7', 1000, 0, NULL, NULL, NULL, NULL),
(8, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A8', 1000, 0, NULL, NULL, NULL, NULL),
(9, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A9', 1000, 0, NULL, NULL, NULL, NULL),
(10, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A10', 1000, 0, NULL, NULL, NULL, NULL),
(11, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A11', 1000, 0, NULL, NULL, NULL, NULL),
(12, 1, 3, NULL, NULL, NULL, 1000, 0, 0, 0, 'A12', 1000, 0, NULL, NULL, NULL, NULL),
(13, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'CL10', 1000, 0, NULL, NULL, NULL, NULL),
(14, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C13', 1500, 0, NULL, NULL, NULL, NULL),
(15, 1, 9, NULL, NULL, NULL, 1500, 0, 0, 0, 'C14', 1500, 0, NULL, NULL, NULL, NULL),
(16, 1, 9, NULL, NULL, NULL, 1000, 0, 0, 0, 'B31', 1000, 0, NULL, NULL, NULL, NULL),
(18, 1, 4, 5, 0, 0, 500, 500, 0, 0, 'A18', 1000, 0, NULL, NULL, NULL, NULL),
(19, 1, 3, 0, 0, 0, 2400, 0, 0, 0, 'A6', 2400, 0, NULL, NULL, NULL, NULL),
(21, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A1', 1000, 0, NULL, NULL, NULL, NULL),
(22, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A19', 1000, 0, NULL, NULL, NULL, NULL),
(23, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A20', 1000, 0, NULL, NULL, NULL, NULL),
(24, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A21', 1000, 0, NULL, NULL, NULL, NULL),
(25, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A22', 1000, 0, NULL, NULL, NULL, NULL),
(26, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A37', 1000, 0, NULL, NULL, NULL, NULL),
(27, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B1', 1000, 0, NULL, NULL, NULL, NULL),
(28, 2, 20, 0, 0, 0, 1000, 0, 0, 0, 'B2', 1000, 0, NULL, NULL, NULL, NULL),
(29, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B19', 1000, 0, NULL, NULL, NULL, NULL),
(30, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B20', 1000, 0, NULL, NULL, NULL, NULL),
(31, 2, 21, 0, 0, 0, 1000, 0, 0, 0, 'B21', 1000, 0, NULL, NULL, NULL, NULL),
(32, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A24', 1000, 0, NULL, NULL, NULL, NULL),
(33, 2, 19, 0, 0, 0, 1000, 0, 0, 0, 'A25', 1000, 0, '20x50', 'East', 750, 'Available'),
(34, 1, 1, 0, 0, 0, 1000, 0, 0, 0, 'A2', 1000, 0, '20x50', 'East', 850, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

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

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`bid`, `builder_id`, `project_name`, `status`, `description`, `start_date`, `end_date`, `budget`) VALUES
(1, 83, 'Raghunath Nagrii', 'Active', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', '2024-04-05', '2026-07-30', 0.00),
(1, 83, 'Raghunath Nagrii', 'Active', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', '2024-04-05', '2026-07-30', 0.00),
(1, 83, 'Raghunath Nagrii', 'Active', 'Raghunath Nagri is a vibrant residential community known for its peaceful environment and modern amenities. The colony features well-planned layouts, green spaces, and a variety of housing options, catering to families and individuals alike.\r\n\r\nKey roads, including Road 30, Road 40, and Road 50, enhance accessibility within the colony and connect residents to nearby urban centers. These roads are designed to accommodate smooth traffic flow and provide easy access to essential facilities such as parks, schools, shopping areas, and healthcare services.\r\n\r\nThe strategic location of Raghunath Nagri, with its proximity to major thoroughfares, ensures that residents enjoy both tranquility and convenience, making it an ideal place to live. Overall, Raghunath Nagri embodies a blend of comfort, community spirit, and accessibility.', '2024-04-05', '2026-07-30', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE `project_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `pid` int(50) NOT NULL,
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
  `isFeatured` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`pid`, `title`, `pcontent`, `type`, `bhk`, `stype`, `bedroom`, `bathroom`, `balcony`, `kitchen`, `hall`, `floor`, `size`, `price`, `location`, `city`, `state`, `feature`, `pimage`, `pimage1`, `pimage2`, `pimage3`, `pimage4`, `uid`, `status`, `mapimage`, `topmapimage`, `groundmapimage`, `totalfloor`, `date`, `isFeatured`) VALUES
(25, 'Zills Home', '', 'house', '4 BHK', 'sale', 4, 2, 0, 1, 1, '2nd Floor', 1869, 219690, '39 Bailey Drive', 'Floson', 'Colotana', '<p>&nbsp;</p>\r\n<!---feature area start--->\r\n<div class=\"col-md-4\">\r\n<ul>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Property Age : </span>10 Years</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Swiming Pool : </span>Yes</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Parking : </span>Yes</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">GYM : </span>Yes</li>\r\n</ul>\r\n</div>\r\n<div class=\"col-md-4\">\r\n<ul>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Type : </span>Appartment</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Security : </span>Yes</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Dining Capacity : </span>10 People</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Church/Temple : </span>Yes</li>\r\n</ul>\r\n</div>\r\n<div class=\"col-md-4\">\r\n<ul>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">3rd Party : </span>No</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Elevator : </span>Yes</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">CCTV : </span>Yes</li>\r\n<li class=\"mb-3\"><span class=\"text-secondary font-weight-bold\">Water Supply : </span>Ground Water / Tank</li>\r\n</ul>\r\n</div>\r\n<!---feature area end---->\r\n<p>&nbsp;</p>', 'zillhms1.jpg', 'zillhms2.jpg', 'zillhms3.jpg', 'zillhms4.jpg', 'zillhms5.jpg', 30, 'available', 'floorplan_sample.jpg', 'zillhms7.jpg', 'zillhms6.jpg', '2 Floor', '2022-07-22 22:29:20', 0),
(30, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:16:30', 1),
(31, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:16:30', 1),
(32, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:16:30', 0),
(33, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:16:30', 1),
(34, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:16:30', 0),
(35, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:17:16', 1),
(36, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:17:16', 1),
(37, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:17:16', 0),
(38, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:17:16', 1),
(39, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:17:16', 0),
(40, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:24:24', 1),
(41, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:24:24', 1),
(42, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:24:24', 0),
(43, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:24:24', 1),
(44, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:24:24', 0),
(45, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:25:53', 1),
(46, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:25:53', 1),
(47, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:25:53', 0),
(48, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:25:53', 1),
(49, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:25:53', 0),
(50, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:33:03', 1),
(51, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:33:03', 1),
(52, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:33:03', 0),
(53, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:33:03', 1),
(54, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:33:03', 0),
(55, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-15 10:47:53', 1),
(56, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-15 10:47:53', 1),
(57, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-15 10:47:53', 0),
(58, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-15 10:47:53', 1),
(59, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-15 10:47:53', 0),
(60, 'Luxury Villa in Gomti Nagar', 'Beautiful luxury villa with modern amenities and spacious rooms. Perfect for family living with garden and parking space.', 'Villa', '4 BHK', 'sale', 4, 3, 2, 1, 1, '2', 2500, 15000000, 'Gomti Nagar', 'Lucknow', 'Uttar Pradesh', 'Swimming Pool, Garden, Parking, Security, Power Backup', 'property1.jpg', 'property1a.jpg', 'property1b.jpg', 'property1c.jpg', 'property1d.jpg', 3, 'available', 'map1.jpg', 'topmap1.jpg', 'groundmap1.jpg', '2', '2025-04-18 17:08:35', 1),
(61, 'Modern Apartment in Hazratganj', 'Contemporary apartment with excellent city views. Well-connected location with all modern facilities.', 'Apartment', '3 BHK', 'rent', 3, 2, 1, 1, 1, '5', 1500, 25000, 'Hazratganj', 'Lucknow', 'Uttar Pradesh', 'Lift, Security, Power Backup, Parking', 'property2.jpg', 'property2a.jpg', 'property2b.jpg', 'property2c.jpg', 'property2d.jpg', 3, 'available', 'map2.jpg', 'topmap2.jpg', 'groundmap2.jpg', '10', '2025-04-18 17:08:35', 1),
(62, 'Commercial Space in Indira Nagar', 'Prime commercial property in busy market area. Excellent for retail or office space with high footfall.', 'Shop', '0 BHK', 'sale', 0, 1, 0, 0, 1, '1', 800, 7500000, 'Indira Nagar', 'Lucknow', 'Uttar Pradesh', 'Parking, Security, Power Backup', 'property3.jpg', 'property3a.jpg', 'property3b.jpg', 'property3c.jpg', 'property3d.jpg', 4, 'available', 'map3.jpg', 'topmap3.jpg', 'groundmap3.jpg', '1', '2025-04-18 17:08:35', 0),
(63, 'Residential Plot in Raghunath Nagri', 'Well-located residential plot in developing area. Good investment opportunity with future growth potential.', 'Residential Plot', '0 BHK', 'sale', 0, 0, 0, 0, 0, '0', 1200, 3600000, 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 'Electricity, Water Connection, Road Access', 'property4.jpg', 'property4a.jpg', 'property4b.jpg', 'property4c.jpg', 'property4d.jpg', 5, 'available', 'map4.jpg', 'topmap4.jpg', 'groundmap4.jpg', '0', '2025-04-18 17:08:35', 1),
(64, 'Office Space in Ganga Nagri', 'Modern office space with all business amenities. Located in business district with excellent connectivity.', 'Office Space', '0 BHK', 'rent', 0, 2, 0, 1, 1, '3', 1000, 45000, 'Ganga Nagri', 'Varanasi', 'Uttar Pradesh', 'Air Conditioning, Parking, Security, Conference Room', 'property5.jpg', 'property5a.jpg', 'property5b.jpg', 'property5c.jpg', 'property5d.jpg', 5, 'available', 'map5.jpg', 'topmap5.jpg', 'groundmap5.jpg', '5', '2025-04-18 17:08:35', 0);

-- --------------------------------------------------------

--
-- Table structure for table `property_type`
--

CREATE TABLE `property_type` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_type`
--

INSERT INTO `property_type` (`id`, `type`, `description`, `status`, `date`) VALUES
(1, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 04:31:17'),
(2, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 04:31:17'),
(3, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 04:31:17'),
(4, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 04:31:17'),
(5, 'Shop', 'Commercial retail space', 1, '2025-04-15 04:31:17'),
(6, 'Office Space', 'Commercial office space', 1, '2025-04-15 04:31:17'),
(7, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 04:35:18'),
(8, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 04:35:18'),
(9, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 04:35:18'),
(10, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 04:35:18'),
(11, 'Shop', 'Commercial retail space', 1, '2025-04-15 04:35:18'),
(12, 'Office Space', 'Commercial office space', 1, '2025-04-15 04:35:18'),
(13, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 04:46:30'),
(14, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 04:46:30'),
(15, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 04:46:30'),
(16, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 04:46:30'),
(17, 'Shop', 'Commercial retail space', 1, '2025-04-15 04:46:30'),
(18, 'Office Space', 'Commercial office space', 1, '2025-04-15 04:46:30'),
(19, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 04:47:16'),
(20, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 04:47:16'),
(21, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 04:47:16'),
(22, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 04:47:16'),
(23, 'Shop', 'Commercial retail space', 1, '2025-04-15 04:47:16'),
(24, 'Office Space', 'Commercial office space', 1, '2025-04-15 04:47:16'),
(25, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 04:55:27'),
(26, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 04:55:27'),
(27, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 04:55:27'),
(28, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 04:55:27'),
(29, 'Shop', 'Commercial retail space', 1, '2025-04-15 04:55:27'),
(30, 'Office Space', 'Commercial office space', 1, '2025-04-15 04:55:27'),
(31, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 05:03:03'),
(32, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 05:03:03'),
(33, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 05:03:03'),
(34, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 05:03:03'),
(35, 'Shop', 'Commercial retail space', 1, '2025-04-15 05:03:03'),
(36, 'Office Space', 'Commercial office space', 1, '2025-04-15 05:03:03'),
(37, 'Residential Plot', 'Land for residential building construction', 1, '2025-04-15 05:17:53'),
(38, 'Commercial Plot', 'Land for commercial building construction', 1, '2025-04-15 05:17:53'),
(39, 'Villa', 'Independent luxury house with garden', 1, '2025-04-15 05:17:53'),
(40, 'Apartment', 'Unit in multi-dwelling building', 1, '2025-04-15 05:17:53'),
(41, 'Shop', 'Commercial retail space', 1, '2025-04-15 05:17:53'),
(42, 'Office Space', 'Commercial office space', 1, '2025-04-15 05:17:53');

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resell_plots`
--

CREATE TABLE `resell_plots` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `selling_type` varchar(100) NOT NULL,
  `plot_location` varchar(255) NOT NULL,
  `plot_size` float NOT NULL,
  `plot_dimensions` varchar(100) NOT NULL,
  `plot_facing` varchar(100) NOT NULL,
  `road_access` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `plot_category` varchar(100) NOT NULL,
  `full_address` text NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_master`
--

CREATE TABLE `site_master` (
  `site_id` int(10) NOT NULL,
  `site_name` varchar(200) NOT NULL,
  `district` varchar(100) NOT NULL,
  `tehsil` varchar(200) NOT NULL,
  `gram` varchar(300) NOT NULL,
  `area` float NOT NULL,
  `available_area` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `site_master`
--

INSERT INTO `site_master` (`site_id`, `site_name`, `district`, `tehsil`, `gram`, `area`, `available_area`) VALUES
(1, 'Suryoday Colony', 'Gorakhpur', 'Sadar', 'Prempur', 696960, 61708),
(2, 'Raghunath Nagari', 'Gorakhpur', 'Sadar', 'Rampur Tappa Rajdhani', 479160, 150734),
(1, 'Suryoday Colony', 'Gorakhpur', 'Sadar', 'Prempur', 696960, 61708),
(2, 'Raghunath Nagari', 'Gorakhpur', 'Sadar', 'Rampur Tappa Rajdhani', 479160, 150734),
(1, 'Suryoday Colony', 'Gorakhpur', 'Sadar', 'Prempur', 696960, 61708),
(2, 'Raghunath Nagari', 'Gorakhpur', 'Sadar', 'Rampur Tappa Rajdhani', 479160, 150734);

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'header_menu_items', '[\r\n    {\"text\": \"Home\", \"url\": \"/\", \"icon\": \"fa-home\"},\r\n    {\"text\": \"Properties\", \"url\": \"/property.php\", \"icon\": \"fa-building\"},\r\n    {\"text\": \"About\", \"url\": \"/about.php\", \"icon\": \"fa-info-circle\"},\r\n    {\"text\": \"Contact\", \"url\": \"/contact.php\", \"icon\": \"fa-envelope\"}\r\n]', '2025-04-10 21:52:28', '2025-04-10 21:52:28'),
(2, 'site_logo', 'assets/images/logo.png', '2025-04-10 21:52:28', '2025-04-10 21:52:28'),
(3, 'header_styles', '{\r\n    \"background\": \"#1e3c72\",\r\n    \"text_color\": \"#ffffff\"\r\n}', '2025-04-10 21:52:28', '2025-04-10 21:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `sponsor_running_no`
--

CREATE TABLE `sponsor_running_no` (
  `current_no` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sponsor_running_no`
--

INSERT INTO `sponsor_running_no` (`current_no`) VALUES
(44),
(44),
(44);

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `sid` int(50) NOT NULL,
  `sname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`sid`, `sname`) VALUES
(2, 'Colotana'),
(3, 'Floaii'),
(4, 'Virconsin'),
(7, 'West Misstana\n\n'),
(9, 'New Pennrk\n\n'),
(10, 'Louiswa\n\n'),
(15, 'Wisginia\n\n'),
(16, 'Uttar Pradesh'),
(17, 'Bihar'),
(18, 'Uttar Pradesh'),
(19, 'Delhi'),
(20, 'Maharashtra'),
(21, 'Karnataka'),
(22, 'Tamil Nadu'),
(23, 'Uttar Pradesh'),
(24, 'Delhi'),
(25, 'Maharashtra'),
(26, 'Karnataka'),
(27, 'Tamil Nadu'),
(28, 'Uttar Pradesh'),
(29, 'Delhi'),
(30, 'Maharashtra'),
(31, 'Karnataka'),
(32, 'Tamil Nadu'),
(33, 'Uttar Pradesh'),
(34, 'Delhi'),
(35, 'Maharashtra'),
(36, 'Karnataka'),
(37, 'Tamil Nadu'),
(38, 'Uttar Pradesh'),
(39, 'Delhi'),
(40, 'Maharashtra'),
(41, 'Karnataka'),
(42, 'Tamil Nadu'),
(43, 'Uttar Pradesh'),
(44, 'Delhi'),
(45, 'Maharashtra'),
(46, 'Karnataka'),
(47, 'Tamil Nadu'),
(48, 'Uttar Pradesh'),
(49, 'Delhi'),
(50, 'Maharashtra'),
(51, 'Karnataka'),
(52, 'Tamil Nadu');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_hierarchy`
--

CREATE TABLE `team_hierarchy` (
  `id` int(11) NOT NULL,
  `associate_id` int(11) NOT NULL,
  `upline_id` int(11) NOT NULL,
  `level` int(11) NOT NULL COMMENT 'Level in hierarchy (1 for direct sponsor, 2 for sponsor''s sponsor, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_hierarchy`
--

INSERT INTO `team_hierarchy` (`id`, `associate_id`, `upline_id`, `level`, `created_at`) VALUES
(1, 2, 1, 1, '2025-03-29 20:57:10'),
(2, 3, 1, 1, '2025-04-01 18:05:40'),
(3, 4, 2, 1, '2025-04-02 19:30:27'),
(4, 4, 1, 2, '2025-04-02 19:30:27'),
(5, 5, 2, 1, '2025-04-02 19:32:32'),
(6, 5, 1, 2, '2025-04-02 19:32:32'),
(7, 6, 4, 1, '2025-04-02 19:34:06'),
(8, 6, 2, 2, '2025-04-02 19:34:06'),
(9, 6, 1, 3, '2025-04-02 19:34:06'),
(11, 7, 6, 1, '2025-04-02 19:35:00'),
(12, 7, 4, 2, '2025-04-02 19:35:00'),
(13, 7, 2, 3, '2025-04-02 19:35:00'),
(14, 7, 1, 4, '2025-04-02 19:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(50) NOT NULL,
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
  `job_role` varchar(50) NOT NULL DEFAULT 'Associate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `sponsor_id`, `sponsored_by`, `uname`, `uemail`, `uphone`, `upass`, `utype`, `uimage`, `bank_name`, `account_number`, `ifsc_code`, `bank_micr`, `bank_branch`, `bank_district`, `bank_state`, `account_type`, `pan`, `adhaar`, `nominee_name`, `nominee_relation`, `nominee_contact`, `address`, `date_of_birth`, `join_date`, `is_updated`, `job_role`) VALUES
(1, 'APS', 'Board', 'APS', 'test@test.com', '0000000000', '0926c950fe247c3b465eb13e258ee468d239a065', 'assosiate', 'MASTER Data', 'MASTER Data', 'MASTER Data', 'MASTER Data', NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-08 20:40:23', '', 'Associate'),
(47, 'APS0002', 'APS', 'Abhay Singh', 'techguruabhay@gmail.com', '7007444842', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-15 19:07:10', '', 'Associate'),
(48, 'APS0003', 'APS', 'Pravin kumar Prabhat', 'pravin.prabhat@yahoo.com', '9026336001', '611e08dd84f3b85fc1e02947060a040d5c750b4b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-16 01:37:20', '', 'Associate'),
(49, 'APS0004', 'APS', 'Anita Singh', 'rudra.vir007@gmail.com', '7068013668', '611e08dd84f3b85fc1e02947060a040d5c750b4b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-16 01:39:27', '', 'Associate'),
(50, 'APS0005', 'APS', 'Anuj kumar srivastava', 'devsrivastava74@gmail.com', '8707742187', '0357779512dca2ffdad8d06141fe287a45b87831', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-16 06:16:34', '', 'Associate'),
(51, 'APS0006', 'APS0002', 'Rachna gupta', 'rachana@gmail.com', '7007414234', '9820ad076021fa968c706577be1470625db63366', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-17 03:40:25', '', 'Associate'),
(52, 'APS0007', 'APS', 'Puneet kumar sinha', 'puneetsinha123@gmail.com', '9935883444', 'e9c8c6c1f951080df0b91ddad6121fe6439a94ac', 'assosiate', NULL, 'STATE BANK OF INDIA', '36127852694', 'SBIN0018456', '273002054', 'BICCHIA', 'GORAKHPUR', 'UTTAR PRADESH', 'savings', 'BOZPS7983G', 2147483647, 'Amita Srivastava', 'wife', '8574680436', '28AJungle tulsi ram bichhiya pac camp gorakhpur', '1984-07-10', '2024-09-20 10:03:38', 'Y', 'Associate'),
(53, 'APS0008', 'APS0007', 'Rahul Verma', 'rv83810@gmail.com', '8858763451', 'e53fcde5b60889071303ed98dca4d80523824dde', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-20 10:25:31', '', 'Associate'),
(54, 'APS0009', 'APS0007', 'Neeraj Kumar Singh', 'nerajsingh235@gmail.com', '9506362690', 'c53b4d09fb29013888c8715f5110b59ac0346394', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-20 10:37:13', '', 'Associate'),
(55, 'APS00010', 'APS', 'Pramod kumar sharma', 'Pramod.rich1989@gmail.com', '8318037728', '76f26c37699fd39e96db293bd6bcb0a691246e90', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-20 10:48:22', '', 'Associate'),
(56, 'APS00011', 'APS', 'ashok kumar', 'ashok12@gmail.com', '8808403728', 'bfe922dfaf37728991e0a3d1f08e56859faca3f6', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 09:42:30', '', 'Associate'),
(57, 'APS00012', 'APS0002', 'rinku chauhan', 'rinku@gmail.com', '9219494408', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 09:47:51', '', 'Associate'),
(58, 'APS00013', 'APS00011', 'SUNIL KUMAR', 'sunil12@gamil.com', '7348127038', '82ba8bdf5003966aeb2e25ccb830cf0a9c1f2afe', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 09:49:43', '', 'Associate'),
(59, 'APS00014', 'APS00013', 'irfan ahmad', 'irfan12@gmail.com', '8318431354', 'e129d3c1f37a5b9e2ba4c4ac7cd885be6463415f', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 09:53:49', '', 'Associate'),
(60, 'APS00015', 'APS00012', 'priya mishra', 'priyag.9671@gmail.com', '9140640713', '82f40b03fa848374452931f33369473f2e1c7e76', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 09:54:28', '', 'Associate'),
(61, 'APS00016', 'APS00014', 'Rishikesh', 'rishi12@gmail.com', '8172938998', 'd9bee7fba681500321780ac004cd0007815c1309', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:11:01', '', 'Associate'),
(62, 'APS00017', 'APS00015', 'rajni verma', 'rajniskn@gmail.com', '9956390332', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:11:45', '', 'Associate'),
(63, 'APS00018', 'APS00016', 'Rahul kannujiya', 'rahul12@gmail.com', '7607952353', 'e53fcde5b60889071303ed98dca4d80523824dde', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:14:25', '', 'Associate'),
(64, 'APS00019', 'APS00017', 'anuradha rai', 'anuradharai@gmail.com', '7678767656', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:15:00', '', 'Associate'),
(65, 'APS00020', 'APS00018', 'avinash pandey', 'avinash12@gmail.com', '9517241234', '104ac41228995dbcdcbae3d4e5bb649cea9ab07f', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:17:33', '', 'Associate'),
(66, 'APS00021', 'APS00019', 'avinash tiwari', 'tiwari1210@gmail.com', '8318721419', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:18:30', '', 'Associate'),
(67, 'APS00022', 'APS00021', 'shalu bharti', 'shalubharti@gmail.com', '9792398767', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:20:24', '', 'Associate'),
(68, 'APS00023', 'APS00022', 'anshika upadhyay', 'itsanshika454@gmail.com', '7754831158', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:21:46', '', 'Associate'),
(69, 'APS00024', 'APS00023', 'priyanka yadav', 'priyakayadav@gmail.com', '7607702191', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-21 10:23:41', '', 'Associate'),
(70, 'APS00025', 'aps', 'maggi', 'maggi@gmail.com', '6765676566', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 06:39:42', '', 'Associate'),
(74, 'APS00027', 'aps', 'Abhay kumar singh', 'techguruabhay@gmail.com', '7007444842', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, 'STATE BANK OF INDIA', '3245566766555', 'SBIN0005608', '273002004', 'MOHADDIPUR', 'GORAKHPUR', 'UTTAR PRADESH', 'savings', 'FAUPS8878H', 2147483647, 'TESTING', 'TEST', '6767776766', 'near ganpati lawn singhariya kunraghat', '2000-02-03', '2024-10-07 18:26:30', 'Y', 'Associate'),
(75, 'APS00028', 'aps00023', 'abhay', 'abhay444@gmail.com', '6576646546', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 22:01:30', '', 'Associate'),
(76, 'APS00029', 'aps00012', 'abhay', 'anjuuuuu@gmail.com', '6565654345', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 14:37:23', '', 'Associate'),
(83, 'APS00036', '', 'builder', 'builder@gmail.com', '3764765456', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'builder', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 17:35:07', '', 'Associate'),
(84, 'APS00037', '', 'user', 'user@gmail.com', '4553767598', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 17:35:33', '', 'Associate'),
(85, 'APS00038', '', 'agent', 'agent@gmail.com', '5546745554', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'agent', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 17:44:21', '', 'Associate'),
(86, 'APS00039', '', 'anuj22', 'anuj7656@gmail.com', '7898786566', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 'agent', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 20:24:14', '', 'Associate'),
(87, 'APS00040', 'APS', 'Abhay kumar singh', 'techgure434hjhuabhay@gmail.com', '7004499842', '0926c950fe247c3b465eb13e258ee468d239a065', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 10:32:39', '', 'Associate'),
(88, 'APS00041', 'APS', 'Rohit', 'rohit123@gmail.com', '1234556786', '0d12c3a5cdea9a9c7ddaa65bc6ae2b82077da76b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 14:00:32', '', 'Associate'),
(89, 'APS00042', 'aps', 'abhayy', 'abhayy3007@gmail.com', '5656787676', '2e742be9e4d504f92157d65ec044a4ea4814a69b', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 07:33:33', '', 'Associate'),
(90, 'APS00043', 'aps00023', 'praveen', 'praveen@gmail.com', '676765665', '8e0a81a58450418541796c0dfc3d9ba0b86e6655', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-27 11:14:03', '', 'Associate'),
(91, 'APS00044', 'APS', 'abhay kumar singh', 'abhay3007@gmail.com', '7007444842', '8e0a81a58450418541796c0dfc3d9ba0b86e6655', 'assosiate', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'savings', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-27 17:49:41', '', 'Associate');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `oauth_provider` enum('google','email') DEFAULT 'email',
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `utype` enum('user','agent','builder','associate') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `google_id`, `oauth_provider`, `phone`, `profile_image`, `utype`, `created_at`, `updated_at`, `status`, `last_login`) VALUES
(1, 'Christine', 'christine@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7777444455', NULL, 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(2, 'Alice Howard', 'howarda@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7775552214', NULL, 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(3, 'Thomas Olson', 'thomas@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7896665555', NULL, 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(4, 'Cynthia N. Moore', 'moore@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7896547855', NULL, 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(5, 'Carl Jones', 'carl@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '1458887969', NULL, 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(6, 'Noah Stones', 'noah@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7965555544', NULL, 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(7, 'Fred Godines', 'fred@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '7850002587', NULL, 'builder', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(8, 'Michael', 'michael@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', NULL, 'email', '8542221140', NULL, 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(9, 'APS Dream Homes', 'apsdreamhomes44@gmail.com', NULL, '117146075006286142736', 'google', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIMoXkQx0RSPT3GG5TcSavItW5SBUgj6NIINj80nhdzJhbJdHs=s96-c', 'user', '2025-03-24 21:18:04', '2025-03-25 19:14:38', 'active', '2025-03-25 19:14:38'),
(10, 'Abhay Singh', 'techguruabhay@gmail.com', NULL, '100351578011036728410', 'google', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocLG4eiICz7yojANU1pwjmpl-TBkSrxhgULqDO8QxVC6oiRIT6YkZg=s96-c', 'user', '2025-03-24 21:34:25', '2025-03-25 09:05:52', 'active', '2025-03-25 09:05:52'),
(11, 'Abhay kumar singh', 'abhay77777@gmail.com', '$2y$10$Se1LYf10txgc/X0EdxtKr.4EtncqvN/P9Ig87ztpkqNk4FscJKu5C', NULL, 'email', '7007666565', NULL, 'user', '2025-03-25 17:30:52', '2025-03-25 17:30:52', 'active', NULL),
(13, 'Abhay kumar singh', 'abhay7778877@gmail.com', '$2y$10$NS71fMRGj5ZpF6KuWorKwOTgOeh65NA6GfZm6mdYdUKk3eUe5olaC', NULL, 'email', '7007699599', NULL, 'user', '2025-03-25 19:44:24', '2025-03-25 19:44:24', 'active', NULL),
(14, 'APS Company Sponsor', 'sponsor@apsdreamhomes.com', '$2y$10$c2jgS2d77Np.74N5LSDIF.w.TR7R1AhavHZhgRLiJAamiuclEuFjW', NULL, 'email', '9876543210', NULL, 'associate', '2025-03-27 20:21:30', '2025-03-27 20:21:30', 'active', NULL),
(27, 'raju rajpoot', 'raju556767@gmail.com', '$2y$10$NZMXbAuf5N1VGgFlx0BcT.CXUhvXNfg2emBAz1dtZOB5n0tugpYLW', NULL, 'email', '7897787656', NULL, 'user', '2025-03-29 20:25:04', '2025-03-29 20:25:04', 'active', NULL),
(32, 'raju rajpoot', 'rajut5456323767@gmail.com', '$2y$10$tgXpRAsQtDeF.EvnSZ7gyOj0EbtU14u/YOVmuTPyFQdtXiOTRs7Li', NULL, 'email', '7897765556', NULL, 'associate', '2025-03-29 20:57:10', '2025-03-29 20:57:10', 'active', NULL),
(33, 'raju rajpoot singh', 'rajut54563253767@gmail.com', '$2y$10$.yX9Xp55uVPGt0V9.9hX6eJw749hy8ZD4wt3XFbrHItM2QT0nOCm6', NULL, 'email', '7897565656', NULL, 'associate', '2025-04-01 18:05:40', '2025-04-01 18:05:40', 'active', NULL),
(34, 'ruhitwo', 'ruhi2@gmail.com', '$2y$10$dh.42xn.6d8AKfYsFaxjlediKrfkFPe5GuBBs3vC.AoVwcZ9aUrYm', NULL, 'email', '5466776435', NULL, 'associate', '2025-04-02 19:30:27', '2025-04-02 19:30:27', 'active', NULL),
(35, 'ruhithree', 'ruhi3@gmail.com', '$2y$10$o8FlNyDEXuXXVWCsxGK5L.1wlMTktOSs6oudAOA/Rd2faKl4QpIFK', NULL, 'email', '8788776787', NULL, 'associate', '2025-04-02 19:32:32', '2025-04-02 19:32:32', 'active', NULL),
(36, 'ruhifour', 'ruhi4@gmail.com', '$2y$10$bUo2ZhK4KZ7xToKaOWXcaO1zxgomTHAb51r357nq0FMBv3ePZ2ofy', NULL, 'email', '8779787787', NULL, 'associate', '2025-04-02 19:34:06', '2025-04-02 19:34:06', 'active', NULL),
(37, 'ruhisix', 'ruhi6@gmail.com', '$2y$10$ZenpMb0wtdzJ.4o1EKOpwuBCEyERDzTr4C3yRTY4LZg9OhIbe9lSC', NULL, 'email', '6764543654', NULL, 'associate', '2025-04-02 19:35:00', '2025-04-02 19:35:00', 'active', NULL),
(38, 'Rahul Sharma', 'rahul@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', NULL, 'email', '9876543210', NULL, 'user', '2025-04-15 04:46:30', '2025-04-15 04:46:30', 'active', NULL),
(39, 'Priya Singh', 'priya@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', NULL, 'email', '8765432109', NULL, 'user', '2025-04-15 04:46:30', '2025-04-15 04:46:30', 'active', NULL),
(40, 'Amit Kumar', 'amit@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', NULL, 'email', '7654321098', NULL, 'agent', '2025-04-15 04:46:30', '2025-04-15 04:46:30', 'active', NULL),
(41, 'Neha Gupta', 'neha@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', NULL, 'email', '6543210987', NULL, 'builder', '2025-04-15 04:46:30', '2025-04-15 04:46:30', 'active', NULL),
(42, 'Vikram Patel', 'vikram@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', NULL, 'email', '5432109876', NULL, 'agent', '2025-04-15 04:46:30', '2025-04-15 04:46:30', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_backup`
--

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

--
-- Dumping data for table `user_backup`
--

INSERT INTO `user_backup` (`id`, `name`, `email`, `password`, `phone`, `utype`, `created_at`, `updated_at`, `status`, `last_login`) VALUES
(1, 'Christine', 'christine@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7777444455', 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(2, 'Alice Howard', 'howarda@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7775552214', 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(3, 'Thomas Olson', 'thomas@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7896665555', 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(4, 'Cynthia N. Moore', 'moore@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7896547855', 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(5, 'Carl Jones', 'carl@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '1458887969', 'agent', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(6, 'Noah Stones', 'noah@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7965555544', 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(7, 'Fred Godines', 'fred@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '7850002587', 'builder', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL),
(8, 'Michael', 'michael@mail.com', '6812f136d636e737248d365016f8cfd5139e387c', '8542221140', 'user', '2025-03-22 07:55:36', '2025-03-22 07:55:36', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_key` varchar(50) NOT NULL,
  `permission_value` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `fk_activity_lead` (`lead_id`),
  ADD KEY `fk_activity_opportunity` (`opportunity_id`),
  ADD KEY `idx_activity_type` (`type`),
  ADD KEY `idx_activity_due_date` (`due_date`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aid`),
  ADD UNIQUE KEY `aemail` (`aemail`),
  ADD UNIQUE KEY `aemail_2` (`aemail`),
  ADD UNIQUE KEY `unique_admin_email` (`aemail`),
  ADD KEY `idx_email` (`aemail`),
  ADD KEY `idx_admin_user` (`auser`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `associates`
--
ALTER TABLE `associates`
  ADD PRIMARY KEY (`associate_id`),
  ADD UNIQUE KEY `uid` (`uid`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_associate_uid` (`uid`),
  ADD KEY `idx_associate_sponsor` (`sponsor_id`),
  ADD KEY `idx_associate_referral` (`referral_code`),
  ADD KEY `fk_associate_user` (`user_id`),
  ADD KEY `current_level_id` (`current_level_id`);

--
-- Indexes for table `associate_levels`
--
ALTER TABLE `associate_levels`
  ADD PRIMARY KEY (`level_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_booking_customer_name` (`customer_name`),
  ADD KEY `idx_booking_dates` (`booking_date`,`next_payment_date`),
  ADD KEY `idx_booking_status` (`status`,`payment_status`);

--
-- Indexes for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_booking_payment_date` (`payment_date`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`campaign_id`),
  ADD KEY `idx_campaign_status` (`status`),
  ADD KEY `idx_campaign_dates` (`start_date`,`end_date`);

--
-- Indexes for table `campaign_members`
--
ALTER TABLE `campaign_members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `unique_campaign_lead` (`campaign_id`,`lead_id`),
  ADD KEY `fk_member_lead` (`lead_id`),
  ADD KEY `idx_member_status` (`status`);

--
-- Indexes for table `career_applications`
--
ALTER TABLE `career_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_career_email` (`email`),
  ADD KEY `idx_career_created` (`created_at`);

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `fk_state_city` (`sid`);

--
-- Indexes for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_commission_associate` (`associate_id`),
  ADD KEY `idx_commission_booking` (`booking_id`),
  ADD KEY `idx_commission_upline` (`upline_id`),
  ADD KEY `idx_commission_date` (`transaction_date`);

--
-- Indexes for table `communications`
--
ALTER TABLE `communications`
  ADD PRIMARY KEY (`communication_id`),
  ADD KEY `fk_communication_lead` (`lead_id`),
  ADD KEY `fk_communication_opportunity` (`opportunity_id`),
  ADD KEY `idx_communication_date` (`communication_date`);

--
-- Indexes for table `components`
--
ALTER TABLE `components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `content_backups`
--
ALTER TABLE `content_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`fid`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layout_templates`
--
ALTER TABLE `layout_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`lead_id`),
  ADD KEY `idx_lead_status` (`status`),
  ADD KEY `idx_lead_assigned` (`assigned_to`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `migration_errors`
--
ALTER TABLE `migration_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_error_time` (`error_time`),
  ADD KEY `idx_affected_uid` (`affected_uid`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`opportunity_id`),
  ADD KEY `fk_opportunity_lead` (`lead_id`),
  ADD KEY `idx_opportunity_stage` (`stage`),
  ADD KEY `idx_opportunity_assigned` (`assigned_to`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `property_type`
--
ALTER TABLE `property_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resell_plots`
--
ALTER TABLE `resell_plots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`sid`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hierarchy_associate` (`associate_id`),
  ADD KEY `idx_hierarchy_upline` (`upline_id`),
  ADD KEY `idx_hierarchy_level` (`level`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `sponsor_id` (`sponsor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_status` (`status`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about`
--
ALTER TABLE `about`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `aid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `associates`
--
ALTER TABLE `associates`
  MODIFY `associate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `associate_levels`
--
ALTER TABLE `associate_levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `booking_payments`
--
ALTER TABLE `booking_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaign_members`
--
ALTER TABLE `campaign_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `career_applications`
--
ALTER TABLE `career_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `cid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `communications`
--
ALTER TABLE `communications`
  MODIFY `communication_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `components`
--
ALTER TABLE `components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_backups`
--
ALTER TABLE `content_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `fid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `layout_templates`
--
ALTER TABLE `layout_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `lead_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migration_errors`
--
ALTER TABLE `migration_errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `opportunity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `pid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `property_type`
--
ALTER TABLE `property_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resell_plots`
--
ALTER TABLE `resell_plots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `sid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activity_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_activity_opportunity` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`opportunity_id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_chatbot_interactions`
--
ALTER TABLE `ai_chatbot_interactions`
  ADD CONSTRAINT `ai_chatbot_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `associates`
--
ALTER TABLE `associates`
  ADD CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `booking_payments`
--
ALTER TABLE `booking_payments`
  ADD CONSTRAINT `booking_payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `campaign_members`
--
ALTER TABLE `campaign_members`
  ADD CONSTRAINT `fk_member_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`campaign_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_member_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE;

--
-- Constraints for table `city`
--
ALTER TABLE `city`
  ADD CONSTRAINT `fk_state_city` FOREIGN KEY (`sid`) REFERENCES `state` (`sid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `commission_transactions`
--
ALTER TABLE `commission_transactions`
  ADD CONSTRAINT `fk_commission_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_commission_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE SET NULL;

--
-- Constraints for table `communications`
--
ALTER TABLE `communications`
  ADD CONSTRAINT `fk_communication_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_communication_opportunity` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`opportunity_id`) ON DELETE CASCADE;

--
-- Constraints for table `components`
--
ALTER TABLE `components`
  ADD CONSTRAINT `components_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `content_backups`
--
ALTER TABLE `content_backups`
  ADD CONSTRAINT `content_backups_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`),
  ADD CONSTRAINT `content_backups_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `layout_templates`
--
ALTER TABLE `layout_templates`
  ADD CONSTRAINT `layout_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `fk_opportunity_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`lead_id`) ON DELETE SET NULL;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pages_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `team_hierarchy`
--
ALTER TABLE `team_hierarchy`
  ADD CONSTRAINT `fk_hierarchy_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hierarchy_upline` FOREIGN KEY (`upline_id`) REFERENCES `associates` (`associate_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
