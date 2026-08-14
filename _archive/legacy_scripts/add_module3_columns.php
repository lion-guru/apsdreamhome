<?php
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
$db = \App\Core\Database\Database::getInstance();

$alterations = [
    'tds_register' => [
        ['financial_year', "VARCHAR(10) NULL AFTER return_period"],
        ['quarter', "VARCHAR(5) NULL AFTER financial_year"],
        ['deductee_user_id', "BIGINT(20) UNSIGNED NULL AFTER deductee_name"],
    ],
    'gst_transactions' => [
        ['financial_year', "VARCHAR(10) NULL AFTER return_period"],
    ],
    'vendor_payments' => [
        ['vendor_type', "VARCHAR(50) NULL AFTER vendor_name"],
    ],
    'expenses' => [
        ['payment_mode', "VARCHAR(30) NULL DEFAULT 'cash' AFTER description"],
    ],
    'cheque_register' => [
        // already has all needed columns
    ],
    'bank_accounts' => [
        // already has all needed columns
    ],
    'petty_cash' => [
        // already has all needed columns
    ],
    'bank_reconciliation' => [
        ['reconciliation_date', "DATE NULL"],
        ['opening_balance', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
        ['closing_balance', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
    ],
    'cash_flow_forecast' => [
        ['forecast_date', "DATE NOT NULL DEFAULT (CURRENT_DATE)"],
        ['opening_balance', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
        ['expected_receipts', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
        ['expected_payments', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
        ['closing_balance', "DECIMAL(15,2) NULL DEFAULT '0.00'"],
    ],
];

$added = 0; $skipped = 0; $errored = 0;
foreach ($alterations as $table => $cols) {
    foreach ($cols as [$name, $def]) {
        try {
            $exists = $db->fetchOne("SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?", ['apsdreamhome', $table, $name]);
            if ($exists) { $skipped++; continue; }
            $db->execute("ALTER TABLE `$table` ADD COLUMN `$name` $def");
            $added++;
            echo "  +$table.$name" . PHP_EOL;
        } catch (Exception $e) {
            $errored++;
            echo "  X $table.$name: " . $e->getMessage() . PHP_EOL;
        }
    }
}
echo PHP_EOL . "Added: $added, Skipped: $skipped, Errored: $errored" . PHP_EOL;?>