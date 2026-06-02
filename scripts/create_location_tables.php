<?php
/**
 * Create Location Master Tables
 * For smart autocomplete and cascading dropdowns
 */

// Bootstrap the app
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "Creating Location Master Tables...\n\n";

$queries = [

// 1. Countries Table
"CREATE TABLE IF NOT EXISTS countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    iso_code VARCHAR(3),
    phone_code VARCHAR(10),
    currency VARCHAR(10),
    currency_symbol VARCHAR(5),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 2. States Table
"CREATE TABLE IF NOT EXISTS states (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_country (country_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 3. Districts Table
"CREATE TABLE IF NOT EXISTS districts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    state_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE,
    INDEX idx_state (state_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 4. Cities Table
"CREATE TABLE IF NOT EXISTS cities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    district_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('city', 'town', 'village') DEFAULT 'city',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE,
    INDEX idx_district (district_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 5. Pincodes Table
"CREATE TABLE IF NOT EXISTS pincodes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pincode VARCHAR(10) NOT NULL UNIQUE,
    city_id INT UNSIGNED,
    district_id INT UNSIGNED,
    state_id INT UNSIGNED,
    country_id INT UNSIGNED DEFAULT 1,
    area_name VARCHAR(200),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    INDEX idx_pincode (pincode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 6. Banks Table (IFSC master)
"CREATE TABLE IF NOT EXISTS banks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    short_name VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// 7. Bank Branches Table (IFSC codes)
"CREATE TABLE IF NOT EXISTS bank_branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_id INT UNSIGNED NOT NULL,
    ifsc VARCHAR(20) NOT NULL UNIQUE,
    branch VARCHAR(200) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    district VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bank_id) REFERENCES banks(id) ON DELETE CASCADE,
    INDEX idx_ifsc (ifsc),
    INDEX idx_bank (bank_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

];

$created = 0;
foreach ($queries as $sql) {
    preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $matches);
    $tableName = $matches[1] ?? 'unknown';
    
    try {
        $db->execute($sql);
        echo "✅ Created: $tableName\n";
        $created++;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⏭️  Already exists: $tableName\n";
        } else {
            echo "❌ Error creating $tableName: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Created $created tables!\n";
