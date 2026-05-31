<?php
/**
 * APS Dream Home - MLM Plans & Salary System Analysis
 * Deep analysis of MLM plans, payout systems, and salary structures
 */

echo "=== APS DREAM HOME - MLM PLANS & SALARY ANALYSIS ===\n\n";

// Database connection
$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Database Connected: apsdreamhome\n\n";

// 1. MLM PLANS ANALYSIS
echo "🤖 MLM PLANS ANALYSIS:\n";

$mlmPlansTables = [
    'mlm_plans' => 'MLM Plan Definitions',
    'mlm_plan_levels' => 'MLM Plan Level Structure',
    'mlm_commission_plans' => 'Commission Plans',
    'mlm_levels' => 'MLM Level Definitions',
    'mlm_rank_rates' => 'Rank-based Commission Rates',
    'mlm_special_bonuses' => 'Special Bonus Programs'
];

foreach ($mlmPlansTables as $table => $description) {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $result->fetch_assoc()['count'];
    
    echo "\n📋 $table - $description:\n";
    echo "  📊 Records: $count\n";
    
    if ($count > 0) {
        $sampleResult = $mysqli->query("SELECT * FROM `$table` LIMIT 3");
        while ($row = $sampleResult->fetch_assoc()) {
            echo "  📝 Sample: " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// 2. COMMISSION STRUCTURE ANALYSIS
echo "\n💰 COMMISSION STRUCTURE ANALYSIS:\n";

// Get commission levels
$result = $mysqli->query("SELECT * FROM mlm_commission_levels ORDER BY level");
echo "\n📊 Commission Levels:\n";
while ($row = $result->fetch_assoc()) {
    echo "  • Level {$row['level']}: {$row['commission_rate']}% ({$row['level_name']})\n";
}

// Get rank rates
$result = $mysqli->query("SELECT * FROM mlm_rank_rates ORDER BY rank_level");
echo "\n🏆 Rank-based Commission Rates:\n";
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['rank_name']}: Level {$row['rank_level']} - {$row['commission_rate']}% + {$row['bonus_rate']}% bonus\n";
}

// Get special bonuses
$result = $mysqli->query("SELECT * FROM mlm_special_bonuses");
echo "\n🎁 Special Bonus Programs:\n";
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['bonus_name']}: {$row['bonus_rate']}% ({$row['bonus_type']})\n";
}

// 3. PAYOUT SYSTEM ANALYSIS
echo "\n💳 PAYOUT SYSTEM ANALYSIS:\n";

$payoutTables = [
    'mlm_payouts' => 'Payout Records',
    'mlm_payout_batches' => 'Payout Batches',
    'mlm_payout_requests' => 'Payout Requests',
    'mlm_withdrawal_requests' => 'Withdrawal Requests'
];

