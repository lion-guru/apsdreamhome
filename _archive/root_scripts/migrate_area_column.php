<?php
/**
 * Add area_sqft column to properties table
 * Copies data from 'area' column
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Starting migration...\n";

// Check if column exists
$stmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='properties' AND COLUMN_NAME='area_sqft'");
if ($stmt->fetch()) {
    echo "area_sqft column already exists!\n";
} else {
    // Add column
    $pdo->exec("ALTER TABLE properties ADD COLUMN area_sqft DECIMAL(10,2) DEFAULT NULL AFTER area");
    echo "Added area_sqft column to properties table\n";
}

// Copy data from area to area_sqft
$affected = $pdo->exec("UPDATE properties SET area_sqft = area WHERE area_sqft IS NULL AND area IS NOT NULL");
echo "Copied $affected rows from 'area' to 'area_sqft'\n";

// Verify
$stmt = $pdo->query("SELECT COUNT(*) as total, 
                     SUM(CASE WHEN area_sqft IS NOT NULL THEN 1 ELSE 0 END) as with_sqft,
                     SUM(CASE WHEN area IS NOT NULL THEN 1 ELSE 0 END) as with_area
                     FROM properties");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nVerification:\n";
echo "Total rows: " . $result['total'] . "\n";
echo "With area_sqft: " . $result['with_sqft'] . "\n";
echo "With area: " . $result['with_area'] . "\n";

echo "\nMigration complete!\n";
