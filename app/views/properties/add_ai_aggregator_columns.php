<?php

/**
 * Migration: Add AI Aggregator & Lead Capture Columns
 * Run this file once to update your properties table.
 */

$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding AI Aggregator columns to properties table...\n";

    $sql = "ALTER TABLE properties
            ADD COLUMN source ENUM('internal', 'ai_fetched', 'user_submitted') DEFAULT 'internal' AFTER status,
            ADD COLUMN original_url VARCHAR(500) NULL AFTER source,
            ADD COLUMN owner_contact VARCHAR(50) NULL AFTER original_url";

    $conn->exec($sql);
    echo "✓ Successfully added 'source', 'original_url', and 'owner_contact' columns!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ Columns already exist!\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
