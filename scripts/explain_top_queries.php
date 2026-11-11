<?php
/**
 * Slow query EXPLAIN check (Phase 1 verification)
 * Usage: php scripts/explain_top_queries.php
 * Output: logs/testing/explain_YYYY-MM-DD_His.txt + stdout
 * Checks index usage on 5 heavy queries (leads, plots, bookings, notifications, colonies)
 */
require 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

$queries = [
  'leads_status_created' => "SELECT * FROM leads WHERE status = 'new' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 20",
  'leads_assigned' => "SELECT * FROM leads WHERE assigned_to = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 20",
  'plots_by_colony' => "SELECT * FROM plots WHERE colony_id = 1 AND status = 'available' ORDER BY plot_number",
  'notifications_unread' => "SELECT * FROM notifications WHERE user_id = 1 AND is_read = 0 ORDER BY created_at DESC",
  'colonies_active' => "SELECT * FROM colonies WHERE is_active = 1 ORDER BY name",
  'bookings_customer' => "SELECT * FROM plot_bookings WHERE customer_id = 1 AND status NOT IN ('cancelled') ORDER BY created_at DESC LIMIT 20",
];

$logsDir = 'C:/xampp/htdocs/apsdreamhome/logs/testing';
if (!is_dir($logsDir)) mkdir($logsDir, 0755, true);
$logFile = $logsDir . '/explain_' . date('Y-m-d_His') . '.txt';

function w($msg) { global $logFile; echo $msg . "\n"; file_put_contents($logFile, $msg . "\n", FILE_APPEND); }

w("=== EXPLAIN top queries " . date('c') . " ===");
foreach ($queries as $name => $sql) {
  w("\n-- $name --");
  w($sql);
  try {
    $rows = $db->query("EXPLAIN " . $sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
      w(sprintf("  type=%s key=%s rows=%s Extra=%s", $r['type'] ?? '', $r['key'] ?? '(NULL)', $r['rows'] ?? '', $r['Extra'] ?? ''));
      if (empty($r['key'])) w("  *** WARNING: full scan (no index) on $name ***");
    }
  } catch (\Throwable $e) {
    w("  EXPLAIN failed: " . $e->getMessage());
  }
}
w("\nLog saved: $logFile");
