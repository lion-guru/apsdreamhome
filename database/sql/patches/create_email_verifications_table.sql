-- Migration: Create email_verifications table
-- Date: 2024-01-22
-- Description: Table to store email verification tokens for user registration

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraint if users table exists
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_email_verifications_user_id`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Insert a sample verification record for testing
INSERT INTO `email_verifications` (`user_id`, `token`, `expires_at`) VALUES
(1, 'test_verification_token_123', DATE_ADD(NOW(), INTERVAL 24 HOUR))
ON DUPLICATE KEY UPDATE token = VALUES(token);
