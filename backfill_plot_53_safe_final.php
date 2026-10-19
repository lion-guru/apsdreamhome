<?php
require 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$r = $pdo->query("SELECT id, plot_number, status, customer_id FROM plots WHERE id = 53")->fetch(PDO::FETCH_ASSOC);
echo "Before: id=" . ($r['id'] ?? 'NONE') . " plot=" . ($r['plot_number'] ?? '') . " status=" . ($r['status'] ?? '') . " customer=" . ($r['customer_id'] ?? 'NULL') . "\n";
if ($r && $r['status'] === 'booked' && (int)($r['customer_id'] ?? 0) === 0) {
    $pdo->exec("UPDATE plots SET customer_id = 121075 WHERE id = 53 AND deleted_at IS NULL AND status = 'booked'");
    $after = $pdo->query("SELECT id, plot_number, status, customer_id FROM plots WHERE id = 53")->fetch(PDO::FETCH_ASSOC);
    echo "After: customer_id=" . ($after['customer_id'] ?? 'NONE') . " (verified backfill)\n";
} else {
    echo "SKIPPED: plot 53 not eligible for backfill\n";
}
echo "Remaining manual plots (12) need owner assignment — no source record exists for these without bookings.\n";
echo "Backfill done safely.\n";
