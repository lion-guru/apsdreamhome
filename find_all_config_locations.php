<?php
/**
 * Find all configuration locations in project where MLM/Payout details might be stored
 */

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== FINDING ALL CONFIGURATION LOCATIONS ===\n\n";

// 1. Check database tables with config data
echo "🔍 DATABASE CONFIGURATION TABLES:\n";
$result = $mysqli->query("SHOW TABLES");
$configTables = [];

while ($row = $result->fetch_array()) {
    $tableName = $row[0];
    if (strpos($tableName, 'config') !== false || 
        strpos($tableName, 'setting') !== false || 
        strpos($tableName, 'option') !== false ||
        strpos($tableName, 'mlm') !== false) {
        $configTables[] = $tableName;
    }
}

foreach ($configTables as $table) {
    echo "  📋 $table\n";
    
    // Get sample data
    $sampleResult = $mysqli->query("SELECT * FROM `$table` LIMIT 3");
    if ($sampleResult && $sampleResult->num_rows > 0) {
        echo "    📝 Sample data:\n";
        while ($sampleRow = $sampleResult->fetch_assoc()) {
            echo "      " . json_encode($sampleRow, JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// 2. Check JSON configuration files
echo "\n🔍 JSON CONFIGURATION FILES:\n";
$jsonFiles = [
    'config/app_config.json',
    'config/mlm_config.json',
    'config/payout_config.json',
    'config/commission_config.json',
    'storage/config/mlm_settings.json',
    'storage/config/payout_settings.json'
];

foreach ($jsonFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "  ✅ Found: $file\n";
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        if ($data) {
            echo "    📊 Contains " . count($data) . " configuration items\n";
            
            // Look for MLM/Payout specific settings
            $relevantKeys = [];
            foreach ($data as $key => $value) {
                if (strpos($key, 'payout') !== false || 
                    strpos($key, 'mlm') !== false || 
                    strpos($key, 'commission') !== false ||
                    strpos($key, 'threshold') !== false ||
                    strpos($key, 'minimum') !== false) {
                    $relevantKeys[] = $key;
                }
            }
            
            if (!empty($relevantKeys)) {
                echo "    🎯 Relevant keys: " . implode(', ', $relevantKeys) . "\n";
            }
        }
    } else {
        echo "  ❌ Missing: $file\n";
    }
}

// 3. Check PHP configuration files
echo "\n🔍 PHP CONFIGURATION FILES:\n";
$phpConfigFiles = [
    'config/mlm_settings.php',
    'config/payout_settings.php',
    'config/commission_settings.php',
    'app/config/mlm.php',
    'app/config/payout.php'
];

foreach ($phpConfigFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "  ✅ Found: $file\n";
        $content = file_get_contents($filePath);
        
        // Look for relevant configuration
        if (strpos($content, 'payout') !== false || 
            strpos($content, 'mlm') !== false || 
            strpos($content, 'commission') !== false ||
            strpos($content, 'threshold') !== false ||
            strpos($content, 'minimum') !== false) {
            echo "    🎯 Contains MLM/Payout configuration\n";
            
            // Extract relevant lines
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, 'payout') !== false || 
                    strpos($line, 'threshold') !== false ||
                    strpos($line, 'minimum') !== false) {
                    echo "      Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    } else {
        echo "  ❌ Missing: $file\n";
    }
}

// 4. Check .env file
echo "\n🔍 ENVIRONMENT FILE (.env):\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "  ✅ Found: .env\n";
    $content = file_get_contents($envFile);
    $lines = explode("\n", $content);
    
    $envConfigs = [];
    foreach ($lines as $line) {
        if (strpos($line, 'PAYOUT') !== false || 
            strpos($line, 'MLM') !== false || 
            strpos($line, 'COMMISSION') !== false ||
            strpos($line, 'THRESHOLD') !== false ||
            strpos($line, 'MINIMUM') !== false) {
            $envConfigs[] = trim($line);
        }
    }
    
    if (!empty($envConfigs)) {
        echo "    📊 Found " . count($envConfigs) . " relevant configurations:\n";
        foreach ($envConfigs as $config) {
            echo "      • $config\n";
        }
    } else {
        echo "    ℹ️ No MLM/Payout configurations found\n";
    }
} else {
    echo "  ❌ Missing: .env\n";
}

// 5. Check MCP configuration
echo "\n🔍 MCP CONFIGURATION FILES:\n";
$mcpFiles = [
    '.windsurf/mcp_config.env',
    '.windsurf/mcp_servers.json'
];

foreach ($mcpFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "  ✅ Found: $file\n";
        $content = file_get_contents($filePath);
        
        if (strpos($content, 'PAYOUT') !== false || 
            strpos($content, 'MLM') !== false || 
            strpos($content, 'COMMISSION') !== false) {
            echo "    🎯 Contains MLM/Payout configuration\n";
            
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (strpos($line, 'PAYOUT') !== false || 
                    strpos($line, 'MLM') !== false || 
                    strpos($line, 'COMMISSION') !== false) {
                    echo "      Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

// 6. Check service classes for hardcoded values
echo "\n🔍 SERVICE CLASSES WITH CONFIGURATION:\n";
$serviceFiles = [
    'app/Services/CommissionService.php',
    'app/Services/PaymentService.php',
    'app/Services/MLM/CommissionService.php',
    'app/Services/Commission/HybridManager.php'
];

foreach ($serviceFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "  📄 Checking: $file\n";
        $content = file_get_contents($filePath);
        
        // Look for hardcoded configuration values
        $patterns = [
            '/payout.*threshold.*\d+/' => 'Payout threshold',
            '/minimum.*amount.*\d+/' => 'Minimum amount',
            '/commission.*rate.*\d+/' => 'Commission rate',
            '/level.*\d+.*commission/' => 'Level commission'
        ];
        
        foreach ($patterns as $pattern => $description) {
            if (preg_match($pattern, $content, $matches)) {
                echo "    🎯 Found $description: " . $matches[0] . "\n";
            }
        }
    }
}

// 7. Summary of where configuration should be
echo "\n📋 CONFIGURATION LOCATION SUMMARY:\n";
echo "  🗄️ DATABASE: app_config table (created)\n";
echo "  📄 JSON FILES: config/ directory\n";
echo "  📄 PHP FILES: config/ and app/config/ directories\n";
echo "  🌍 ENVIRONMENT: .env file\n";
echo "  🔧 MCP: .windsurf/ directory\n";
echo "  💻 SERVICES: app/Services/ classes\n";

echo "\n🎯 RECOMMENDATION:\n";
echo "  ✅ Use app_config table for centralized configuration\n";
echo "  ✅ Update all files to read from database\n";
echo "  ✅ Keep hardcoded values in service classes to minimum\n";
echo "  ✅ Use .env for environment-specific values\n";

echo "\n🏁 CONFIGURATION LOCATION ANALYSIS COMPLETE\n";

?>
