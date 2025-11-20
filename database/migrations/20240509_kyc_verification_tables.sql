-- KYC Verification Tables Migration

-- Create kyc_verification table
CREATE TABLE IF NOT EXISTS `kyc_verification` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `associate_id` INT NOT NULL,
    `aadhar_doc` VARCHAR(255) NOT NULL,
    `pan_doc` VARCHAR(255) NOT NULL,
    `address_doc` VARCHAR(255) NOT NULL,
    `status` ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    `verified_by` INT NULL,
    `verification_notes` TEXT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `verified_at` TIMESTAMP NULL,
    
    UNIQUE KEY `unique_associate_kyc` (`associate_id`),
    FOREIGN KEY (`associate_id`) REFERENCES `user` (`uid`) ON DELETE CASCADE,
    FOREIGN KEY (`verified_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create kyc_verification_history table for audit trail
CREATE TABLE IF NOT EXISTS `kyc_verification_history` (
    `history_id` INT AUTO_INCREMENT PRIMARY KEY,
    `kyc_id` INT NOT NULL,
    `status_from` ENUM('Pending', 'Verified', 'Rejected') NOT NULL,
    `status_to` ENUM('Pending', 'Verified', 'Rejected') NOT NULL,
    `changed_by` INT NOT NULL,
    `change_reason` TEXT NULL,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`kyc_id`) REFERENCES `kyc_verification` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger to log KYC status changes
DELIMITER //
CREATE TRIGGER `log_kyc_status_change` AFTER UPDATE ON `kyc_verification`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO `kyc_verification_history` 
        (kyc_id, status_from, status_to, changed_by, change_reason)
        VALUES 
        (NEW.id, OLD.status, NEW.status, NEW.verified_by, NEW.verification_notes);
    END IF;
END;//
DELIMITER ;

-- Add indexes for performance
CREATE INDEX `idx_kyc_associate` ON `kyc_verification` (`associate_id`);
CREATE INDEX `idx_kyc_status` ON `kyc_verification` (`status`);
CREATE INDEX `idx_kyc_history_kyc_id` ON `kyc_verification_history` (`kyc_id`);
