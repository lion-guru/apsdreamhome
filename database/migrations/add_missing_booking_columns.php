<?php

/**
 * Migration: Add Missing Columns to Bookings Table
 * 
 * This migration adds columns that the BookingController expects but don't exist in the bookings table.
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
    
    echo "Adding missing columns to bookings table...\n";
    
    // Add total_amount column (if not exists)
    try {
        $sql = "ALTER TABLE bookings ADD COLUMN total_amount decimal(10,2) DEFAULT 0.00 AFTER amount";
        $conn->exec($sql);
        echo "✓ Added total_amount column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✓ total_amount column already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Add payment_status column (if not exists)
    try {
        $sql = "ALTER TABLE bookings ADD COLUMN payment_status enum('pending','partial','paid','overdue') DEFAULT 'pending' AFTER status";
        $conn->exec($sql);
        echo "✓ Added payment_status column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✓ payment_status column already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Add booking_date column (if not exists) - this is an alias for visit_date
    try {
        $sql = "ALTER TABLE bookings ADD COLUMN booking_date date DEFAULT NULL AFTER visit_date";
        $conn->exec($sql);
        echo "✓ Added booking_date column\n";
        
        // Copy visit_date to booking_date for existing records
        $conn->exec("UPDATE bookings SET booking_date = visit_date WHERE booking_date IS NULL");
        echo "✓ Copied visit_date to booking_date for existing records\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✓ booking_date column already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Add notes column (if not exists)
    try {
        $sql = "ALTER TABLE bookings ADD COLUMN notes text DEFAULT NULL AFTER special_requirements";
        $conn->exec($sql);
        echo "✓ Added notes column\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✓ notes column already exists\n";
        } else {
            throw $e;
        }
    }
    
    echo "\n✓ All missing columns added successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
