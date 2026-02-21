<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/bootstrap.php';

    $app = \App\Core\App::getInstance();
    $db = $app->db();

    if (!$db) {
        echo "DB connection failed (null)\n";
        exit(1);
    }

    $stmt = $db->query("SHOW COLUMNS FROM bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns for 'bookings':\n";
    foreach ($columns as $col) {
        echo "{$col['Field']} ({$col['Type']})\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
