-- APS Dream Home - Projects Table Structure
-- This script creates the projects table and related tables for the main website

-- Projects table - Main projects/sites table
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_code` varchar(50) NOT NULL UNIQUE,
  `project_type` enum('residential','commercial','mixed','plotting') NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `total_area` decimal(15,2) DEFAULT 0.00,
  `total_plots` int(11) DEFAULT 0,
  `available_plots` int(11) DEFAULT 0,
  `price_per_sqft` decimal(15,2) DEFAULT 0.00,
  `base_price` decimal(15,2) DEFAULT 0.00,
  `project_status` enum('planning','ongoing','completed','cancelled') DEFAULT 'ongoing',
  `possession_date` date DEFAULT NULL,
  `rera_number` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `highlights` json DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `layout_map` varchar(255) DEFAULT NULL,
  `brochure` varchar(255) DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `virtual_tour` text DEFAULT NULL,
  `booking_amount` decimal(15,2) DEFAULT 0.00,
  `emi_available` tinyint(1) DEFAULT 0,
  `developer_name` varchar(255) DEFAULT NULL,
  `developer_contact` varchar(20) DEFAULT NULL,
  `developer_email` varchar(255) DEFAULT NULL,
  `project_head` varchar(255) DEFAULT NULL,
  `project_manager` varchar(255) DEFAULT NULL,
  `sales_manager` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_youtube` varchar(255) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `meta_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  KEY `idx_project_code` (`project_code`),
  KEY `idx_city` (`city`),
  KEY `idx_project_type` (`project_type`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_project_status` (`project_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site visits/bookings table
CREATE TABLE IF NOT EXISTS `site_visits` (
  `visit_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) NOT NULL,
  `visitor_name` varchar(255) NOT NULL,
  `visitor_email` varchar(255) NOT NULL,
  `visitor_phone` varchar(20) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`visit_id`),
  KEY `idx_project_code` (`project_code`),
  KEY `idx_visitor_email` (`visitor_email`),
  KEY `idx_status` (`status`),
  KEY `idx_preferred_date` (`preferred_date`),
  FOREIGN KEY (`project_code`) REFERENCES `projects`(`project_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project inquiries table
CREATE TABLE IF NOT EXISTS `project_inquiries` (
  `inquiry_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) NOT NULL,
  `inquirer_name` varchar(255) NOT NULL,
  `inquirer_email` varchar(255) NOT NULL,
  `inquirer_phone` varchar(20) NOT NULL,
  `inquiry_type` enum('general','pricing','availability','site_visit','callback') DEFAULT 'general',
  `message` text DEFAULT NULL,
  `status` enum('new','in_progress','responded','closed') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inquiry_id`),
  KEY `idx_project_code` (`project_code`),
  KEY `idx_inquirer_email` (`inquirer_email`),
  KEY `idx_status` (`status`),
  KEY `idx_inquiry_type` (`inquiry_type`),
  FOREIGN KEY (`project_code`) REFERENCES `projects`(`project_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Project amenities master table
