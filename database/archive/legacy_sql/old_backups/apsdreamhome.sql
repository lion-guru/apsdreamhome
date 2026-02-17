-- =====================================================
-- APS DREAM HOME - MAIN DATABASE FILE
-- =====================================================
-- Project: APS Dream Home Real Estate Platform
-- Database: apsdreamhome
-- Generated: 2025-09-30
-- Version: 1.0.0
-- =====================================================

-- =====================================================
-- DATABASE SCHEMA
-- =====================================================

-- Users and Authentication Tables
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'customer',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Properties Management
CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `property_type` varchar(50) DEFAULT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'available',
  `agent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Site Settings Table (for Header/Footer)
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Site Settings
INSERT INTO `site_settings` (`setting_name`, `setting_value`, `setting_type`) VALUES
('site_title', 'APS Dream Homes Pvt Ltd - Leading Real Estate Developer', 'text'),
('site_description', 'APS Dream Homes - Your trusted partner in finding dream properties in Gorakhpur, UP. We offer premium residential and commercial properties with modern amenities.', 'textarea'),
('contact_phone', '+91-9554000001', 'text'),
('contact_email', 'info@apsdreamhomes.com', 'email'),
('contact_phone2', '+91-9554000001', 'text'),
('company_address', '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008', 'textarea'),
('company_registration', 'APS Dream Homes Pvt Ltd - Reg. No. U45201UP2020PTC135678', 'text'),
('working_hours', 'Mon-Sat: 9:00 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM', 'text'),
('facebook_url', 'https://www.facebook.com/apsdreamhomes', 'url'),
('instagram_url', 'https://www.instagram.com/apsdreamhomes', 'url'),
('linkedin_url', 'https://www.linkedin.com/company/aps-dream-homes', 'url'),
('youtube_url', 'https://www.youtube.com/channel/apsdreamhomes', 'url'),
('twitter_url', 'https://twitter.com/apsdreamhomes', 'url'),
('footer_about_text', 'APS Dream Homes Pvt Ltd is a leading real estate developer in Gorakhpur, UP, specializing in premium residential and commercial properties. With over 5 years of experience, we have successfully delivered 50+ projects and served 1000+ happy customers.', 'textarea'),
('footer_copyright', '© 2025 APS Dream Homes Pvt Ltd. All Rights Reserved.', 'text'),
('primary_color', '#1a237e', 'color'),
('secondary_color', '#ffd700', 'color'),
('header_bg_color', '#1a237e', 'color'),
('footer_bg_color', '#2c3e50', 'color'),
('meta_keywords', 'real estate gorakhpur, property gorakhpur, flats gorakhpur, apartments gorakhpur, commercial property up, residential property up', 'text'),
('meta_author', 'APS Dream Homes Pvt Ltd', 'text'),
('og_title', 'APS Dream Homes - Premium Real Estate in Gorakhpur', 'text'),
('og_description', 'Find your dream property with APS Dream Homes. Premium residential and commercial properties in Gorakhpur, UP.', 'text'),
('og_image', '/assets/images/aps-logo.png', 'url'),
('newsletter_title', 'Subscribe to Our Newsletter', 'text'),
('newsletter_subtitle', 'Get latest property updates and exclusive offers', 'text'),
('footer_contact_title', 'Get In Touch', 'text'),
('footer_quick_links_title', 'Quick Links', 'text'),
('footer_property_types_title', 'Property Types', 'text'),
('footer_company_title', 'About APS Dream Homes', 'text');

-- Schema Versions Table
CREATE TABLE `schema_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `schema_versions` (`version`, `description`) VALUES
('1.0.0', 'Initial database setup with site settings for header/footer');

