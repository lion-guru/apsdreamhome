<?php
// Phase 2b: for the 11 pairs where the mission-named index is UNIQUE and the twin
// is plain, drop the PLAIN twin instead (keeps constraint, still reclaims space).
// Run: php scripts/migrate_phase2b_dedup_unique_twins.php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// [table, DROP (plain twin), KEEP (unique)]
$pairs = [
    ['ad_placements', 'idx_slot_key', 'slot_key'],
    ['crm_settings', 'idx_setting_key', 'setting_key'],
    ['daily_sales_report', 'idx_dsr_date', 'report_date'],
    ['digilocker_sessions', 'idx_session', 'session_id'],
    ['esign_transactions', 'idx_transaction', 'transaction_id'],
    ['messaging_participants', 'idx_conversation_user', 'uk_conv_user'],
    ['mlm_network_tree', 'idx_associate', 'idx_unique_associate'],
    ['reward_redemptions', 'idx_code', 'redemption_code'],
    ['upi_payments', 'idx_transaction', 'transaction_ref'],
    ['user_sessions', 'idx_us_token', 'session_token'],
    ['whatsapp_templates', 'idx_code', 'template_code'],
];

function indexInfo($pdo, $table, $name) {
    $rows = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $cols = []; $nonUnique = null;
    foreach ($rows as $r) {
        if ($r['Key_name'] === $name) { $cols[(int)$r['Seq_in_index']] = $r['Column_name']; $nonUnique = (int)$r['Non_unique']; }
    }
    if (!$cols) return null;
    ksort($cols);
    return ['cols' => array_values($cols), 'non_unique' => $nonUnique];
}

foreach ($pairs as [$table, $drop, $keep]) {
    $d = indexInfo($pdo, $table, $drop);
    $k = indexInfo($pdo, $table, $keep);
    if ($d === null) { echo "OK $table.$drop already gone\n"; continue; }
    if ($k === null) { echo "SKIP $table.$drop: keeper $keep missing\n"; continue; }
    if ($d['cols'] !== $k['cols']) { echo "SKIP $table.$drop: columns differ\n"; continue; }
    if ($d['non_unique'] === 0) { echo "SKIP $table.$drop: unexpectedly UNIQUE, manual review\n"; continue; }
    $pdo->exec("ALTER TABLE `$table` DROP INDEX `$drop`");
    echo "DROPPED $table.$drop (kept UNIQUE $keep)\n";
}
echo "DONE phase2b\n";
