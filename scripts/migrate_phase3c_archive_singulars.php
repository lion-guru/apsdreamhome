<?php
// Phase 3C: archive legacy singular tables AFTER code repoint (idempotent).
// - RENAME audit_log -> _archive_audit_log_202609 (data already in audit_logs)
// - RENAME event_log -> _archive_event_log_202609 (row already in event_logs)
// - DROP security_log (0 rows; writer moved to security_logs)
// - DROP demand_letter_template (0 rows; canonical is demand_letter_templates)
// Guards: renames only if source exists and archive missing; drops only if empty.
// Run: php scripts/migrate_phase3c_archive_singulars.php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function tableExists($pdo, $t) {
    $s = $pdo->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?");
    $s->execute([$t]);
    return (bool)$s->fetchColumn();
}

foreach (['audit_log' => '_archive_audit_log_202609', 'event_log' => '_archive_event_log_202609'] as $src => $dst) {
    if (!tableExists($pdo, $src)) { echo "OK $src already archived\n"; continue; }
    if (tableExists($pdo, $dst)) { echo "SKIP $src: $dst already exists — manual review\n"; continue; }
    $pdo->exec("RENAME TABLE `$src` TO `$dst`");
    echo "RENAMED $src -> $dst\n";
}

foreach (['security_log', 'demand_letter_template'] as $t) {
    if (!tableExists($pdo, $t)) { echo "OK $t already gone\n"; continue; }
    $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($c > 0) { echo "SKIP $t: has $c rows — manual review, NOT dropped\n"; continue; }
    $pdo->exec("DROP TABLE `$t`");
    echo "DROPPED empty $t\n";
}
echo "DONE phase3c\n";
