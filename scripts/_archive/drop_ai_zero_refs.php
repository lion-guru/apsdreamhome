<?php
/**
 * Drop AI feature-scaffolding tables - CONSERVATIVE PASS
 * Only drop tables with 0 code references AND 0 incoming FKs
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

// CONSERVATIVE: only drop tables with EXACTLY 0 code references
// This avoids breaking any feature that might use them
$zeroCodeRefDrops = [
    'ai_interactions',          // 0 refs, 1 row
    'ai_lead_agent_jobs',       // 0 refs, 1 row
    'ai_learning_data',         // 0 refs, 1 row
    'ai_logs',                  // 0 refs, 1 row
    'ai_user_learning_progress',// 0 refs, 1 row
    'ai_user_profiles',         // 0 refs, 1 row
    'ai_workflow_patterns',     // 0 refs, 1 row
    'voice_assistant_config',   // 0 refs, 1 row
];

$dropped = 0;
$skipped = 0;
foreach ($zeroCodeRefDrops as $name) {
    // Safety: check 0 FKs to it
    $fkCount = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$name' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();

    if ($fkCount > 0) {
        echo "SKIP $name -- has $fkCount incoming FKs\n";
        $skipped++;
        continue;
    }

    try {
        $pdo->exec("DROP TABLE IF EXISTS `$name`");
        echo "✓ DROPPED $name (0 code refs, 0 FKs in)\n";
        $dropped++;
    } catch (Exception $e) {
        echo "✗ FAILED $name: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== CONSERVATIVE SUMMARY ===\n";
echo "Dropped: $dropped (tables with ZERO code references)\n";
echo "Skipped: $skipped (had incoming FKs)\n";
echo "Tables: $before → $after\n";
echo "\nNote: 23 more tables with 1-2 code refs are NOT dropped (safer to keep).\n";
echo "Run 'php scripts/ai_schema_audit.php' to see full list.\n";
