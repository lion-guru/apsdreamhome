<?php
/**
 * Fix mlm_settings: gen1_match_pct, gen2_match_pct, gen3_match_pct
 * AGENTS.md spec: Gen1=100%, Gen2=50%, Gen3=25%
 */
require_once dirname(__DIR__) . '/app/Core/ConfigService.php';
require_once dirname(__DIR__) . '/app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

$updates = [
    'gen1_match_pct' => '100',
    'gen2_match_pct' => '50',
    'gen3_match_pct' => '25',
];

foreach ($updates as $key => $value) {
    $stmt = $db->prepare("
        INSERT INTO mlm_settings (setting_key, setting_value, description)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$key, $value, "Matching bonus percentage for generation " . substr($key, 3, 1)]);
    echo "SET: {$key} = {$value}\n";
}

echo "\n--- Verification ---\n";
$stmt = $db->query("SELECT setting_key, setting_value FROM mlm_settings WHERE setting_key IN ('gen1_match_pct','gen2_match_pct','gen3_match_pct')");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['setting_key']} = {$row['setting_value']}\n";
}
echo "\nDone.\n";
