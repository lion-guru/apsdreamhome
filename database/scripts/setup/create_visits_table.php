<?php
define('APP_ROOT', dirname(__DIR__, 3));

$config = require __DIR__ . '/../../../config/database.php';
$dbConfig = $config['database'];

$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);

    $sql = "CREATE TABLE IF NOT EXISTS visits (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        associate_id BIGINT(20) UNSIGNED NOT NULL,
        customer_id INT NULL,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        location_address VARCHAR(255),
        notes TEXT,
        visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        image_proof VARCHAR(255) NULL,
        FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'visits' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
