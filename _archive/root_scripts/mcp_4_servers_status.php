<?php
/**
 * APS Dream Home - 4 MCP Servers Status Check
 * Verify the 4 specific MCP servers: fetch, mcp-playwright, puppeteer, sequential-thinking
 */

echo "=== APS DREAM HOME - 4 MCP SERVERS STATUS ===\n\n";

// Load configuration
$mcpConfigFile = __DIR__ . '/.windsurf/mcp_servers.json';
$mcpEnvFile = __DIR__ . '/.windsurf/mcp_config.env';
$postmanFile = __DIR__ . '/postman_collection.json';

echo "🔧 CONFIGURATION FILES STATUS:\n";

if (file_exists($mcpConfigFile)) {
    echo "✅ MCP Servers Config: EXISTS\n";
    $mcpConfig = json_decode(file_get_contents($mcpConfigFile), true);
    $serverCount = count($mcpConfig['mcpServers'] ?? []);
    echo "📊 Total MCP Servers: $serverCount\n";
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

if (file_exists($postmanFile)) {
    echo "✅ Postman Collection: EXISTS\n";
    $postmanConfig = json_decode(file_get_contents($postmanFile), true);
    $apiCount = count($postmanConfig['item'] ?? []);
    echo "📊 API Endpoints: $apiCount\n";
} else {
    echo "❌ Postman Collection: MISSING\n";
}

echo "\n🚀 4 SPECIFIC MCP SERVERS:\n";

// Check the 4 specific servers
$targetServers = [
    'fetch' => 'API Integration Server',
    'mcp-playwright' => 'Advanced Web Automation (Playwright)',
    'puppeteer' => 'Web Automation Server',
    'mcp-sequential-thinking' => 'Sequential Thinking Server'
];

foreach ($targetServers as $serverName => $description) {
    echo "\n🔧 $serverName - $description:\n";
    
    if (isset($mcpConfig['mcpServers'][$serverName])) {
        $config = $mcpConfig['mcpServers'][$serverName];
        echo "  ✅ Status: CONFIGURED\n";
        echo "  📋 Command: " . ($config['command'] ?? 'N/A') . "\n";
        echo "  🎯 Args: " . implode(' ', $config['args'] ?? []) . "\n";
        
        if (isset($config['env'])) {
            echo "  🌍 Environment Variables:\n";
            foreach ($config['env'] as $key => $value) {
                echo "    - $key: $value\n";
            }
        }
    } else {
        echo "  ❌ Status: NOT CONFIGURED\n";
    }
}

echo "\n🔑 API KEYS CONFIGURATION:\n";

// Check API keys in environment
if (file_exists($mcpEnvFile)) {
    $envContent = file_get_contents($mcpEnvFile);
    
    $apiKeys = [
        'MAIN_API_KEY' => 'aps2024-ai-main-key-secure',
        'SECONDARY_API_KEY' => 'aps2024-ai-secondary-key-secure',
        'DATABASE_API_KEY' => 'aps2024-db-key-secure',
        'AI_VALUATION_API_KEY' => 'aps2024-ai-valuation-key-secure',
        'AI_ANALYTICS_API_KEY' => 'aps2024-ai-analytics-key-secure'
    ];
    
    foreach ($apiKeys as $key => $expectedValue) {
        if (strpos($envContent, $key) !== false && strpos($envContent, $expectedValue) !== false) {
            echo "✅ $key: CONFIGURED\n";
        } else {
            echo "❌ $key: NOT CONFIGURED\n";
        }
    }
}

echo "\n📮 POSTMAN COLLECTION STATUS:\n";

if (file_exists($postmanFile)) {
    $postmanConfig = json_decode(file_get_contents($postmanFile), true);
    
    echo "✅ Collection Name: " . ($postmanConfig['info']['name'] ?? 'N/A') . "\n";
    echo "✅ Version: " . ($postmanConfig['info']['version'] ?? 'N/A') . "\n";
    echo "✅ Description: " . ($postmanConfig['info']['description'] ?? 'N/A') . "\n";
    
    if (isset($postmanConfig['variable'])) {
        echo "✅ Variables: " . count($postmanConfig['variable']) . " configured\n";
        
        foreach ($postmanConfig['variable'] as $var) {
            echo "  - {$var['key']}: " . ($var['value'] ?? 'empty') . "\n";
        }
    }
    
    if (isset($postmanConfig['item'])) {
        $totalRequests = 0;
        foreach ($postmanConfig['item'] as $category) {
            if (isset($category['item'])) {
                $totalRequests += count($category['item']);
            }
        }
        echo "✅ Total API Requests: $totalRequests\n";
    }
}

echo "\n🌐 API ENDPOINTS READY:\n";

// List all API endpoints from Postman collection
if (file_exists($postmanFile)) {
    $postmanConfig = json_decode(file_get_contents($postmanFile), true);
    
    if (isset($postmanConfig['item'])) {
        foreach ($postmanConfig['item'] as $category) {
            echo "\n📂 " . ($category['name'] ?? 'Unknown Category') . ":\n";
            
            if (isset($category['item'])) {
                foreach ($category['item'] as $endpoint) {
                    $method = $endpoint['request']['method'] ?? 'GET';
                    $url = $endpoint['request']['url']['raw'] ?? 'N/A';
                    echo "  ✅ $method: $url\n";
                }
            }
        }
    }
}

echo "\n📊 READINESS SCORE:\n";

$checks = [
    'MCP Config File' => file_exists($mcpConfigFile),
    'Environment Config' => file_exists($mcpEnvFile),
    'Postman Collection' => file_exists($postmanFile),
    'Fetch Server' => isset($mcpConfig['mcpServers']['fetch']),
    'Playwright Server' => isset($mcpConfig['mcpServers']['mcp-playwright']),
    'Puppeteer Server' => isset($mcpConfig['mcpServers']['puppeteer']),
    'Sequential Thinking' => isset($mcpConfig['mcpServers']['mcp-sequential-thinking']),
    'API Keys Configured' => file_exists($mcpEnvFile) && strpos(file_get_contents($mcpEnvFile), 'MAIN_API_KEY') !== false
];

$passedChecks = 0;
$totalChecks = count($checks);

foreach ($checks as $check => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "$status $check\n";
    if ($result) $passedChecks++;
}

$readinessScore = round(($passedChecks / $totalChecks) * 100, 2);
echo "\n📈 Readiness Score: $readinessScore%\n";

if ($readinessScore >= 80) {
    echo "🚀 4 MCP SERVERS: READY FOR USE\n";
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Import postman_collection.json into Postman\n";
    echo "2. Test authentication endpoints first\n";
    echo "3. Use API key: aps2024-ai-main-key-secure\n";
    echo "4. Test AI valuation endpoints\n";
    echo "5. Verify all MCP server functionality\n";
} else {
    echo "⚠️ 4 MCP SERVERS: NEEDS CONFIGURATION\n";
}

echo "\n🔗 POSTMAN IMPORT INSTRUCTIONS:\n";
echo "1. Open Postman\n";
echo "2. Click Import > Select File\n";
echo "3. Choose: postman_collection.json\n";
echo "4. Set variables: baseUrl=http://localhost:8000\n";
echo "5. Set apiKey: aps2024-ai-main-key-secure\n";
echo "6. Test endpoints starting with authentication\n";

echo "\n🏆 4 MCP SERVERS STATUS CHECK COMPLETE\n";

?>
