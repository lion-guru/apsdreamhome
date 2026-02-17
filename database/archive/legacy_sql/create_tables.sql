-- Create colonies table
CREATE TABLE IF NOT EXISTS `colonies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text,
  `total_area` varchar(50) DEFAULT NULL,
  `developed_area` varchar(50) DEFAULT NULL,
  `total_plots` int(11) DEFAULT 0,
  `available_plots` int(11) DEFAULT 0,
  `completion_status` enum('Planning','Under Development','Completed') DEFAULT 'Planning',
  `status` enum('available','sold_out','coming_soon') DEFAULT 'available',
  `starting_price` decimal(15,2) DEFAULT 0.00,
  `current_price` decimal(15,2) DEFAULT 0.00,
  `features` text,
  `amenities` text,
  `coordinates` text,
  `developer` varchar(255) DEFAULT 'APS Dream Homes Private Limited',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create plots table
CREATE TABLE IF NOT EXISTS `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `size` decimal(10,2) NOT NULL COMMENT 'in square feet',
  `price` decimal(15,2) NOT NULL,
  `status` enum('available','booked','sold','blocked') DEFAULT 'available',
  `facing` varchar(50) DEFAULT NULL,
  `corner_plot` tinyint(1) DEFAULT 0,
  `booking_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `colony_id` (`colony_id`),
  CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample colonies data
INSERT INTO `colonies` (`name`, `location`, `description`, `total_area`, `developed_area`, `total_plots`, `available_plots`, `completion_status`, `status`, `starting_price`, `current_price`, `features`, `amenities`, `coordinates`, `developer`) VALUES
('Suryoday Colony', 'Gorakhpur, Uttar Pradesh', 'Premium residential colony developed by APS Dream Homes in Gorakhpur', '25 Acres', '25 Acres', 200, 0, 'Completed', 'sold_out', 1200000.00, 1500000.00, '24/7 Security,Wide Roads,Green Spaces,Community Hall,Children Play Area', 'Power Backup,Water Supply,Sewage System,Street Lights,Landscaped Gardens', '{"latitude": 26.7606, "longitude": 83.3732}', 'APS Dream Homes Private Limited'),
('Raghunath Nagri', 'Gorakhpur, Uttar Pradesh', 'Luxury residential project with modern amenities', '15 Acres', '15 Acres', 150, 0, 'Completed', 'sold_out', 1500000.00, 1800000.00, 'Gated Community,24/7 Security,Club House,Swimming Pool,Jogging Track', 'Power Backup,Water Supply,Underground Electricity,Landscaped Gardens', '{"latitude": 26.7445, "longitude": 83.4032}', 'APS Dream Homes Private Limited'),
('Brajradha Nagri', 'Gorakhpur, Uttar Pradesh', 'Affordable housing with all basic amenities', '20 Acres', '20 Acres', 180, 0, 'Completed', 'sold_out', 1000000.00, 1300000.00, '24/7 Security,Park,Community Center,Children Play Area', 'Water Supply,Electricity,Street Lights', '{"latitude": 26.7523, "longitude": 83.3921}', 'APS Dream Homes Private Limited'),
('Stuti Bihar', 'Sonbarsa, Gorakhpur', 'Peaceful living in the lap of nature', '30 Acres', '30 Acres', 250, 0, 'Completed', 'sold_out', 800000.00, 1100000.00, 'Green Belt,24/7 Security,Temple,Community Hall', 'Water Supply,Electricity,Well-connected Roads', '{"latitude": 26.7356, "longitude": 83.4154}', 'APS Dream Homes Private Limited');

-- Insert sample plots for Suryoday Colony
INSERT INTO `plots` (`colony_id`, `plot_number`, `size`, `price`, `status`, `facing`, `corner_plot`, `booking_amount`) VALUES
(1, 'SYD-101', 2000.00, 1500000.00, 'sold', 'East', 0, 150000.00),
(1, 'SYD-102', 2200.00, 1650000.00, 'sold', 'North', 1, 165000.00),
(1, 'SYD-103', 1800.00, 1350000.00, 'sold', 'West', 0, 135000.00);

-- Insert sample plots for Raghunath Nagri
INSERT INTO `plots` (`colony_id`, `plot_number`, `size`, `price`, `status`, `facing`, `corner_plot`, `booking_amount`) VALUES
(2, 'RN-201', 2500.00, 1800000.00, 'sold', 'South', 1, 180000.00),
(2, 'RN-202', 2300.00, 1740000.00, 'sold', 'East', 0, 174000.00);

-- Insert sample plots for Brajradha Nagri
INSERT INTO `plots` (`colony_id`, `plot_number`, `size`, `price`, `status`, `facing`, `corner_plot`, `booking_amount`) VALUES
(3, 'BN-301', 1500.00, 1300000.00, 'sold', 'North', 0, 130000.00),
(3, 'BN-302', 1600.00, 1350000.00, 'sold', 'East', 1, 135000.00);

-- Insert sample plots for Stuti Bihar
INSERT INTO `plots` (`colony_id`, `plot_number`, `size`, `price`, `status`, `facing`, `corner_plot`, `booking_amount`) VALUES
(4, 'SB-401', 1200.00, 1100000.00, 'sold', 'West', 0, 110000.00),
(4, 'SB-402', 1300.00, 1150000.00, 'sold', 'South', 1, 115000.00);

-- Add indexes for better performance
ALTER TABLE `colonies` ADD FULLTEXT KEY `ft_name_location` (`name`,`location`);
ALTER TABLE `plots` ADD INDEX `idx_plot_status` (`status`);
