<?php
/**
 * Add PDF path columns to booking/payment/refund tables.
 *
 * Run: php scripts/add_pdf_columns.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$adds = [
    // plot_bookings: track generated PDFs
    [
        'table' => 'plot_bookings',
        'column' => 'agreement_pdf',
        'ddl' => "ALTER TABLE `plot_bookings` ADD COLUMN `agreement_pdf` VARCHAR(500) DEFAULT NULL AFTER `esign_url`",
    ],
    [
        'table' => 'plot_bookings',
        'column' => 'allotment_pdf',
        'ddl' => "ALTER TABLE `plot_bookings` ADD COLUMN `allotment_pdf` VARCHAR(500) DEFAULT NULL AFTER `agreement_pdf`",
    ],
    // booking_payment_schedules: track demand letter PDFs
    [
        'table' => 'booking_payment_schedules',
        'column' => 'demand_letter_pdf',
        'ddl' => "ALTER TABLE `booking_payment_schedules` ADD COLUMN `demand_letter_pdf` VARCHAR(500) DEFAULT NULL AFTER `accrued_penalty`",
    ],
    // booking_refunds: track refund voucher PDFs
    [
        'table' => 'booking_refunds',
        'column' => 'voucher_pdf',
        'ddl' => "ALTER TABLE `booking_refunds` ADD COLUMN `voucher_pdf` VARCHAR(500) DEFAULT NULL AFTER `refund_mode`",
    ],
];

$added = 0;
$skipped = 0;

foreach ($adds as $item) {
    $table = $item['table'];
    $column = $item['column'];

    // Check if column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    if ($stmt->fetch()) {
        echo "  [SKIP] $table.$column already exists\n";
        $skipped++;
        continue;
    }

    try {
        $pdo->exec($item['ddl']);
        echo "  [OK]   $table.$column added\n";
        $added++;
    } catch (PDOException $e) {
        echo "  [ERR]  $table.$column failed: " . $e->getMessage() . "\n";
    }
}

echo "\nDone: $added added, $skipped skipped\n";
