<?php
// Check database structure for states, districts, cities
try {
    $db = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Checking Tables for Location Data ===\n\n";
    
    // Get all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $locationTables = ['states', 'districts', 'cities', 'locations', 'regions', 'zones'];
    $relevantTables = [];
    
    foreach ($tables as $table) {
        $lowerTable = strtolower($table);
        foreach ($locationTables as $loc) {
            if (strpos($lowerTable, $loc) !== false) {
                $relevantTables[] = $table;
                break;
            }
        }
    }
    
    if (empty($relevantTables)) {
        echo "No dedicated state/district/city tables found.\n\n";
    } else {
        echo "Found location tables: " . implode(', ', $relevantTables) . "\n\n";
    }
    
    // Check sites table structure
    echo "=== Sites Table Structure ===\n";
    $siteColumns = $db->query("DESCRIBE sites")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($siteColumns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n=== Sample Sites Data ===\n";
    $sites = $db->query("SELECT id, site_name, state, district, city, location FROM sites LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sites as $site) {
        echo "ID: " . $site['id'] . " | Name: " . $site['site_name'] . " | State: " . ($site['state'] ?? 'NULL') . " | District: " . ($site['district'] ?? 'NULL') . " | City: " . ($site['city'] ?? 'NULL') . " | Location: " . ($site['location'] ?? 'NULL') . "\n";
    }
    
    // Check distinct values
    echo "\n=== Distinct States ===\n";
    $states = $db->query("SELECT DISTINCT state FROM sites WHERE state IS NOT NULL AND state != ''")->fetchAll(PDO::FETCH_COLUMN);
    print_r($states);
    
    echo "\n=== Distinct Districts ===\n";
    $districts = $db->query("SELECT DISTINCT district FROM sites WHERE district IS NOT NULL AND district != ''")->fetchAll(PDO::FETCH_COLUMN);
    print_r($districts);
    
    echo "\n=== Distinct Cities ===\n";
    $cities = $db->query("SELECT DISTINCT city FROM sites WHERE city IS NOT NULL AND city != ''")->fetchAll(PDO::FETCH_COLUMN);
    print_r($cities);
    
    echo "\n=== Distinct Locations ===\n";
    $locations = $db->query("SELECT DISTINCT location FROM sites WHERE location IS NOT NULL AND location != ''")->fetchAll(PDO::FETCH_COLUMN);
    print_r($locations);
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
