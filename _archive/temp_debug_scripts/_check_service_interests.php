<?php
$c = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$r = $pdo->query("SHOW TABLES LIKE 'service_interests'");
$exists = $r->fetch();
echo "service_interests table exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;

if ($exists) {
    $r2 = $pdo->query("SHOW CREATE TABLE service_interests");
    $row = $r2->fetch(PDO::FETCH_ASSOC);
    echo "Schema:\n" . $row['Create Table'] . PHP_EOL;
    $count = $pdo->query("SELECT COUNT(*) FROM service_interests")->fetchColumn();
    echo "Rows: $count\n";
} else {
    // Check if similar table exists
    $r3 = $pdo->query("SHOW TABLES LIKE '%service%'");
    $similar = $r3->fetchAll(PDO::FETCH_COLUMN);
    echo "Similar tables: " . implode(', ', $similar) . PHP_EOL;
    
    // Check leads table columns
    echo "\nCreating service_interests table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_interests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id BIGINT UNSIGNED NULL,
        service_type VARCHAR(100) NOT NULL,
        property_id BIGINT UNSIGNED NULL,
        status VARCHAR(30) DEFAULT 'new',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_service_type (service_type),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "service_interests table CREATED successfully\n";
}
