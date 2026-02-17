-- Create the news table for APS Dream Home
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `summary` TEXT,
    `image` VARCHAR(255),
    `content` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample news data
INSERT INTO `news` (`title`, `date`, `summary`, `image`, `content`) VALUES
('Welcome to APS Dream Homes!', '2025-04-01', 'We are excited to announce the launch of our new platform.', 'news1.jpg', 'Full content of the news article goes here.'),
('Market Update: April 2025', '2025-04-10', 'Latest trends and updates in the real estate market.', 'news2.jpg', 'Detailed content for market update.');
