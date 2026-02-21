<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

define('APP_ROOT', dirname(__DIR__));
$appRoot = APP_ROOT;

// Load .env
if (file_exists($appRoot . '/.env')) {
    $dotenv = Dotenv::createImmutable($appRoot);
    $dotenv->safeLoad();
}

$config = require $appRoot . '/config/database.php';
$dbConfig = $config['database'];

$host = $dbConfig['host'];
$dbname = $dbConfig['database'];
$user = $dbConfig['username'];
$pass = $dbConfig['password'];

echo "Connecting to database '{$dbname}' at '{$host}'...\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "Fixing 'booking_summary' view...\n";

$sql = "
CREATE OR REPLACE VIEW `booking_summary` AS
SELECT 
    b.id AS booking_id,
    b.visit_date AS booking_date,
    b.status AS booking_status,
    b.amount,
    b.booking_type,
    c.id AS customer_id,
    c.name AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    p.id AS property_id,
    p.title AS property_title,
    p.location AS property_location,
    p.city AS property_city,
    p.state AS property_state,
    p.price AS property_price,
    b.created_at
FROM bookings b
LEFT JOIN customers c ON b.customer_id = c.id
LEFT JOIN properties p ON b.property_id = p.id;
";

try {
    $pdo->exec($sql);
    echo "View 'booking_summary' created successfully.\n";
    
    // Verify it works
    $stmt = $pdo->query("SELECT * FROM booking_summary LIMIT 1");
    $stmt->fetch();
    echo "Verification SELECT successful.\n";

} catch (PDOException $e) {
    echo "Error creating view: " . $e->getMessage() . "\n";
    exit(1);
}
