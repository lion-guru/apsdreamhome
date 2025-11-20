-- Add approved column to properties table if it doesn't exist
ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS approved TINYINT(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0;

-- Create property_reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS property_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255) NOT NULL,
    comment TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update the properties table to ensure it has all necessary columns
ALTER TABLE properties 
MODIFY COLUMN status ENUM('draft', 'published', 'sold', 'rented', 'inactive') DEFAULT 'draft',
ADD COLUMN IF NOT EXISTS property_type VARCHAR(50) AFTER slug,
ADD COLUMN IF NOT EXISTS sale_date DATE AFTER status,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update existing queries to use the correct column names
-- This is a placeholder for any data migration that might be needed
-- based on the actual application logic

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_property_status ON properties(status);
CREATE INDEX IF NOT EXISTS idx_property_type ON properties(property_type);
CREATE INDEX IF NOT EXISTS idx_property_approved ON properties(approved);
CREATE INDEX IF NOT EXISTS idx_property_featured ON properties(is_featured);
