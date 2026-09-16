<?php
// Phase 4C: drop dead backup tables AFTER mysqldump archive (idempotent).
// Archive: database/_archive_backup_tables_20260916.sql (verify exists first).
// NOTE: backup_plot_bookings is INTENTIONALLY KEPT — it parents 36 live
// booking_payment_schedules rows (ids 9003-9008, emi_active). Dropping it
// would orphan live EMI data. See mission report.
// Run: php scripts/migrate_phase4c_drop_backup_tables.php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$archive = __DIR__ . '/../database/_archive_backup_tables_20260916.sql';
if (!file_exists($archive) || filesize($archive) < 5000) {
    echo "ABORT: archive $archive missing or too small — refusing to drop\n";
    exit(1);
}
echo "Archive OK (" . filesize($archive) . " bytes)\n";

$drop = ['backup_bookings', 'backup_colonies', 'backup_config', 'backup_integrity',
         'backup_logs', 'backup_schedules', 'mlm_rank_benefits_backup_20260626'];
foreach ($drop as $t) {
    $s = $pdo->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME=?");
    $s->execute([$t]);
    if (!$s->fetchColumn()) { echo "OK $t already gone\n"; continue; }
    $pdo->exec("DROP TABLE `$t`");
    echo "DROPPED $t\n";
}
echo "KEPT backup_plot_bookings (parents live EMI schedules) — see report\n";
echo "DONE phase4c\n";
