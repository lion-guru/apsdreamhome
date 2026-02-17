-- Create users table for all user types
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('user', 'associate', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create associates table for associate-specific data
CREATE TABLE IF NOT EXISTS associates (
    associate_id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(10) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    sponsor_id VARCHAR(10),
    referral_code VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sponsor_id) REFERENCES associates(uid) ON DELETE SET NULL
);