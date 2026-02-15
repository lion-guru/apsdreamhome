<?php
require_once __DIR__ . '/init.php';

// Function to create table if it doesn't exist
function createTableIfNotExists($db, $tableName, $sql)
{
    $checkTable = $db->query("SHOW TABLES LIKE :tableName", ['tableName' => $tableName]);
    if ($checkTable->rowCount() == 0) {
        if (!$db->execute($sql)) {
            error_log("Error creating $tableName table");
            return false;
        }
        error_log("Created $tableName table successfully");
    }
    return true;
}

// Create properties table
$propertiesSQL = "CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type_id INT,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(255),
    area DECIMAL(10,2),
    bedrooms INT,
    bathrooms INT,
    status ENUM('available', 'sold', 'rented') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create property_types table
$propertyTypesSQL = "CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create customers table
$customersSQL = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create bookings table
$bookingsSQL = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create payments table
$paymentsSQL = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'upi') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

// Create all tables
$tables = [
    'properties' => $propertiesSQL,
    'property_types' => $propertyTypesSQL,
    'customers' => $customersSQL,
    'bookings' => $bookingsSQL,
    'payments' => $paymentsSQL
];

$allTablesCreated = true;
foreach ($tables as $tableName => $sql) {
    if (!createTableIfNotExists($db, $tableName, $sql)) {
        $allTablesCreated = false;
        echo "Error creating $tableName table<br>";
    }
}

if ($allTablesCreated) {
    echo "All tables created successfully!<br>";

    // Insert some default property types if none exist
    $row = $db->fetch("SELECT COUNT(*) as count FROM property_types");
    $typeCount = $row['count'] ?? 0;

    if ($typeCount == 0) {
        $defaultTypes = [
            ['Apartment', 'Modern residential units in multi-story buildings'],
            ['Villa', 'Luxury independent houses with private gardens'],
            ['Plot', 'Open land for construction'],
            ['Commercial', 'Properties for business use'],
            ['Penthouse', 'Luxury apartments on the top floor']
        ];

        foreach ($defaultTypes as $type) {
            $db->execute("INSERT INTO property_types (type, description) VALUES (:type, :description)", [
                'type' => $type[0],
                'description' => $type[1]
            ]);
        }
        echo "Default property types added<br>";
    }
}
