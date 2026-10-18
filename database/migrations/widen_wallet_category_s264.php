<?php
/**
 * Widen wallet_transactions.transaction_category with 'investment' (Session 264).
 * Idempotent: MODIFY runs ONLY when 'investment' is absent from the enum.
 *
 * Background: InvestmentService::processInstallment writes category
 * 'investment' for every SIP wallet debit, but the live ENUM
 * (referral|commission|bonus|emi_transfer|withdrawal|adjustment) rejects it —
 * strict mode 1364s, the txn rolls back, and wallet-funded SIP can never
 * succeed. Widen-only; all existing values preserved.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$row = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wallet_transactions' AND COLUMN_NAME = 'transaction_category'")->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "SKIP transaction_category (column absent)\nDone.\n";
    return;
}
if (strpos($row['COLUMN_TYPE'], "'investment'") !== false) {
    echo "SKIP transaction_category (investment already present)\nDone.\n";
    return;
}
$pdo->exec("ALTER TABLE wallet_transactions MODIFY COLUMN transaction_category ENUM('referral','commission','bonus','emi_transfer','withdrawal','adjustment','investment') NOT NULL");
echo "FIXED transaction_category (+investment)\nDone.\n";
