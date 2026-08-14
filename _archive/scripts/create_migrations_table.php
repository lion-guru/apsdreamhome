<?php
/**
 * Create _migrations tracking table
 * Tracks which scripts/operations have been applied to the database
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
echo "Connected. Creating _migrations table...\n";

$pdo->exec("
    CREATE TABLE IF NOT EXISTS _migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        script_name VARCHAR(255) NOT NULL UNIQUE,
        category VARCHAR(50) NOT NULL DEFAULT 'misc',
        description TEXT,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        execution_time_ms INT UNSIGNED,
        rows_affected INT UNSIGNED,
        status ENUM('success','failed','rolled_back') DEFAULT 'success',
        notes TEXT,
        INDEX idx_category (category),
        INDEX idx_applied_at (applied_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "âœ“ Created _migrations table\n";

// Backfill: record all past cleanup phases
$phases = [
    ['phase1_dead_tables', 'cleanup', 'Drop 4 dead tables (customers, admin_users, associates, employees) + 2 broken views'],
    ['phase2_mlm_consolidation', 'cleanup', 'Drop 31 MLM duplicates, restore 4 over-dropped tables'],
    ['phase2_user_unification', 'cleanup', 'Drop user extension tables with 0 refs'],
    ['phase3_ai_cleanup', 'cleanup', 'Drop 23 AI/voice/chat tables (3-pass safety)'],
    ['phase3_bulk_cleanup', 'cleanup', 'Drop 178 tables with 0 code refs, 0 FKs, 0 views'],
    ['phase4_drop_1ref_trycatch', 'cleanup', 'Drop 15 1-ref tables all in try/catch'],
    ['phase5_drop_2ref_trycatch', 'cleanup', 'Drop 4 2-ref tables all in try/catch'],
    ['phase7_drop_1ref_method_trycatch', 'cleanup', 'Drop 2 tables in try/catch method'],
    ['phase8_drop_fake_data', 'cleanup', 'Drop 5 large fake seed data tables (ai_tools_directory 1000 rows, points_rules 6030)'],
    ['phase9_drop_more_5ref', 'cleanup', 'Drop 26 more with <=5 refs + 0 FKs + try/catch'],
    ['phase10_drop_empty_low_ref', 'cleanup', 'Drop 2 empty low-ref tables'],
    ['drop_broken_views', 'cleanup', 'Drop 3 broken views (sync_queue_summary, booking_summary, employee_performance)'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO _migrations (script_name, category, description, applied_at) VALUES (?, ?, ?, NOW())");
$count = 0;
foreach ($phases as [$name, $cat, $desc]) {
    $stmt->execute([$name, $cat, $desc]);
    $count++;
}
echo "âœ“ Backfilled $count phase records\n";

$total = $pdo->query("SELECT COUNT(*) FROM _migrations")->fetchColumn();
echo "\nFinal: $total migrations tracked\n";
echo "\nUsage:\n";
echo "  - Run: scripts/track_migration.php <name> <category> [description]\n";
echo "  - View: SELECT * FROM _migrations ORDER BY applied_at DESC;\n";?>