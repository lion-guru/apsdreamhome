<?php
// Phase 3A: merge legacy singular log rows into canonical plural tables (idempotent).
// - audit_log (1115 rows, simple schema) -> audit_logs (rich schema)
// - event_log (1 row) -> event_logs
// Does NOT drop/rename anything (renames happen in Phase 3C after code repoint).
// Run: php scripts/migrate_phase3a_log_merge.php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function tableExists($pdo, $t) {
    $s = $pdo->prepare("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME=?");
    $s->execute([$t]);
    return (bool)$s->fetchColumn();
}

if (!tableExists($pdo, 'audit_log')) { echo "audit_log gone (archived) — nothing to merge\n"; }
else {

// 1. audit_log -> audit_logs (dedupe-safe: skip rows already merged via merge marker in metadata)
$pdo->exec("INSERT INTO audit_logs (tenant_id, user_id, user_role, action, description, ip_address, created_at)
    SELECT tenant_id, COALESCE(user_id, 0), COALESCE(NULLIF(user_role, ''), 'system'),
           action, details, ip_address, created_at
    FROM audit_log a
    WHERE NOT EXISTS (
        SELECT 1 FROM audit_logs l
        WHERE l.action = a.action COLLATE utf8mb4_unicode_ci
          AND l.created_at = a.created_at
          AND COALESCE(l.description, '') = COALESCE(a.details, '') COLLATE utf8mb4_unicode_ci
          AND COALESCE(l.ip_address, '') = COALESCE(a.ip_address, '') COLLATE utf8mb4_unicode_ci
    )");
echo "audit_logs merged\n";
}

// 2. event_log (1 row) -> event_logs
if (!tableExists($pdo, 'event_log')) { echo "event_log gone (archived) — nothing to merge\n"; }
else {
$pdo->exec("INSERT INTO event_logs (event_type, source, event_data, severity, tenant_id, created_at)
    SELECT event_type, CONCAT('legacy_event_log:', COALESCE(event_id, id)),
           event_data,
           CASE WHEN priority >= 4 THEN 'error' WHEN priority = 3 THEN 'warning' ELSE 'info' END,
           tenant_id, created_at
    FROM event_log e
    WHERE NOT EXISTS (
        SELECT 1 FROM event_logs l
        WHERE l.source = CONCAT('legacy_event_log:', COALESCE(e.event_id, e.id)) COLLATE utf8mb4_unicode_ci
    )");
echo "event_logs merged\n";
}

foreach (['audit_logs', 'event_logs'] as $t) {
    if (!tableExists($pdo, $t)) { echo "$t: MISSING!\n"; continue; }
    $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "$t: $c rows\n";
}
foreach (['_archive_audit_log_202609', '_archive_event_log_202609', 'audit_log', 'event_log'] as $t) {
    if (tableExists($pdo, $t)) {
        $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t: $c rows\n";
    }
}
echo "DONE phase3a\n";
