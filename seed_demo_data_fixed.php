<?php
// Seed demo data for ERP dashboards - FIXED dates
// Run from project root: php seed_demo_data.php

require_once __DIR__ . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Seeding demo data (fixed dates)...\n";

$today = date('Y-m-d');
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

// 1. Seed daily_cash_book with recent data (last 30 days)
echo "Seeding daily_cash_book...\n";
$receiptTypes = ['receipt', 'payment'];
$cashModes = ['cash', 'cheque', 'online', 'upi'];
$descriptions = [
    'Plot booking collection', 'EMI collection', 'Down payment', 'Registration fee',
    'Maintenance charge', 'Water bill', 'Electricity bill', 'Office expense',
    'Site visit expense', 'Marketing expense', 'Salary payment', 'Vendor payment'
];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $numEntries = rand(2, 5);
    
    for ($j = 0; $j < $numEntries; $j++) {
        $type = $receiptTypes[array_rand($receiptTypes)];
        $amount = $type === 'receipt' ? rand(5000, 500000) : rand(1000, 200000);
        $mode = $cashModes[array_rand($cashModes)];
        $desc = $descriptions[array_rand($descriptions)];
        $ref = 'REF' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        try {
            $db->execute(
                "INSERT INTO daily_cash_book (transaction_date, transaction_type, amount, transaction_mode, description, reference_no, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$date, $type, $amount, $mode, $desc, $ref]
            );
        } catch (\Exception $e) {}
    }
}
echo "  Done.\n";

// 2. Seed cheque_register
echo "Seeding cheque_register...\n";
$chequeStatuses = ['cleared', 'pending', 'bounced'];
$banks = ['SBI', 'HDFC', 'ICICI', 'AXIS', 'PNB', 'BOB'];

