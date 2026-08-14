<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Seed bank interest rates
$banks = [
    ['SBI', 'State Bank of India', 8.50, 30, 300000, 100000000, 0.35],
    ['HDFC', 'HDFC Bank', 8.60, 30, 300000, 100000000, 0.50],
    ['ICICI', 'ICICI Bank', 8.65, 30, 300000, 100000000, 0.50],
    ['AXIS', 'Axis Bank', 8.70, 30, 300000, 100000000, 0.50],
    ['PNB', 'Punjab National Bank', 8.55, 30, 300000, 100000000, 0.35],
    ['BOB', 'Bank of Baroda', 8.50, 30, 300000, 100000000, 0.35],
    ['CAN', 'Canara Bank', 8.55, 30, 300000, 100000000, 0.35],
    ['UNION', 'Union Bank of India', 8.60, 30, 300000, 100000000, 0.35],
    ['IND', 'Indian Bank', 8.65, 30, 300000, 100000000, 0.35],
    ['CENTRAL', 'Central Bank of India', 8.50, 30, 300000, 100000000, 0.35],
    ['UCO', 'UCO Bank', 8.70, 30, 300000, 100000000, 0.35],
    ['BOM', 'Bank of Maharashtra', 8.55, 30, 300000, 100000000, 0.35],
    ['IDBI', 'IDBI Bank', 8.80, 30, 300000, 100000000, 0.50],
    ['YES', 'Yes Bank', 8.90, 30, 300000, 100000000, 0.50],
    ['KOTAK', 'Kotak Mahindra Bank', 8.60, 30, 300000, 100000000, 0.50],
    ['INDUS', 'IndusInd Bank', 8.75, 30, 300000, 100000000, 0.50],
    ['FEDERAL', 'Federal Bank', 8.70, 30, 300000, 100000000, 0.50],
    ['JKB', 'J&K Bank', 8.65, 30, 300000, 100000000, 0.50],
    ['KVB', 'Karur Vysya Bank', 8.80, 30, 300000, 100000000, 0.50],
    ['CSB', 'Catholic Syrian Bank', 8.75, 30, 300000, 100000000, 0.50],
    ['DBS', 'DBS Bank', 8.50, 30, 500000, 100000000, 0.50],
    ['CITI', 'Citibank', 8.60, 30, 500000, 100000000, 0.50],
    ['HSBC', 'HSBC Bank', 8.65, 30, 500000, 100000000, 0.50],
    ['SCB', 'Standard Chartered', 8.70, 30, 500000, 100000000, 0.50],
    ['IDFC', 'IDFC First Bank', 8.50, 30, 300000, 100000000, 0.50],
    ['RBL', 'RBL Bank', 8.85, 30, 300000, 100000000, 0.50],
];

$stmt = $pdo->prepare("
    INSERT INTO bank_interest_rates 
    (bank_code, bank_name, rate, max_tenure, min_loan_amount, max_loan_amount, processing_fee_percent)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        bank_name = VALUES(bank_name),
        rate = VALUES(rate),
        max_tenure = VALUES(max_tenure),
        min_loan_amount = VALUES(min_loan_amount),
        max_loan_amount = VALUES(max_loan_amount),
        processing_fee_percent = VALUES(processing_fee_percent),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($banks as $bank) {
    try {
        $stmt->execute($bank);
        $inserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted/Updated $inserted bank interest rates\n";

// Verify
$count = $pdo->query("SELECT COUNT(*) FROM bank_interest_rates WHERE is_active = 1")->fetchColumn();
echo "Total active banks: $count\n";?>