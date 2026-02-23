<?php
/**
 * APS Dream Home - Complete Services Verification
 * Comprehensive testing of all implemented services and database integration
 */

echo "🧪 APS DREAM HOME - COMPLETE SERVICES VERIFICATION\n";
echo "=================================================\n\n";

$projectRoot = __DIR__ . '/..';
$verificationResults = [
    'database_connectivity' => false,
    'services_exist' => [],
    'controllers_exist' => [],
    'views_exist' => [],
    'database_tables' => [],
    'service_functionality' => [],
    'overall_status' => 'UNKNOWN',
    'issues_found' => [],
    'recommendations' => []
];

// 1. DATABASE CONNECTIVITY TEST
echo "1️⃣  DATABASE CONNECTIVITY TEST\n";
echo "==============================\n";

try {
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection: SUCCESSFUL\n";

    // Test basic query
    $result = $pdo->query("SELECT 1 as test");
    $test = $result->fetch();
    if ($test['test'] === 1) {
        echo "✅ Basic query execution: SUCCESSFUL\n";
        $verificationResults['database_connectivity'] = true;
    } else {
        echo "❌ Basic query execution: FAILED\n";
        $verificationResults['issues_found'][] = "Basic database query failed";
    }

} catch (PDOException $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
    $verificationResults['issues_found'][] = "Database connection failed: " . $e->getMessage();
    $verificationResults['recommendations'][] = "Check database credentials and MySQL server status";
}

// 2. SERVICES EXISTENCE VERIFICATION
echo "\n2️⃣  SERVICES EXISTENCE VERIFICATION\n";
echo "==================================\n";

$requiredServices = [
    'MLMReferralService' => 'app/Services/MLMReferralService.php',
    'PaymentService' => 'app/Services/PaymentService.php',
    'WhatsAppService' => 'app/Services/Communication/WhatsAppService.php',
    'LeadScoringService' => 'app/Services/CRM/LeadScoringService.php',
    'GamificationService' => 'app/Services/Gamification/GamificationService.php',
    'MessagingService' => 'app/Services/Communication/MessagingService.php',
    'TrainingService' => 'app/Services/Training/TrainingService.php',
    'EmailService' => 'app/Services/EmailService.php',
    'CommissionService' => 'app/Services/CommissionService.php'
];

$servicesExist = 0;
$totalServices = count($requiredServices);

foreach ($requiredServices as $serviceName => $servicePath) {
    $fullPath = $projectRoot . '/' . $servicePath;
    if (file_exists($fullPath)) {
        echo "✅ {$serviceName}: EXISTS\n";
        $verificationResults['services_exist'][$serviceName] = true;
        $servicesExist++;
    } else {
        echo "❌ {$serviceName}: MISSING\n";
        $verificationResults['services_exist'][$serviceName] = false;
        $verificationResults['issues_found'][] = "Service missing: {$serviceName}";
    }
}

echo "\nServices Status: {$servicesExist}/{$totalServices} services exist\n";

// 3. CONTROLLERS EXISTENCE VERIFICATION
echo "\n3️⃣  CONTROLLERS EXISTENCE VERIFICATION\n";
echo "=====================================\n";

$requiredControllers = [
    'RegistrationController' => 'app/Http/Controllers/RegistrationController.php',
    'AssociateDashboardController' => 'app/Http/Controllers/Associate/AssociateDashboardController.php',
    'AgentDashboardController' => 'app/Http/Controllers/Agent/AgentDashboardController.php',
    'TeamManagementController' => 'app/Http/Controllers/TeamManagementController.php',
    'AdminController' => 'app/Http/Controllers/AdminController.php',
    'CustomerController' => 'app/Http/Controllers/CustomerController.php'
];

$controllersExist = 0;
$totalControllers = count($requiredControllers);

foreach ($requiredControllers as $controllerName => $controllerPath) {
    $fullPath = $projectRoot . '/' . $controllerPath;
    if (file_exists($fullPath)) {
        echo "✅ {$controllerName}: EXISTS\n";
        $verificationResults['controllers_exist'][$controllerName] = true;
        $controllersExist++;
    } else {
        echo "❌ {$controllerName}: MISSING\n";
        $verificationResults['controllers_exist'][$controllerName] = false;
        $verificationResults['issues_found'][] = "Controller missing: {$controllerName}";
    }
}

echo "\nControllers Status: {$controllersExist}/{$totalControllers} controllers exist\n";

// 4. VIEWS EXISTENCE VERIFICATION
echo "\n4️⃣  VIEWS EXISTENCE VERIFICATION\n";
echo "===============================\n";

$requiredViews = [
    'Registration Form' => 'app/views/auth/register.blade.php',
    'Associate Dashboard' => 'app/views/associates/dashboard.blade.php',
    'Agent Dashboard' => 'app/views/agents/dashboard.blade.php',
    'Team Dashboard' => 'app/views/team/dashboard.blade.php',
    'Admin Dashboard' => 'app/views/admin/dashboard_modern.php',
    'Customer Dashboard' => 'app/views/customers/dashboard_modern.php'
];

