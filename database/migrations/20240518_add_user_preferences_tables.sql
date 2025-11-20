-- Create amenities table if not exists
CREATE TABLE IF NOT EXISTS `amenities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user_preferences table if not exists
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preferred_property_types` text DEFAULT NULL,
  `preferred_locations` text DEFAULT NULL,
  `min_bedrooms` int(11) DEFAULT NULL,
  `max_price` decimal(15,2) DEFAULT NULL,
  `min_area` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_user_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user_amenity_preferences table if not exists
CREATE TABLE IF NOT EXISTS `user_amenity_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_amenity` (`user_id`,`amenity_id`),
  KEY `amenity_id` (`amenity_id`),
  CONSTRAINT `fk_user_amenity_pref_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_amenity_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert common amenities if they don't exist
INSERT IGNORE INTO `amenities` (`name`, `category`, `icon`) VALUES
('Swimming Pool', 'Outdoor', 'swimming-pool'),
('Gym', 'Indoor', 'dumbbell'),
('Parking', 'Parking', 'parking'),
('Garden', 'Outdoor', 'tree'),
('Security', 'Security', 'shield-alt'),
('Elevator', 'Building', 'elevator'),
('Power Backup', 'Utility', 'bolt'),
('Water Supply', 'Utility', 'tint'),
('Club House', 'Community', 'home'),
('Play Area', 'Outdoor', 'child'),
('Intercom', 'Security', 'phone'),
('Shopping Center', 'Nearby', 'shopping-cart'),
('Hospital', 'Nearby', 'hospital'),
('School', 'Nearby', 'school'),
('Park', 'Nearby', 'tree'),
('Air Conditioning', 'Indoor', 'snowflake'),
('Heating', 'Indoor', 'thermometer-half'),
('Fireplace', 'Indoor', 'fire'),
('Balcony', 'Outdoor', 'square'),
('Laundry', 'Utility', 'tshirt');

-- Add indexes for better performance
ALTER TABLE `user_preferences` ADD INDEX `idx_user_prefs` (`user_id`);
ALTER TABLE `user_amenity_preferences` ADD INDEX `idx_user_amenity` (`user_id`, `amenity_id`);
