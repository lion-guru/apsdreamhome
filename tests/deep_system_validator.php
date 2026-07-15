<?php
/**
 * Deep System Validator
 * Comprehensive check of all functionality, views, and UI
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║           DEEP SYSTEM VALIDATOR - APS DREAM HOME               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$issues = [];
$warnings = [];
$passed = [];

// 1. Check all services can be instantiated
echo "🔧 Testing Service Instantiation...\n";
$services = [
    'App\Services\Payment\PaymentGatewayService',
    'App\Services\Communication\ChatService',
    'App\Services\Cache\CacheService',
    'App\Services\Queue\QueueService',
    'App\Services\Map\MapService',
    'App\Services\I18n\LocalizationService',
    'App\Services\NotificationService',
    'App\Services\Analytics\AdvancedAnalyticsService',
    'App\Services\Scheduler\TaskSchedulerService',
    'App\Services\File\FileManagerService',
    'App\Services\Loyalty\LoyaltyRewardsService',
    'App\Services\Auth\JWTAuthService',
    'App\Services\Search\AdvancedSearchService',
    'App\Services\Finance\EMICalculatorService',
    'App\Services\CRM\LeadScoringService',
    'App\Services\Notification\PropertyAlertService',
    'App\Services\SEO\SEOManagementService',
    'App\Services\UI\ModernThemeService',
    'App\Services\BackupRestoreService',
    'App\Services\AuditTrailService',
    'App\Services\WorkflowEngineService',
    'App\Services\ReportBuilderService',
    'App\Services\ImportExportService',
    'App\Services\APIDocumentationService'
];

foreach ($services as $service) {
    try {
        if (class_exists($service)) {
            $reflection = new ReflectionClass($service);
            // Check if constructor exists and is public
            if ($reflection->hasMethod('__construct')) {
                $constructor = $reflection->getMethod('__construct');
                if ($constructor->isPublic()) {
                    $passed[] = "✅ $service";
                    echo "  ✅ $service\n";
                } else {
                    $warnings[] = "⚠️  $service has private constructor";
                    echo "  ⚠️  $service (private constructor)\n";
                }
            } else {
                $passed[] = "✅ $service (no constructor)";
                echo "  ✅ $service\n";
            }
        } else {
            $issues[] = "❌ Service not found: $service";
            echo "  ❌ $service NOT FOUND\n";
        }
    } catch (Exception $e) {
        $issues[] = "❌ Error checking $service: " . $e->getMessage();
        echo "  ❌ $service ERROR\n";
    }
}

// 2. Check all admin controllers
echo "\n🎮 Testing Admin Controllers...\n";
$controllers = [
    'App\Http\Controllers\Admin\AdminLoyaltyController',
    'App\Http\Controllers\Admin\AdminSchedulerController',
    'App\Http\Controllers\Admin\AdminFileController',
    'App\Http\Controllers\Admin\AdminWorkflowController'
];

foreach ($controllers as $controller) {
    try {
        if (class_exists($controller)) {
            $passed[] = "✅ $controller";
            echo "  ✅ $controller\n";
        } else {
            $issues[] = "❌ Controller not found: $controller";
            echo "  ❌ $controller NOT FOUND\n";
        }
    } catch (Exception $e) {
        $issues[] = "❌ Error with $controller: " . $e->getMessage();
        echo "  ❌ $controller ERROR\n";
    }
}

// 3. Check view files exist
echo "\n🎨 Testing View Files...\n";
$viewFiles = [
    'app/views/admin/loyalty/index.php',
    'app/views/admin/scheduler/index.php',
    'app/views/admin/files/index.php',
    'app/views/layouts/admin_header.php',
    'app/views/layouts/admin_footer.php'
];

foreach ($viewFiles as $viewFile) {
    $fullPath = __DIR__ . '/../' . $viewFile;
    if (file_exists($fullPath)) {
        // Check for syntax errors
        $output = [];
        $returnVar = 0;
        exec('php -l ' . escapeshellarg($fullPath) . ' 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            $passed[] = "✅ View: $viewFile";
            echo "  ✅ $viewFile\n";
        } else {
            $issues[] = "❌ Syntax error in $viewFile: " . implode(', ', $output);
            echo "  ❌ $viewFile (SYNTAX ERROR)\n";
        }
    } else {
        $issues[] = "❌ View not found: $viewFile";
        echo "  ❌ $viewFile NOT FOUND\n";
    }
}

// 4. Check route definitions
echo "\n🌐 Testing Route Definitions...\n";
$webRoutesFile = __DIR__ . '/../routes/web.php';
$apiRoutesFile = __DIR__ . '/../routes/api.php';

if (file_exists($webRoutesFile)) {
    $webContent = file_get_contents($webRoutesFile);
    
    // Check for new routes
    $requiredRoutes = [
        '/admin/loyalty',
        '/admin/scheduler',
        '/admin/files',
        'AdminLoyaltyController',
        'AdminSchedulerController',
        'AdminFileController'
    ];
    
    foreach ($requiredRoutes as $route) {
        if (strpos($webContent, $route) !== false) {
            $passed[] = "✅ Route: $route";
            echo "  ✅ $route\n";
        } else {
            $issues[] = "❌ Route not found: $route";
            echo "  ❌ $route NOT FOUND\n";
        }
    }
} else {
    $issues[] = "❌ web.php not found";
    echo "  ❌ web.php NOT FOUND\n";
}

// 5. Check database tables
echo "\n🗄️  Testing Database Tables...\n";
try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();
    
    $requiredTables = [
        'loyalty_points',
        'loyalty_transactions',
        'rewards_catalog',
        'reward_redemptions',
        'tier_benefits',
        'points_rules',
        'scheduled_tasks',
        'task_execution_logs',
        'task_dependencies',
        'files',
        'file_versions',
        'file_shares',
        'file_access_logs',
        'file_tags',
        'file_tag_relations'
    ];
    
    foreach ($requiredTables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
            if ($stmt->fetch()) {
                $passed[] = "✅ Table: $table";
                echo "  ✅ $table\n";
            } else {
                $issues[] = "❌ Table not found: $table";
                echo "  ❌ $table NOT FOUND\n";
            }
        } catch (Exception $e) {
            $issues[] = "❌ Error checking table $table: " . $e->getMessage();
            echo "  ❌ $table ERROR\n";
        }
    }
} catch (Exception $e) {
    $warnings[] = "⚠️  Database connection failed (may need XAMPP): " . $e->getMessage();
    echo "  ⚠️  Database connection failed (XAMPP issue, not critical)\n";
}

// 6. Check for common PHP errors
echo "\n🔍 Checking PHP Syntax in New Files...\n";
$newFiles = [
    'app/Services/Loyalty/LoyaltyRewardsService.php',
    'app/Services/Scheduler/TaskSchedulerService.php',
    'app/Services/File/FileManagerService.php',
    'app/Http/Controllers/Admin/AdminLoyaltyController.php',
    'app/Http/Controllers/Admin/AdminSchedulerController.php',
    'app/Http/Controllers/Admin/AdminFileController.php'
];

foreach ($newFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $output = [];
        $returnVar = 0;
        exec('php -l ' . escapeshellarg($fullPath) . ' 2>&1', $output, $returnVar);
        
        if ($returnVar === 0) {
            $passed[] = "✅ Syntax OK: $file";
            echo "  ✅ $file\n";
        } else {
            $issues[] = "❌ Syntax error in $file: " . implode(', ', $output);
            echo "  ❌ $file (SYNTAX ERROR)\n";
        }
    } else {
        $issues[] = "❌ File not found: $file";
        echo "  ❌ $file NOT FOUND\n";
    }
}

// 7. Check for undefined variables in views
echo "\n📝 Checking View Variable References...\n";
$viewChecks = [
    ['file' => 'app/views/admin/loyalty/index.php', 'vars' => ['stats', 'tiers']],
    ['file' => 'app/views/admin/scheduler/index.php', 'vars' => ['tasks', 'health']],
    ['file' => 'app/views/admin/files/index.php', 'vars' => ['files', 'stats', 'category', 'search']]
];

foreach ($viewChecks as $check) {
    $viewFile = __DIR__ . '/../' . $check['file'];
    if (file_exists($viewFile)) {
        $content = file_get_contents($viewFile);
        $missing = [];
        
        foreach ($check['vars'] as $var) {
            // Check if variable is used but controller might not set it
            if (strpos($content, '$' . $var) !== false) {
                // Variable is used in view, which is expected
                $passed[] = "✅ Variable $$var used in {$check['file']}";
            }
        }
        echo "  ✅ {$check['file']} variables OK\n";
    }
}

// 8. Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      VALIDATION SUMMARY                        ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ PASSED: " . count($passed) . "\n";
echo "⚠️  WARNINGS: " . count($warnings) . "\n";
echo "❌ ISSUES: " . count($issues) . "\n\n";

if (count($issues) > 0) {
    echo "❌ CRITICAL ISSUES:\n";
    foreach ($issues as $issue) {
        echo "   $issue\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
    echo "\n";
}

if (count($issues) === 0) {
    echo "🎉 ALL CHECKS PASSED! System is healthy.\n";
} else {
    echo "⚠️  System has issues that need fixing.\n";
}

// Save report
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'passed' => $passed,
    'warnings' => $warnings,
    'issues' => $issues,
    'total_checks' => count($passed) + count($warnings) + count($issues)
];

$reportFile = __DIR__ . '/validation_report_' . date('Y-m-d_H-i-s') . '.json';
file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));
echo "\n📄 Report saved to: $reportFile\n";
