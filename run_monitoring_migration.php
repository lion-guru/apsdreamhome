<?php

/**
 * Run system monitoring tables migration
 */

require_once __DIR__ . '/database/migrations/create_system_monitoring_tables.php';

// Try to load environment
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'apsdreamhome';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $migration = new CreateSystemMonitoringTables($db);
    $migration->up();

    echo "✅ System monitoring tables migration completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