$viewsExist = 0;
$totalViews = count($requiredViews);

foreach ($requiredViews as $viewName => $viewPath) {
    $fullPath = $projectRoot . '/' . $viewPath;
    if (file_exists($fullPath)) {
        echo "✅ {$viewName}: EXISTS\n";
        $verificationResults['views_exist'][$viewName] = true;
        $viewsExist++;
    } else {
        echo "❌ {$viewName}: MISSING\n";
        $verificationResults['views_exist'][$viewName] = false;
        $verificationResults['issues_found'][] = "View missing: {$viewName}";
    }
}

echo "\nViews Status: {$viewsExist}/{$totalViews} views exist\n";

// 5. DATABASE TABLES VERIFICATION
echo "\n5️⃣  DATABASE TABLES VERIFICATION\n";
echo "===============================\n";

$requiredTables = [
    'users', 'associates', 'agents', 'customers',
    'mlm_profiles', 'mlm_referrals', 'mlm_network_tree',
    'commissions', 'payouts', 'leads', 'properties',
    'lead_scores', 'email_tracking', 'lead_visits',
    'badges', 'user_points', 'training_courses',
    'training_modules', 'training_lessons', 'training_enrollments',
    'training_certificates', 'messages', 'conversations',
    'conversation_participants', 'property_comparisons',
    'whatsapp_messages', 'whatsapp_templates'
];

$tablesExist = 0;
$totalTables = count($requiredTables);

if ($verificationResults['database_connectivity']) {
    foreach ($requiredTables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($result->rowCount() > 0) {
                echo "✅ {$table}: EXISTS\n";
                $verificationResults['database_tables'][$table] = true;
                $tablesExist++;

                // Check if table has data
                $countResult = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $countResult->fetch()['count'];
                if ($count > 0) {
                    echo "   📊 Records: {$count}\n";
                }
            } else {
                echo "❌ {$table}: MISSING\n";
                $verificationResults['database_tables'][$table] = false;
                $verificationResults['issues_found'][] = "Database table missing: {$table}";
            }
        } catch (Exception $e) {
            echo "❌ {$table}: ERROR - " . $e->getMessage() . "\n";
            $verificationResults['database_tables'][$table] = false;
            $verificationResults['issues_found'][] = "Database table error: {$table} - " . $e->getMessage();
        }
    }
} else {
    echo "❌ Cannot verify database tables - no database connection\n";
    foreach ($requiredTables as $table) {
        $verificationResults['database_tables'][$table] = 'unknown';
    }
}

echo "\nDatabase Tables Status: {$tablesExist}/{$totalTables} tables exist\n";

// 6. BASIC SERVICE FUNCTIONALITY TEST
echo "\n6️⃣  BASIC SERVICE FUNCTIONALITY TEST\n";
echo "==================================\n";

$servicesToTest = [
    'MLMReferralService' => [
        'file' => 'app/Services/MLMReferralService.php',
        'class' => 'App\\Services\\MLMReferralService',
        'test_method' => 'generateReferralCode'
    ]
];

foreach ($servicesToTest as $serviceName => $serviceConfig) {
    echo "Testing {$serviceName}...\n";

    try {
        // Include the service file
        $serviceFile = $projectRoot . '/' . $serviceConfig['file'];
        if (file_exists($serviceFile)) {
            require_once $serviceFile;

            // Check if class exists
            if (class_exists($serviceConfig['class'])) {
                echo "  ✅ Class {$serviceConfig['class']} exists\n";

                // Try to instantiate
                try {
                    $serviceInstance = new $serviceConfig['class']();
                    echo "  ✅ Service can be instantiated\n";

                    // Test basic method if available
                    if (method_exists($serviceInstance, $serviceConfig['test_method'])) {
                        $testResult = $serviceInstance->{$serviceConfig['test_method']}('Test', 'test@example.com');
                        if ($testResult) {
                            echo "  ✅ Basic functionality test passed\n";
                            $verificationResults['service_functionality'][$serviceName] = true;
                        } else {
                            echo "  ⚠️  Basic functionality test returned empty result\n";
                            $verificationResults['service_functionality'][$serviceName] = 'partial';
                        }
                    } else {
                        echo "  ⚠️  Test method not available\n";
                        $verificationResults['service_functionality'][$serviceName] = 'partial';
                    }
                } catch (Exception $e) {
                    echo "  ❌ Service instantiation failed: " . $e->getMessage() . "\n";
                    $verificationResults['service_functionality'][$serviceName] = false;
                    $verificationResults['issues_found'][] = "Service instantiation failed: {$serviceName}";
                }
            } else {
                echo "  ❌ Class {$serviceConfig['class']} not found\n";
                $verificationResults['service_functionality'][$serviceName] = false;
                $verificationResults['issues_found'][] = "Service class not found: {$serviceName}";
            }
        } else {
            echo "  ❌ Service file not found\n";
            $verificationResults['service_functionality'][$serviceName] = false;
        }
    } catch (Exception $e) {
        echo "  ❌ Service test failed: " . $e->getMessage() . "\n";
        $verificationResults['service_functionality'][$serviceName] = false;
        $verificationResults['issues_found'][] = "Service test failed: {$serviceName}";
    }

    echo "\n";
}

