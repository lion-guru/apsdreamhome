<?php
/**
 * APS Dream Home - Services & Database Tables Status Check
 * Comprehensive analysis of services and their database requirements
 */

echo "🔍 APS DREAM HOME - SERVICES & DATABASE TABLES STATUS\n";
echo "====================================================\n\n";

// Services to check
$servicesToCheck = [
    'PaymentService' => ['payments', 'invoices'],
    'WhatsAppService' => ['whatsapp_messages', 'whatsapp_templates'],
    'LeadScoringService' => ['lead_scores', 'email_tracking', 'lead_visits'],
    'GamificationService' => ['badges', 'user_badges', 'user_points'],
    'MessagingService' => ['messages', 'conversations', 'conversation_participants'],
    'TrainingService' => ['training_courses', 'training_modules', 'training_lessons', 'training_enrollments', 'training_certificates'],
    'InvoiceService' => ['invoices'],
    'GSTTaxReportService' => ['purchase_invoices'],
    'PropertyComparisonService' => ['property_comparisons']
];

$projectRoot = __DIR__ . '/..';

echo "📋 SERVICE EXISTENCE CHECK\n";
echo "=========================\n";

$servicesStatus = [];
$totalServices = 0;
$existingServices = 0;

foreach ($servicesToCheck as $serviceName => $tables) {
    $totalServices++;
    $serviceExists = false;

    // Check different possible locations
    $possiblePaths = [
        "app/Services/{$serviceName}.php",
        "app/Services/{$serviceName}/{$serviceName}.php",
        "app/Services/CRM/{$serviceName}.php",
        "app/Services/Communication/{$serviceName}.php",
        "app/Services/Finance/{$serviceName}.php",
        "app/Services/Gamification/{$serviceName}.php",
        "app/Services/Property/{$serviceName}.php",
        "app/Services/Training/{$serviceName}.php",
        "app/Services/Payment/{$serviceName}.php"
    ];

    foreach ($possiblePaths as $path) {
        if (file_exists($projectRoot . '/' . $path)) {
            $serviceExists = true;
            $servicesStatus[$serviceName]['service_file'] = $path;
            break;
        }
    }

    if ($serviceExists) {
        echo "✅ {$serviceName}: EXISTS\n";
        $existingServices++;
    } else {
        echo "❌ {$serviceName}: MISSING\n";
    }

    $servicesStatus[$serviceName]['exists'] = $serviceExists;
    $servicesStatus[$serviceName]['tables'] = $tables;
}

echo "\nServices Status: {$existingServices}/{$totalServices} services exist\n\n";

echo "🗄️  DATABASE TABLES CHECK\n";
echo "========================\n";

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

$tablesStatus = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ Database connection successful\n\n";

    // Get all existing tables
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $existingTables = array_flip($existingTables);

    $totalTablesNeeded = 0;
    $tablesExist = 0;

    foreach ($servicesToCheck as $serviceName => $tables) {
        echo "Service: {$serviceName}\n";
        $tablesStatus[$serviceName] = [];

        foreach ($tables as $table) {
            $totalTablesNeeded++;
            if (isset($existingTables[$table])) {
                echo "  ✅ {$table}: EXISTS\n";
                $tablesStatus[$serviceName][$table] = true;
                $tablesExist++;
            } else {
                echo "  ❌ {$table}: MISSING\n";
                $tablesStatus[$serviceName][$table] = false;
            }
        }
        echo "\n";
    }

    echo "Tables Status: {$tablesExist}/{$totalTablesNeeded} tables exist\n\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Cannot check table existence\n\n";
}

echo "📜 DATABASE SCRIPTS CHECK\n";
echo "=========================\n";

$databaseScripts = [
    'PaymentService' => [
        'create_payment_system_tables.php',
        'create_invoice_system_tables.php',
        'setup/create_payment_tables.php'
    ],
    'WhatsAppService' => [
        'create_whatsapp_integration_tables.php',
        'setup/create_whatsapp_tables.php'
    ],
    'LeadScoringService' => [
        'create_lead_scoring_tables.php',
        'setup/create_lead_scoring_tables.php'
    ],
    'GamificationService' => [
        'create_gamification_tables.php',
        'setup/create_gamification_tables.php'
    ],
    'MessagingService' => [
        'create_messaging_tables.php',
        'setup/create_messaging_tables.php'
    ],
    'TrainingService' => [
        'create_training_module_tables.php',
        'setup/create_training_tables.php'
    ],
    'InvoiceService' => [
        'create_invoice_system_tables.php',
        'setup/create_invoice_tables.php'
    ],
    'GSTTaxReportService' => [
        'create_gst_tax_reporting_tables.php',
        'setup/create_gst_tables.php'
    ],
    'PropertyComparisonService' => [
        'create_property_comparison_tables.php',
        'setup/create_property_comparison_tables.php'
    ]
];

$scriptsStatus = [];
$totalScripts = 0;
$existingScripts = 0;

foreach ($databaseScripts as $serviceName => $scripts) {
    echo "Service: {$serviceName}\n";
    $scriptsStatus[$serviceName] = [];

    foreach ($scripts as $script) {
        $totalScripts++;
        $scriptPath = $projectRoot . '/database/scripts/' . $script;

        if (file_exists($scriptPath)) {
            echo "  ✅ {$script}: EXISTS\n";
            $scriptsStatus[$serviceName][$script] = true;
            $existingScripts++;
        } else {
            echo "  ❌ {$script}: MISSING\n";
            $scriptsStatus[$serviceName][$script] = false;
        }
    }
    echo "\n";
}

