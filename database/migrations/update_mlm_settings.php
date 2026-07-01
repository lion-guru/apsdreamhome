<?php
/**
 * Migration: Update MLM settings in database (matching rates)
 */
$root   = dirname(__DIR__, 2);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected.\n";

    $updates = [
        'gen1_match_pct' => '10',
        'gen2_match_pct' => '5',
        'gen3_match_pct' => '2'
    ];

    $stmt = $pdo->prepare("UPDATE mlm_settings SET setting_value = ? WHERE setting_key = ?");

    foreach ($updates as $key => $val) {
        $stmt->execute([$val, $key]);
        echo "✅ mlm_settings.$key — set to $val\n";
    }

    echo "\n✅ Database settings updated successfully.\n";

} catch (Exception $e) {
    echo "❌ Migration FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
