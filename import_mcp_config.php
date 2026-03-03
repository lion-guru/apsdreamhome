<?php
/**
 * APS Dream Home - MCP Configuration Importer
 * Import existing MCP configuration from Windsurf/Codeium
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔧 MCP Configuration Importer</h1>";
echo "<p>Import your existing MCP configuration from Windsurf/Codeium</p>";

// Check if file exists in common locations
$configPaths = [
    'C:\Users\abhay\.codeium\windsurf\mcp_config.json',
    'C:\Users\abhay\.windsurf\mcp_config.json',
    'C:\Users\abhay\.config\windsurf\mcp_config.json',
    getenv('USERPROFILE') . '\.codeium\windsurf\mcp_config.json',
    getenv('USERPROFILE') . '\.windsurf\mcp_config.json'
];

$configFound = false;
$configData = null;

foreach ($configPaths as $path) {
    if (file_exists($path)) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 10px;'>";
        echo "<h3>✅ MCP Configuration Found!</h3>";
        echo "<p><strong>Location:</strong> {$path}</p>";
        
        try {
            $configContent = file_get_contents($path);
            $configData = json_decode($configContent, true);
            
            if ($configData) {
                echo "<p><strong>Status:</strong> Valid JSON configuration</p>";
                $configFound = true;
                
                // Display configuration summary
                echo "<h4>📊 Configuration Summary:</h4>";
                echo "<ul>";
                
                if (isset($configData['mcpServers'])) {
                    echo "<li><strong>MCP Servers:</strong> " . count($configData['mcpServers']) . " configured</li>";
                    
                    foreach ($configData['mcpServers'] as $serverName => $serverConfig) {
                        echo "<li>🔧 {$serverName}: ";
                        if (isset($serverConfig['command'])) {
                            echo "Command: {$serverConfig['command']}";
                        }
                        if (isset($serverConfig['args'])) {
                            echo " | Args: " . implode(', ', $serverConfig['args']);
                        }
                        echo "</li>";
                    }
                }
                
                if (isset($configData['servers'])) {
                    echo "<li><strong>Servers:</strong> " . count($configData['servers']) . " configured</li>";
                }
                
                echo "</ul>";
                
                // Import button
                echo "<form method='post' action='import_mcp_config.php'>";
                echo "<input type='hidden' name='config_path' value='{$path}'>";
                echo "<button type='submit' class='btn btn-primary' style='padding: 10px 20px; font-size: 16px;'>";
                echo "🚀 Import to APS Dream Home";
                echo "</button>";
                echo "</form>";
                
            } else {
                echo "<p><strong>Status:</strong> Invalid JSON format</p>";
            }
            
        } catch (Exception $e) {
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
        break;
    }
}

if (!$configFound) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
    echo "<h3>❌ MCP Configuration Not Found</h3>";
    echo "<p>Could not find MCP configuration file in any of these locations:</p>";
    echo "<ul>";
    foreach ($configPaths as $path) {
        echo "<li>{$path}</li>";
    }
    echo "</ul>";
    
    echo "<h4>🔧 Manual Import Option:</h4>";
    echo "<p>You can manually copy your MCP configuration and paste it below:</p>";
    
    echo "<form method='post' action='import_mcp_config.php'>";
    echo "<textarea name='manual_config' rows='10' cols='80' style='width: 100%; height: 200px; font-family: monospace;'";
    echo "placeholder='Paste your MCP configuration JSON here...'></textarea><br><br>";
    echo "<button type='submit' class='btn btn-primary' style='padding: 10px 20px; font-size: 16px;'>";
    echo "🚀 Import Manual Configuration";
    echo "</button>";
    echo "</form>";
    
    echo "</div>";
}

// Show current APS Dream Home MCP configuration
echo "<div style='background: #e2e3e5; padding: 15px; margin: 10px; border-radius: 10px;'>";
echo "<h3>📋 Current APS Dream Home MCP Configuration</h3>";

$currentConfigFile = __DIR__ . '/config/mcp_servers.json';
if (file_exists($currentConfigFile)) {
    $currentConfig = json_decode(file_get_contents($currentConfigFile), true);
    
    if (isset($currentConfig['mcpServers'])) {
        echo "<p><strong>Configured Servers:</strong> " . count($currentConfig['mcpServers']) . "</p>";
        echo "<ul>";
        foreach ($currentConfig['mcpServers'] as $serverName => $serverConfig) {
            echo "<li>🔧 {$serverName}</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>No current configuration found</p>";
}

echo "</div>";

// Quick access links
echo "<div style='background: #d1ecf1; padding: 15px; margin: 10px; border-radius: 10px;'>";
echo "<h3>🔗 Quick Access</h3>";
echo "<ul>";
echo "<li><a href='/mcp_configuration_gui.php' target='_blank'>🎛️ MCP Configuration GUI</a></li>";
echo "<li><a href='/mcp_dashboard.php' target='_blank'>📊 MCP Dashboard</a></li>";
echo "<li><a href='/start_mcp_servers.php' target='_blank'>🚀 Start MCP Servers</a></li>";
echo "</ul>";
echo "</div>";
?>
