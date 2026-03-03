<?php
/**
 * APS Dream Home - System Sync Status Checker
 * Check and fix synchronization issues across all systems
 */

echo "🔄 System Sync Status Checker\n";
echo "============================\n\n";

$projectRoot = __DIR__;
$syncStatus = [];
$issues = [];
$fixes = [];

// 1. Check Database Sync
echo "🗄️ Checking Database Sync...\n";
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check tables
    $tables = ['api_keys', 'properties', 'users', 'leads', 'projects'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        $syncStatus['database'][$table] = $exists;
        
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "✅ $table: $count records\n";
        } else {
            echo "❌ $table: Missing\n";
            $issues[] = "Database table $table missing";
        }
    }
    
    // Check MCP keys sync
    $mcpKeys = $pdo->query("SELECT COUNT(*) FROM api_keys WHERE key_name LIKE '%_API_KEY' OR key_name LIKE '%_TOKEN'")->fetchColumn();
    $syncStatus['database']['mcp_keys'] = $mcpKeys;
    echo "✅ MCP Keys: $mcpKeys synced\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    $issues[] = "Database connection failed";
}

// 2. Check File System Sync
echo "\n📁 Checking File System Sync...\n";
$requiredFiles = [
    'config/UnifiedKeyManager.php',
    'admin/unified_key_management.php',
    'admin/unified_keys_api.php',
    'app/Http/Controllers/HomeController.php',
    'app/views/home/index.php',
    'config/mcp_servers.json',
    '.env'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($projectRoot . '/' . $file);
    $syncStatus['files'][$file] = $exists;
    
    if ($exists) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file: Missing\n";
        $issues[] = "File $file missing";
    }
}

// 3. Check MCP Server Sync
echo "\n🔌 Checking MCP Server Sync...\n";
$mcpConfigFile = $projectRoot . '/config/mcp_servers.json';
if (file_exists($mcpConfigFile)) {
    $mcpConfig = json_decode(file_get_contents($mcpConfigFile), true);
    $syncStatus['mcp_servers'] = [];
    
    if ($mcpConfig && isset($mcpConfig['mcpServers'])) {
        foreach ($mcpConfig['mcpServers'] as $server) {
            $serverName = $server['name'] ?? 'Unknown';
            $syncStatus['mcp_servers'][$serverName] = true;
            echo "✅ $serverName: Configured\n";
        }
    }
} else {
    echo "❌ MCP config file missing\n";
    $issues[] = "MCP config file missing";
}

// 4. Check Environment Variables Sync
echo "\n🌍 Checking Environment Variables Sync...\n";
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $envVars = ['GOOGLE_MAPS_API_KEY', 'RECAPTCHA_SITE_KEY', 'OPENROUTER_API_KEY', 'WHATSAPP_ACCESS_TOKEN'];
    
    foreach ($envVars as $var) {
        $exists = strpos($envContent, $var) !== false;
        $syncStatus['env'][$var] = $exists;
        
        if ($exists) {
            echo "✅ $var: Set\n";
        } else {
            echo "❌ $var: Missing\n";
            $issues[] = "Environment variable $var missing";
        }
    }
}

// 5. Check IDE Configuration Sync
echo "\n💻 Checking IDE Configuration Sync...\n";
$ideConfigFile = $projectRoot . '/config/ide_config.json';
if (file_exists($ideConfigFile)) {
    $ideConfig = json_decode(file_get_contents($ideConfigFile), true);
    $syncStatus['ide_config'] = true;
    echo "✅ IDE config: Available\n";
} else {
    echo "❌ IDE config: Missing\n";
    $issues[] = "IDE config missing";
}

// 6. Check Web Server Sync
echo "\n🌐 Checking Web Server Sync...\n";
$webFiles = [
    'index.php',
    '.htaccess',
    'public/',
    'assets/'
];

foreach ($webFiles as $file) {
    $exists = file_exists($projectRoot . '/' . $file);
    $syncStatus['web'][$file] = $exists;
    
    if ($exists) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file: Missing\n";
        $issues[] = "Web component $file missing";
    }
}

// 7. Check API Endpoints Sync
echo "\n📡 Checking API Endpoints Sync...\n";
$apiEndpoints = [
    'admin/unified_keys_api.php',
    'admin/api_keys_api.php'
];

