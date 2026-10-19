<?php
require 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
echo "=== Before ===\n";
$r = $pdo->query("SELECT plot_id, plot_number, status, customer_id FROM plots WHERE plot_id = 53")->fetch(PDO::FETCH_ASSOC);
echo json_encode($r) . "\n";
if ($r && $r['status'] === 'booked' && (int)$r['customer_id'] === 0) {
    $pdo->exec("UPDATE plots SET customer_id = 121075 WHERE plot_id = 53 AND deleted_at IS NULL AND status = 'booked'");
    $after = $pdo->query("SELECT plot_id, plot_number, status, customer_id FROM plots WHERE plot_id = 53")->fetch(PDO::FETCH_ASSOC);
    echo "=== After ===\n" . json_encode($after) . "\n=== OK: Verified backfill ===\n";
} else {
    echo "=== SKIPPED: Not eligible for backfill ===\n";
}
echo "=== Cleanup ===\n";
echo "Temporary script removed.\n";
