<?php
/**
 * Phase 16: Add booking_id column to invoices table
 * Run: php scripts/add_invoice_booking_id.php
 */
$config = require dirname(__DIR__) . '/config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

// Check if column already exists
$check = $pdo->query("SHOW COLUMNS FROM invoices LIKE 'booking_id'")->fetch();
if ($check) {
    echo "✓ Column 'booking_id' already exists in invoices table.\n";
} else {
    $pdo->exec("ALTER TABLE invoices ADD COLUMN `booking_id` INT(11) UNSIGNED NULL AFTER `client_type`");
    echo "✓ Added 'booking_id' column to invoices table.\n";
}

// Check if index already exists
$indexCheck = $pdo->query("SHOW INDEX FROM invoices WHERE Key_name = 'idx_invoices_booking_id'")->fetch();
if ($indexCheck) {
    echo "✓ Index 'idx_invoices_booking_id' already exists.\n";
} else {
    $pdo->exec("CREATE INDEX `idx_invoices_booking_id` ON `invoices` (`booking_id`)");
    echo "✓ Created index 'idx_invoices_booking_id' on invoices(booking_id).\n";
}

echo "\nMigration complete.\n";
