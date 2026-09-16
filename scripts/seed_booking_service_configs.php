<?php
/**
 * Seed service_configs with booking group defaults.
 * Admin-configurable via /admin/service-configs "Booking" tab.
 * Run: php scripts/seed_booking_service_configs.php
 */

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db   = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$rows = [
    [
        'service_name' => 'booking',
        'config_key'   => 'token_amount',
        'config_value' => '51000',
        'config_type'  => 'number',
        'description'  => 'Flat booking token amount (₹). 0 disables flat token, falls back to percentage.',
        'is_secret'    => 0,
        'group_name'   => 'booking',
        'sort_order'   => 1,
    ],
    [
        'service_name' => 'booking',
        'config_key'   => 'token_pct',
        'config_value' => '25',
        'config_type'  => 'number',
        'description'  => 'Token as percentage of deal price (e.g. 25 = 25%). Used when token_amount is 0.',
        'is_secret'    => 0,
        'group_name'   => 'booking',
        'sort_order'   => 2,
    ],
    [
        'service_name' => 'booking',
        'config_key'   => 'agreement_threshold_pct',
        'config_value' => '25',
        'config_type'  => 'number',
        'description'  => 'Paid-share (%) of deal price that triggers agreement/paperwork.',
        'is_secret'    => 0,
        'group_name'   => 'booking',
        'sort_order'   => 3,
    ],
    [
        'service_name' => 'booking',
        'config_key'   => 'token_refundable',
        'config_value' => '0',
        'config_type'  => 'boolean',
        'description'  => 'Whether token is refundable (0 = non-refundable).',
        'is_secret'    => 0,
        'group_name'   => 'booking',
        'sort_order'   => 4,
    ],
    [
        'service_name' => 'booking',
        'config_key'   => 'token_due_days',
        'config_value' => '15',
        'config_type'  => 'number',
        'description'  => 'Days given to pay the token amount after booking.',
        'is_secret'    => 0,
        'group_name'   => 'booking',
        'sort_order'   => 5,
    ],
];

$inserted = 0;
$skipped  = 0;

$stmt = $pdo->prepare("
    INSERT INTO service_configs
        (service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");

foreach ($rows as $row) {
    $check = $pdo->prepare(
        "SELECT id FROM service_configs WHERE service_name = ? AND config_key = ?"
    );
    $check->execute([$row['service_name'], $row['config_key']]);
    if ($check->fetch()) {
        echo "SKIP  {$row['config_key']} (already exists)\n";
        $skipped++;
        continue;
    }

    $stmt->execute([
        $row['service_name'],
        $row['config_key'],
        $row['config_value'],
        $row['config_type'],
        $row['description'],
        $row['is_secret'],
        $row['group_name'],
        $row['sort_order'],
    ]);
    echo "OK    {$row['config_key']} = {$row['config_value']}\n";
    $inserted++;
}

echo "\nDone: {$inserted} inserted, {$skipped} skipped\n";
