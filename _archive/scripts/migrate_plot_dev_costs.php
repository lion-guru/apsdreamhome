<?php
/**
 * Migrate plot_development_costs -> colony_development_costs
 * Run: php scripts/migrate_plot_dev_costs.php
 */
require_once __DIR__ . '/../config/database.php';

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Fetch legacy records
$legacy = $pdo->query("
    SELECT * FROM plot_development_costs
")->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($legacy) . " legacy records to migrate\n";

$typeMap = [
    'land' => 'land_acquisition',
    'development' => 'road',
    'amenities' => 'landscaping',
    'legal' => 'legal',
    'misc' => 'other',
];

$inserted = 0;
foreach ($legacy as $row) {
    $costType = $typeMap[$row['cost_type']] ?? 'other';
    
    // Check if already migrated
    $exists = $pdo->prepare("
        SELECT id FROM colony_development_costs 
        WHERE colony_id = ? AND cost_type = ? AND amount = ? AND invoice_number = ?
    ");
    $exists->execute([$row['colony_id'], $costType, $row['amount'], $row['invoice_number'] ?? '']);
    if ($exists->fetch()) {
        echo "Skipping duplicate: colony_id={$row['colony_id']} type={$costType} amount={$row['amount']}\n";
        continue;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO colony_development_costs 
        (colony_id, cost_type, vendor_name, work_description, invoice_number, invoice_date, 
         amount, gst_amount, tds_section, payment_status, paid_amount, balance_amount, 
         completion_date, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $row['colony_id'],
        $costType,
        $row['vendor_name'] ?? null,
        $row['description'] ?? null,
        $row['invoice_number'] ?? null,
        $row['invoice_date'] ?? null,
        $row['amount'],
        0.00, // gst_amount
        null, // tds_section
        $row['payment_status'] ?? 'unpaid',
        $row['paid_amount'] ?? 0,
        $row['amount'] - ($row['paid_amount'] ?? 0), // balance_amount
        null, // completion_date
        $row['payment_status'] === 'completed' ? 'completed' : 'planned',
        $row['created_at'],
        $row['updated_at']
    ]);
    
    $inserted++;
    echo "Migrated: colony_id={$row['colony_id']} type={$costType} amount={$row['amount']}\n";
}

echo "\nMigration complete. Inserted $inserted new records.\n";