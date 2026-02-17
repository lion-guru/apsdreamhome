<?php
require_once __DIR__ . '/app/core/App.php';
require_once __DIR__ . '/app/Helpers/env.php';

try {
    $app = new \App\Core\App(__DIR__);
    $db = $app->database();
    $pdo = $db->getConnection();
    echo "Database connection successful!\n";
    echo "Database name: " . $pdo->query('select database()')->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