for ($i = 0; $i < 20; $i++) {
    $date = date('Y-m-d', strtotime("-" . rand(0, 60) . " days"));
    $status = $chequeStatuses[array_rand($chequeStatuses)];
    $amount = rand(10000, 1000000);
    $bank = $banks[array_rand($banks)];
    $chequeNo = 'CHQ' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    try {
        $db->execute(
            "INSERT INTO cheque_register (cheque_date, cheque_no, bank_name, amount, status, party_name, clearing_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$date, $chequeNo, $bank, $amount, $status, 'Party ' . rand(1, 50), $status === 'cleared' ? date('Y-m-d', strtotime($date . ' +2 days')) : null]
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 3. Seed tds_register
echo "Seeding tds_register...\n";
$tdsStatuses = ['pending', 'deposited', 'filed', 'verified'];

for ($i = 0; $i < 15; $i++) {
    $date = date('Y-m-d', strtotime("-" . rand(0, 365) . " days"));
    $status = $tdsStatuses[array_rand($tdsStatuses)];
    $amount = rand(5000, 50000);
    $section = ['192', '194C', '194H', '194I', '194J'][array_rand([0,1,2,3,4])];
    $gross = rand(50000, 500000);
    $rate = [1, 2, 5, 10][array_rand([0,1,2,3])];
    $tds = round($gross * $rate / 100, 2);
    
    try {
        $db->execute(
            "INSERT INTO tds_register (transaction_date, tds_section, gross_amount, tds_rate, tds_amount, status, deductee_name, deductee_pan, deductee_user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [date('Y-m-d', strtotime("-" . rand(0, 365) . " days")), $section, rand(50000, 500000), $rate, rand(5000, 50000), $status, 'Deductee ' . rand(1, 100), 'ABCDE' . rand(1000, 9999) . 'F', rand(1, 10)]
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 4. Update mlm_commission_ledger - add some paid this month
echo "Updating mlm_commission_ledger for current month...\n";
$currentMonth = date('m');
$currentYear = date('Y');

try {
    $db->execute(
        "UPDATE mlm_commission_ledger SET status='paid', paid_at=NOW(), source_user_id=1 WHERE status='pending' AND MONTH(created_at)=? AND YEAR(created_at)=? LIMIT 10",
        [$currentMonth, $currentYear]
    );
    
    $count = $db->fetch("SELECT COUNT(*) as c FROM mlm_commission_ledger WHERE MONTH(created_at)=? AND YEAR(created_at)=? AND status='paid'", [$currentMonth, $currentYear])['c'] ?? 0;
    if ($count < 5) {
        for ($i = 0; $i < 5; $i++) {
            $db->execute(
                "INSERT INTO mlm_commission_ledger (booking_id, beneficiary_user_id, source_user_id, commission_type, amount, payment_amount, status, created_at, paid_at, calculation_engine) VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW(), NOW(), 'hybrid')",
                [rand(1, 200), rand(1, 10), rand(1, 10), ['direct_sale', 'override', 'generation_bonus', 'matching_bonus'][array_rand([0,1,2,3])], rand(1000, 50000), rand(1000, 50000)]
            );
        }
    }
} catch (\Exception $e) { echo "  Error: " . $e->getMessage() . "\n"; }
echo "  Done.\n";

// 5. Seed mlm_payouts
echo "Seeding mlm_payouts...\n";
$payoutStatuses = ['pending', 'processing', 'completed', 'failed'];

for ($i = 0; $i < 10; $i++) {
    $status = $payoutStatuses[array_rand($payoutStatuses)];
    $amount = rand(5000, 200000);
    $userId = rand(1, 10);
    
    try {
        $db->execute(
            "INSERT INTO mlm_payouts (user_id, amount, status, requested_at, processed_at, created_at) VALUES (?, ?, ?, NOW(), ?, NOW())",
            [$userId, $amount, $status, $status === 'completed' ? date('Y-m-d H:i:s') : null]
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 6. Seed employee_attendance
echo "Seeding employee_attendance...\n";
$empStatuses = ['present', 'absent', 'late', 'half_day'];

for ($i = 0; $i < 50; $i++) {
    $date = date('Y-m-d', strtotime("-" . rand(0, 30) . " days"));
    $empId = rand(1, 10);
    $status = $empStatuses[array_rand($empStatuses)];
    
    try {
        $db->execute(
            "INSERT INTO employee_attendance (employee_id, attendance_date, status, check_in, check_out, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$empId, $date, $status, '09:00:00', '18:00:00']
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 7. Seed employee_leave_requests
echo "Seeding employee_leave_requests...\n";
$leaveTypes = ['casual', 'sick', 'earned', 'maternity'];
$leaveStatuses = ['pending', 'approved', 'rejected'];

for ($i = 0; $i < 20; $i++) {
    $empId = rand(1, 10);
    $type = $leaveTypes[array_rand($leaveTypes)];
    $status = $leaveStatuses[array_rand($leaveStatuses)];
    $from = date('Y-m-d', strtotime("+" . rand(1, 30) . " days"));
    $to = date('Y-m-d', strtotime($from . " +" . rand(1, 5) . " days"));
    
    try {
        $db->execute(
            "INSERT INTO employee_leave_requests (employee_id, leave_type, status, start_date, end_date, reason, applied_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [$empId, $type, $status, $from, $to, 'Personal reason']
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 8. Seed daily_operations_log
echo "Seeding daily_operations_log...\n";
$opTypes = ['site_visit', 'client_meeting', 'document_verification', 'payment_collection', 'survey', 'inspection'];

for ($i = 0; $i < 50; $i++) {
    $date = date('Y-m-d', strtotime("-" . rand(0, 30) . " days"));
    $type = $opTypes[array_rand($opTypes)];
    $status = ['completed', 'in_progress', 'pending'][array_rand([0,1,2])];
    
    try {
        $db->execute(
            "INSERT INTO daily_operations_log (log_date, operation_type, description, status, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$date, $type, 'Operation description for ' . $type, $status, rand(1, 10)]
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 9. Seed department_requests
echo "Seeding department_requests...\n";
$depts = ['SALES', 'FIN', 'MKTG', 'HR', 'IT', 'OPS', 'LAND', 'LEGAL'];
$reqStatuses = ['pending', 'assigned', 'in_progress', 'completed'];
$priorities = ['low', 'medium', 'high', 'urgent'];

for ($i = 0; $i < 30; $i++) {
    $dept = $depts[array_rand($depts)];
    $status = $reqStatuses[array_rand($reqStatuses)];
    $priority = $priorities[array_rand($priorities)];
    $created = date('Y-m-d', strtotime("-" . rand(0, 30) . " days"));
    $due = date('Y-m-d', strtotime($created . " +" . rand(1, 15) . " days"));
    
    try {
        $db->execute(
            "INSERT INTO department_requests (department_code, title, description, status, priority, requested_by, assigned_to, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$dept, 'Request ' . rand(1, 1000), 'Description for request', $status, $priority, rand(1, 10), rand(1, 10), $due]
        );
    } catch (\Exception $e) {}
}
echo "  Done.\n";

// 10. Update booking_payment_schedules
echo "Updating booking_payment_schedules for current period...\n";
try {
    $db->execute(
        "UPDATE booking_payment_schedules SET status='overdue' WHERE status='pending' AND due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND RAND() < 0.3"
    );
    $db->execute(
        "UPDATE booking_payment_schedules SET due_date = DATE_ADD(CURDATE(), INTERVAL FLOOR(RAND()*30) DAY) WHERE status='pending' AND due_date < CURDATE() LIMIT 20"
    );
} catch (\Exception $e) { echo "  Error: " . $e->getMessage() . "\n"; }
echo "  Done.\n";

// 11. Seed some mlm_commission_ledger for current month
echo "Seeding mlm_commission_ledger for current month...\n";
try {
    for ($i = 0; $i < 10; $i++) {
        $db->execute(
            "INSERT INTO mlm_commission_ledger (booking_id, beneficiary_user_id, source_user_id, commission_type, amount, payment_amount, status, created_at, paid_at, calculation_engine) VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW(), NOW(), 'hybrid')",
            [rand(1, 200), rand(1, 10), rand(1, 10), ['direct_sale', 'override', 'generation_bonus', 'matching_bonus', 'level_bonus', 'royalty_pool'][array_rand([0,1,2,3,4,5])], rand(500, 100000), rand(500, 100000)]
        );
    }
} catch (\Exception $e) { echo "  Error: " . $e->getMessage() . "\n"; }
echo "  Done.\n";

// 12. Seed some reports data
echo "Seeding reports tables...\n";
try {
    $db->execute("INSERT INTO finance_reports (report_type, report_date, data, generated_at) VALUES ('daily_collection', CURDATE(), '{\"total\": 150000}', NOW())");
    $db->execute("INSERT INTO finance_reports (report_type, report_date, data, generated_at) VALUES ('monthly_summary', DATE_FORMAT(CURDATE(), '%Y-%m-01'), '{\"revenue\": 5000000}', NOW())");
} catch (\Exception $e) {}
echo "  Done.\n";

echo "\n✅ Demo data seeding complete!\n";
echo "\nRun E2E tests to verify: node testing/visual_tests/E2E_MASTER_TEST.mjs\n";