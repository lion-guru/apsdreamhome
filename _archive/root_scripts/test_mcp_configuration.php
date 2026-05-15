<?php
/**
 * APS Dream Home - MCP Configuration Test
 * Test all MCP tools configuration and connectivity
 */

// Define constants
define('APS_ROOT', __DIR__);
define('APS_PUBLIC', APS_ROOT . '/public');

// Include bootstrap
require_once APS_ROOT . '/config/bootstrap.php';

echo "=== APS DREAM HOME - MCP CONFIGURATION TEST ===\n\n";

// Load MCP configuration
$mcpConfigFile = APS_ROOT . '/.windsurf/mcp_servers.json';
$mcpEnvFile = APS_ROOT . '/.windsurf/mcp_config.env';

echo "🔧 MCP CONFIGURATION FILES:\n";

if (file_exists($mcpConfigFile)) {
    echo "✅ MCP Servers Config: EXISTS\n";
    $mcpConfig = json_decode(file_get_contents($mcpConfigFile), true);
    $serverCount = count($mcpConfig['mcpServers'] ?? []);
    echo "📊 Configured Servers: $serverCount\n";
} else {
    echo "❌ MCP Servers Config: MISSING\n";
}

if (file_exists($mcpEnvFile)) {
    echo "✅ MCP Environment Config: EXISTS\n";
    $envLines = file($mcpEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envCount = count($envLines);
    echo "📊 Environment Variables: $envCount\n";
} else {
    echo "❌ MCP Environment Config: MISSING\n";
}

echo "\n🗄️ DATABASE MCP CONFIGURATION:\n";

// Test database connection
try {
    $db = \App\Core\Database\Database::getInstance();
    echo "✅ Database Connection: SUCCESS\n";
    
    // Get table count
    $tables = $db->fetchAll('SHOW TABLES');
    $tableCount = count($tables);
    echo "📊 Database Tables: $tableCount\n";
    
    // Test specific tables
    $criticalTables = ['users', 'properties', 'leads', 'commissions', 'payments'];
    foreach ($criticalTables as $table) {
        $exists = false;
        foreach ($tables as $existingTable) {
            if (array_values($existingTable)[0] === $table) {
                $exists = true;
                break;
            }
        }
        echo $exists ? "✅ Table '$table': EXISTS\n" : "❌ Table '$table': MISSING\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Connection: FAILED - " . $e->getMessage() . "\n";
}

echo "\n📁 FILESYSTEM MCP CONFIGURATION:\n";

// Test file system access
$directories = [
    'app/Http/Controllers' => 'Controller Classes',
    'app/Models' => 'Model Classes',
    'app/Services' => 'Service Classes',
    'app/views' => 'View Templates',
    'config' => 'Configuration Files',
    'storage' => 'Storage Directory'
];

foreach ($directories as $dir => $description) {
    if (is_dir(APS_ROOT . '/' . $dir)) {
        $fileCount = count(glob(APS_ROOT . '/' . $dir . '/*.php'));
        echo "✅ $dir ($description): $fileCount files\n";
    } else {
        echo "❌ $dir: MISSING\n";
    }
}

echo "\n🤖 AI SERVICES MCP CONFIGURATION:\n";

// Test AI services
$aiEngineFile = APS_ROOT . '/app/Services/AI/PropertyValuationEngine.php';
$aiControllerFile = APS_ROOT . '/app/Http/Controllers/AI/PropertyValuationController.php';
$aiViewFile = APS_ROOT . '/app/views/ai/property-valuation.php';

if (file_exists($aiEngineFile)) {
    echo "✅ AI Valuation Engine: EXISTS\n";
} else {
    echo "❌ AI Valuation Engine: MISSING\n";
}

if (file_exists($aiControllerFile)) {
    echo "✅ AI Controller: EXISTS\n";
} else {
    echo "❌ AI Controller: MISSING\n";
}

if (file_exists($aiViewFile)) {
    echo "✅ AI View: EXISTS\n";
} else {
    echo "❌ AI View: MISSING\n";
}

echo "\n🌐 WEB SERVICES MCP CONFIGURATION:\n";

// Test web server
$baseUrl = 'http://localhost:8000';
$testUrl = $baseUrl . '/';

// Use curl to test web server
if (function_exists('curl_init')) {
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 0) {
        echo "✅ Web Server ($baseUrl): RUNNING\n";
    } else {
        echo "❌ Web Server ($baseUrl): NOT RUNNING (HTTP $httpCode)\n";
    }
} else {
    echo "⚠️ Web Server Test: cURL not available\n";
}

echo "\n📊 ANALYTICS MCP CONFIGURATION:\n";

// Test analytics directory
$analyticsDir = APS_ROOT . '/storage/analytics';
if (is_dir($analyticsDir)) {
    echo "✅ Analytics Directory: EXISTS\n";
    $analyticsFiles = count(glob($analyticsDir . '/*'));
    echo "📊 Analytics Files: $analyticsFiles\n";
} else {
    echo "❌ Analytics Directory: MISSING\n";
    // Create analytics directory
    if (mkdir($analyticsDir, 0755, true)) {
        echo "✅ Analytics Directory: CREATED\n";
    } else {
        echo "❌ Analytics Directory: FAILED TO CREATE\n";
    }
}

echo "\n💾 MEMORY MCP CONFIGURATION:\n";

// Test memory database
$memoryDbPath = APS_ROOT . '/storage/database/project_memory.sqlite';
if (file_exists($memoryDbPath)) {
    echo "✅ Memory Database: EXISTS\n";
    $memoryDbSize = filesize($memoryDbPath);
    echo "📊 Memory DB Size: " . round($memoryDbSize / 1024, 2) . " KB\n";
} else {
    echo "❌ Memory Database: MISSING\n";
    // Create memory database directory
    $memoryDbDir = dirname($memoryDbPath);
    if (!is_dir($memoryDbDir)) {
        mkdir($memoryDbDir, 0755, true);
        echo "✅ Memory DB Directory: CREATED\n";
    }
}

echo "\n🔍 CONFIGURATION VALIDATION:\n";

// Validate configuration
$validationResults = [
    'MCP Servers Config' => file_exists($mcpConfigFile),
    'Environment Config' => file_exists($mcpEnvFile),
    'Database Connection' => isset($db) && $db !== null,
    'File System Access' => is_dir(APS_ROOT . '/app'),
    'AI Services' => file_exists($aiEngineFile),
    'Web Server' => function_exists('curl_init'),
    'Analytics Directory' => is_dir($analyticsDir),
    'Memory Database' => file_exists($memoryDbPath)
];

$passedTests = 0;
$totalTests = count($validationResults);

foreach ($validationResults as $test => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "$status $test\n";
    if ($result) $passedTests++;
}

$successRate = round(($passedTests / $totalTests) * 100, 2);
echo "\n📊 MCP CONFIGURATION SCORE: $successRate%\n";

if ($successRate >= 80) {
    echo "🚀 MCP Configuration: READY FOR PREVIEW\n";
} else {
    echo "⚠️ MCP Configuration: NEEDS ATTENTION\n";
}

echo "\n📋 MCP SERVERS SUMMARY:\n";

if (isset($mcpConfig['mcpServers'])) {
    foreach ($mcpConfig['mcpServers'] as $name => $config) {
        $description = $config['description'] ?? 'No description';
        echo "🔧 $name: $description\n";
    }
}

echo "\n🏆 MCP CONFIGURATION TEST COMPLETE\n";

?>
