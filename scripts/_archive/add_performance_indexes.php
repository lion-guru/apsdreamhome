<?php
/**
 * Performance Indexes - Add composite indexes on hot tables
 * Idempotent: checks if index exists before adding
 */

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$added = 0;
$skipped = 0;
$errors = 0;

// Helper: check if index exists
function indexExists(PDO $pdo, string $table, string $indexName): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?");
    $stmt->execute(['apsdreamhome', $table, $indexName]);
    return (int)$stmt->fetchColumn() > 0;
}

// Helper: add index safely
function addIndex(PDO $pdo, string $table, string $indexName, string $columns): void {
    global $added, $skipped, $errors;
    if (indexExists($pdo, $table, $indexName)) {
        echo "  SKIP: $table.$indexName (already exists)" . PHP_EOL;
        $skipped++;
        return;
    }
    try {
        $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` ($columns)";
        $pdo->exec($sql);
        echo "  ADDED: $table.$indexName ($columns)" . PHP_EOL;
        $added++;
    } catch (PDOException $e) {
        echo "  ERROR: $table.$indexName - " . $e->getMessage() . PHP_EOL;
        $errors++;
    }
}

echo "=== Performance Index Migration ===" . PHP_EOL . PHP_EOL;

// --- 1. leads (843 rows, hot table) ---
echo "[1] leads" . PHP_EOL;
// Common: WHERE source = ? AND status = ?
addIndex($pdo, 'leads', 'idx_leads_source_status', 'source, status');
// Common: WHERE assigned_to = ? ORDER BY created_at DESC
addIndex($pdo, 'leads', 'idx_leads_assigned_created', 'assigned_to, created_at');
// Common: WHERE lead_score > ? ORDER BY lead_score DESC
addIndex($pdo, 'leads', 'idx_leads_score_status', 'lead_score, status');
// Common: WHERE campaign_id = ? AND status = ?
addIndex($pdo, 'leads', 'idx_leads_campaign_status', 'campaign_id, status');

// --- 2. ai_lead_scores (238 rows) ---
echo PHP_EOL . "[2] ai_lead_scores" . PHP_EOL;
// Common: WHERE lead_id = ? ORDER BY score DESC (best score per lead)
addIndex($pdo, 'ai_lead_scores', 'idx_ai_scores_lead_score', 'lead_id, score');
// Common: WHERE grade = ? AND score > ?
addIndex($pdo, 'ai_lead_scores', 'idx_ai_scores_grade_score', 'grade, score');

// --- 3. audit_log (362 rows) ---
echo PHP_EOL . "[3] audit_log" . PHP_EOL;
// Common: WHERE entity_type = ? AND entity_id = ? (entity audit trail)
addIndex($pdo, 'audit_log', 'idx_audit_entity', 'entity_type, entity_id');
// Common: WHERE action = ? ORDER BY created_at DESC
addIndex($pdo, 'audit_log', 'idx_audit_action_date', 'action, created_at');

// --- 4. admin_menu_items (265 rows) ---
echo PHP_EOL . "[4] admin_menu_items" . PHP_EOL;
// Common: WHERE section = ? AND parent_id = ? AND is_active = ?
addIndex($pdo, 'admin_menu_items', 'idx_menu_section_parent', 'section, parent_id, is_active');

// --- 5. inventory_plots (311 rows) ---
echo PHP_EOL . "[5] inventory_plots" . PHP_EOL;
// Common: WHERE block_name = ? AND status = ? (block availability)
addIndex($pdo, 'inventory_plots', 'idx_inv_plots_block_status', 'block_name, status');

// --- 6. activities (265 rows) ---
echo PHP_EOL . "[6] activities" . PHP_EOL;
// Common: WHERE lead_id = ? AND type = ? (activity by type per lead)
addIndex($pdo, 'activities', 'idx_activities_lead_type', 'lead_id, type');
// Common: WHERE assigned_to = ? AND completed = ? (my open tasks)
addIndex($pdo, 'activities', 'idx_activities_assigned_completed', 'assigned_to, completed');

// --- 7. workflow_steps (9028 rows - largest!) ---
echo PHP_EOL . "[7] workflow_steps" . PHP_EOL;
// Common: WHERE workflow_id = ? AND status = ?
addIndex($pdo, 'workflow_steps', 'idx_wf_steps_workflow_status', 'workflow_id, status');

// --- 8. rewards_catalog (6372 rows) ---
echo PHP_EOL . "[8] rewards_catalog" . PHP_EOL;
// Common: WHERE is_active = ? AND reward_type = ? AND points_cost <= ?
addIndex($pdo, 'rewards_catalog', 'idx_rewards_type_cost_active', 'reward_type, points_cost, is_active');

// --- 9. scheduled_tasks (4883 rows) ---
echo PHP_EOL . "[9] scheduled_tasks" . PHP_EOL;
// Common: WHERE task_type = ? AND status = ? AND next_run <= ? (cron pickup)
addIndex($pdo, 'scheduled_tasks', 'idx_sched_task_type_status_run', 'task_type, status, next_run');

// --- 10. ab_events (1104 rows) ---
echo PHP_EOL . "[10] ab_events" . PHP_EOL;
// Common: WHERE experiment_id = ? AND event_type = ? (conversion count)
addIndex($pdo, 'ab_events', 'idx_ab_exp_event_type', 'experiment_id, event_type');

// --- 11. points_rules (901 rows) ---
echo PHP_EOL . "[11] points_rules" . PHP_EOL;
// Common: WHERE is_active = 1 AND action_type = ? AND start_date <= NOW() AND end_date >= NOW()
addIndex($pdo, 'points_rules', 'idx_points_active_action_dates', 'is_active, action_type, start_date, end_date');

// --- 12. cities (833 rows) ---
echo PHP_EOL . "[12] cities" . PHP_EOL;
// Common: WHERE state_id = ? ORDER BY name (state dropdown)
addIndex($pdo, 'cities', 'idx_cities_state_name', 'state_id, name');

// --- 13. properties (129 rows) ---
echo PHP_EOL . "[13] properties" . PHP_EOL;
// Common: WHERE status = ? AND type = ? AND price BETWEEN ? AND ? (search)
addIndex($pdo, 'properties', 'idx_prop_status_type_price', 'status, type, price');
// Common: WHERE city = ? AND status = ? ORDER BY created_at DESC
addIndex($pdo, 'properties', 'idx_prop_city_status_date', 'city, status, created_at');

// --- 14. plots (187 rows) ---
echo PHP_EOL . "[14] plots" . PHP_EOL;
// Common: WHERE colony_id = ? AND status = ? AND area_sqft BETWEEN ? AND ?
addIndex($pdo, 'plots', 'idx_plots_colony_status_area', 'colony_id, status, area_sqft');
// Common: WHERE status = ? AND total_price BETWEEN ? AND ? (price search)
addIndex($pdo, 'plots', 'idx_plots_status_price', 'status, total_price');

// --- 15. chat_messages (116 rows) ---
echo PHP_EOL . "[15] chat_messages" . PHP_EOL;
// Common: WHERE session_id = ? ORDER BY created_at
addIndex($pdo, 'chat_messages', 'idx_chat_session_date', 'session_id, created_at');

// --- Summary ---
echo PHP_EOL . str_repeat('=', 50) . PHP_EOL;
echo "SUMMARY:" . PHP_EOL;
echo "  Added:   $added indexes" . PHP_EOL;
echo "  Skipped: $skipped (already existed)" . PHP_EOL;
echo "  Errors:  $errors" . PHP_EOL;
echo "  Total:   " . ($added + $skipped + $errors) . PHP_EOL;
echo str_repeat('=', 50) . PHP_EOL;
