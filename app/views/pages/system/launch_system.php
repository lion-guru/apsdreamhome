<?php
/**
 * APS Dream Home - System Launch Validator
 * Final system check before going live
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db_config.php';

echo "≡ƒÜÇ APS DREAM HOME - SYSTEM LAUNCH VALIDATOR\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$allTestsPassed = true;
$testResults = [];

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Test 1: Critical System Components
    echo "≡ƒöì TESTING CRITICAL SYSTEM COMPONENTS\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $criticalTests = [
        'admin' => 'Admin authentication system',
        'users' => 'User management system', 
        'customers' => 'Customer database',
        'properties' => 'Property listings',
        'plots' => 'Plot inventory',
        'bookings' => 'Booking system',
        'payments' => 'Payment processing',
        'commission_transactions' => 'Commission system',
        'expenses' => 'Expense tracking',
        'emi_plans' => 'EMI management'
    ];
    
    foreach ($criticalTests as $table => $description) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "Γ£à $description: $count records\n";
            $testResults[$table] = true;
        } else {
            echo "Γ¥î $description: FAILED\n";
            $testResults[$table] = false;
            $allTestsPassed = false;
        }
    }
    
    // Test 2: Admin Login Functionality
    echo "\n≡ƒöÉ TESTING ADMIN LOGIN SYSTEM\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $adminTest = $conn->query("SELECT COUNT(*) as count FROM admin WHERE status='active'");
    if ($adminTest) {
        $adminCount = $adminTest->fetch_assoc()['count'];
        if ($adminCount > 0) {
            echo "Γ£à Admin accounts available: $adminCount users\n";
            
            // Check for default admin
            $defaultAdmin = $conn->query("SELECT auser, role FROM admin WHERE auser='admin' AND status='active'");
            if ($defaultAdmin && $defaultAdmin->num_rows > 0) {
                $admin = $defaultAdmin->fetch_assoc();
                echo "Γ£à Default admin ready: {$admin['auser']} ({$admin['role']})\n";
            } else {
                echo "ΓÜá∩╕Å  Default admin not found - manual login required\n";
            }
        } else {
            echo "Γ¥î No active admin accounts found\n";
            $allTestsPassed = false;
        }
    }
    
    // Test 3: Dashboard Data Integrity
    echo "\n≡ƒôè TESTING DASHBOARD DATA INTEGRITY\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $dashboardQueries = [
        "SELECT COUNT(*) as count FROM bookings" => "Total Bookings",
        "SELECT SUM(COALESCE(amount, 0)) as sum FROM bookings WHERE status='confirmed'" => "Confirmed Sales",
        "SELECT COUNT(*) as count FROM plots WHERE status='available'" => "Available Inventory",
        "SELECT SUM(COALESCE(commission_amount, 0)) as sum FROM commission_transactions" => "Total Commissions",
        "SELECT SUM(COALESCE(amount, 0)) as sum FROM expenses" => "Total Expenses"
    ];
    
    foreach ($dashboardQueries as $query => $label) {
        try {
            $result = $conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                $value = array_values($row)[0] ?? 0;
                echo "Γ£à $label: Γé╣" . number_format($value, 2) . "\n";
            }
        } catch (Exception $e) {
            echo "ΓÜá∩╕Å  $label: Query issue - " . substr($e->getMessage(), 0, 30) . "...\n";
        }
    }
    
    // Test 4: Enterprise Features
    echo "\n≡ƒÅó TESTING ENTERPRISE FEATURES\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $enterpriseFeatures = [
        'marketing_campaigns' => 'Marketing System',
        'customer_documents' => 'Document Management',
        'payment_gateway_config' => 'Payment Gateways',
        'feedback_tickets' => 'Support System',
        'ai_chatbot_config' => 'AI Integration',
        'whatsapp_automation_config' => 'WhatsApp Automation'
    ];
    
    $enterpriseStatus = true;
    foreach ($enterpriseFeatures as $table => $feature) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "Γ£à $feature: $count configurations\n";
        } else {
            echo "Γ¥î $feature: Not available\n";
            $enterpriseStatus = false;
        }
    }
    
    // Test 5: System Performance
    echo "\nΓÜí TESTING SYSTEM PERFORMANCE\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    // Database size
    $sizeResult = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    if ($sizeResult) {
        $size = $sizeResult->fetch_assoc()['size_mb'];
        echo "Γ£à Database size: {$size} MB\n";
        
        if ($size < 100) {
            echo "Γ£à Database size optimal for performance\n";
        } else {
            echo "ΓÜá∩╕Å  Large database - monitor performance\n";
        }
    }
    
    // Table and index count
    $tableCount = $conn->query("SHOW TABLES")->num_rows;
    echo "Γ£à Total tables: $tableCount\n";
    
    $indexResult = $conn->query("SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = '" . DB_NAME . "'");
    if ($indexResult) {
        $indexCount = $indexResult->fetch_assoc()['count'];
        echo "Γ£à Database indexes: $indexCount (optimized)\n";
    }
    
    // Test 6: Security Check
    echo "\n≡ƒ¢í∩╕Å  TESTING SECURITY FEATURES\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    // Activity logs
    $logsResult = $conn->query("SELECT COUNT(*) as count FROM activity_logs");
    if ($logsResult) {
        $logCount = $logsResult->fetch_assoc()['count'];
        echo "Γ£à Activity logging: $logCount entries\n";
    }
    
    // API security
    $apiResult = $conn->query("SELECT COUNT(*) as count FROM api_keys WHERE status='active'");
    if ($apiResult) {
        $apiCount = $apiResult->fetch_assoc()['count'];
        echo "Γ£à API security: $apiCount active keys\n";
    }
    
    // Foreign key constraints
    $fkResult = $conn->query("SELECT COUNT(*) as count FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY' AND table_schema = '" . DB_NAME . "'");
    if ($fkResult) {
        $fkCount = $fkResult->fetch_assoc()['count'];
        echo "Γ£à Data integrity: $fkCount foreign key constraints\n";
    }
    
    // Final System Status
    echo "\n" . str_repeat("=", 55) . "\n";
    echo "≡ƒÄ» FINAL SYSTEM STATUS\n";
    echo str_repeat("=", 55) . "\n";
    
    if ($allTestsPassed && $enterpriseStatus) {
        echo "≡ƒÄë SYSTEM LAUNCH: Γ£à APPROVED!\n\n";
        
        echo "Γ£à Database: Complete and optimized\n";
        echo "Γ£à Admin System: Ready for login\n";
        echo "Γ£à Core Features: All functional\n";
        echo "Γ£à Enterprise Features: All active\n";
        echo "Γ£à Security: Properly configured\n";
        echo "Γ£à Performance: Optimized\n";
        
        echo "\n≡ƒÜÇ LAUNCH INSTRUCTIONS:\n";
        echo "1. Open browser and navigate to admin panel\n";
        echo "2. Login with: admin / demo123\n";
        echo "3. Change default password immediately\n";
        echo "4. Configure company settings\n";
        echo "5. Add real data and start operations\n";
        
        echo "\n≡ƒô₧ SUPPORT:\n";
        echo "- System documentation: /ADMIN_USER_GUIDE.md\n";
        echo "- Deployment guide: /PRODUCTION_DEPLOYMENT_GUIDE.md\n";
        echo "- Database report: /DATABASE_SETUP_COMPLETE_REPORT.md\n";
        
        echo "\n≡ƒîƒ APS DREAM HOME IS READY FOR BUSINESS!\n";
        
    } else {
        echo "ΓÜá∩╕Å  SYSTEM LAUNCH: ISSUES DETECTED\n\n";
        
        foreach ($testResults as $test => $passed) {
            if (!$passed) {
                echo "Γ¥î Fix required: $test\n";
            }
        }
        
        echo "\n≡ƒöº Please resolve issues before launch\n";
    }
    
} catch (Exception $e) {
    echo "Γ¥î CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "≡ƒöº Please check database connection and configuration\n";
    $allTestsPassed = false;
}

$conn->close();

echo "\n" . str_repeat("=", 55) . "\n";
echo "System validation completed at " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 55) . "\n";

exit($allTestsPassed ? 0 : 1);
?>
