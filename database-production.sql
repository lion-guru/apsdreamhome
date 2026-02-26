-- APS Dream Home - Production Database Initialization
-- Run this script to set up the production database

-- Create production database
CREATE DATABASE IF NOT EXISTS apsdreamhome_prod
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Create database user with proper permissions
CREATE USER IF NOT EXISTS 'apsdreamhome_user'@'localhost'
IDENTIFIED BY 'SECURE_PASSWORD_CHANGE_THIS';

-- Grant all privileges on the production database
GRANT ALL PRIVILEGES ON apsdreamhome_prod.* TO 'apsdreamhome_user'@'localhost';

-- Grant backup permissions for automated backups
GRANT SELECT, LOCK TABLES ON apsdreamhome_prod.* TO 'apsdreamhome_user'@'localhost';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Use the production database
USE apsdreamhome_prod;

-- Create core tables for APS Dream Home

-- Users table (extends Laravel's default users table)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role ENUM('customer', 'agent', 'associate', 'employee', 'admin') DEFAULT 'customer',
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Personal access tokens for API authentication
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password resets
CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT NULL,
    price DECIMAL(15,2) NOT NULL,
    property_type ENUM('apartment', 'villa', 'commercial', 'plot', 'house') DEFAULT 'apartment',
    property_type_id BIGINT UNSIGNED NULL,
    bedrooms TINYINT UNSIGNED NULL,
    bathrooms TINYINT UNSIGNED NULL,
    area DECIMAL(10,2) NULL,
    area_unit ENUM('sqft', 'sqm', 'acre', 'hectare') DEFAULT 'sqft',

    -- Location details
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    location VARCHAR(255) NULL, -- For backward compatibility

    -- Property features
    furnished ENUM('furnished', 'semi-furnished', 'unfurnished') DEFAULT 'unfurnished',
    parking BOOLEAN DEFAULT FALSE,
    garden BOOLEAN DEFAULT FALSE,
    balcony BOOLEAN DEFAULT FALSE,
    lift BOOLEAN DEFAULT FALSE,
    security BOOLEAN DEFAULT FALSE,
    power_backup BOOLEAN DEFAULT FALSE,

    -- Status and visibility
    status ENUM('available', 'sold', 'rented', 'under_construction', 'ready_to_move') DEFAULT 'available',
    featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    priority TINYINT DEFAULT 0,

    -- Metadata
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    tags JSON NULL,

    -- Ownership
    agent_id BIGINT UNSIGNED NULL,
    owner_name VARCHAR(255) NULL,
    owner_phone VARCHAR(20) NULL,
    owner_email VARCHAR(255) NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_active (is_active),
    INDEX idx_property_type (property_type),
    INDEX idx_city (city),
    INDEX idx_price (price),
    INDEX idx_agent (agent_id),
    FULLTEXT idx_search (title, description, location, city, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order TINYINT DEFAULT 0,
    alt_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property types table
CREATE TABLE IF NOT EXISTS property_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects/Colonies table
CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT NULL,
    location TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    developer_name VARCHAR(255) NULL,
    total_units INT NULL,
    available_units INT NULL,
    possession_date DATE NULL,
    rera_number VARCHAR(100) NULL,

    -- Project status
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    is_active BOOLEAN DEFAULT TRUE,

    -- Features
    amenities JSON NULL,
    nearby_locations JSON NULL,

    -- Media
    banner_image VARCHAR(500) NULL,
    gallery_images JSON NULL,

    -- SEO
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_active (is_active),
    INDEX idx_city (city),
    FULLTEXT idx_search (name, description, location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Leads/Inquiries table
CREATE TABLE IF NOT EXISTS leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NULL,

    -- Lead source and type
    source ENUM('website', 'whatsapp', 'phone', 'walk_in', 'referral') DEFAULT 'website',
    lead_type ENUM('property_inquiry', 'project_inquiry', 'general', 'complaint', 'feedback') DEFAULT 'general',

    -- Property/Project reference
    property_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,

    -- Assignment
    assigned_to BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL,

    -- Status tracking
    status ENUM('new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won', 'lost', 'follow_up') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    -- Follow up
    next_follow_up TIMESTAMP NULL,
    last_contact TIMESTAMP NULL,
    notes TEXT NULL,

    -- Tracking
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    referrer_url VARCHAR(500) NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_property (property_id),
    INDEX idx_project (project_id),
    INDEX idx_next_follow_up (next_follow_up)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) NULL,
    model_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_model (model_type, model_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default property types
INSERT IGNORE INTO property_types (type, description, icon, sort_order) VALUES
('apartment', 'Modern residential apartments', 'fas fa-building', 1),
('villa', 'Luxury villas and bungalows', 'fas fa-home', 2),
('commercial', 'Commercial spaces and offices', 'fas fa-briefcase', 3),
('plot', 'Residential and commercial plots', 'fas fa-map-marked-alt', 4),
('house', 'Independent houses', 'fas fa-house-user', 5);

-- Insert default admin user (CHANGE PASSWORD AFTER FIRST LOGIN)
INSERT IGNORE INTO users (name, email, password, role, is_active) VALUES
('System Administrator', 'admin@apsdreamhome.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);

-- Insert sample properties for testing
INSERT IGNORE INTO properties (
    title, slug, description, price, property_type, bedrooms, bathrooms, area,
    city, state, address, status, featured, is_active
) VALUES
('Luxury 3BHK Apartment in Gorakhpur', 'luxury-3bhk-gorakhpur', 'Beautiful 3BHK apartment with modern amenities', 4500000.00, 'apartment', 3, 2, 1200.00,
 'Gorakhpur', 'Uttar Pradesh', 'Civil Lines, Gorakhpur', 'available', TRUE, TRUE),

('Premium Villa with Garden', 'premium-villa-garden', 'Spacious villa with private garden and parking', 8500000.00, 'villa', 4, 3, 2500.00,
 'Lucknow', 'Uttar Pradesh', 'Gomti Nagar, Lucknow', 'available', TRUE, TRUE),

('Commercial Space in Business District', 'commercial-space-business-district', 'Prime commercial space perfect for office or retail', 12000000.00, 'commercial', NULL, 2, 2000.00,
 'Kanpur', 'Uttar Pradesh', 'Mall Road, Kanpur', 'available', FALSE, TRUE);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_properties_composite ON properties (status, featured, is_active, city);
CREATE INDEX IF NOT EXISTS idx_leads_composite ON leads (status, priority, assigned_to, next_follow_up);

-- Create a view for property analytics
CREATE OR REPLACE VIEW property_analytics AS
SELECT
    COUNT(*) as total_properties,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_properties,
    SUM(CASE WHEN featured = TRUE THEN 1 ELSE 0 END) as featured_properties,
    AVG(price) as avg_price,
    MIN(price) as min_price,
    MAX(price) as max_price,
    COUNT(DISTINCT city) as cities_covered
FROM properties
WHERE is_active = TRUE;

-- Create a view for lead analytics
CREATE OR REPLACE VIEW lead_analytics AS
SELECT
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
    SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_leads,
    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_leads,
    AVG(CASE WHEN next_follow_up IS NOT NULL THEN DATEDIFF(next_follow_up, NOW()) ELSE NULL END) as avg_follow_up_days
FROM leads
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);

COMMIT;
