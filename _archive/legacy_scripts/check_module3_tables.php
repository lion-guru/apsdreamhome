<?php
require 'C:\xampp\htdocs\apsdreamhome\app\Core\ConfigService.php';
require 'C:\xampp\htdocs\apsdreamhome\app\Core\Database\Database.php';
App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();
$tables = [
    'bank_accounts_master','daily_cash_book','petty_cash','cheque_register',
    'bank_reconciliation','bank_reconciliation_items','tds_register','gst_transactions',
    'cheque_bounce_log','demand_letter_template','cash_flow_forecast','expense_approvals',
    'vendor_payments','tds_certificates_issued','payment_voucher_log',
    'chart_of_accounts','journal_entries','journal_entry_lines'
];
foreach ($tables as $t) {
    $r = $db->fetch('SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$t]);
    if ($r['c'] > 0) {
        $cnt = $db->fetchOne("SELECT COUNT(*) c FROM $t");
        echo str_pad($t, 32) . " EXISTS rows=" . ($cnt['c'] ?? 0) . PHP_EOL;
    } else {
        echo str_pad($t, 32) . " MISSING" . PHP_EOL;
    }
}?>