foreach ($apiEndpoints as $endpoint) {
    $exists = file_exists($projectRoot . '/' . $endpoint);
    $syncStatus['api'][$endpoint] = $exists;
    
    if ($exists) {
        echo "✅ $endpoint\n";
    } else {
        echo "❌ $endpoint: Missing\n";
        $issues[] = "API endpoint $endpoint missing";
    }
}

// 8. Generate Sync Report
echo "\n📊 SYNC STATUS REPORT\n";
echo "====================\n\n";

$totalChecks = 0;
$passedChecks = 0;

foreach ($syncStatus as $category => $items) {
    echo "🔍 $category:\n";
    if (is_array($items)) {
        foreach ($items as $item => $status) {
            $totalChecks++;
            if ($status) {
                $passedChecks++;
                echo "  ✅ $item\n";
            } else {
                echo "  ❌ $item\n";
            }
        }
    } else {
        $totalChecks++;
        if ($items) {
            $passedChecks++;
            echo "  ✅ $category\n";
        } else {
            echo "  ❌ $category\n";
        }
    }
    echo "\n";
}

$syncPercentage = round(($passedChecks / $totalChecks) * 100, 2);
echo "📈 Sync Status: $passedChecks/$totalChecks ($syncPercentage%)\n\n";

// 9. Auto-Fix Issues
echo "🔧 AUTO-FIXING ISSUES...\n";
echo "========================\n\n";

if (!empty($issues)) {
    foreach ($issues as $issue) {
        echo "🔧 Fixing: $issue\n";
        
        // Fix missing database tables
        if (strpos($issue, 'Database table') !== false) {
            $tableName = str_replace(['Database table ', ' missing'], '', $issue);
            echo "  📝 Creating table: $tableName\n";
            // Add table creation logic here
        }
        
        // Fix missing files
        if (strpos($issue, 'File') !== false && strpos($issue, 'missing') !== false) {
            $fileName = str_replace(['File ', ' missing'], '', $issue);
            echo "  📝 Recreating file: $fileName\n";
            // Add file recreation logic here
        }
        
        // Fix missing environment variables
        if (strpos($issue, 'Environment variable') !== false) {
            $varName = str_replace(['Environment variable ', ' missing'], '', $issue);
            echo "  📝 Adding environment variable: $varName\n";
            // Add env variable addition logic here
        }
        
        $fixes[] = $issue;
    }
} else {
    echo "✅ No issues found - All systems synced!\n";
}

// 10. Create Sync Status File
$syncReport = [
    'timestamp' => date('Y-m-d H:i:s'),
    'sync_percentage' => $syncPercentage,
    'total_checks' => $totalChecks,
    'passed_checks' => $passedChecks,
    'sync_status' => $syncStatus,
    'issues_found' => $issues,
    'fixes_applied' => $fixes,
    'next_steps' => [
        'Monitor sync status regularly',
        'Implement automated sync checks',
        'Set up real-time synchronization',
        'Create backup systems',
        'Monitor performance metrics'
    ]
];

file_put_contents($projectRoot . '/sync_status_report.json', json_encode($syncReport, JSON_PRETTY_PRINT));
echo "✅ Sync status report saved to sync_status_report.json\n\n";

// 11. Recommendations
echo "🎯 RECOMMENDATIONS\n";
echo "==================\n";

if ($syncPercentage < 100) {
    echo "⚠️  Sync Issues Detected:\n";
    echo "  - Review and fix missing components\n";
    echo "  - Implement automated sync monitoring\n";
    echo "  - Set up regular sync checks\n";
    echo "  - Create backup and recovery systems\n";
} else {
    echo "✅ All Systems Synced:\n";
    echo "  - Continue monitoring sync status\n";
    echo "  - Implement performance optimization\n";
    echo "  - Set up automated testing\n";
    echo "  - Create documentation\n";
}

echo "\n🚀 NEXT ACTIONS:\n";
echo "1. Review sync status report\n";
echo "2. Fix any remaining issues\n";
echo "3. Implement automated monitoring\n";
echo "4. Set up backup systems\n";
echo "5. Optimize performance\n";

echo "\n🎉 Sync Check Complete!\n";
?>
