-- Create properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10,2),
    type ENUM('house', 'apartment', 'villa', 'land', 'commercial') NOT NULL,
    status ENUM('available', 'sold', 'rented', 'under_contract', 'off_market') NOT NULL DEFAULT 'available',
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
