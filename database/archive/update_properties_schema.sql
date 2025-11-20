-- Property Types Table
CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);

-- Properties Table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description LONGTEXT,
    property_type_id INT,
    price DECIMAL(15, 2) NOT NULL,
    area DECIMAL(10, 2) NOT NULL,
    area_unit VARCHAR(20) DEFAULT 'sq.ft',
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    garages INT DEFAULT 0,
    year_built YEAR,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    featured_image VARCHAR(255),
    gallery TEXT, -- JSON array of image paths
    video_tour_url VARCHAR(255),
    virtual_tour_url VARCHAR(255),
    floor_plans TEXT, -- JSON array of floor plan objects
    features TEXT, -- JSON array of features
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'sold', 'rented') DEFAULT 'draft',
    listing_status ENUM('for_sale', 'for_rent', 'sold', 'rented') DEFAULT 'for_sale',
    views INT DEFAULT 0,
    agent_id INT,
    created_by INT,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_listing_status (listing_status),
    INDEX idx_is_featured (is_featured),
    INDEX idx_agent (agent_id),
    FULLTEXT INDEX idx_search (title, description, address, city, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Amenities Table
CREATE TABLE IF NOT EXISTS property_amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    amenity_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id)
);

-- Property Visits Table
CREATE TABLE IF NOT EXISTS property_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    agent_id INT,
    visitor_name VARCHAR(255) NOT NULL,
    visitor_email VARCHAR(255) NOT NULL,
    visitor_phone VARCHAR(50) NOT NULL,
    visit_datetime DATETIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    token VARCHAR(64) UNIQUE,
    cancellation_reason TEXT,
    feedback TEXT,
    rating TINYINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_agent (agent_id),
    INDEX idx_visitor_email (visitor_email),
    INDEX idx_visit_datetime (visit_datetime),
    INDEX idx_status (status)
);

-- Visit Reminders Table
CREATE TABLE IF NOT EXISTS visit_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    reminder_type ENUM('24h', '1h', 'custom') NOT NULL,
    reminder_time DATETIME NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES property_visits(id) ON DELETE CASCADE,
    INDEX idx_visit (visit_id),
    INDEX idx_status (status),
    INDEX idx_reminder_time (reminder_time)
);

-- Property Favorites Table
CREATE TABLE IF NOT EXISTS property_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_property (user_id, property_id),
    INDEX idx_user (user_id),
    INDEX idx_property (property_id)
);

-- Insert default property types if they don't exist
INSERT IGNORE INTO property_types (name, slug, description, icon, status) VALUES
('Apartment', 'apartment', 'Residential units within a larger building', 'fa-building', 'active'),
('Villa', 'villa', 'Luxury standalone house with private garden', 'fa-home', 'active'),
('House', 'house', 'Single-family residential building', 'fa-home', 'active'),
('Office', 'office', 'Commercial office space', 'fa-building', 'active'),
('Building', 'building', 'Commercial building with multiple units', 'fa-building', 'active'),
('Townhouse', 'townhouse', 'Multi-floor home sharing walls with adjacent properties', 'fa-home', 'active'),
('Shop', 'shop', 'Retail commercial space', 'fa-store', 'active'),
('Garage', 'garage', 'Parking or storage space', 'fa-warehouse', 'active'),
('Land', 'land', 'Vacant land for development', 'fa-mountain', 'active'),
('Farm', 'farm', 'Agricultural land with or without structures', 'fa-tractor', 'active');

-- Create a trigger to update the updated_at timestamp on property_visits
DELIMITER //
CREATE TRIGGER before_property_visits_update
BEFORE UPDATE ON property_visits
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- Create a procedure to clean up old visits
DELIMITER //
CREATE PROCEDURE cleanup_old_visits(IN days_old INT)
BEGIN
    DELETE FROM property_visits 
    WHERE status IN ('completed', 'cancelled', 'no_show')
    AND updated_at < DATE_SUB(NOW(), INTERVAL days_old DAY);
END //
DELIMITER ;

-- Create an event to run the cleanup monthly
DELIMITER //
CREATE EVENT IF NOT EXISTS monthly_visits_cleanup
ON SCHEDULE EVERY 1 MONTH
STARTS TIMESTAMPADD(DAY, 1, LAST_DAY(CURRENT_DATE)) + INTERVAL 1 HOUR
DO
BEGIN
    CALL cleanup_old_visits(90); -- Keep records for 90 days
END //
DELIMITER ;

-- Create a view for agent dashboard
CREATE OR REPLACE VIEW agent_property_stats AS
SELECT 
    p.agent_id,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(p.id) AS total_properties,
    SUM(CASE WHEN p.status = 'published' THEN 1 ELSE 0 END) AS active_listings,
    SUM(CASE WHEN p.listing_status = 'sold' OR p.listing_status = 'rented' THEN 1 ELSE 0 END) AS closed_deals,
    COUNT(DISTINCT pv.id) AS total_visits,
    SUM(CASE WHEN pv.status = 'scheduled' THEN 1 ELSE 0 END) AS upcoming_visits,
    SUM(CASE WHEN pv.status = 'completed' THEN 1 ELSE 0 END) AS completed_visits,
    AVG(pv.rating) AS avg_rating
FROM properties p
LEFT JOIN property_visits pv ON p.id = pv.property_id
LEFT JOIN users u ON p.agent_id = u.id
GROUP BY p.agent_id, u.first_name, u.last_name, u.email;

-- Create a full-text search index on properties
ALTER TABLE properties ADD FULLTEXT ft_property_search (title, description, address, city, state, zip_code);

-- Create indexes for better performance
CREATE INDEX idx_property_type ON properties(property_type_id);
CREATE INDEX idx_property_location ON properties(city, state);
CREATE INDEX idx_property_price ON properties(price);
CREATE INDEX idx_property_bedrooms ON properties(bedrooms);
CREATE INDEX idx_property_bathrooms ON properties(bathrooms);

-- Create a view for featured properties
CREATE OR REPLACE VIEW featured_properties AS
SELECT 
    p.*,
    pt.name AS property_type_name,
    pt.slug AS property_type_slug,
    CONCAT(u.first_name, ' ', u.last_name) AS agent_name,
    u.phone AS agent_phone,
    u.email AS agent_email,
    u.profile_image AS agent_photo
FROM properties p
LEFT JOIN property_types pt ON p.property_type_id = pt.id
LEFT JOIN users u ON p.agent_id = u.id
WHERE p.is_featured = TRUE 
AND p.status = 'published'
ORDER BY p.updated_at DESC
LIMIT 12;
