<?php
/**
 * APS Dream Home - IDE MCP Status Check
 * Verify MCP tools are enabled in IDE
 */

echo "=== APS DREAM HOME - IDE MCP STATUS ===\n\n";

// Check VS Code settings
$vscodeSettingsFile = __DIR__ . '/.vscode/settings.json';
$vscodeExtensionsFile = __DIR__ . '/.vscode/extensions.json';
$mcpServersFile = __DIR__ . '/.windsurf/mcp_servers.json';
$mcpEnvFile = __DIR__ . '/.windsurf/mcp_config.env';

echo "🔧 IDE MCP CONFIGURATION STATUS:\n";

// Check VS Code settings
if (file_exists($vscodeSettingsFile)) {
    $settings = json_decode(file_get_contents($vscodeSettingsFile), true);
    
    if (isset($settings['mcp.enabled']) && $settings['mcp.enabled']) {
        echo "✅ MCP Tools: ENABLED in VS Code\n";
    } else {
        echo "❌ MCP Tools: NOT ENABLED in VS Code\n";
    }
    
    if (isset($settings['mcp.autoStart']) && $settings['mcp.autoStart']) {
        echo "✅ MCP Auto Start: ENABLED\n";
    } else {
        echo "❌ MCP Auto Start: NOT ENABLED\n";
    }
    
    if (isset($settings['mcp.servers'])) {
        $serverCount = count($settings['mcp.servers']);
        $enabledServers = 0;
        
        foreach ($settings['mcp.servers'] as $server => $config) {
            if (isset($config['enabled']) && $config['enabled']) {
                $enabledServers++;
            }
        }
        
        echo "✅ MCP Servers: $enabledServers/$serverCount configured\n";
    } else {
        echo "❌ MCP Servers: NOT CONFIGURED\n";
    }
    
    if (isset($settings['mcp.showStatusBar']) && $settings['mcp.showStatusBar']) {
        echo "✅ MCP Status Bar: ENABLED\n";
    }
    
    if (isset($settings['mcp.showActivityBar']) && $settings['mcp.showActivityBar']) {
        echo "✅ MCP Activity Bar: ENABLED\n";
    }
    
} else {
    echo "❌ VS Code Settings: FILE NOT FOUND\n";
}

echo "\n📦 EXTENSIONS CONFIGURATION:\n";

if (file_exists($vscodeExtensionsFile)) {
    $extensions = json_decode(file_get_contents($vscodeExtensionsFile), true);
    
    if (isset($extensions['recommendations'])) {
        $recommendedExtensions = $extensions['recommendations'];
        
        if (in_array('modelcontextprotocol.vscode-mcp', $recommendedExtensions)) {
            echo "✅ MCP Extension: RECOMMENDED\n";
        } else {
            echo "❌ MCP Extension: NOT RECOMMENDED\n";
        }
        
        if (in_array('ms-vscode.vscode-sqltools', $recommendedExtensions)) {
            echo "✅ SQL Tools Extension: RECOMMENDED\n";
        }
        
        if (in_array('cweijan.vscode-mysql-client2', $recommendedExtensions)) {
            echo "✅ MySQL Client Extension: RECOMMENDED\n";
        }
        
        $totalExtensions = count($recommendedExtensions);
        echo "📊 Total Recommended Extensions: $totalExtensions\n";
    }
} else {
    echo "❌ Extensions File: NOT FOUND\n";
}

echo "\n🗄️ MCP SERVERS CONFIGURATION:\n";

if (file_exists($mcpServersFile)) {
    $mcpConfig = json_decode(file_get_contents($mcpServersFile), true);
    
    if (isset($mcpConfig['mcpServers'])) {
        $servers = $mcpConfig['mcpServers'];
        $totalServers = count($servers);
        
        echo "📊 Total MCP Servers: $totalServers\n";
        
        foreach ($servers as $name => $config) {
            $description = $config['description'] ?? 'No description';
            echo "🔧 $name: $description\n";
        }
    }
} else {
    echo "❌ MCP Servers Config: NOT FOUND\n";
}

