-- APS Dream Home Final - Database Schema Only
-- This file contains only the database structure without sample data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apsdreamhome`;

-- Table structure for table `sites`
CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `total_area` decimal(10,2) NOT NULL COMMENT 'in acres',
  `developed_area` decimal(10,2) DEFAULT 0.00,
  `site_type` enum('residential','commercial','mixed','industrial') DEFAULT 'residential',
  `status` enum('planning','under_development','active','completed','inactive') DEFAULT 'planning',
  `manager_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_site_status` (`status`),
  KEY `idx_site_type` (`site_type`),
  KEY `idx_site_location` (`city`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `associate_levels`
CREATE TABLE IF NOT EXISTS `associate_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `min_business` decimal(15,2) NOT NULL DEFAULT 0.00,
  `max_business` decimal(15,2) NOT NULL DEFAULT 99999999.99,
  `commission_percentage` decimal(5,2) NOT NULL,
  `direct_referral_bonus` decimal(5,2) DEFAULT 0.00,
  `level_bonus` decimal(5,2) DEFAULT 0.00,
  `reward_description` text DEFAULT NULL,
  `min_team_size` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_name` (`level_name`),
  KEY `idx_level_business` (`min_business`,`max_business`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `associates`
CREATE TABLE IF NOT EXISTS `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT 1,
  `total_business` decimal(15,2) DEFAULT 0.00,
  `total_earnings` decimal(15,2) DEFAULT 0.00,
  `join_date` date NOT NULL,
  `status` enum('active','inactive','suspended','terminated') DEFAULT 'active',
  `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_email` (`email`),
  KEY `idx_associate_mobile` (`mobile`),
  KEY `idx_associate_sponsor` (`sponsor_id`),
  KEY `idx_associate_level` (`level_id`),
  KEY `idx_associate_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `customers`
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL COMMENT 'Associate ID who referred',
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `credit_score` int(11) DEFAULT NULL,
  `preferred_contact` enum('email','sms','call','whatsapp') DEFAULT 'call',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_email` (`email`),
  KEY `idx_customer_mobile` (`mobile`),
  KEY `idx_customer_referred` (`referred_by`),
  KEY `idx_customer_status` (`status`),
  KEY `idx_customer_city` (`city`),
  KEY `idx_customer_kyc` (`kyc_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `properties`
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) NOT NULL,
  `property_type` enum('plot','apartment','villa','commercial','agricultural') NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `plot_development_id` int(11) DEFAULT NULL,
  `size` decimal(10,2) NOT NULL COMMENT 'in sqft',
  `price` decimal(15,2) NOT NULL,
  `status` enum('available','booked','sold','under_development','blocked') DEFAULT 'available',
  `amenities` json DEFAULT NULL,
  `location` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `facing` enum('north','south','east','west','northeast','northwest','southeast','southwest') DEFAULT NULL,
  `floor_plan` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `video_tour` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rera_number` varchar(100) DEFAULT NULL,
  `possession_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_property_site` (`site_id`),
  KEY `fk_property_plot_dev` (`plot_development_id`),
  KEY `idx_property_type` (`property_type`),
  KEY `idx_property_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `bookings`
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `booking_number` varchar(50) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_plan` enum('full_payment','installment','emi') DEFAULT 'full_payment',
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `source` enum('direct','associate','online','agent') DEFAULT 'direct',
  `remarks` text DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `fk_booking_customer` (`customer_id`),
  KEY `fk_booking_property` (`property_id`),
  KEY `fk_booking_associate` (`associate_id`),
  KEY `idx_booking_status` (`status`),
  KEY `idx_booking_date` (`booking_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `payments`
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_type` enum('booking_amount','emi','full_payment','penalty','refund') DEFAULT 'booking_amount',
  `payment_method` enum('cash','bank_transfer','upi','credit_card','debit_card','cheque','online') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `status` enum('pending','success','failed','cancelled','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payment_customer` (`customer_id`),
  KEY `fk_payment_booking` (`booking_id`),
  KEY `idx_payment_status` (`status`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `emi_schedule`
CREATE TABLE IF NOT EXISTS `emi_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `emi_number` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','waived') DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `payment_id` int(11) DEFAULT NULL,
  `reminder_sent` int(11) DEFAULT 0,
  `last_reminder` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_emi_customer` (`customer_id`),
  KEY `fk_emi_booking` (`booking_id`),
  KEY `fk_emi_payment` (`payment_id`),
  KEY `idx_emi_due_date` (`due_date`),
  KEY `idx_emi_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraints after all tables are created
ALTER TABLE `associates`
  ADD CONSTRAINT `fk_associate_level` FOREIGN KEY (`level_id`) REFERENCES `associate_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_associate_sponsor` FOREIGN KEY (`sponsor_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customer_referred` FOREIGN KEY (`referred_by`) REFERENCES `associates` (`id`) ON DELETE SET NULL;

ALTER TABLE `emi_schedule`
  ADD CONSTRAINT `fk_emi_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_emi_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_emi_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

ALTER TABLE `properties`
  ADD CONSTRAINT `fk_property_plot_dev` FOREIGN KEY (`plot_development_id`) REFERENCES `plot_development` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_property_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL;

-- Create views
CREATE OR REPLACE VIEW `land_management_summary` AS
SELECT
    f.name as farmer_name,
    f.mobile as farmer_mobile,
    lp.land_area,
    lp.purchase_price,
    lp.price_per_acre,
    lp.purchase_date,
    COUNT(pd.id) as total_plots,
    SUM(CASE WHEN pd.status = 'sold' THEN 1 ELSE 0 END) as plots_sold,
    SUM(pd.sold_price) as total_revenue,
    SUM(pd.profit_loss) as total_profit
FROM farmers f
LEFT JOIN land_purchases lp ON f.id = lp.farmer_id
LEFT JOIN plot_development pd ON lp.id = pd.land_purchase_id
GROUP BY f.id, lp.id;

CREATE OR REPLACE VIEW `builder_performance` AS
SELECT
    b.name as builder_name,
    b.specialization,
    b.rating,
    COUNT(cp.id) as total_projects,
    SUM(cp.budget_allocated) as total_budget,
    AVG(cp.progress_percentage) as avg_progress,
    SUM(CASE WHEN cp.status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
    SUM(bp.payment_amount) as total_payments
FROM builders b
LEFT JOIN construction_projects cp ON b.id = cp.builder_id
LEFT JOIN builder_payments bp ON b.id = bp.builder_id
GROUP BY b.id;

CREATE OR REPLACE VIEW `customer_summary` AS
SELECT
    c.name as customer_name,
    c.email,
    c.mobile,
    c.kyc_status,
    COUNT(DISTINCT b.id) as total_bookings,
    SUM(p.amount) as total_payments,
    COUNT(DISTINCT ci.id) as total_inquiries,
    COUNT(DISTINCT cd.id) as total_documents
FROM customers c
LEFT JOIN bookings b ON c.id = b.customer_id
LEFT JOIN payments p ON c.id = p.customer_id
LEFT JOIN customer_inquiries ci ON c.id = ci.customer_id
LEFT JOIN customer_documents cd ON c.id = cd.customer_id
GROUP BY c.id;

CREATE OR REPLACE VIEW `mlm_performance` AS
SELECT
    a.name as associate_name,
    a.email,
    a.mobile,
    al.level_name,
    a.total_business,
    a.total_earnings,
    COUNT(DISTINCT mc.id) as total_commissions,
    SUM(mc.commission_amount) as total_commission_earned,
    COUNT(DISTINCT b.id) as total_referrals
FROM associates a
LEFT JOIN associate_levels al ON a.level_id = al.id
LEFT JOIN mlm_commissions mc ON a.id = mc.associate_id
LEFT JOIN associates b ON a.id = b.sponsor_id
GROUP BY a.id;

-- Insert default data
INSERT IGNORE INTO `associate_levels` (`level_name`, `min_business`, `max_business`, `commission_percentage`, `direct_referral_bonus`, `level_bonus`, `reward_description`, `min_team_size`, `status`) VALUES
('Bronze', 0.00, 100000.00, 2.00, 0.50, 0.00, 'Entry level associate', 0, 'active'),
('Silver', 100001.00, 500000.00, 3.00, 0.75, 0.25, 'Silver level with increased commission', 3, 'active'),
('Gold', 500001.00, 2000000.00, 4.00, 1.00, 0.50, 'Gold level with premium benefits', 10, 'active'),
('Platinum', 2000001.00, 5000000.00, 5.00, 1.25, 0.75, 'Platinum level with exclusive rewards', 25, 'active'),
('Diamond', 5000001.00, 10000000.00, 6.00, 1.50, 1.00, 'Diamond level with maximum benefits', 50, 'active'),
('Crown Diamond', 10000001.00, 999999999.99, 7.00, 2.00, 1.50, 'Highest level with royal treatment', 100, 'active');

-- Add triggers
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_plot_profit_loss` 
BEFORE UPDATE ON `plot_development` 
FOR EACH ROW
BEGIN
    IF NEW.status = 'sold' AND NEW.sold_price IS NOT NULL THEN
        SET NEW.profit_loss = NEW.sold_price - NEW.development_cost;
    END IF;
END$$

CREATE TRIGGER IF NOT EXISTS `update_builder_project_counts` 
AFTER INSERT ON `construction_projects` 
FOR EACH ROW
BEGIN
    UPDATE builders
    SET total_projects = total_projects + 1,
        ongoing_projects = ongoing_projects + 1
    WHERE id = NEW.builder_id;
END$$

CREATE TRIGGER IF NOT EXISTS `update_builder_counts_on_completion` 
AFTER UPDATE ON `construction_projects` 
FOR EACH ROW
BEGIN
    IF OLD.status != 'completed' AND NEW.status = 'completed' THEN
        UPDATE builders
        SET completed_projects = completed_projects + 1,
            ongoing_projects = ongoing_projects - 1
        WHERE id = NEW.builder_id;
    END IF;
END$$

CREATE TRIGGER IF NOT EXISTS `update_associate_business` 
AFTER INSERT ON `bookings` 
FOR EACH ROW
BEGIN
    IF NEW.associate_id IS NOT NULL AND NEW.status = 'confirmed' THEN
        UPDATE associates
        SET total_business = total_business + NEW.total_amount
        WHERE id = NEW.associate_id;
    END IF;
END$$
DELIMITER ;

COMMIT;
