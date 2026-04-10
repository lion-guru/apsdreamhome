<?php

/**
 * Migration: Delete Missing Gallery Image Record
 * 
 * This migration deletes the gallery_images record for the missing image file
 * gallery_1775737368_5845b931.JPG which is causing a 404 error.
 */

// Database configuration
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Deleting missing gallery image record...\n";
    
    // Delete the record with the missing image
    $sql = "DELETE FROM gallery_images WHERE image_path = 'assets/images/gallery/gallery_1775737368_5845b931.JPG'";
    $stmt = $conn->prepare($sql);
    $affected = $stmt->execute();
    
    if ($affected > 0) {
        echo "✓ Deleted $affected record(s) for missing image gallery_1775737368_5845b931.JPG\n";
    } else {
        echo "⚠ No records found with that image path\n";
    }
    
    echo "\n✓ Missing gallery image record deleted successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