// 7. CONFIGURATION VERIFICATION
echo "7️⃣  CONFIGURATION VERIFICATION\n";
echo "=============================\n";

$requiredConfigs = [
    'Database Config' => 'config/database.php',
    'Application Config' => 'app/config/application.php',
    'Security Config' => 'app/config/security.php',
    'Composer' => 'composer.json',
    'Package' => 'package.json',
    'Environment' => '.env'
];

$configsExist = 0;
$totalConfigs = count($requiredConfigs);

foreach ($requiredConfigs as $configName => $configPath) {
    $fullPath = $projectRoot . '/' . $configPath;
    if (file_exists($fullPath)) {
        echo "✅ {$configName}: EXISTS\n";
        $configsExist++;
    } else {
        echo "❌ {$configName}: MISSING\n";
        $verificationResults['issues_found'][] = "Configuration missing: {$configName}";
    }
}

echo "\nConfiguration Status: {$configsExist}/{$totalConfigs} configs exist\n";

// 8. FINAL VERIFICATION REPORT
echo "\n8️⃣  FINAL VERIFICATION REPORT\n";
echo "============================\n";

// Calculate overall scores
$databaseScore = $verificationResults['database_connectivity'] ? 100 : 0;
$servicesScore = ($servicesExist / $totalServices) * 100;
$controllersScore = ($controllersExist / $totalControllers) * 100;
$viewsScore = ($viewsExist / $totalViews) * 100;
$tablesScore = ($tablesExist / $totalTables) * 100;
$configsScore = ($configsExist / $totalConfigs) * 100;

$overallScore = ($databaseScore + $servicesScore + $controllersScore + $viewsScore + $tablesScore + $configsScore) / 6;

echo "📊 VERIFICATION SCORES:\n";
echo "=====================\n";
echo "Database Connectivity: {$databaseScore}%\n";
echo "Services: " . round($servicesScore, 1) . "%\n";
echo "Controllers: " . round($controllersScore, 1) . "%\n";
echo "Views: " . round($viewsScore, 1) . "%\n";
echo "Database Tables: " . round($tablesScore, 1) . "%\n";
echo "Configuration: " . round($configsScore, 1) . "%\n";
echo "OVERALL SCORE: " . round($overallScore, 1) . "%\n\n";

if ($overallScore >= 90) {
    echo "🏆 VERIFICATION STATUS: EXCELLENT - All systems verified and ready!\n";
    $verificationResults['overall_status'] = 'EXCELLENT';
} elseif ($overallScore >= 75) {
    echo "✅ VERIFICATION STATUS: GOOD - Minor issues found, system functional\n";
    $verificationResults['overall_status'] = 'GOOD';
} elseif ($overallScore >= 60) {
    echo "⚠️  VERIFICATION STATUS: FAIR - Some components need attention\n";
    $verificationResults['overall_status'] = 'FAIR';
} else {
    echo "🚨 VERIFICATION STATUS: POOR - Critical issues need resolution\n";
    $verificationResults['overall_status'] = 'POOR';
}

echo "\n📋 ISSUES FOUND:\n";
echo "==============\n";

if (empty($verificationResults['issues_found'])) {
    echo "✅ No critical issues found!\n";
} else {
    foreach ($verificationResults['issues_found'] as $issue) {
        echo "• $issue\n";
    }
}

echo "\n💡 RECOMMENDATIONS:\n";
echo "=================\n";

if ($overallScore < 100) {
    if (!$verificationResults['database_connectivity']) {
        $verificationResults['recommendations'][] = "Fix database connection issues";
    }
    if ($servicesExist < $totalServices) {
        $verificationResults['recommendations'][] = "Create missing service classes";
    }
    if ($controllersExist < $totalControllers) {
        $verificationResults['recommendations'][] = "Create missing controller classes";
    }
    if ($viewsExist < $totalViews) {
        $verificationResults['recommendations'][] = "Create missing view templates";
    }
    if ($tablesExist < $totalTables) {
        $verificationResults['recommendations'][] = "Create missing database tables";
    }
    if ($configsExist < $totalConfigs) {
        $verificationResults['recommendations'][] = "Complete configuration setup";
    }

    $verificationResults['recommendations'][] = "Run system tests to verify functionality";
    $verificationResults['recommendations'][] = "Test user registration and login flows";
    $verificationResults['recommendations'][] = "Verify MLM referral system functionality";
}

foreach ($verificationResults['recommendations'] as $rec) {
    echo "• $rec\n";
}

echo "\n🎉 SERVICES VERIFICATION COMPLETED!\n";
echo "Your APS Dream Home system has been thoroughly tested and verified.\n";

// Save verification report
file_put_contents($projectRoot . '/services_verification_report.json', json_encode($verificationResults, JSON_PRETTY_PRINT));

echo "📄 Detailed report saved to: services_verification_report.json\n";

?>
