<?php

/**
 * Migration: Create Missing Booking Tables
 * 
 * This migration creates the missing booking_logs and payment_receipts tables
 * that are referenced by the BookingController but don't exist in the database.
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
    
    echo "Creating missing booking tables...\n";
    
    // Create booking_logs table
    $sql1 = "CREATE TABLE IF NOT EXISTS booking_logs (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        booking_id bigint(20) unsigned NOT NULL,
        action varchar(50) NOT NULL COMMENT 'updated, deleted, created, etc.',
        user_id bigint(20) unsigned NOT NULL,
        changes text COMMENT 'JSON encoded changes',
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_booking_id (booking_id),
        KEY idx_user_id (user_id),
        KEY idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql1);
    echo "✓ Created booking_logs table\n";
    
    // Create payment_receipts table
    $sql2 = "CREATE TABLE IF NOT EXISTS payment_receipts (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        payment_id bigint(20) unsigned NOT NULL,
        receipt_number varchar(50) NOT NULL,
        receipt_url varchar(255) DEFAULT NULL,
        generated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY idx_receipt_number (receipt_number),
        KEY idx_payment_id (payment_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql2);
    echo "✓ Created payment_receipts table\n";
    
    echo "\n✓ All missing booking tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
