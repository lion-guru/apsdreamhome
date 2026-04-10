<?php

/**
 * Migration: Fix Bookings Customer ID Type
 * 
 * This migration changes bookings.customer_id from varchar to bigint to match users.id type
 * for proper JOIN operations.
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

    echo "Fixing bookings.customer_id type...\n";

    // First, check if there are any non-numeric values in customer_id
    $stmt = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE customer_id REGEXP '[^0-9]'");
    $nonNumeric = $stmt->fetch()['count'];

    if ($nonNumeric > 0) {
        echo "⚠ Found $nonNumeric non-numeric customer_id values. These will be set to NULL.\n";
        $conn->exec("UPDATE bookings SET customer_id = NULL WHERE customer_id REGEXP '[^0-9]'");
        echo "✓ Set non-numeric customer_id values to NULL\n";
    }

    // Drop foreign key constraint if exists
    try {
        $conn->exec("ALTER TABLE bookings DROP FOREIGN KEY fk_bookings_customer_id");
        echo "✓ Dropped foreign key constraint fk_bookings_customer_id\n";
    } catch (PDOException $e) {
        // Foreign key might not exist, continue
        echo "⚠ Foreign key constraint not found or already dropped\n";
    }

    // Change customer_id from varchar(20) to bigint(20) unsigned
    try {
        $sql = "ALTER TABLE bookings MODIFY COLUMN customer_id bigint(20) unsigned DEFAULT NULL AFTER project_id";
        $conn->exec($sql);
        echo "✓ Changed bookings.customer_id to bigint(20) unsigned\n";
    } catch (PDOException $e) {
        echo "✗ Error changing customer_id type: " . $e->getMessage() . "\n";
        exit(1);
    }

    // Recreate foreign key constraint
    try {
        $sql = "ALTER TABLE bookings ADD CONSTRAINT fk_bookings_customer_id FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE";
        $conn->exec($sql);
        echo "✓ Recreated foreign key constraint fk_bookings_customer_id\n";
    } catch (PDOException $e) {
        echo "⚠ Could not recreate foreign key constraint: " . $e->getMessage() . "\n";
    }

    // Also fix associate_id to match users.id type
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE associate_id REGEXP '[^0-9]' AND associate_id != 0");
        $nonNumeric = $stmt->fetch()['count'];

        if ($nonNumeric > 0) {
            echo "⚠ Found $nonNumeric non-numeric associate_id values. These will be set to 0.\n";
            $conn->exec("UPDATE bookings SET associate_id = 0 WHERE associate_id REGEXP '[^0-9]' AND associate_id != 0");
            echo "✓ Set non-numeric associate_id values to 0\n";
        }

        $sql = "ALTER TABLE bookings MODIFY COLUMN associate_id bigint(20) unsigned DEFAULT 0 AFTER customer_id";
        $conn->exec($sql);
        echo "✓ Changed bookings.associate_id to bigint(20) unsigned\n";
    } catch (PDOException $e) {
        echo "✗ Error changing associate_id type: " . $e->getMessage() . "\n";
        exit(1);
    }

    echo "\n✓ Bookings table customer_id and associate_id types fixed successfully!\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
