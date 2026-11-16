<?php
/**
 * S666: Widen overtime_requests with the columns OvertimeService was designed
 * around (the live table never carried them, so the employee "Apply Overtime"
 * path died with 1054 on every request).
 *
 * Idempotent: information_schema existence checks; re-run = all SKIP.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== Overtime schema widen (S666) ===\n";

$cols = [
    ['start_time',      "ADD COLUMN start_time time NULL AFTER overtime_date"],
    ['end_time',        "ADD COLUMN end_time time NULL AFTER start_time"],
    ['rate_multiplier', "ADD COLUMN rate_multiplier decimal(5,2) NULL DEFAULT 1.00 AFTER hours"],
    ['hourly_rate',     "ADD COLUMN hourly_rate decimal(12,2) NULL DEFAULT 0.00 AFTER rate_multiplier"],
    ['amount',          "ADD COLUMN amount decimal(12,2) NULL DEFAULT 0.00 AFTER hourly_rate"],
    ['requested_at',    "ADD COLUMN requested_at datetime NULL AFTER remarks"],
];
foreach ($cols as [$name, $ddl]) {
    $chk = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'overtime_requests' AND column_name = ?");
    $chk->execute([$name]);
    if ((int) $chk->fetchColumn() > 0) {
        echo "  SKIP: {$name}\n";
        continue;
    }
    $pdo->exec("ALTER TABLE overtime_requests {$ddl}");
    echo "  ADD: {$name}\n";
}

$chk = $pdo->prepare("SELECT COUNT(*) FROM schema_migrations WHERE version = 'add_overtime_request_detail_columns_s666.php'");
$chk->execute();
if ((int) $chk->fetchColumn() === 0) {
    $ins = $pdo->prepare("INSERT INTO schema_migrations (version, name, executed_at) VALUES ('add_overtime_request_detail_columns_s666.php', 'overtime_request_detail_columns_s666', NOW())");
    $ins->execute();
    echo "  schema_migrations: recorded\n";
} else {
    echo "  schema_migrations: already recorded\n";
}
echo "DONE\n";