echo "Scripts Status: {$existingScripts}/{$totalScripts} scripts exist\n\n";

echo "📊 COMPREHENSIVE STATUS SUMMARY\n";
echo "==============================\n";

$summary = [
    'services' => [
        'total' => count($servicesToCheck),
        'existing' => $existingServices,
        'missing' => count($servicesToCheck) - $existingServices
    ],
    'tables' => [
        'total' => $totalTablesNeeded,
        'existing' => $tablesExist,
        'missing' => $totalTablesNeeded - $tablesExist
    ],
    'scripts' => [
        'total' => $totalScripts,
        'existing' => $existingScripts,
        'missing' => $totalScripts - $existingScripts
    ]
];

echo "SERVICES:\n";
echo "  Total: {$summary['services']['total']}\n";
echo "  Existing: {$summary['services']['existing']}\n";
echo "  Missing: {$summary['services']['missing']}\n\n";

echo "DATABASE TABLES:\n";
echo "  Total: {$summary['tables']['total']}\n";
echo "  Existing: {$summary['tables']['existing']}\n";
echo "  Missing: {$summary['tables']['missing']}\n\n";

echo "DATABASE SCRIPTS:\n";
echo "  Total: {$summary['scripts']['total']}\n";
echo "  Existing: {$summary['scripts']['existing']}\n";
echo "  Missing: {$summary['scripts']['missing']}\n\n";

// Calculate overall health score
$servicesScore = ($existingServices / $summary['services']['total']) * 100;
$tablesScore = ($tablesExist / $summary['tables']['total']) * 100;
$scriptsScore = ($existingScripts / $summary['scripts']['total']) * 100;

$overallScore = ($servicesScore + $tablesScore + $scriptsScore) / 3;

echo "🏥 HEALTH SCORES\n";
echo "===============\n";
echo "Services: " . round($servicesScore, 1) . "%\n";
echo "Tables: " . round($tablesScore, 1) . "%\n";
echo "Scripts: " . round($scriptsScore, 1) . "%\n";
echo "Overall: " . round($overallScore, 1) . "%\n\n";

if ($overallScore >= 90) {
    echo "🏆 EXCELLENT: All services and tables are properly set up!\n";
} elseif ($overallScore >= 70) {
    echo "✅ GOOD: Most components are in place.\n";
} elseif ($overallScore >= 50) {
    echo "⚠️  FAIR: Some components need attention.\n";
} else {
    echo "🚨 POOR: Significant components are missing.\n";
}

echo "\n💡 RECOMMENDATIONS\n";
echo "==================\n";

$recommendations = [];

// Services recommendations
if ($summary['services']['missing'] > 0) {
    $recommendations[] = "Create {$summary['services']['missing']} missing service classes";
}

// Tables recommendations
if ($summary['tables']['missing'] > 0) {
    $recommendations[] = "Create and run database scripts for {$summary['tables']['missing']} missing tables";
}

// Scripts recommendations
if ($summary['scripts']['missing'] > 0) {
    $recommendations[] = "Create {$summary['scripts']['missing']} missing database migration scripts";
}

echo "Priority Actions:\n";
foreach ($recommendations as $i => $rec) {
    echo ($i + 1) . ". $rec\n";
}

echo "\n🔧 MISSING COMPONENTS\n";
echo "====================\n";

// List missing services
$missingServices = array_filter($servicesStatus, fn($s) => !$s['exists']);
if (!empty($missingServices)) {
    echo "Missing Services:\n";
    foreach ($missingServices as $name => $status) {
        echo "  • $name\n";
    }
    echo "\n";
}

// List missing tables
$missingTables = [];
foreach ($tablesStatus as $service => $tables) {
    foreach ($tables as $table => $exists) {
        if (!$exists) {
            $missingTables[] = $table;
        }
    }
}
if (!empty($missingTables)) {
    echo "Missing Tables:\n";
    foreach ($missingTables as $table) {
        echo "  • $table\n";
    }
    echo "\n";
}

// List missing scripts
$missingScripts = [];
foreach ($scriptsStatus as $service => $scripts) {
    foreach ($scripts as $script => $exists) {
        if (!$exists) {
            $missingScripts[] = $script;
        }
    }
}
if (!empty($missingScripts)) {
    echo "Missing Database Scripts:\n";
    foreach ($missingScripts as $script) {
        echo "  • $script\n";
    }
}

// Save comprehensive report
$comprehensiveReport = [
    'scan_date' => date('Y-m-d H:i:s'),
    'services_status' => $servicesStatus,
    'tables_status' => $tablesStatus,
    'scripts_status' => $scriptsStatus,
    'summary' => $summary,
    'health_scores' => [
        'services' => round($servicesScore, 1),
        'tables' => round($tablesScore, 1),
        'scripts' => round($scriptsScore, 1),
        'overall' => round($overallScore, 1)
    ],
    'missing_components' => [
        'services' => array_keys($missingServices),
        'tables' => $missingTables,
        'scripts' => $missingScripts
    ]
];

file_put_contents($projectRoot . '/services_database_status.json', json_encode($comprehensiveReport, JSON_PRETTY_PRINT));

echo "\n📄 Comprehensive report saved to: services_database_status.json\n";

echo "\n🎉 STATUS CHECK COMPLETED!\n";
echo "Your APS Dream Home services and database tables have been thoroughly analyzed.\n";

?>
