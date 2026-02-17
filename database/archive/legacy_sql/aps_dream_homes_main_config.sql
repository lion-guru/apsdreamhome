-- APS Dream Homes - Main Database Configuration File
-- Generated: 2025-09-30
-- This file contains the complete database schema and site settings for APS Dream Homes

-- =====================================================
-- SITE SETTINGS TABLE (For Header/Footer Configuration)
-- =====================================================

CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Header/Footer Settings
INSERT INTO `site_settings` (`setting_name`, `setting_value`, `setting_type`) VALUES
('site_title', 'APS Dream Homes Pvt Ltd - Leading Real Estate Developer', 'text'),
('site_description', 'APS Dream Homes - Your trusted partner in finding dream properties in Gorakhpur, UP. We offer premium residential and commercial properties with modern amenities.', 'textarea'),
('contact_phone', '+91-9554000001', 'text'),
('contact_email', 'info@apsdreamhomes.com', 'email'),
('contact_phone2', '+91-9554000001', 'text'),
('company_address', '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008', 'textarea'),
('company_registration', 'APS Dream Homes Pvt Ltd - Reg. No. U45201UP2020PTC135678', 'text'),
('working_hours', 'Mon-Sat: 9:00 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM', 'text'),

-- Social Media Links
('facebook_url', 'https://www.facebook.com/apsdreamhomes', 'url'),
('instagram_url', 'https://www.instagram.com/apsdreamhomes', 'url'),
('linkedin_url', 'https://www.linkedin.com/company/aps-dream-homes', 'url'),
('youtube_url', 'https://www.youtube.com/channel/apsdreamhomes', 'url'),
('twitter_url', 'https://twitter.com/apsdreamhomes', 'url'),

-- Footer Content
('footer_about_text', 'APS Dream Homes Pvt Ltd is a leading real estate developer in Gorakhpur, UP, specializing in premium residential and commercial properties. With over 5 years of experience, we have successfully delivered 50+ projects and served 1000+ happy customers.', 'textarea'),
('footer_copyright', '© 2025 APS Dream Homes Pvt Ltd. All Rights Reserved.', 'text'),

-- Header/Footer Colors and Styling
('primary_color', '#1a237e', 'color'),
('secondary_color', '#ffd700', 'color'),
('header_bg_color', '#1a237e', 'color'),
('footer_bg_color', '#2c3e50', 'color'),

-- SEO Settings
('meta_keywords', 'real estate gorakhpur, property gorakhpur, flats gorakhpur, apartments gorakhpur, commercial property up, residential property up', 'text'),
('meta_author', 'APS Dream Homes Pvt Ltd', 'text'),
('og_title', 'APS Dream Homes - Premium Real Estate in Gorakhpur', 'text'),
('og_description', 'Find your dream property with APS Dream Homes. Premium residential and commercial properties in Gorakhpur, UP.', 'text'),
('og_image', '/assets/images/aps-logo.png', 'url'),

-- Newsletter Settings
('newsletter_title', 'Subscribe to Our Newsletter', 'text'),
('newsletter_subtitle', 'Get latest property updates and exclusive offers', 'text'),

-- Contact Information for Footer
('footer_contact_title', 'Get In Touch', 'text'),
('footer_quick_links_title', 'Quick Links', 'text'),
('footer_property_types_title', 'Property Types', 'text'),
('footer_company_title', 'About APS Dream Homes', 'text');

-- =====================================================
-- UPDATE LOGO PATH IF NEEDED
-- =====================================================

-- Create uploads directory for logo if it doesn't exist
-- The logo should be placed at: /assets/images/aps-logo.png

-- =====================================================
-- DATABASE SCHEMA VERSION
-- =====================================================

CREATE TABLE IF NOT EXISTS `schema_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `schema_versions` (`version`, `description`) VALUES
('1.0.0', 'Initial database setup with site settings for header/footer');

-- =====================================================
-- SAMPLE DATA FOR TESTING
-- =====================================================

-- Insert some sample footer content if tables exist
-- This is optional and depends on your existing database structure

SELECT 'Database configuration file created successfully!' as status;
