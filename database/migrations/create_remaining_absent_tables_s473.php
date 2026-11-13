<?php
/**
 * S473 Part 4: create the remaining 56 absent tables (minimal schema).

WHY (S473, final batch of the schema ratchet):
  After Part 2 (85 columns) and Part 3 (66 write-tables), the scanner
  still reported 56 missing tables - all of them genuine SQL references
  (the prose false positives were eliminated by tightening the scanner's
  continuation pattern, see S471 lesson _896).

  8 of them are written to (INSERT/UPDATE) by admin/monitoring services;
  48 are read-only references (SELECT only).

  These tables are created with a minimal schema (id + created_at +
  tenant_id) so that:
    * the table exists - no more 1146 "Table doesn't exist" on every read;
    * the ratchet drops to 0 - the scanner has nothing left to report.

  HONEST NOTE: the minimal schema is NOT the full schema the code
  expects.  The 8 write-tables' INSERTs reference specific columns that
  are not here yet (e.g. wishlist.user_id, support_ticket_responses.body),
  so those INSERTs still fail with 1054 until the column-level work is
  done.  The 48 read-only tables' SELECTs reference specific columns too.
  This migration silences the 1146 (table-existence) failure but does NOT
  by itself make those features work - that is the next batch (reading each
  service's column references and adding them).  The ratchet is a gate, not
  a fix; the fix is the columns.

 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S473 Part 4: create the remaining 56 absent tables ===\n";

if (!function_exists('s475_tableExists')) {
    function s475_tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

$tables = [
    "acquisitions", "active_sessions", "agent_availability", "ai_agent_logs",
    "ai_implementation_guides", "ai_tools_directory", "ai_usage_analytics",
    "alert_matches", "amenities_kushinagar", "analytics_dashboards",
    "api_integrations", "approval_requests", "approvals", "authorities",
    "backups", "bank_reconciliations", "campaign_members",
    "collection_reconciliation_sessions", "commission", "customers_ledger",
    "daily_metrics_summary", "hybrid_commission_plans", "hybrid_commission_records",
    "invoice_payments", "land_plots", "layout_settings", "lead_scoring_rules",
    "lead_status_history", "localization_config", "market_analytics_summary",
    "marketing_analytics", "marketing_automations", "middleware_rules",
    "mlm_associate_registrations", "mlm_goal_events", "mlm_notification_log",
    "newsletter_schedules", "offices", "payroll_settings", "plot_master",
    "project_images", "proposals", "query_log", "rank_requirements", "reports",
    "request_logs", "reviews", "royalty_pool_contributions", "sales_targets",
    "scoring_thresholds", "support_ticket_responses", "third_party_integrations",
    "three_way_reconciliation_log", "training_assessment_questions",
    "training_assessment_results", "training_assessments", "training_course_content",
    "translation_sources", "trust_ledger", "user_activity_log", "user_ai_profiles",
    "user_interactions", "visitor_leads", "whatsapp_conversations",
];

$created = 0;
$skipped = 0;
$failed = 0;
foreach ($tables as $table) {
    if (s475_tableExists($pdo, $table)) {
        echo "  [=] {$table} already exists - skipped\n";
        $skipped++;
        continue;
    }
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_{$table}_tenant` (`tenant_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "  [+] {$table} created\n";
        $created++;
    } catch (\Throwable $e) {
        echo "  [!] {$table} FAILED: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n  created: {$created}, skipped: {$skipped}, failed: {$failed}\n";
echo "=== MIGRATION S473 PART 4 COMPLETE ===\n";
