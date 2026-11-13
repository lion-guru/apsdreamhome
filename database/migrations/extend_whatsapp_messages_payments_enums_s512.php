<?php
/**
 * Widen two enums so the code can write the values it already writes (Session 512).
 *
 * - whatsapp_messages.status: append 'pending'
 *   WhatsAppManagerService::sendWhatsAppMessage() INSERTs status='pending' (queued,
 *   not yet dispatched) and later UPDATEs it to sent|failed. Under STRICT_TRANS_TABLES
 *   the INSERT 1265'd on every message, so nothing ever logged.
 * - payments.payment_type: append 'foreclosure'
 *   App\Models\EMI::processForeclosure() INSERTs payment_type='foreclosure' for EMI
 *   prepayment/foreclosure money movements. Under STRICT_TRANS_TABLES the INSERT
 *   1265'd, killing the foreclosure payment record after the user had paid.
 *
 * Both members appended at the END => existing value indices (and stored rows)
 * are preserved. Idempotent: each ALTER runs ONLY when the value is absent,
 * so this is safe to re-run after any DB restore.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

function columnType(\PDO $pdo, string $table, string $column): ?string {
    $row = $pdo->query(
        "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'"
    )->fetch(\PDO::FETCH_ASSOC);
    return $row ? strtolower($row['COLUMN_TYPE']) : null;
}

// 1) whatsapp_messages.status -> ENUM('sent','delivered','read','failed','pending') NULL DEFAULT 'sent'
$wt = columnType($pdo, 'whatsapp_messages', 'status');
if ($wt === null) {
    echo "SKIP whatsapp_messages.status (column absent)\n";
} elseif (strpos($wt, "'pending'") !== false) {
    echo "SKIP whatsapp_messages.status ('pending' already present)\n";
} else {
    $pdo->exec("ALTER TABLE `whatsapp_messages` MODIFY `status`
        ENUM('sent','delivered','read','failed','pending') NULL DEFAULT 'sent'");
    echo "FIXED whatsapp_messages.status ('pending' appended)\n";
}

// 2) payments.payment_type -> ENUM('booking','down_payment','emi','full_payment','commission','other','foreclosure') NOT NULL
$pt = columnType($pdo, 'payments', 'payment_type');
if ($pt === null) {
    echo "SKIP payments.payment_type (column absent)\n";
} elseif (strpos($pt, "'foreclosure'") !== false) {
    echo "SKIP payments.payment_type ('foreclosure' already present)\n";
} else {
    $pdo->exec("ALTER TABLE `payments` MODIFY `payment_type`
        ENUM('booking','down_payment','emi','full_payment','commission','other','foreclosure') NOT NULL");
    echo "FIXED payments.payment_type ('foreclosure' appended)\n";
}

// 3) Verify both
foreach ([['whatsapp_messages', 'status'], ['payments', 'payment_type']] as [$t, $c]) {
    $after = columnType($pdo, $t, $c);
    echo "VERIFY {$t}.{$c}: {$after}\n";
}
echo "Done.\n";
