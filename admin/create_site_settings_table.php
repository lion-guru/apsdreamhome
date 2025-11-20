<?php
// Script to create the missing 'site_settings' table based on the existing 'settings' table structure
require_once __DIR__ . '/../includes/config.php';

global $con;
$conn = $con;
if (!$conn) {
    die("Database connection failed. Please check your config.");
}

// Create the site_settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS site_settings (
    `key` varchar(100) NOT NULL,
    `value` text,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "site_settings table created successfully or already exists.\n";
    // Optionally copy settings from 'settings' table if needed
    $copy = $conn->query("INSERT IGNORE INTO site_settings (`key`, `value`) SELECT `key`, `value` FROM settings");
    if ($copy) {
        echo "Copied settings from 'settings' to 'site_settings'.\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
$conn->close();
