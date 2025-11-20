-- Property Favorites and Inquiries System Tables
-- Add these tables to your database for the favorites and inquiry system

-- Table: property_favorites
-- Stores user's favorite properties
CREATE TABLE IF NOT EXISTS property_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_user_id (user_id),
    INDEX idx_property_id (property_id),
    UNIQUE KEY unique_user_property (user_id, property_id),

    -- Foreign key constraints (if you want referential integrity)
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: property_inquiries
-- Stores property inquiries from users
CREATE TABLE IF NOT EXISTS property_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    user_id INT NULL, -- NULL for guest inquiries
    guest_name VARCHAR(255) NULL,
    guest_email VARCHAR(255) NULL,
    guest_phone VARCHAR(20) NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    inquiry_type ENUM('general', 'viewing', 'price', 'availability', 'offer') DEFAULT 'general',
    status ENUM('new', 'in_progress', 'responded', 'closed') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT NULL, -- Admin/agent user ID
    response_message TEXT NULL,
    responded_at TIMESTAMP NULL,
    responded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_property_id (property_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_assigned_to (assigned_to),

    -- Foreign key constraints
    CONSTRAINT fk_inquiries_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    CONSTRAINT fk_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_inquiries_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_inquiries_responder FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inquiry_attachments
-- Optional: For file attachments to inquiries
CREATE TABLE IF NOT EXISTS inquiry_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inquiry_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Foreign key constraint
    CONSTRAINT fk_attachments_inquiry FOREIGN KEY (inquiry_id) REFERENCES property_inquiries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing
INSERT INTO property_favorites (user_id, property_id) VALUES
(1, 1), -- Assuming user ID 1 and property ID 1 exist
(1, 2);

INSERT INTO property_inquiries (property_id, user_id, subject, message, inquiry_type, status, priority) VALUES
(1, 1, 'Interested in this property', 'I am very interested in this property. Can you please provide more details about the neighborhood and amenities?', 'general', 'new', 'high'),
(2, NULL, 'Property viewing request', 'Hi, I would like to schedule a viewing for this property. Please let me know available times.', 'viewing', 'new', 'medium');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_favorites_user_property ON property_favorites(user_id, property_id);
CREATE INDEX IF NOT EXISTS idx_inquiries_property_status ON property_inquiries(property_id, status);
CREATE INDEX IF NOT EXISTS idx_inquiries_assigned_status ON property_inquiries(assigned_to, status);
