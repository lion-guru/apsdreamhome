<?php
/**
 * Script to seed the bookings table with sample data
 */

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

// Function to execute SQL queries
function executeQuery($conn, $sql) {
    try {
        if ($conn->query($sql) === TRUE) {
            echo "Query executed successfully\n";
            return true;
        } else {
            echo "Error executing query: " . $conn->error . "\n";
            return false;
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
        return false;
    }
}

// Function to get random user ID
function getRandomUserId($conn) {
    $result = $conn->query("SELECT id FROM users ORDER BY RAND() LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    return 1; // Fallback to admin user
}

// Function to get random associate ID
function getRandomAssociateId($conn) {
    $result = $conn->query("SELECT id FROM associates ORDER BY RAND() LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    return null;
}

// Function to get random plot ID
function getRandomPlotId($conn) {
    $result = $conn->query("SELECT id FROM plots ORDER BY RAND() LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }

    // If no plots exist, create a sample project and plot
    // First, check if projects table exists and has required columns
    $result = $conn->query("SHOW COLUMNS FROM projects");
    $hasCity = false;
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'city') {
                $hasCity = true;
                break;
            }
        }
    }

    $cityField = $hasCity ? "city," : "";
    $cityValue = $hasCity ? "'Sample City'," : "";
    $projectName = "Sample Project " . rand(100, 999);

    // Create project
    if ($hasCity) {
        $conn->query("INSERT INTO projects (name, city, status) VALUES ('$projectName', 'Sample City', 'active')");
    } else {
        $conn->query("INSERT INTO projects (name, status) VALUES ('$projectName', 'active')");
    }

    $projectId = $conn->insert_id;

    // Create plot
    $conn->query("INSERT INTO plots (project_id, plot_no, size_sqft, status) VALUES ($projectId, 'P001', 1200, 'available')");

    return $conn->insert_id;
}

// Main function to seed bookings
function seedBookings($conn, $count = 10) {
    $statuses = ['booked', 'cancelled', 'completed'];
    $success = true;
    
    // Begin transaction
    $conn->begin_transaction();
    
    echo "Starting to seed $count bookings...\n";
    
    try {
        // Clear existing bookings if any
        $conn->query("TRUNCATE TABLE bookings");
        
        // Create sample bookings
        for ($i = 1; $i <= $count; $i++) {
            $plotId = getRandomPlotId($conn);
            $customerId = getRandomUserId($conn);
            $associateId = getRandomAssociateId($conn);
            $status = $statuses[array_rand($statuses)];
            $amount = rand(100000, 5000000); // Random amount between 1L and 50L
            $bookingDate = date('Y-m-d', strtotime("-$i days"));
            
            $sql = "INSERT INTO bookings 
                    (plot_id, customer_id, associate_id, booking_date, status, amount, created_at)
                    VALUES 
                    ($plotId, $customerId, " . ($associateId ?: 'NULL') . ", 
                    '$bookingDate', '$status', $amount, NOW())";
            
            if (!executeQuery($conn, $sql)) {
                throw new Exception("Failed to insert booking #$i");
            }
            
            echo ".";
            if ($i % 10 === 0) echo "\n";
        }
        
        // Commit the transaction
        $conn->commit();
        echo "\nSuccessfully seeded $count bookings!\n";
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "\nError: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
echo "=== Booking Data Seeder ===\n\n";

try {
    // Get database connection
    $conn = getDbConnection();
    
    if ($conn === null) {
        throw new Exception("Failed to connect to the database");
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Check if bookings table exists
    $result = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($result === false || $result->num_rows === 0) {
        throw new Exception("The 'bookings' table does not exist. Please run the database setup first.");
    }
    
    // Seed bookings
    seedBookings($conn, 20);
    
    // Display sample data
    echo "\nSample booking data created. Here are the first 5 records:\n";
    $result = $conn->query("SELECT b.*, u.name as customer_name, p.plot_no 
                           FROM bookings b 
                           LEFT JOIN users u ON b.customer_id = u.id 
                           LEFT JOIN plots p ON b.plot_id = p.id 
                           ORDER BY b.id DESC LIMIT 5");
    
    if ($result && $result->num_rows > 0) {
        echo str_pad("ID", 5) . str_pad("Customer", 20) . str_pad("Plot", 10) . 
             str_pad("Amount", 15) . str_pad("Status", 15) . "Booking Date\n";
        echo str_repeat("-", 75) . "\n";
        
        while ($row = $result->fetch_assoc()) {
            echo str_pad($row['id'], 5) . 
                 str_pad(substr($row['customer_name'] ?? 'N/A', 0, 18), 20) . 
                 str_pad($row['plot_no'] ?? 'N/A', 10) . 
                 str_pad(number_format($row['amount']), 15) . 
                 str_pad($row['status'], 15) . 
                 $row['booking_date'] . "\n";
        }
    }
    
    echo "\nYou can now check the admin dashboard to see the bookings.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn) && $conn->error) {
        echo "Database Error: " . $conn->error . "\n";
    }
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>
