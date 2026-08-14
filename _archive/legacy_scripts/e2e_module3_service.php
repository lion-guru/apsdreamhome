<?php
// Direct PHP end-to-end test: get CSRF token, POST, verify
define('APP_ROOT', dirname(__DIR__));
require 'C:\xampp\htdocs\apsdreamhome\app\core\Autoloader.php';
\App\Core\ConfigService::getInstance();

// Simulate the controller end-to-end via internal calls
$db = \App\Core\Database\Database::getInstance();

echo "=== Pre-test row count ===" . PHP_EOL;
echo "bank_accounts: " . $db->fetchOne("SELECT COUNT(*) c FROM bank_accounts")['c'] . PHP_EOL;
echo "tds_register: " . $db->fetchOne("SELECT COUNT(*) c FROM tds_register")['c'] . PHP_EOL;
echo "expenses: " . $db->fetchOne("SELECT COUNT(*) c FROM expenses")['c'] . PHP_EOL;
echo PHP_EOL;

$svc = new \App\Services\Accounting\MoneyWorkflowService();

echo "=== Test 1: createBankAccount via service ===" . PHP_EOL;
$id1 = $svc->createBankAccount([
    'account_name' => 'Module3 E2E Bank',
    'account_number' => 'E2E' . time(),
    'ifsc_code' => 'E2E0001234',
    'bank_name' => 'E2E Bank',
    'branch' => 'E2E Branch',
    'account_type' => 'current',
    'opening_balance' => 50000,
    'status' => 'active'
]);
echo "  Created bank_account id: $id1" . PHP_EOL;

echo "=== Test 2: recordTransaction via service ===" . PHP_EOL;
$id2 = $svc->recordTransaction([
    'transaction_type' => 'receipt',
    'amount' => 2500,
    'transaction_date' => '2026-06-07',
    'payment_mode' => 'cash',
    'party_name' => 'E2E Customer',
    'narration' => 'E2E test receipt'
]);
echo "  Created cash book entry: " . json_encode($id2) . PHP_EOL;

echo "=== Test 3: recordTdsProxy ===" . PHP_EOL;
$id3 = $svc->recordTdsProxy([
    'tds_date' => '2026-06-07',
    'section_code' => '194J',
    'deductee_user_id' => 1,
    'deductee_name' => 'E2E Professional',
    'gross_amount' => 50000,
    'tds_rate' => 10,
    'tds_amount' => 5000,
    'financial_year' => '2025-26',
    'quarter' => 'Q1'
]);
echo "  Created tds_register id: $id3" . PHP_EOL;

echo "=== Test 4: recordGstProxy ===" . PHP_EOL;
$id4 = $svc->recordGstProxy([
    'transaction_date' => '2026-06-07',
    'transaction_type' => 'output',
    'supply_type' => 'intra',
    'gst_rate' => 18,
    'taxable_amount' => 10000,
    'cgst' => 900,
    'sgst' => 900,
    'igst' => 0,
    'party_name' => 'E2E GST Customer',
    'financial_year' => '2025-26'
]);
echo "  Created gst_transactions id: $id4" . PHP_EOL;

echo "=== Test 5: submitExpense ===" . PHP_EOL;
$id5 = $svc->submitExpense([
    'expense_date' => '2026-06-07',
    'category' => 'office_supplies',
    'amount' => 750,
    'description' => 'E2E test expense',
    'payment_mode' => 'cash'
]);
echo "  Created expense id: $id5" . PHP_EOL;

echo "=== Test 6: recordVendorPayment ===" . PHP_EOL;
$id6 = $svc->recordVendorPayment([
    'payment_date' => '2026-06-07',
    'vendor_type' => 'contractor',
    'vendor_id' => 1,
    'vendor_name' => 'E2E Vendor',
    'amount' => 15000,
    'tds_deducted' => 1500,
    'gst_amount' => 0,
    'payment_mode' => 'bank'
]);
echo "  Created vendor_payments id: $id6" . PHP_EOL;

echo "=== Test 7: issueChequeWithVoucher ===" . PHP_EOL;
$id7 = $svc->issueChequeWithVoucher([
    'cheque_date' => '2026-06-07',
    'cheque_number' => 'E2E-CHK-' . time(),
    'amount' => 3500,
    'bank_account_id' => 1,
    'payee_name' => 'E2E Payee',
    'purpose' => 'E2E test'
]);
echo "  Created cheque_register id: $id7" . PHP_EOL;

echo "=== Test 8: topupPettyCash ===" . PHP_EOL;
$id8 = $svc->topupPettyCash(1000, [
    'topup_date' => '2026-06-07',
    'source' => 'Main Bank',
    'remarks' => 'E2E topup'
]);
echo "  Created petty_cash id: $id8" . PHP_EOL;

echo PHP_EOL . "=== Post-test row counts ===" . PHP_EOL;
foreach (['bank_accounts' => 'createBankAccount', 'payment_transactions' => 'recordTransaction', 'tds_register' => 'recordTdsProxy', 'gst_transactions' => 'recordGstProxy', 'expenses' => 'submitExpense', 'vendor_payments' => 'recordVendorPayment', 'cheque_register' => 'issueChequeWithVoucher', 'petty_cash' => 'topupPettyCash'] as $t => $m) {
    $c = $db->fetchOne("SELECT COUNT(*) c FROM $t")['c'];
    echo str_pad($t, 25) . " : $c rows ($m)" . PHP_EOL;
}

echo PHP_EOL . "=== Dashboard stats via service ===" . PHP_EOL;
$stats = $svc->getDashboardStats();
foreach ($stats as $k => $v) {
    if (is_array($v)) { $v = json_encode($v); }
    echo "  $k = $v" . PHP_EOL;
}?>