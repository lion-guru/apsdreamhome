<?php
// Idempotent: align salary_payouts with the batch-payout UI (controller+view use
// payout_batch_id/amount/payout_date/payment_method/notes/reference_no).
require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();
$have = [];
foreach ($pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='salary_payouts'")->fetchAll(PDO::FETCH_COLUMN) as $c) $have[$c] = true;
$add = [
    'payout_batch_id' => 'VARCHAR(40) NULL',
    'amount' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
    'payout_date' => 'DATE NULL',
    'payment_method' => 'VARCHAR(40) NOT NULL DEFAULT ' . "'bank_transfer'",
    'notes' => 'TEXT NULL',
    'reference_no' => 'VARCHAR(80) NULL',
];
foreach ($add as $col => $def) {
    if (!isset($have[$col])) {
        $pdo->exec("ALTER TABLE salary_payouts ADD COLUMN $col $def");
        echo "added $col\n";
    }
}
echo "done\n";