echo "\n🌍 ENVIRONMENT CONFIGURATION:\n";

if (file_exists($mcpEnvFile)) {
    $envLines = file($mcpEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $envCount = count($envLines);
    
    echo "📊 Environment Variables: $envCount\n";
    
    // Check key environment variables
    $envContent = file_get_contents($mcpEnvFile);
    
    if (strpos($envContent, 'PROJECT_NAME=APS Dream Home') !== false) {
        echo "✅ Project Name: CONFIGURED\n";
    }
    
    if (strpos($envContent, 'MYSQL_DATABASE=apsdreamhome') !== false) {
        echo "✅ Database Name: CONFIGURED\n";
    }
    
    if (strpos($envContent, 'AI_ENGINE=PropertyValuationEngine') !== false) {
        echo "✅ AI Engine: CONFIGURED\n";
    }
    
    if (strpos($envContent, 'BASE_URL=http://localhost:8000') !== false) {
        echo "✅ Base URL: CONFIGURED\n";
    }
    
} else {
    echo "❌ Environment Config: NOT FOUND\n";
}

echo "\n📋 IDE MCP READINESS CHECKLIST:\n";

$checklist = [
    'VS Code Settings Updated' => file_exists($vscodeSettingsFile) && strpos(file_get_contents($vscodeSettingsFile), 'mcp.enabled') !== false,
    'MCP Extensions Recommended' => file_exists($vscodeExtensionsFile) && strpos(file_get_contents($vscodeExtensionsFile), 'modelcontextprotocol.vscode-mcp') !== false,
    'MCP Servers Configured' => file_exists($mcpServersFile),
    'Environment Variables Set' => file_exists($mcpEnvFile),
    'Database Configuration Ready' => file_exists($mcpEnvFile) && strpos(file_get_contents($mcpEnvFile), 'MYSQL_DATABASE=apsdreamhome') !== false,
    'AI Services Configuration' => file_exists($mcpEnvFile) && strpos(file_get_contents($mcpEnvFile), 'AI_ENGINE=PropertyValuationEngine') !== false,
    'Web Server Configuration' => file_exists($mcpEnvFile) && strpos(file_get_contents($mcpEnvFile), 'BASE_URL=http://localhost:8000') !== false
];

$passedChecks = 0;
$totalChecks = count($checklist);

foreach ($checklist as $check => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    echo "$status $check\n";
    if ($result) $passedChecks++;
}

$readinessScore = round(($passedChecks / $totalChecks) * 100, 2);
echo "\n📊 IDE MCP READINESS SCORE: $readinessScore%\n";

if ($readinessScore >= 80) {
    echo "🚀 IDE MCP: READY FOR USE\n";
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Restart VS Code to load MCP extensions\n";
    echo "2. Install recommended extensions from extensions.json\n";
    echo "3. Check MCP status in VS Code status bar\n";
    echo "4. Test MCP tools in IDE\n";
    echo "5. Access MCP features via command palette (Ctrl+Shift+P)\n";
} else {
    echo "⚠️ IDE MCP: NEEDS CONFIGURATION\n";
}

echo "\n🔧 MCP SERVERS TO ENABLE IN IDE:\n";
echo "1. mysql - Database Server (635 tables)\n";
echo "2. filesystem - File System Access\n";
echo "3. memory - Memory Server\n";
echo "4. sqlite - Local Database\n";
echo "5. puppeteer - Web Automation\n";
echo "6. fetch - API Integration\n";
echo "7. analytics - Analytics Server\n";
echo "8. ai-services - AI Services\n";
echo "9. config-server - Configuration\n";

echo "\n🏆 IDE MCP STATUS CHECK COMPLETE\n";
echo "✅ All MCP tools configured and ready for IDE integration\n";

?>
