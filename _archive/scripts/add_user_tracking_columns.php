<?php
/**
 * Migration: Add user tracking columns to user_properties table
 * Tracks WHO posted the property (associate/customer/agent)
 */

$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL\n\n";
    
    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM user_properties LIKE 'posted_by'");
    $postedByExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM user_properties LIKE 'posted_by_type'");
    $postedByTypeExists = $stmt->fetch();
    
    // Add posted_by column if not exists
    if (!$postedByExists) {
        $pdo->exec("ALTER TABLE user_properties ADD COLUMN posted_by INT DEFAULT NULL AFTER user_id");
        echo "✅ Added posted_by column\n";
    } else {
        echo "✓ posted_by column already exists\n";
    }
    
    // Add posted_by_type column if not exists
    if (!$postedByTypeExists) {
        $pdo->exec("ALTER TABLE user_properties ADD COLUMN posted_by_type VARCHAR(20) DEFAULT NULL AFTER posted_by");
        echo "✅ Added posted_by_type column\n";
    } else {
        echo "✓ posted_by_type column already exists\n";
    }
    
    // Add index for faster queries
    try {
        $pdo->exec("CREATE INDEX idx_posted_by ON user_properties(posted_by)");
        echo "✅ Added index on posted_by\n";
    } catch (PDOException $e) {
        echo "✓ Index on posted_by already exists\n";
    }
    
    try {
        $pdo->exec("CREATE INDEX idx_posted_by_type ON user_properties(posted_by_type)");
        echo "✅ Added index on posted_by_type\n";
    } catch (PDOException $e) {
        echo "✓ Index on posted_by_type already exists\n";
    }
    
    echo "\n🎉 User tracking columns added successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
