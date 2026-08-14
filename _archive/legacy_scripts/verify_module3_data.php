<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();

$tables = [
    'bank_accounts' => ['id', 'account_name', 'account_number', 'ifsc_code', 'bank_name', 'opening_balance', 'created_at'],
    'payment_transactions' => ['id', 'transaction_type', 'amount', 'transaction_date', 'payment_mode', 'party_name'],
    'petty_cash' => ['id', 'transaction_type', 'amount', 'transaction_date'],
    'cheque_register' => ['id', 'cheque_number', 'cheque_date', 'amount', 'payee_name', 'status'],
    'tds_register' => ['id', 'tds_date', 'section_code', 'deductee_name', 'gross_amount', 'tds_amount'],
    'gst_transactions' => ['id', 'transaction_date', 'transaction_type', 'party_name', 'taxable_amount', 'cgst', 'sgst', 'igst'],
    'expenses' => ['id', 'expense_date', 'category', 'amount', 'description', 'status'],
    'vendor_payments' => ['id', 'payment_date', 'vendor_type', 'vendor_name', 'amount', 'tds_deducted'],
    'demand_letter_templates' => ['id', 'template_name', 'template_type', 'active', 'created_at'],
];

foreach ($tables as $t => $cols) {
    $c = $db->fetchOne("SELECT COUNT(*) AS c FROM $t")['c'];
    $lastRow = $db->fetchOne("SELECT * FROM $t ORDER BY id DESC LIMIT 1");
    echo str_pad($t, 30) . " : " . $c . " rows";
    if ($lastRow) {
        echo " | last: ";
        $parts = [];
        foreach ($cols as $col) {
            if (isset($lastRow[$col])) {
                $v = (string)$lastRow[$col];
                if (strlen($v) > 30) $v = substr($v, 0, 27) . '...';
                $parts[] = "$col=" . $v;
            }
        }
        echo implode(', ', array_slice($parts, 0, 3));
    }
    echo PHP_EOL;
}?>