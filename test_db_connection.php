<?php
/**
 * Database Connection Test
 */

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Config.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test database connection
try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'apsdreamhome';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection successful!\n";
    echo "📊 Database: $database\n";
    echo "🌐 Host: $host:$port\n";

    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables found: " . count($tables) . "\n";
    
    // Check key tables
    $key_tables = ['team_members', 'testimonials', 'properties', 'users', 'projects'];
    foreach ($key_tables as $table) {
        if (in_array($table, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "✅ $table: $count records\n";
        } else {
            echo "❌ $table: Table not found\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
