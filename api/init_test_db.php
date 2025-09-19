<?php
/**
 * Initialize Test Database
 * 
 * This script sets up the test database with sample data for API testing.
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'name' => 'aps_dream_home',
    'user' => 'root',
    'pass' => ''
];

// Test user credentials
$testUser = [
    'email' => 'admin@example.com',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'status' => 'active'
];

// [Previous code remains the same until the end of the file...]

function insertSampleData($pdo, $testUser, $sampleProperties, $sampleCustomers, $sampleLeads, $sampleVisits, $sampleNotifications, $sampleTemplates, $sampleAvailability, $sampleReminders) {
    try {
        // Insert sample visit reminders
        foreach ($sampleReminders as $reminder) {
            $stmt = $pdo->prepare("INSERT INTO `visit_reminders` (`visit_id`, `reminder_type`, `status`, `scheduled_at`, `created_at`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $reminder['visit_id'],
                $reminder['reminder_type'],
                $reminder['status'],
                $reminder['scheduled_at'],
                $reminder['created_at']
            ]);
        }
        // Commit transaction
        $pdo->commit();
        echo "✅ Sample data inserted successfully\n";
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        die("❌ Error inserting sample data: " . $e->getMessage() . "\n");
    }
}

// Main execution
try {
    // Create database connection
    $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbConfig['name']}`");
    
    echo "✅ Connected to database successfully\n";
    
    // Create tables
    createTables($pdo);
    
    // Insert sample data
    insertSampleData(
        $pdo, 
        $testUser, 
        $sampleProperties, 
        $sampleCustomers, 
        $sampleLeads, 
        $sampleVisits, 
        $sampleNotifications, 
        $sampleTemplates, 
        $sampleAvailability, 
        $sampleReminders
    );
    
    echo "\n✅ Database initialization completed successfully!\n";
    echo "Test user: admin@example.com / admin123\n";
    
} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
?>