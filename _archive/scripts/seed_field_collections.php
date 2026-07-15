<?php
/**
 * Seed field_collections with sample data
 * Run: php scripts/seed_field_collections.php
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    // Check if table exists and has data
    $exists = $pdo->query("SHOW TABLES LIKE 'field_collections'")->fetch();
    if (!$exists) {
        echo "✗ field_collections table does not exist. Run migration first.\n";
        exit(1);
    }
    $count = $pdo->query("SELECT COUNT(*) as c FROM field_collections")->fetch()['c'];
    if ($count > 0) {
        echo "✓ field_collections already has $count rows. Skipping seed.\n";
        exit(0);
    }
    // Grab first associate and agent user IDs
    $associate = $pdo->query("SELECT id FROM users WHERE role='associate' LIMIT 1")->fetch();
    $agent = $pdo->query("SELECT id FROM users WHERE role='agent' LIMIT 1")->fetch();
    $associateId = $associate ? $associate['id'] : 0;
    $agentId = $agent ? $agent['id'] : 0;
    $insert = "INSERT INTO field_collections (user_id, user_role, collection_date, customer_name, customer_phone, amount, payment_mode, status, receipt_generated, receipt_number, remarks) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, 'pending', 0, NULL, ?)";
    $stmt = $pdo->prepare($insert);
    $seeded = 0;
    if ($associateId) {
        $stmt->execute([$associateId, 'associate', 'Ravi Kumar', '9876543210', 25000.00, 'cash', 'Partial payment for plot A-101']);
        $seeded++;
        $stmt->execute([$associateId, 'associate', 'Suman Devi', '9876543211', 50000.00, 'cheque', 'Booking amount cheque']);
        $seeded++;
    }
    if ($agentId) {
        $stmt->execute([$agentId, 'agent', 'Amit Singh', '9876543212', 75000.00, 'cash', 'Site visit collection']);
        $seeded++;
        $stmt->execute([$agentId, 'agent', 'Priya Sharma', '9876543213', 100000.00, 'online', 'Online transfer for plot B-202']);
        $seeded++;
    }
    echo "✓ Seeded $seeded field collection records.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
