-- Disable foreign key checks to avoid issues with dependencies
SET FOREIGN_KEY_CHECKS = 0;

-- Add missing tables that don't exist
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
  KEY `idx_site_location` (`city`, `state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add any missing columns to existing tables
ALTER TABLE `bookings` 
  ADD COLUMN IF NOT EXISTS `booking_number` varchar(50) DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `total_amount` decimal(15,2) DEFAULT NULL AFTER `amount`,
  ADD COLUMN IF NOT EXISTS `payment_plan` enum('full_payment','installment','emi') DEFAULT 'full_payment' AFTER `total_amount`,
  ADD COLUMN IF NOT EXISTS `source` enum('direct','associate','online','agent') DEFAULT 'direct' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `remarks` text DEFAULT NULL AFTER `source`,
  ADD COLUMN IF NOT EXISTS `documents` json DEFAULT NULL AFTER `remarks`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) DEFAULT NULL AFTER `documents`,
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `created_at`;

-- Update existing records to set default values for new columns
UPDATE `bookings` SET `total_amount` = `amount` WHERE `total_amount` IS NULL;

-- Create indexes if they don't exist
CREATE INDEX IF NOT EXISTS `idx_booking_status` ON `bookings` (`status`);
CREATE INDEX IF NOT EXISTS `idx_booking_date` ON `bookings` (`booking_date`);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show a summary of the changes
SELECT 'Database update completed successfully!' AS message;
