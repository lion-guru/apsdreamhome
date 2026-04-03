<?php
/**
 * Find all configuration tables and check for Gemini/AI settings
 */

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== FINDING CONFIGURATION TABLES ===\n\n";

// 1. Get all tables
$result = $mysqli->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "📊 Total Tables: " . count($tables) . "\n\n";

// 2. Find configuration-related tables
$configTables = [];
foreach ($tables as $table) {
    if (strpos($table, 'config') !== false || 
        strpos($table, 'setting') !== false || 
        strpos($table, 'option') !== false ||
        strpos($table, 'api') !== false ||
        strpos($table, 'env') !== false) {
        $configTables[] = $table;
    }
}

echo "🔍 CONFIGURATION TABLES FOUND:\n";
foreach ($configTables as $table) {
    echo "  📋 $table\n";
}

// 3. Check each config table for AI/Gemini data
echo "\n🔍 CHECKING FOR AI/GEMINI DATA:\n";
$foundConfigs = [];

foreach ($configTables as $table) {
    // Get table structure first
    $structureResult = $mysqli->query("DESCRIBE `$table`");
    $columns = [];
    while ($col = $structureResult->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
    
    // Look for relevant columns
    $relevantColumns = [];
    foreach ($columns as $column) {
        if (strpos($column, 'gemini') !== false || 
            strpos($column, 'ai') !== false || 
            strpos($column, 'google') !== false ||
            strpos($column, 'key') !== false ||
            strpos($column, 'value') !== false ||
            strpos($column, 'config') !== false) {
            $relevantColumns[] = $column;
        }
    }
    
    if (!empty($relevantColumns)) {
        echo "\n  📄 Table: $table\n";
        echo "    🔍 Relevant Columns: " . implode(', ', $relevantColumns) . "\n";
        
        // Try to find AI/Gemini records
        $whereConditions = [];
        foreach ($relevantColumns as $col) {
            if (strpos($col, 'gemini') !== false || strpos($col, 'ai') !== false || strpos($col, 'google') !== false) {
                $whereConditions[] = "`$col` LIKE '%gemini%' OR `$col` LIKE '%ai%' OR `$col` LIKE '%google%'";
            }
        }
        
        if (!empty($whereConditions)) {
            $sql = "SELECT * FROM `$table` WHERE " . implode(' OR ', $whereConditions) . " LIMIT 5";
            $dataResult = $mysqli->query($sql);
            
            if ($dataResult && $dataResult->num_rows > 0) {
                echo "    ✅ Found AI/Gemini data:\n";
                while ($row = $dataResult->fetch_assoc()) {
                    echo "      📝 " . json_encode($row, JSON_PRETTY_PRINT) . "\n";
                    $foundConfigs[] = ['table' => $table, 'data' => $row];
                }
            } else {
                echo "    ℹ️ No AI/Gemini data found\n";
            }
        }
    }
}

// 4. Check for existing configuration files
echo "\n🔍 CHECKING CONFIGURATION FILES:\n";
$configPaths = [
    'config/',
    '.windsurf/',
    'storage/config/',
    'app/config/'
];

$configFiles = [];
foreach ($configPaths as $path) {
    $fullPath = __DIR__ . '/' . $path;
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '*.{json,php,env,ini}', GLOB_BRACE);
        foreach ($files as $file) {
            if (is_file($file)) {
                $configFiles[] = $file;
            }
        }
    }
}

echo "📁 Configuration Files Found: " . count($configFiles) . "\n";
foreach ($configFiles as $file) {
    $relativePath = str_replace(__DIR__ . '/', '', $file);
    echo "  📄 $relativePath\n";
    
    // Check file content for AI/Gemini references
    $content = file_get_contents($file);
    if (strpos($content, 'gemini') !== false || strpos($content, 'ai') !== false || strpos($content, 'google') !== false) {
        echo "    ✅ Contains AI/Gemini references\n";
        
        // Extract relevant lines
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'gemini') !== false || strpos($line, 'ai') !== false || strpos($line, 'google') !== false) {
                echo "      Line " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    }
}

// 5. Create configuration if needed
if (empty($foundConfigs)) {
    echo "\n🔧 NO AI/GEMINI CONFIGURATION FOUND\n";
    echo "🔧 CREATING CONFIGURATION...\n";
    
    // Create app_config table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS app_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            config_key VARCHAR(255) UNIQUE NOT NULL,
            config_value TEXT,
            config_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_config_key (config_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($mysqli->query($createTableSQL)) {
        echo "  ✅ Created app_config table\n";
        
        // Insert default Gemini configuration
        $configs = [
            'gemini_project_id' => '',
            'gemini_api_key' => '',
            'gemini_model' => 'gemini-1.5-flash',
            'gemini_enabled' => 'false',
            'gemini_max_tokens' => '8192',
            'gemini_temperature' => '0.7',
            'ai_service_provider' => 'google',
            'ai_enabled' => 'false'
        ];
        
        foreach ($configs as $key => $value) {
            $stmt = $mysqli->prepare("INSERT INTO app_config (config_key, config_value, config_type, description) VALUES (?, ?, 'string', ?)");
            $description = "Gemini/AI configuration: " . str_replace('_', ' ', $key);
            $stmt->bind_param('sss', $key, $value, $description);
            $stmt->execute();
            echo "    ✅ Created config: $key\n";
        }
        
        echo "  ✅ Configuration created successfully\n";
        
    } else {
        echo "  ❌ Failed to create app_config table\n";
    }
} else {
    echo "\n✅ AI/Gemini configuration already exists\n";
}

// 6. Show current configuration
echo "\n📊 CURRENT CONFIGURATION:\n";
$result = $mysqli->query("SELECT * FROM app_config WHERE config_key LIKE '%gemini%' OR config_key LIKE '%ai%' ORDER BY config_key");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $value = $row['config_value'];
        if (strpos($row['config_key'], 'key') !== false || strpos($row['config_key'], 'secret') !== false) {
            $value = $value ? '***SET***' : '***EMPTY***';
        }
        echo "  📋 {$row['config_key']}: $value\n";
    }
}

echo "\n🎯 NEXT STEPS:\n";
echo "  1. Update gemini_project_id with your Google Cloud Project ID\n";
echo "  2. Update gemini_api_key with your Gemini API Key\n";
echo "  3. Set gemini_enabled to 'true'\n";
echo "  4. Update VS Code settings with same values\n";
echo "  5. Restart VS Code\n";

echo "\n🏁 CONFIGURATION ANALYSIS COMPLETE\n";

?>
