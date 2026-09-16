<?php
// Phase 5: leads with assigned_to pointing to non-existent users.
// Safety: archives (id, assigned_to) pairs to database/_archive_lead_assignments_20260916.sql
// BEFORE nulling, so the operation is reversible. Idempotent.
// NOTE: booking_payment_schedules "ghosts" are INTENTIONALLY untouched:
//  - 16 rows are status='paid' (Rs.17.3L real money history)
//  - 36 rows parent to backup_plot_bookings (emi_active live bookings)
//  - bps.status enum has no cancelled/archived value (pending/paid/overdue/partial)
// Run: php scripts/migrate_phase5_lead_reassign.php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$bad = $pdo->query("SELECT l.id, l.assigned_to FROM leads l LEFT JOIN users u ON u.id=l.assigned_to WHERE l.assigned_to IS NOT NULL AND u.id IS NULL ORDER BY l.id")->fetchAll(PDO::FETCH_ASSOC);
echo "orphaned assignments: " . count($bad) . "\n";
if (count($bad) > 0) {
    $file = __DIR__ . '/../database/_archive_lead_assignments_20260916.sql';
    $sql = "-- Lead assignment rollback archive 2026-09-16\n-- Re-apply via: UPDATE leads SET assigned_to=<val> WHERE id=<id>\n";
    foreach ($bad as $r) $sql .= "UPDATE leads SET assigned_to = {$r['assigned_to']} WHERE id = {$r['id']};\n";
    file_put_contents($file, $sql);
    echo "Rollback archive written (" . strlen($sql) . " bytes)\n";
    $n = $pdo->exec("UPDATE leads SET assigned_to = NULL WHERE assigned_to NOT IN (SELECT id FROM users)");
    echo "Nulled $n leads\n";
} else {
    echo "Nothing to do\n";
}
echo "DONE phase5\n";
