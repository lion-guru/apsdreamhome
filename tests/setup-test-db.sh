#!/bin/bash

# Set database credentials
DB_USER="root"
DB_PASS=""
DB_NAME="apsdreamhome_test"
TEST_USER="testuser"
TEST_PASS="testpass"

# Create database and user
mysql -u"$DB_USER" -p"$DB_PASS" -e "
    DROP DATABASE IF EXISTS $DB_NAME;
    CREATE DATABASE $DB_NAME;
    USE $DB_NAME;
    
    -- Create properties table
    CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        bedrooms INT NOT NULL,
        owner_contact VARCHAR(255),
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    -- Insert test data
    INSERT INTO properties (title, location, address, type, price, bedrooms) VALUES
        ('Test Property 1', 'Test Location', '123 Test St', 'house', 250000, 3),
        ('Test Property 2', 'Another Location', '456 Test Ave', 'apartment', 150000, 2),
        ('Test Property 3', 'Test Location', '789 Test Blvd', 'house', 350000, 4);
    
    -- Create test user and grant privileges
    DROP USER IF EXISTS '$TEST_USER'@'localhost';
    CREATE USER '$TEST_USER'@'localhost' IDENTIFIED BY '$TEST_PASS';
    GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$TEST_USER'@'localhost';
    FLUSH PRIVILEGES;
"

echo "Test database '$DB_NAME' has been created and seeded with test data."
