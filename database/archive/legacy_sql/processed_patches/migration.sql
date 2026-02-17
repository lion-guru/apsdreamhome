-- Backup existing data
CREATE TABLE IF NOT EXISTS user_backup AS SELECT * FROM user;
CREATE TABLE IF NOT EXISTS associates_backup AS SELECT * FROM associates;

-- Drop tables with foreign key constraints first
DROP TABLE IF EXISTS team_hierarchy;
DROP TABLE IF EXISTS commission_transactions;
DROP TABLE IF EXISTS contact;
DROP TABLE IF EXISTS associate_performance;
DROP TABLE IF EXISTS associates;
DROP TABLE IF EXISTS referrals;
DROP TABLE IF EXISTS user;

-- Create new users table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create new associates table with proper foreign key
CREATE TABLE IF NOT EXISTS associates (
    associate_id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(10) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    sponsor_id VARCHAR(10),
    referral_code VARCHAR(10) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Migrate data from backup
INSERT INTO users (name, email, password, phone, utype)
SELECT uname, uemail, upass, uphone, utype FROM user_backup;

-- Drop backup tables
DROP TABLE IF EXISTS user_backup;
DROP TABLE IF EXISTS associates_backup;