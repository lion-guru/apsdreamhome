<?php
/**
 * APS Dream Home - MCP Configuration Import Handler
 * Process and import MCP configuration from Windsurf/Codeium
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>🔄 MCP Configuration Import</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imported = false;
    $configData = null;
    
    // Check if importing from file
    if (!empty($_POST['config_path'])) {
        $configPath = $_POST['config_path'];
        
        if (file_exists($configPath)) {
            try {
                $configContent = file_get_contents($configPath);
                $configData = json_decode($configContent, true);
                
                if ($configData) {
                    $imported = true;
                    echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 10px;'>";
                    echo "<h3>✅ Configuration Loaded from File</h3>";
                    echo "<p><strong>Source:</strong> {$configPath}</p>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
                    echo "<h3>❌ Invalid JSON in File</h3>";
                    echo "<p>The configuration file contains invalid JSON</p>";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
                echo "<h3>❌ Error Reading File</h3>";
                echo "<p>" . $e->getMessage() . "</p>";
                echo "</div>";
            }
        }
    }
    
    // Check if importing from manual input
    if (!empty($_POST['manual_config'])) {
        try {
            $configData = json_decode($_POST['manual_config'], true);
            
            if ($configData) {
                $imported = true;
                echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 10px;'>";
                echo "<h3>✅ Configuration Loaded from Manual Input</h3>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
                echo "<h3>❌ Invalid JSON in Manual Input</h3>";
                echo "<p>Please check your JSON syntax</p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
            echo "<h3>❌ Error Parsing Manual Input</h3>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    // Process the imported configuration
    if ($imported && $configData) {
        echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border-radius: 10px;'>";
        echo "<h3>🔧 Processing Configuration...</h3>";
        
        // Merge with existing APS Dream Home configuration
        $apsConfigFile = __DIR__ . '/config/mcp_servers.json';
        $apsConfig = [];
        
        if (file_exists($apsConfigFile)) {
            $apsConfig = json_decode(file_get_contents($apsConfigFile), true) ?: [];
        }
        
        // Ensure mcpServers structure exists
        if (!isset($apsConfig['mcpServers'])) {
            $apsConfig['mcpServers'] = [];
        }
        
        $importedCount = 0;
        $updatedCount = 0;
        
        // Process different configuration formats
        if (isset($configData['mcpServers'])) {
            // Standard MCP format
            foreach ($configData['mcpServers'] as $serverName => $serverConfig) {
                if (!isset($apsConfig['mcpServers'][$serverName])) {
                    $apsConfig['mcpServers'][$serverName] = $serverConfig;
                    $importedCount++;
                } else {
                    $apsConfig['mcpServers'][$serverName] = array_merge($apsConfig['mcpServers'][$serverName], $serverConfig);
                    $updatedCount++;
                }
            }
        } elseif (isset($configData['servers'])) {
            // Alternative format
            foreach ($configData['servers'] as $serverName => $serverConfig) {
                if (!isset($apsConfig['mcpServers'][$serverName])) {
                    $apsConfig['mcpServers'][$serverName] = $serverConfig;
                    $importedCount++;
                } else {
                    $apsConfig['mcpServers'][$serverName] = array_merge($apsConfig['mcpServers'][$serverName], $serverConfig);
                    $updatedCount++;
                }
            }
        }
        
        // Save the merged configuration
        $jsonContent = json_encode($apsConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (file_put_contents($apsConfigFile, $jsonContent)) {
            echo "<h4>✅ Import Successful!</h4>";
            echo "<p><strong>New servers imported:</strong> {$importedCount}</p>";
            echo "<p><strong>Existing servers updated:</strong> {$updatedCount}</p>";
            echo "<p><strong>Total servers in APS Dream Home:</strong> " . count($apsConfig['mcpServers']) . "</p>";
            
            // Display imported servers
            echo "<h5>📋 Imported/Updated Servers:</h5>";
            echo "<ul>";
            foreach ($apsConfig['mcpServers'] as $serverName => $serverConfig) {
                echo "<li>🔧 <strong>{$serverName}</strong>";
                if (isset($serverConfig['command'])) {
                    echo " - Command: {$serverConfig['command']}";
                }
                if (isset($serverConfig['args'])) {
                    echo " - Args: " . implode(', ', $serverConfig['args']);
                }
                echo "</li>";
            }
            echo "</ul>";
            
            // Next steps
            echo "<h5>🚀 Next Steps:</h5>";
            echo "<ol>";
            echo "<li><a href='/mcp_configuration_gui.php' target='_blank'>Open MCP Configuration GUI</a> to review and configure API keys</li>";
            echo "<li><a href='/start_mcp_servers.php' target='_blank'>Start MCP Servers</a> to activate imported servers</li>";
            echo "<li><a href='/mcp_dashboard.php' target='_blank'>View MCP Dashboard</a> to monitor server status</li>";
            echo "</ol>";
            
        } else {
            echo "<h4>❌ Failed to Save Configuration</h4>";
            echo "<p>Could not write to: {$apsConfigFile}</p>";
        }
        
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 10px; border-radius: 10px;'>";
    echo "<h3>❌ Invalid Request</h3>";
    echo "<p>Please use the import form to submit configuration</p>";
    echo "</div>";
}

// Back to importer
echo "<div style='background: #e2e3e5; padding: 15px; margin: 10px; border-radius: 10px;'>";
echo "<h3>🔗 Navigation</h3>";
echo "<p><a href='import_mcp_config.php'>← Back to Importer</a></p>";
echo "<p><a href='/mcp_configuration_gui.php' target='_blank'>🎛️ MCP Configuration GUI</a></p>";
echo "<p><a href='/mcp_dashboard.php' target='_blank'>📊 MCP Dashboard</a></p>";
echo "</div>";
?>
