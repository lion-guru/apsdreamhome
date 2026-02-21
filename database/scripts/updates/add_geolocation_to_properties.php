<?php
/**
 * Script to add latitude and longitude columns to properties table for map functionality
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to database successfully.\n";

    // Function to execute SQL queries
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "Query executed successfully\n";
                return true;
            } else {
                echo "Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Main function to add latitude and longitude columns
    function addGeolocationColumns($pdo) {
        $success = true;

        // Check if columns already exist
        try {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `properties` LIKE 'latitude'");
            $stmt->execute();
            $result = $stmt->fetchAll();

            if (count($result) == 0) {
                // Add latitude column
                $sql = "ALTER TABLE `properties` ADD COLUMN `latitude` DECIMAL(10,8) NULL COMMENT 'Latitude coordinate for map location'";
                $success = $success && executeQuery($pdo, $sql);
            } else {
                echo "Latitude column already exists\n";
            }

            $stmt = $pdo->prepare("SHOW COLUMNS FROM `properties` LIKE 'longitude'");
            $stmt->execute();
            $result = $stmt->fetchAll();

            if (count($result) == 0) {
                // Add longitude column
                $sql = "ALTER TABLE `properties` ADD COLUMN `longitude` DECIMAL(11,8) NULL COMMENT 'Longitude coordinate for map location'";
                $success = $success && executeQuery($pdo, $sql);
            } else {
                echo "Longitude column already exists\n";
            }

            // Add index for better performance on map queries
            $sql = "CREATE INDEX IF NOT EXISTS `idx_properties_location` ON `properties` (`latitude`, `longitude`)";
            $success = $success && executeQuery($pdo, $sql);

            return $success;
        } catch (Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    echo "Starting database update for geolocation columns...\n";

    if (addGeolocationColumns($pdo)) {
        echo "Geolocation columns added successfully!\n";
    } else {
        echo "Failed to add geolocation columns.\n";
        exit(1);
    }

    echo "Database update completed.\n";

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
