-- === CRM/ERP & Plot Management Enhancements (2025-04-21) ===
-- Unified Users Table (if not already present, otherwise migrate data)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','associate','user','agent','builder') DEFAULT 'user',
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attachments Table for images, maps, documents
CREATE TABLE IF NOT EXISTS `attachments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `related_type` ENUM('plot','project','user','other') NOT NULL,
  `related_id` INT DEFAULT NULL,
  `uploaded_by` INT DEFAULT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plot Sales/Transactions Table
CREATE TABLE IF NOT EXISTS `plot_sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plot_id` INT NOT NULL,
  `buyer_id` INT NOT NULL,
  `seller_id` INT DEFAULT NULL,
  `sale_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) DEFAULT NULL,
  `notes` TEXT,
  FOREIGN KEY (`plot_id`) REFERENCES `plots`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Log for workflow/audit
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update Plots Table (add status, owner, map)
ALTER TABLE `plots`
  ADD COLUMN IF NOT EXISTS `status` ENUM('available','sold','booked','hold') DEFAULT 'available',
  ADD COLUMN IF NOT EXISTS `current_owner` INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `map_attachment_id` INT DEFAULT NULL;
