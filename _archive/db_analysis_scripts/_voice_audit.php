<?php
/**
 * Analyze Voice AI tables for consolidation
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    'ai_calling_agents',
    'ai_call_scripts',
    'ai_call_sessions',
    'ai_calling_schedule',
    'ai_call_extracted_leads',
    'ai_call_logs',
    'voice_assistant_config',
    'voice_call_logs',
    'voice_sessions',
];

echo "=== VOICE/AI CALLING TABLES ===\n\n";
foreach ($tables as $t) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t'")->fetchColumn();
    if (!$exists) {
        echo "  [MISSING] $t\n";
        continue;
    }
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    echo "  $t: $rows rows, " . count($cols) . " cols\n";
    foreach ($cols as $c) echo "      - " . $c['Field'] . " (" . $c['Type'] . ")\n";
    echo "\n";
}?>