<?php
// Script to create the missing 'site_settings' table based on the existing 'settings' table structure
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();
if (!$db) {
    die("Database connection failed. Please check your config.");
}

// Create the site_settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS site_settings (
    `key` varchar(100) NOT NULL,
    `value` text,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $db->execute($sql);
    echo "site_settings table created successfully or already exists.\n";
    
    // Optionally copy settings from 'settings' table if needed
    $db->execute("INSERT IGNORE INTO site_settings (`key`, `value`) SELECT `key`, `value` FROM settings");
    echo "Copied settings from 'settings' to 'site_settings'.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
