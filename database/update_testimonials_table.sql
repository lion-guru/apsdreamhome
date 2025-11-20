-- Add new columns if they don't exist
ALTER TABLE `testimonials`
ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) AFTER `client_name`,
ADD COLUMN IF NOT EXISTS `rating` TINYINT(1) DEFAULT 5 AFTER `email`,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Update status enum if needed
ALTER TABLE `testimonials` 
MODIFY COLUMN `status` ENUM('pending','approved','rejected','active','inactive') NOT NULL DEFAULT 'pending';

-- Update existing active records to approved
UPDATE `testimonials` SET `status` = 'approved' WHERE `status` = 'active';

-- Set default rating for existing records
UPDATE `testimonials` SET `rating` = 5 WHERE `rating` IS NULL;
