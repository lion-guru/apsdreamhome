<?php
// Phase 5: leads with assigned_to pointing to non-existent users.
// Safety: archives (id, assigned_to) pairs to database/_archive_lead_assignments_20260916.sql
// BEFORE nulling, so the operation is reversible. Idempotent.
// NOTE: booking_payment_schedules "ghosts" are INTENTIONALLY untouched:
//  - 16 rows are status='paid' (Rs.17.3L real money history)
//  - 36 rows parent to backup_plot_bookings (emi_active live bookings)
//  - bps.status enum has no cancelled/archived value (pending/paid/overdue/partial)
// Run: php scripts/migrate_phase5_lead_reassign.php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $bad = $pdo->query("SELECT l.id, l.assigned_to FROM leads l LEFT JOIN users u ON u.id=l.assigned_to WHERE l.assigned_to IS NOT NULL AND u.id IS NULL ORDER BY l.id")->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    if ($e->getCode() == '42S02') { echo "SKIP: leads table not found\n"; echo "DONE phase5\n"; exit(0); }
    throw $e;
}
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
