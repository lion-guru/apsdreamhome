<?php
/**
 * Seed marketplace lead sources + webhook config (idempotent).
 * Run: php scripts/seed_marketplace_sources.php
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

$sources = [
    ['IndiaMart', 'Leads pushed from IndiaMart seller panel webhook', '#1E88E5', 'fa-industry', 50],
    ['JustDial', 'Leads pushed from JustDial seller panel webhook', '#E53935', 'fa-phone', 51],
    ['TradeIndia', 'Leads pushed from TradeIndia seller panel webhook', '#43A047', 'fa-briefcase', 52],
    ['Meta', 'Leads from Meta (Facebook/Instagram) lead ads', '#8E24AA', 'fa-thumbs-up', 53],
];

$inserted = 0; $skipped = 0;
$check = $pdo->prepare('SELECT id FROM lead_sources WHERE name = ?');
$ins = $pdo->prepare('INSERT INTO lead_sources (name, description, is_active, color, icon, sort_order) VALUES (?,?,?,?,?,?)');
foreach ($sources as [$name, $desc, $color, $icon, $order]) {
    $check->execute([$name]);
    if ($check->fetch()) { echo "SKIP  lead_sources {$name}\n"; $skipped++; continue; }
    $ins->execute([$name, $desc, 1, $color, $icon, $order]);
    echo "OK    lead_sources {$name}\n"; $inserted++;
}

$checkCfg = $pdo->prepare('SELECT id FROM service_configs WHERE service_name = ? AND config_key = ?');
$checkCfg->execute(['marketplace', 'webhook_key']);
if (!$checkCfg->fetch()) {
    $key = bin2hex(random_bytes(16));
    $pdo->prepare('INSERT INTO service_configs (service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())')
        ->execute(['marketplace', 'webhook_key', $key, 'text', 'Shared secret for marketplace lead webhooks (header X-Marketplace-Key)', 1, 'marketplace', 1]);
    echo "OK    service_configs marketplace.webhook_key={$key}\n"; $inserted++;
} else {
    echo "SKIP  service_configs marketplace.webhook_key (already set)\n"; $skipped++;
}

echo "\nDone: {$inserted} inserted, {$skipped} skipped\n";