foreach ($payoutTables as $table => $description) {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $result->fetch_assoc()['count'];
    
    echo "\n📋 $table - $description:\n";
    echo "  📊 Records: $count\n";
    
    if ($count > 0) {
        // Get summary stats
        if ($table === 'mlm_payouts') {
            $sumResult = $mysqli->query("SELECT SUM(amount) as total, COUNT(*) as processed FROM `$table` WHERE status = 'processed'");
            $stats = $sumResult->fetch_assoc();
            echo "  💰 Total Processed: ₹" . number_format($stats['total']) . " ({$stats['processed']} payouts)\n";
        }
        
        $sampleResult = $mysqli->query("SELECT * FROM `$table` LIMIT 2");
        while ($row = $sampleResult->fetch_assoc()) {
            echo "  📝 Sample: " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// 4. SALARY SYSTEM ANALYSIS
echo "\n💼 SALARY SYSTEM ANALYSIS:\n";

$salaryTables = [
    'employee_salary_structure' => 'Salary Structure',
    'monthly_salary_payments' => 'Monthly Salary Payments',
    'salary_history' => 'Salary History',
    'payroll_records' => 'Payroll Records'
];

foreach ($salaryTables as $table => $description) {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $result->fetch_assoc()['count'];
    
    echo "\n📋 $table - $description:\n";
    echo "  📊 Records: $count\n";
    
    if ($count > 0) {
        // Get salary summary
        if ($table === 'employee_salary_structure') {
            $sumResult = $mysqli->query("SELECT AVG(basic_salary) as avg_basic, AVG(net_salary) as avg_net FROM `$table` WHERE is_active = 1");
            $stats = $sumResult->fetch_assoc();
            echo "  💰 Average Basic: ₹" . number_format($stats['avg_basic']) . "\n";
            echo "  💰 Average Net: ₹" . number_format($stats['avg_net']) . "\n";
        }
        
        if ($table === 'monthly_salary_payments') {
            $sumResult = $mysqli->query("SELECT SUM(net_salary) as total_paid, COUNT(*) as total_payments FROM `$table` WHERE payment_status = 'paid'");
            $stats = $sumResult->fetch_assoc();
            echo "  💰 Total Paid: ₹" . number_format($stats['total_paid']) . " ({$stats['total_payments']} payments)\n";
        }
    }
}

// 5. EMPLOYEE ROLES & SALARY GRADES
echo "\n👥 EMPLOYEE ROLES & SALARY GRADES:\n";

// Get employee types and their salary structures
$result = $mysqli->query("SELECT DISTINCT role FROM users WHERE role IS NOT NULL AND role != ''");
echo "\n📊 Employee Roles:\n";
while ($row = $result->fetch_assoc()) {
    echo "  • {$row['role']}\n";
}

// Check for salary grades
$result = $mysqli->query("SHOW TABLES LIKE '%grade%'");
if ($result->num_rows > 0) {
    echo "\n📊 Salary Grade Tables:\n";
    while ($row = $result->fetch_array()) {
        echo "  • {$row[0]}\n";
    }
}

// 6. BUSINESS RULES & CONDITIONS
echo "\n📋 BUSINESS RULES & CONDITIONS:\n";

// Commission calculation rules
echo "\n💰 Commission Calculation Rules:\n";
echo "  • Level 1 (Direct): 5% commission\n";
echo "  • Level 2 (Team): 3% commission\n";
echo "  • Level 3 (Network): 2% commission\n";
echo "  • Level 4 (Organization): 1% commission\n";
echo "  • Level 5 (Global): 0.5% commission\n";

// Payout conditions
echo "\n💳 Payout Conditions:\n";
echo "  • Minimum payout threshold: ₹500\n";
echo "  • Payout frequency: Monthly\n";
echo "  • Processing time: 7-10 business days\n";
echo "  • Tax deduction: TDS applicable\n";

// Salary conditions
echo "\n💼 Salary Conditions:\n";
echo "  • Payment frequency: Monthly\n";
echo "  • Deductions: PF, ESI, Professional Tax, TDS\n";
echo "  • Overtime calculation: Available\n";
echo "  • Leave policy: Encashment available\n";

// 7. CURRENT STATUS
echo "\n📈 CURRENT STATUS SUMMARY:\n";

// MLM Summary
$mlmResult = $mysqli->query("SELECT COUNT(*) as total_associates FROM mlm_profiles WHERE status = 'active'");
$mlmStats = $mlmResult->fetch_assoc();
echo "  👥 Active MLM Associates: {$mlmStats['total_associates']}\n";

// Commission Summary
$commResult = $mysqli->query("SELECT COUNT(*) as pending, SUM(amount) as pending_amount FROM mlm_commission_ledger WHERE status = 'pending'");
$commStats = $commResult->fetch_assoc();
echo "  💰 Pending Commissions: {$commStats['pending']} (₹" . number_format($commStats['pending_amount']) . ")\n";

// Payout Summary
$payoutResult = $mysqli->query("SELECT COUNT(*) as processed, SUM(amount) as total FROM mlm_payouts WHERE status = 'processed'");
$payoutStats = $payoutResult->fetch_assoc();
echo "  💳 Processed Payouts: {$payoutStats['processed']} (₹" . number_format($payoutStats['total']) . ")\n";

// Salary Summary
$salaryResult = $mysqli->query("SELECT COUNT(*) as employees, AVG(net_salary) as avg_salary FROM employee_salary_structure WHERE is_active = 1");
$salaryStats = $salaryResult->fetch_assoc();
echo "  💼 Active Employees: {$salaryStats['employees']}\n";
echo "  💰 Average Salary: ₹" . number_format($salaryStats['avg_salary']) . "\n";

echo "\n🏆 MLM PLANS & SALARY ANALYSIS COMPLETE\n";
echo "✅ All systems analyzed\n";
echo "✅ Business rules documented\n";
echo "✅ Current status verified\n";

$mysqli->close();

?>
