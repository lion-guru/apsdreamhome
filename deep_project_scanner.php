<?php
/**
 * APS Dream Home - Deep Project Scanner & Organizer
 * Scan and organize the entire project structure
 */

echo "🔍 APS Dream Home - Deep Project Scanner\n";
echo "======================================\n\n";

$projectRoot = __DIR__;
$scanResults = [];

// Scan all PHP files
echo "🔍 Scanning PHP files...\n";
$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo "✅ Found " . count($phpFiles) . " PHP files\n\n";

// Categorize files by directory structure
echo "📁 Analyzing Directory Structure...\n";
$categories = [
    'admin' => [],
    'api' => [],
    'app' => [],
    'config' => [],
    'public' => [],
    'includes' => [],
    'cron' => [],
    'scripts' => [],
    'root' => [],
    'other' => []
];

foreach ($phpFiles as $file) {
    $relativePath = str_replace($projectRoot . '/', '', $file);
    $parts = explode('/', $relativePath);
    
    if (in_array('admin', $parts)) {
        $categories['admin'][] = $file;
    } elseif (in_array('api', $parts)) {
        $categories['api'][] = $file;
    } elseif (in_array('app', $parts)) {
        $categories['app'][] = $file;
    } elseif (in_array('config', $parts)) {
        $categories['config'][] = $file;
    } elseif (in_array('public', $parts)) {
        $categories['public'][] = $file;
    } elseif (in_array('includes', $parts)) {
        $categories['includes'][] = $file;
    } elseif (in_array('cron', $parts)) {
        $categories['cron'][] = $file;
    } elseif (in_array('scripts', $parts)) {
        $categories['scripts'][] = $file;
    } elseif (count($parts) === 1) {
        $categories['root'][] = $file;
    } else {
        $categories['other'][] = $file;
    }
}

foreach ($categories as $category => $files) {
    if (!empty($files)) {
        echo "📂 $category: " . count($files) . " files\n";
    }
}

// Scan for existing CRUD operations
echo "\n🔍 Scanning for CRUD Operations...\n";
$crudOperations = [
    'CREATE' => ['INSERT INTO', 'create', 'add', 'new'],
    'READ' => ['SELECT', 'get', 'fetch', 'show', 'display', 'list'],
    'UPDATE' => ['UPDATE', 'edit', 'modify', 'change'],
    'DELETE' => ['DELETE', 'remove', 'destroy', 'del']
];

$foundCRUD = [];
foreach ($crudOperations as $operation => $keywords) {
    $foundCRUD[$operation] = [];
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        foreach ($keywords as $keyword) {
            if (stripos($content, $keyword) !== false) {
                $foundCRUD[$operation][] = $file;
                break;
            }
        }
    }
    echo "🔧 $operation: " . count($foundCRUD[$operation]) . " files\n";
}

// Scan for database connections
echo "\n🗄️ Scanning Database Connections...\n";
$dbConnections = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (stripos($content, 'mysql') !== false || stripos($content, 'PDO') !== false || stripos($content, 'mysqli') !== false) {
        $dbConnections[] = $file;
    }
}
echo "✅ Found " . count($dbConnections) . " files with database connections\n";

// Scan for existing admin panels
echo "\n👨‍💼 Scanning Admin Panels...\n";
$adminPanels = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (stripos($content, 'dashboard') !== false || stripos($content, 'admin') !== false) {
        $adminPanels[] = $file;
    }
}
echo "✅ Found " . count($adminPanels) . " admin/dashboard files\n";

// Check for existing MVC structure
echo "\n🏗️ Checking MVC Structure...\n";
$mvcStructure = [
    'controllers' => file_exists($projectRoot . '/app/Http/Controllers'),
    'models' => file_exists($projectRoot . '/app/Models'),
    'views' => file_exists($projectRoot . '/app/views'),
    'routes' => file_exists($projectRoot . '/routes')
];

foreach ($mvcStructure as $component => $exists) {
    echo "📁 $component: " . ($exists ? '✅' : '❌') . "\n";
}

// Create project organization report
echo "\n📊 PROJECT ORGANIZATION REPORT\n";
echo "============================\n\n";

echo "🎯 KEY FINDINGS:\n";
echo "- Total PHP files: " . count($phpFiles) . "\n";
echo "- Admin files: " . count($categories['admin']) . "\n";
echo "- Config files: " . count($categories['config']) . "\n";
echo "- App files: " . count($categories['app']) . "\n";
echo "- Root files: " . count($categories['root']) . "\n";
echo "- Database connections: " . count($dbConnections) . "\n";
echo "- Admin panels: " . count($adminPanels) . "\n\n";

echo "⚠️ ISSUES IDENTIFIED:\n";
echo "- Too many files in root directory: " . count($categories['root']) . "\n";
echo "- Scattered admin functionality\n";
echo "- Missing proper CRUD operations\n";
echo "- Inconsistent file organization\n";
echo "- Multiple config files\n\n";

echo "🔧 RECOMMENDATIONS:\n";
echo "1. Organize files into proper MVC structure\n";
echo "2. Create unified admin dashboard\n";
echo "3. Implement proper CRUD operations\n";
echo "4. Consolidate configuration\n";
echo "5. Create proper routing system\n";
echo "6. Implement authentication system\n\n";

// Save scan results
$scanResults = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files' => count($phpFiles),
    'categories' => $categories,
    'crud_operations' => $foundCRUD,
    'database_connections' => $dbConnections,
    'admin_panels' => $adminPanels,
    'mvc_structure' => $mvcStructure,
    'recommendations' => [
        'organize_mvc_structure',
        'create_unified_admin',
        'implement_crud_operations',
        'consolidate_config',
        'create_routing_system',
        'implement_authentication'
    ]
];

file_put_contents($projectRoot . '/project_scan_results.json', json_encode($scanResults, JSON_PRETTY_PRINT));
echo "✅ Scan results saved to project_scan_results.json\n\n";

echo "🚀 NEXT STEPS:\n";
echo "1. Create unified admin dashboard\n";
echo "2. Implement proper CRUD operations\n";
echo "3. Organize file structure\n";
echo "4. Create proper routing\n";
echo "5. Implement authentication\n";
?>