CREATE TABLE IF NOT EXISTS `project_amenities` (
  `amenity_id` int(11) NOT NULL AUTO_INCREMENT,
  `amenity_name` varchar(255) NOT NULL,
  `amenity_category` varchar(100) DEFAULT NULL,
  `amenity_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`amenity_id`),
  UNIQUE KEY `uk_amenity_name` (`amenity_name`),
  KEY `idx_category` (`amenity_category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default amenities
INSERT IGNORE INTO `project_amenities` (`amenity_name`, `amenity_category`, `amenity_icon`) VALUES
('24/7 Security', 'security', 'fas fa-shield-alt'),
('Swimming Pool', 'recreation', 'fas fa-swimming-pool'),
('Gymnasium', 'fitness', 'fas fa-dumbbell'),
('Children Play Area', 'recreation', 'fas fa-child'),
('Jogging Track', 'fitness', 'fas fa-running'),
('Club House', 'recreation', 'fas fa-home'),
('Landscaped Gardens', 'environment', 'fas fa-tree'),
('Power Backup', 'utilities', 'fas fa-bolt'),
('Water Supply', 'utilities', 'fas fa-tint'),
('Sewage Treatment', 'utilities', 'fas fa-recycle'),
('Car Parking', 'infrastructure', 'fas fa-car'),
('Elevator', 'infrastructure', 'fas fa-elevator'),
('Intercom', 'communication', 'fas fa-phone'),
('CCTV Surveillance', 'security', 'fas fa-video'),
('Fire Fighting System', 'security', 'fas fa-fire-extinguisher'),
('Rain Water Harvesting', 'environment', 'fas fa-cloud-rain'),
('Solar Power', 'utilities', 'fas fa-sun'),
('Vastu Compliant', 'design', 'fas fa-compass'),
('Shopping Complex', 'commercial', 'fas fa-shopping-cart'),
('School', 'education', 'fas fa-school'),
('Hospital', 'healthcare', 'fas fa-hospital'),
('ATM', 'banking', 'fas fa-credit-card'),
('Pet Park', 'recreation', 'fas fa-paw'),
('Yoga/Meditation Area', 'fitness', 'fas fa-om'),
('Amphitheatre', 'recreation', 'fas fa-theater-masks');

-- Project highlights master table
CREATE TABLE IF NOT EXISTS `project_highlights` (
  `highlight_id` int(11) NOT NULL AUTO_INCREMENT,
  `highlight_name` varchar(255) NOT NULL,
  `highlight_category` varchar(100) DEFAULT NULL,
  `highlight_icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`highlight_id`),
  UNIQUE KEY `uk_highlight_name` (`highlight_name`),
  KEY `idx_category` (`highlight_category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default highlights
INSERT IGNORE INTO `project_highlights` (`highlight_name`, `highlight_category`, `highlight_icon`) VALUES
('Prime Location', 'location', 'fas fa-map-marker-alt'),
('Best Investment Opportunity', 'investment', 'fas fa-chart-line'),
('High Appreciation Potential', 'investment', 'fas fa-trending-up'),
('Excellent Connectivity', 'connectivity', 'fas fa-route'),
('Modern Infrastructure', 'infrastructure', 'fas fa-building'),
('Quality Construction', 'construction', 'fas fa-hard-hat'),
('Timely Possession', 'delivery', 'fas fa-calendar-check'),
('Reputed Developer', 'developer', 'fas fa-user-tie'),
('Eco-Friendly Design', 'environment', 'fas fa-leaf'),
('Smart Home Features', 'technology', 'fas fa-home'),
('Spacious Layouts', 'design', 'fas fa-expand-arrows-alt'),
('Natural Ventilation', 'design', 'fas fa-wind'),
('Abundant Natural Light', 'design', 'fas fa-sun'),
('Green Surroundings', 'environment', 'fas fa-trees'),
('Peaceful Environment', 'lifestyle', 'fas fa-peace');

-- Insert sample projects for Gorakhpur and Lucknow
INSERT IGNORE INTO `projects` (
    `project_name`, `project_code`, `project_type`, `location`, `city`, `state`, `pincode`,
    `description`, `short_description`, `total_area`, `total_plots`, `available_plots`,
    `price_per_sqft`, `base_price`, `project_status`, `possession_date`, `rera_number`,
    `is_featured`, `is_active`, `address`, `highlights`, `amenities`, `booking_amount`,
    `emi_available`, `developer_name`, `contact_number`, `contact_email`, `created_at`
) VALUES
-- Gorakhpur Projects
('Suryoday Colony', 'SDC-001', 'residential', 'Suryoday Colony', 'Gorakhpur', 'Uttar Pradesh', '273001',
'Premium residential plots in the heart of Gorakhpur with modern amenities and excellent connectivity.',
'Premium residential plots with modern amenities', 50000.00, 100, 95, 2500.00, 1250000.00, 'ongoing', '2025-12-31', 'UPRERA123456',
1, 1, 'Suryoday Colony, Near Railway Station, Gorakhpur',
'["Prime Location", "Best Investment Opportunity", "Excellent Connectivity"]',
'["24/7 Security", "Landscaped Gardens", "Power Backup", "Water Supply"]', 100000.00, 1,
'APS Dream Homes Pvt Ltd', '+91-9876543210', 'info@apsdreamhome.com', NOW()),

('Raghunath Nagri', 'RNG-002', 'residential', 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', '273002',
'Spacious residential plots in a peaceful locality with all modern facilities and green surroundings.',
'Spacious plots in peaceful locality', 75000.00, 150, 140, 2200.00, 1650000.00, 'ongoing', '2026-03-31', 'UPRERA123457',
1, 1, 'Raghunath Nagri, Civil Lines, Gorakhpur',
'["Peaceful Environment", "Green Surroundings", "Quality Construction"]',
'["Swimming Pool", "Gymnasium", "Children Play Area", "Jogging Track"]', 150000.00, 1,
'APS Dream Homes Pvt Ltd', '+91-9876543210', 'info@apsdreamhome.com', NOW()),

('Braj Radha Nagri', 'BRN-003', 'residential', 'Braj Radha Nagri', 'Gorakhpur', 'Uttar Pradesh', '273003',
'Luxurious residential plots with world-class amenities and strategic location near major landmarks.',
'Luxurious plots with world-class amenities', 100000.00, 200, 185, 3000.00, 3000000.00, 'ongoing', '2026-06-30', 'UPRERA123458',
1, 1, 'Braj Radha Nagri, Airport Road, Gorakhpur',
'["High Appreciation Potential", "Modern Infrastructure", "Reputed Developer"]',
'["24/7 Security", "Swimming Pool", "Club House", "CCTV Surveillance"]', 250000.00, 1,
'APS Dream Homes Pvt Ltd', '+91-9876543210', 'info@apsdreamhome.com', NOW()),

-- Lucknow Projects
('Shyam City', 'SC-004', 'residential', 'Shyam City', 'Lucknow', 'Uttar Pradesh', '226001',
'Premium residential development in Lucknow with excellent connectivity and modern lifestyle amenities.',
'Premium residential development in Lucknow', 80000.00, 180, 165, 2800.00, 2240000.00, 'ongoing', '2025-09-30', 'UPRERA123459',
1, 1, 'Shyam City, Gomti Nagar Extension, Lucknow',
'["Excellent Connectivity", "Modern Infrastructure", "Best Investment Opportunity"]',
'["Landscaped Gardens", "Gymnasium", "Power Backup", "Car Parking"]', 200000.00, 1,
'APS Dream Homes Pvt Ltd', '+91-9876543210', 'info@apsdreamhome.com', NOW());
