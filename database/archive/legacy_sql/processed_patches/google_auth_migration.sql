-- Add Google OAuth columns to users table
ALTER TABLE `users` 
ADD COLUMN `google_id` VARCHAR(100) NULL AFTER `password`,
ADD COLUMN `oauth_provider` ENUM('google', 'email') DEFAULT 'email' AFTER `google_id`,
ADD COLUMN `profile_image` VARCHAR(255) NULL AFTER `phone`,
ADD COLUMN `user_type` ENUM('user', 'agent', 'builder', 'associate') NOT NULL DEFAULT 'user' AFTER `utype`;

-- Add index for faster Google ID lookups
CREATE INDEX `idx_google_id` ON `users` (`google_id`);

-- Modify password to allow NULL for Google OAuth users
ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NULL;

-- Update user_type enum to include 'associate' if not already included
ALTER TABLE `users` MODIFY COLUMN `utype` ENUM('user', 'agent', 'builder', 'associate') NOT NULL DEFAULT 'user';

-- Add last_login timestamp if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_login` TIMESTAMP NULL DEFAULT NULL;

-- Add created_at and updated_at timestamps if not exists
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Ensure associates table has proper foreign key relationship
ALTER TABLE `associates` ADD CONSTRAINT IF NOT EXISTS `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;