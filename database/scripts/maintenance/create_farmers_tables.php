<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Core\Database\Database;
use App\Core\App;

// Initialize App
$app = new App(dirname(__DIR__, 3));

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    echo "Connected to database successfully.\n";

    // 1. Create farmers table
    echo "Checking 'farmers' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS farmers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(20),
        address TEXT,
        state_id INT,
        district_id INT,
        aadhar_number VARCHAR(50),
        pan_number VARCHAR(50),
        bank_account VARCHAR(50),
        ifsc_code VARCHAR(20),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_state_district (state_id, district_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "'farmers' table created or already exists.\n";

    // 2. Create farmer_land_holdings table
    echo "Checking 'farmer_land_holdings' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS farmer_land_holdings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        farmer_id INT NOT NULL,
        survey_number VARCHAR(100),
        area DECIMAL(10, 2),
        area_unit VARCHAR(50) DEFAULT 'Acre',
        land_type VARCHAR(50),
        location_address TEXT,
        latitude VARCHAR(50),
        longitude VARCHAR(50),
        market_value DECIMAL(15, 2),
        document_number VARCHAR(100),
        status VARCHAR(50) DEFAULT 'available',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
        INDEX idx_farmer (farmer_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "'farmer_land_holdings' table created or already exists.\n";

    // 3. Create land_purchases table
    echo "Checking 'land_purchases' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS land_purchases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        land_holding_id INT NOT NULL,
        land_manager_id VARCHAR(50),
        farmer_id INT,
        price DECIMAL(15, 2),
        advance_amount DECIMAL(15, 2),
        balance_amount DECIMAL(15, 2),
        payment_terms TEXT,
        agreement_date DATE,
        possession_date DATE,
        registration_date DATE,
        status VARCHAR(50) DEFAULT 'pending',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (land_holding_id) REFERENCES farmer_land_holdings(id) ON DELETE CASCADE,
        INDEX idx_land_holding (land_holding_id),
        INDEX idx_land_manager (land_manager_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "'land_purchases' table created or already exists.\n";

    // 4. Check for states and districts
    echo "Checking 'states' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(10),
        status ENUM('active', 'inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);

    echo "Checking 'districts' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS districts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        state_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(10),
        status ENUM('active', 'inactive') DEFAULT 'active',
        FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "States and districts tables checked.\n";

    echo "\nAll required tables for Farmer module checked/created successfully.\n";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
