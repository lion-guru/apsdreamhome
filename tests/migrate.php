#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Tests\Database\MigrationManager;

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'testuser',
    'password' => 'testpass',
    'database' => 'apsdreamhome_test',
];

// Create database connection
$db = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error . "\n");
}

// Set charset
$db->set_charset('utf8mb4');

// Initialize migration manager
$migrationsPath = __DIR__ . '/Database/Migrations';
$migrationManager = new MigrationManager($db, $migrationsPath);

// Get command line arguments
$command = $argv[1] ?? 'migrate';

// Execute command
switch ($command) {
    case 'migrate':
        echo "Running migrations...\n";
        $migrationManager->migrate();
        break;
        
    case 'rollback':
        echo "Rolling back last batch of migrations...\n";
        $migrationManager->rollback();
        break;
        
    case 'refresh':
        echo "Refreshing database...\n";
        $migrationManager->rollback();
        $migrationManager->migrate();
        break;
        
    case 'reset':
        echo "Resetting database...\n";
        // Drop all tables except migrations
        $result = $db->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $table = $row[0];
            if ($table !== 'migrations') {
                $db->query("DROP TABLE IF EXISTS `$table`");
                echo "Dropped table: $table\n";
            }
        }
        
        // Reset migrations
        $db->query("TRUNCATE TABLE migrations");
        echo "Reset complete.\n";
        break;
        
    default:
        echo "Usage: php migrate.php [command]\n";
        echo "Available commands:\n";
        echo "  migrate    - Run pending migrations\n";
        echo "  rollback   - Rollback the last batch of migrations\n";
        echo "  refresh    - Rollback and re-run all migrations\n";
        echo "  reset      - Drop all tables and reset migrations\n";
        break;
}
