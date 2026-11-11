<?php
/**
 * Migration: fix the `inquiries` schema family (add subject, widen status enum)
 *
 * TWO live tables were half-migrated in opposite directions:
 *
 *   inquiries.status          ENUM('pending','in_progress','completed','closed')
 *   financial_inquiries.status ENUM('new','contacted','converted','closed')
 *
 * ...and the CODE uses the union of both. Three real defects fell out:
 *
 * 1. `inquiries` has NO `subject` column, but
 *      - Front\PageController::inquiry() INSERTs (name, email, phone, subject,
 *        message, status, tenant_id, created_at) -> the PUBLIC website lead
 *        form failed for EVERY visitor with
 *        "Unknown column 'subject' in 'field list'" (1054).
 *        The form marks Subject as REQUIRED, so this is real lead data that
 *        must be kept, not dropped.
 *      - MobileUserApiController::getInquiriesData() SELECTs it.
 *
 * 2. `inquiries.status` ENUM is missing 'new' and 'contacted', both of which
 *    the code writes:
 *      - PageController::inquiry() inserts status='new'
 *      - Admin\InquiryController::markContacted() sets status='contacted'
 *    -> "Data truncated for column 'status'" (1265) in strict mode.
 *    The admin UI dropdown (views/admin/inquiries/index.php + view.php)
 *    offers exactly: new, pending, contacted, closed.
 *    The employee portal whitelist (EmployeeController::updateInquiryStatus)
 *    offers exactly: pending, in_progress, completed, closed.
 *    Union of both = new, pending, contacted, in_progress, completed, closed.
 *
 * 3. `financial_inquiries` has NO `notes` column, but
 *    Admin\FinancialInquiryController::updateStatus() appends to it and
 *    views/admin/financial-inquiries/show.php renders it -> 1054 on every
 *    status update.
 *
 * Per AGENTS.md lesson _566 ("live schema beats migration-file schema -
 * converge, don't pick a side") the enum is WIDENED to the union rather than
 * narrowed to one table's view.
 *
 * Idempotent: each step checks the current shape first. Re-running is a no-op.
 *
 * Run via: php database/migrations/fix_inquiries_schema_s449.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

function columnExists(PDO $pdo, string $table, string $col): bool
{
    $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
    return (bool)$r;
}

function columnType(PDO $pdo, string $table, string $col): string
{
    $r = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
    return $r ? (string)$r['Type'] : '';
}

// ---------------------------------------------------------------------------
// 1. inquiries.subject
// ---------------------------------------------------------------------------
if (!columnExists($pdo, 'inquiries', 'subject')) {
    try {
        $pdo->exec("ALTER TABLE `inquiries` ADD COLUMN `subject` VARCHAR(255) NULL DEFAULT NULL AFTER `phone`");
        echo "OK  added inquiries.subject VARCHAR(255)\n";
    } catch (\Throwable $e) {
        echo "ERR failed to add inquiries.subject: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "SKIP inquiries.subject already exists\n";
}

// ---------------------------------------------------------------------------
// 2. inquiries.status  -> union of every value the code writes / the UI offers
// ---------------------------------------------------------------------------
$WANTED = "enum('new','pending','contacted','in_progress','completed','closed')";
$current = columnType($pdo, 'inquiries', 'status');
if (strtolower(trim($current)) !== strtolower(trim($WANTED))) {
    try {
        $pdo->exec("ALTER TABLE `inquiries` MODIFY COLUMN `status` $WANTED NOT NULL DEFAULT 'new'");
        echo "OK  widened inquiries.status\n";
        echo "      was: $current\n";
        echo "      now: $WANTED\n";
    } catch (\Throwable $e) {
        echo "ERR failed to widen inquiries.status: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "SKIP inquiries.status already at the union definition\n";
}

// ---------------------------------------------------------------------------
// 3. financial_inquiries.notes
// ---------------------------------------------------------------------------
if (!columnExists($pdo, 'financial_inquiries', 'notes')) {
    try {
        $pdo->exec("ALTER TABLE `financial_inquiries` ADD COLUMN `notes` TEXT NULL DEFAULT NULL AFTER `message`");
        echo "OK  added financial_inquiries.notes TEXT\n";
    } catch (\Throwable $e) {
        echo "ERR failed to add financial_inquiries.notes: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "SKIP financial_inquiries.notes already exists\n";
}

// ---------------------------------------------------------------------------
// Verify
// ---------------------------------------------------------------------------
$ok = true;
$checks = [
    ['inquiries', 'subject', 'varchar(255)'],
    ['inquiries', 'status', "enum('new','pending','contacted','in_progress','completed','closed')"],
    ['financial_inquiries', 'notes', 'text'],
];
foreach ($checks as [$table, $col, $want]) {
    if (!columnExists($pdo, $table, $col)) {
        echo "ERR $table.$col MISSING\n";
        $ok = false;
        continue;
    }
    $type = strtolower(columnType($pdo, $table, $col));
    if ($type !== strtolower($want)) {
        echo "ERR $table.$col type is '$type', expected '$want'\n";
        $ok = false;
    } else {
        echo "OK  verified $table.$col ($type)\n";
    }
}

// Row counts must be untouched by the ALTERs.
foreach (['inquiries', 'financial_inquiries'] as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "OK  $t rows = $n\n";
}

if (!$ok) { exit(1); }
echo "DONE\n";
