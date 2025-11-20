-- Migration script to add resell_plots table

CREATE TABLE IF NOT EXISTS `resell_plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `selling_type` varchar(100) NOT NULL,
  `plot_location` varchar(255) NOT NULL,
  `plot_size` float NOT NULL,
  `plot_dimensions` varchar(100) NOT NULL,
  `plot_facing` varchar(100) NOT NULL,
  `road_access` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `plot_category` varchar(100) NOT NULL,
  `full_address` text NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;