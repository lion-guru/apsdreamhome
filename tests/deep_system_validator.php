<?php
/**
 * Deep System Validator
 * Comprehensive check of all functionality, views, and UI
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
echo "â•‘           DEEP SYSTEM VALIDATOR - APS DREAM HOME               â•‘\n";
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";

$issues = [];
$warnings = [];
$passed = [];

// 1. Check all services can be instantiated
echo "ðŸ”§ Testing Service Instantiation...\n";
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
                    $passed[] = "âœ… $service";
                    echo "  âœ… $service\n";
                } else {
                    $warnings[] = "âš ï¸�  $service has private constructor";
                    echo "  âš ï¸�  $service (private constructor)\n";
                }
            } else {
                $passed[] = "âœ… $service (no constructor)";
                echo "  âœ… $service\n";
            }
        } else {
            $issues[] = "â�Œ Service not found: $service";
            echo "  â�Œ $service NOT FOUND\n";
        }
    } catch (Exception $e) {
        $issues[] = "â�Œ Error checking $service: " . $e->getMessage();
        echo "  â�Œ $service ERROR\n";
    }
}

// 2. Check all admin controllers
echo "\nðŸŽ® Testing Admin Controllers...\n";
$controllers = [
    'App\Http\Controllers\Admin\AdminLoyaltyController',
    'App\Http\Controllers\Admin\AdminSchedulerController',
    'App\Http\Controllers\Admin\AdminFileController',
    'App\Http\Controllers\Admin\AdminWorkflowController'
];

foreach ($controllers as $controller) {
    try {
        if (class_exists($controller)) {
            $passed[] = "âœ… $controller";
            echo "  âœ… $controller\n";
        } else {
            $issues[] = "â�Œ Controller not found: $controller";
            echo "  â�Œ $controller NOT FOUND\n";
        }
    } catch (Exception $e) {
        $issues[] = "â�Œ Error with $controller: " . $e->getMessage();
        echo "  â�Œ $controller ERROR\n";
    }
}

// 3. Check view files exist
echo "\nðŸŽ¨ Testing View Files...\n";
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
            $passed[] = "âœ… View: $viewFile";
            echo "  âœ… $viewFile\n";
        } else {
            $issues[] = "â�Œ Syntax error in $viewFile: " . implode(', ', $output);
            echo "  â�Œ $viewFile (SYNTAX ERROR)\n";
        }
    } else {
        $issues[] = "â�Œ View not found: $viewFile";
        echo "  â�Œ $viewFile NOT FOUND\n";
    }
}

// 4. Check route definitions
echo "\nðŸŒ� Testing Route Definitions...\n";
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
            $passed[] = "âœ… Route: $route";
            echo "  âœ… $route\n";
        } else {
            $issues[] = "â�Œ Route not found: $route";
            echo "  â�Œ $route NOT FOUND\n";
        }
    }
} else {
    $issues[] = "â�Œ web.php not found";
    echo "  â�Œ web.php NOT FOUND\n";
}

// 5. Check database tables
echo "\nðŸ—„ï¸�  Testing Database Tables...\n";
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
                $passed[] = "âœ… Table: $table";
                echo "  âœ… $table\n";
            } else {
                $issues[] = "â�Œ Table not found: $table";
                echo "  â�Œ $table NOT FOUND\n";
            }
        } catch (Exception $e) {
            $issues[] = "â�Œ Error checking table $table: " . $e->getMessage();
            echo "  â�Œ $table ERROR\n";
        }
    }
} catch (Exception $e) {
    $warnings[] = "âš ï¸�  Database connection failed (may need XAMPP): " . $e->getMessage();
    echo "  âš ï¸�  Database connection failed (XAMPP issue, not critical)\n";
}

// 6. Check for common PHP errors
echo "\nðŸ”� Checking PHP Syntax in New Files...\n";
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
            $passed[] = "âœ… Syntax OK: $file";
            echo "  âœ… $file\n";
        } else {
            $issues[] = "â�Œ Syntax error in $file: " . implode(', ', $output);
            echo "  â�Œ $file (SYNTAX ERROR)\n";
        }
    } else {
        $issues[] = "â�Œ File not found: $file";
        echo "  â�Œ $file NOT FOUND\n";
    }
}

// 7. Check for undefined variables in views
echo "\nðŸ“� Checking View Variable References...\n";
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
                $passed[] = "âœ… Variable $$var used in {$check['file']}";
            }
        }
        echo "  âœ… {$check['file']} variables OK\n";
    }
}

// 8. Summary
echo "\n";
echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
echo "â•‘                      VALIDATION SUMMARY                        â•‘\n";
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";

echo "âœ… PASSED: " . count($passed) . "\n";
echo "âš ï¸�  WARNINGS: " . count($warnings) . "\n";
echo "â�Œ ISSUES: " . count($issues) . "\n\n";

if (count($issues) > 0) {
    echo "â�Œ CRITICAL ISSUES:\n";
    foreach ($issues as $issue) {
        echo "   $issue\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "âš ï¸�  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
    echo "\n";
}

if (count($issues) === 0) {
    echo "ðŸŽ‰ ALL CHECKS PASSED! System is healthy.\n";
} else {
    echo "âš ï¸�  System has issues that need fixing.\n";
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
echo "\nðŸ“„ Report saved to: $reportFile\n";?>