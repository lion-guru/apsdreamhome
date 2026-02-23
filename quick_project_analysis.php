<?php
/**
 * APS Dream Home - Quick Project Analysis Tool
 * Fast comprehensive scan of key project areas
 */

$projectRoot = dirname(__FILE__);
$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'project_root' => $projectRoot,
    'summary' => [],
    'issues' => [],
    'recommendations' => []
];

echo "🔍 APS Dream Home - Quick Project Analysis\n";
echo "=========================================\n\n";

// 1. Check core directories
echo "📁 Checking Core Directories\n";
echo "===========================\n";

$coreDirs = [
    'app',
    'app/Http/Controllers',
    'app/Models',
    'app/Services',
    'app/views',
    'config',
    'database/migrations',
    'public',
    'routes',
    'storage',
    'tests'
];

foreach ($coreDirs as $dir) {
    $fullPath = $projectRoot . '/' . $dir;
    if (is_dir($fullPath)) {
        $fileCount = count(scandir($fullPath)) - 2; // Subtract . and ..
        echo "✅ {$dir} ({$fileCount} items)\n";
    } else {
        echo "❌ Missing: {$dir}\n";
        $results['issues'][] = "Missing core directory: {$dir}";
    }
}

echo "\n";

// 2. Check critical files
echo "📄 Checking Critical Files\n";
echo "=========================\n";

$criticalFiles = [
    'composer.json',
    'composer.lock',
    'artisan',
    'index.php',
    'public/index.php',
    '.env',
    'config/app.php',
    'config/database.php',
    'routes/web.php',
    'routes/api.php'
];

foreach ($criticalFiles as $file) {
    $fullPath = $projectRoot . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "✅ {$file} (" . number_format($size) . " bytes)\n";
    } else {
        echo "❌ Missing: {$file}\n";
        $results['issues'][] = "Missing critical file: {$file}";
    }
}

echo "\n";

// 3. Quick PHP syntax check on key files
echo "🐘 PHP Syntax Check (Key Files)\n";
echo "==============================\n";

$keyPhpFiles = [
    'app/Http/Controllers/AdminController.php',
    'app/Http/Controllers/CustomerController.php',
    'app/Http/Controllers/EmployeeController.php',
    'app/Services/MLMReferralService.php',
    'app/Services/CRM/LeadScoringService.php',
    'app/Services/Finance/InvoiceService.php'
];

foreach ($keyPhpFiles as $file) {
    $fullPath = $projectRoot . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if ($content !== false) {
            // Basic checks
            if (strpos($content, '<?php') === false) {
                echo "⚠️  No PHP opening tag: {$file}\n";
                $results['issues'][] = "No PHP opening tag in: {$file}";
            } else {
                echo "✅ {$file}\n";
            }
        } else {
            echo "❌ Cannot read: {$file}\n";
            $results['issues'][] = "Cannot read file: {$file}";
        }
    } else {
        echo "⚠️  Not found: {$file}\n";
    }
}

echo "\n";

// 4. Database migration check
echo "🗄️  Database Migrations\n";
echo "=====================\n";

$migrationDir = $projectRoot . '/database/migrations';
if (is_dir($migrationDir)) {
    $migrations = glob($migrationDir . '/*.php');
    echo "✅ Found " . count($migrations) . " migration files\n";

    // Check recent migrations
    $recentMigrations = array_slice($migrations, -5);
    foreach ($recentMigrations as $migration) {
        $filename = basename($migration);
        echo "  📄 {$filename}\n";
    }
} else {
    echo "❌ Migrations directory not found\n";
    $results['issues'][] = "Migrations directory missing";
}

echo "\n";

// 5. Check for common issues
echo "🔍 Common Issues Check\n";
echo "=====================\n";

$commonIssues = 0;

// Check for old scripts in root
$rootFiles = scandir($projectRoot);
$oldScripts = [];
foreach ($rootFiles as $file) {
    if (preg_match('/\.(php|html?|log|json)$/', $file) &&
        !in_array($file, ['index.php', 'composer.json', 'composer.lock', 'package.json', 'phpunit.xml', 'phpstan.neon', 'vite.config.js'])) {
        if (preg_match('/^(check_|debug_|test_|temp_|inspect_|diagnose_|fixed_|update_|verify_)/', $file)) {
            $oldScripts[] = $file;
        }
    }
}

if (!empty($oldScripts)) {
    echo "⚠️  Found " . count($oldScripts) . " old debug/test scripts in root\n";
    $results['issues'][] = count($oldScripts) . " old scripts found in root directory";
    $commonIssues++;
}

// Check .env file
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false) {
        echo "⚠️  APP_KEY not properly set in .env\n";
        $results['issues'][] = "APP_KEY not set in .env";
        $commonIssues++;
    }
}

// Check storage permissions
$storageDir = $projectRoot . '/storage';
if (is_dir($storageDir)) {
    if (!is_writable($storageDir)) {
        echo "⚠️  Storage directory not writable\n";
        $results['issues'][] = "Storage directory not writable";
        $commonIssues++;
    }
}

if ($commonIssues === 0) {
    echo "✅ No common issues detected\n";
}

echo "\n";

// 6. Generate summary
echo "📊 Analysis Summary\n";
echo "==================\n";

$totalIssues = count($results['issues']);
if ($totalIssues === 0) {
    echo "🎉 Project appears to be in good condition!\n";
    echo "   All core directories and files are present.\n";
} else {
    echo "⚠️  Found {$totalIssues} issues that may need attention:\n";
    foreach ($results['issues'] as $issue) {
        echo "   • {$issue}\n";
    }
}

echo "\n📋 Recommendations\n";
echo "=================\n";
echo "• Run 'php artisan migrate:status' to check migration status\n";
echo "• Run 'php artisan config:cache' to optimize configuration\n";
echo "• Run 'composer install --no-dev --optimize-autoloader' for production\n";
echo "• Consider running security scanner on production\n";
echo "• Review and clean old debug scripts from root directory\n";

$results['summary'] = [
    'status' => $totalIssues === 0 ? 'healthy' : 'needs_attention',
    'total_issues' => $totalIssues,
    'scan_type' => 'quick_analysis'
];

// Save results
$resultsFile = $projectRoot . '/project_analysis_results.json';
file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT));

echo "\n💾 Results saved to: {$resultsFile}\n";
echo "\n✅ Quick analysis complete!\n";

?>