-- Sample Admin User
INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`) VALUES
('admin', 'admin@apsdreamhomes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'admin', 1),
('demo_agent', 'agent@apsdreamhomes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'agent', 1),
('demo_customer', 'customer@apsdreamhomes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer', 1);

-- Sample Properties
INSERT INTO `properties` (`title`, `description`, `price`, `property_type`, `bedrooms`, `bathrooms`, `area`, `location`, `city`, `state`, `featured`, `status`, `agent_id`) VALUES
('Luxury 3BHK Apartment in Gorakhpur', 'Beautiful 3BHK apartment with modern amenities, parking, and garden view.', 4500000.00, 'apartment', 3, 2, 1200.00, 'Kunraghat Main Road', 'Gorakhpur', 'Uttar Pradesh', 1, 'available', 1),
('Commercial Shop in Prime Location', 'Prime commercial space ideal for retail business with high footfall.', 2500000.00, 'commercial', NULL, 1, 800.00, 'Railway Station Area', 'Gorakhpur', 'Uttar Pradesh', 0, 'available', 1),
('2BHK Flat with Balcony', 'Cozy 2BHK apartment with balcony, modular kitchen, and 24/7 security.', 3200000.00, 'apartment', 2, 2, 950.00, 'Medical College Road', 'Gorakhpur', 'Uttar Pradesh', 1, 'available', 1),
('Villa with Private Garden', 'Spacious villa with private garden, parking, and premium location.', 8500000.00, 'villa', 4, 3, 2000.00, 'Airport Road', 'Gorakhpur', 'Uttar Pradesh', 1, 'available', 1);

-- Newsletter Subscribers Table
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contact Inquiries Table
CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Property Inquiries Table
CREATE TABLE `property_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Testimonials Table
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `testimonials` (`name`, `designation`, `message`, `rating`) VALUES
('Rajesh Kumar', 'Property Buyer', 'Excellent service and quality properties. APS Dream Homes made my dream home purchase smooth and hassle-free.', 5),
('Priya Sharma', 'Happy Customer', 'Professional team with great attention to detail. Highly recommended for anyone looking for quality properties in Gorakhpur.', 5),
('Amit Singh', 'Business Owner', 'Bought a commercial property through APS Dream Homes. Great location and excellent support throughout the process.', 5);

-- Property Types Table
CREATE TABLE `property_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `property_types` (`name`, `description`) VALUES
('Apartment', 'Modern apartments with all amenities'),
('Villa', 'Independent villas with private gardens'),
('Commercial', 'Commercial spaces for business'),
('Plot', 'Residential and commercial plots'),
('Studio', 'Studio apartments for singles');

-- =====================================================
-- ADDITIONAL SAMPLE DATA FOR COMPLETE SYSTEM
-- =====================================================

-- Sample Contact Inquiries
INSERT INTO `contact_inquiries` (`name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
('Vikash Gupta', 'vikash@example.com', '+91-9876543210', 'Property Inquiry', 'I am interested in 3BHK apartments in Gorakhpur.', 'pending'),
('Neha Patel', 'neha@example.com', '+91-9123456789', 'Investment Opportunity', 'Looking for commercial properties for investment.', 'replied');

-- Sample Property Inquiries
INSERT INTO `property_inquiries` (`property_id`, `name`, `email`, `phone`, `message`, `status`) VALUES
(1, 'Suresh Reddy', 'suresh@example.com', '+91-9988776655', 'Interested in the 3BHK apartment. Please provide more details.', 'pending'),
(2, 'Kavita Jain', 'kavita@example.com', '+91-8877665544', 'Need more information about the commercial shop.', 'replied');

-- Sample Newsletter Subscribers
INSERT INTO `newsletter_subscribers` (`email`) VALUES
('subscriber1@example.com'),
('subscriber2@example.com'),
('subscriber3@example.com');

-- =====================================================
-- DATABASE SETUP COMPLETE
-- =====================================================

-- Final status message
SELECT 'APS Dream Home database setup completed successfully!' as status,
       'Database: apsdreamhome' as database_name,
       'Tables created: 8' as tables_count,
       'Settings configured: 20+' as settings_count,
       'Sample data added: Yes' as sample_data;
