<?php
/**
 * Fix Missing Database Tables
 * Creates: states, districts, cities, user_properties, service_interests, users view
 * Seeds from existing state/city tables
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected.\n";

    // 1. Create `states` table (from existing `state`)
    echo "\n--- states ---\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        code VARCHAR(10) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Check if empty, seed from `state` table
    $count = $pdo->query("SELECT COUNT(*) FROM states")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO states (id, name) SELECT sid, sname FROM state");
        echo "Seeded " . $pdo->query("SELECT COUNT(*) FROM states")->fetchColumn() . " states.\n";
    } else {
        echo "Already has $count states.\n";
    }

    // 2. Create `districts` table
    echo "\n--- districts ---\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS districts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        state_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $count = $pdo->query("SELECT COUNT(*) FROM districts")->fetchColumn();
    if ($count == 0) {
        // Seed some common UP districts
        $stateId = $pdo->query("SELECT id FROM states LIMIT 1")->fetchColumn();
        if ($stateId) {
            $districts = ['Gorakhpur', 'Lucknow', 'Varanasi', 'Kushinagar', 'Deoria', 'Maharajganj', 'Azamgarh', 'Jaunpur', 'Basti', 'Siddharthnagar', 'Sant Kabir Nagar', 'Ambedkar Nagar', 'Sultanpur', 'Pratapgarh', 'Allahabad', 'Kanpur', 'Agra', 'Mathura', 'Aligarh', 'Meerut', 'Ghaziabad', 'Noida', 'Bareilly', 'Moradabad', 'Saharanpur', 'Muzaffarnagar'];
            $stmt = $pdo->prepare("INSERT INTO districts (name, state_id) VALUES (?, ?)");
            foreach ($districts as $d) {
                $stmt->execute([$d, $stateId]);
            }
            echo "Seeded " . count($districts) . " districts for state $stateId.\n";
        } else {
            echo "No states found to seed districts.\n";
        }
    } else {
        echo "Already has $count districts.\n";
    }

    // 3. Create `cities` table (from existing `city`)
    echo "\n--- cities ---\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        district_id INT DEFAULT NULL,
        state_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $count = $pdo->query("SELECT COUNT(*) FROM cities")->fetchColumn();
    if ($count == 0) {
        // Seed from existing `city` table - map sid to state
        $cities = $pdo->query("SELECT cid, cname, sid FROM city")->fetchAll();
        if (count($cities) > 0) {
            $stmt = $pdo->prepare("INSERT INTO cities (id, name, state_id) VALUES (?, ?, ?)");
            foreach ($cities as $c) {
                $stmt->execute([$c['cid'], $c['cname'], $c['sid']]);
            }
            echo "Seeded " . count($cities) . " cities from city table.\n";
        } else {
            // Seed default cities
            $stateId = $pdo->query("SELECT id FROM states LIMIT 1")->fetchColumn();
            if ($stateId) {
                $defaultCities = ['Gorakhpur', 'Lucknow', 'Varanasi', 'Kushinagar', 'Padrauna', 'Deoria', 'Maharajganj', 'Noida', 'Ghaziabad', 'Agra', 'Kanpur', 'Allahabad', 'Bareilly', 'Meerut', 'Saharanpur', 'Mathura', 'Aligarh', 'Moradabad', 'Muzaffarnagar', 'Azamgarh', 'Jaunpur', 'Basti'];
                $stmt = $pdo->prepare("INSERT INTO cities (name, state_id) VALUES (?, ?)");
                foreach ($defaultCities as $c) {
                    $stmt->execute([$c, $stateId]);
                }
                echo "Seeded " . count($defaultCities) . " default cities.\n";
            }
        }
    } else {
        echo "Already has $count cities.\n";
    }

    // 4. Create `user_properties` table
    echo "\n--- user_properties ---\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        name VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        email VARCHAR(100) DEFAULT NULL,
        property_type VARCHAR(50) DEFAULT NULL,
        listing_type VARCHAR(20) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        state_id INT DEFAULT NULL,
        district_id INT DEFAULT NULL,
        city_id INT DEFAULT NULL,
        area_sqft DECIMAL(12,2) DEFAULT NULL,
        price DECIMAL(15,2) DEFAULT NULL,
        price_type VARCHAR(20) DEFAULT 'fixed',
        description TEXT DEFAULT NULL,
        status ENUM('pending','verified','approved','rejected') DEFAULT 'pending',
        views INT DEFAULT 0,
        inquiries INT DEFAULT 0,
        image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $count = $pdo->query("SELECT COUNT(*) FROM user_properties")->fetchColumn();
    echo "Has $count user_properties.\n";

    // 5. Create `service_interests` table
    echo "\n--- service_interests ---\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_interests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id INT DEFAULT NULL,
        service_type VARCHAR(50) DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $count = $pdo->query("SELECT COUNT(*) FROM service_interests")->fetchColumn();
    echo "Has $count service_interests.\n";

    // 6. Create `users` VIEW that maps user table to standard column names
    echo "\n--- users VIEW ---\n";
    try {
        $pdo->exec("DROP VIEW IF EXISTS users");
    } catch (\Exception $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }
    try {
        $pdo->exec("CREATE VIEW users AS SELECT 
            uid AS id, 
            uname AS name, 
            uemail AS email, 
            uphone AS phone, 
            upass AS password, 
            utype AS role, 
            join_date AS created_at 
        FROM user");
        echo "Created users VIEW -> user table.\n";
    } catch (\Exception $e) {
        echo "Could not create VIEW: " . $e->getMessage() . "\n";
        // Create as table instead
        $pdo->exec("CREATE TABLE IF NOT EXISTS users LIKE user");
        echo "Created users table as copy of user structure.\n";
    }

    // 7. Fix FK constraints in `properties` that reference `users` (non-existent)
    // Drop FK constraints since users view won't satisfy FK requirements
    echo "\n--- Fix properties FK constraints ---\n";
    try {
        $fks = $pdo->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'properties' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchAll();
        foreach ($fks as $fk) {
            $pdo->exec("ALTER TABLE properties DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
            echo "Dropped FK: {$fk['CONSTRAINT_NAME']}\n";
        }
    } catch (\Exception $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }

    echo "\n=== ALL DONE ===\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
