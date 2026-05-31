<?php
/**
 * Check Gemini/AI Configuration in Database
 */

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== GEMINI/AI CONFIGURATION CHECK ===\n\n";

// 1. Check app_config table for AI/Gemini settings
echo "🔍 CHECKING APP_CONFIG TABLE:\n";
$result = $mysqli->query("SELECT * FROM app_config WHERE config_key LIKE '%gemini%' OR config_key LIKE '%ai%' OR config_key LIKE '%google%'");
echo "📊 AI/Gemini Config Records: " . $result->num_rows . "\n";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  📋 {$row['config_key']}: {$row['config_value']}\n";
    }
} else {
    echo "  ℹ️ No AI/Gemini configuration found in app_config\n";
}

// 2. Check api_configs table
echo "\n🔍 CHECKING API_CONFIGS TABLE:\n";
$result = $mysqli->query("SELECT * FROM api_configs WHERE service_name LIKE '%gemini%' OR service_name LIKE '%ai%' OR service_name LIKE '%google%'");
echo "📊 API Config Records: " . $result->num_rows . "\n";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  📋 {$row['service_name']}: {$row['api_key']} ({$row['status']})\n";
    }
} else {
    echo "  ℹ️ No AI/Gemini API configs found\n";
}

// 3. Check .env file
echo "\n🔍 CHECKING .env FILE:\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    $foundConfigs = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'GEMINI') !== false || strpos($line, 'AI') !== false || strpos($line, 'GOOGLE') !== false) {
            $foundConfigs[] = trim($line);
        }
    }
    
    if (!empty($foundConfigs)) {
        echo "  📊 Found " . count($foundConfigs) . " AI/Gemini configurations:\n";
        foreach ($foundConfigs as $config) {
            echo "    • $config\n";
        }
    } else {
        echo "  ℹ️ No AI/Gemini configurations found in .env\n";
    }
} else {
    echo "  ❌ .env file not found\n";
}

// 4. Check config files
echo "\n🔍 CHECKING CONFIG FILES:\n";
$configFiles = [
    'config/app_config.json',
    'config/ai_config.json',
    'config/gemini_config.json'
];

foreach ($configFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo "  ✅ Found: $file\n";
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        if ($data && isset($data['gemini']) || isset($data['ai']) || isset($data['google'])) {
            echo "    📋 Contains AI/Gemini configuration\n";
        }
    } else {
        echo "  ❌ Missing: $file\n";
    }
}

// 5. Check if we need to create configuration
echo "\n🔧 CONFIGURATION STATUS:\n";

$needsConfig = false;

// Check if any config exists
$hasConfig = false;
$result = $mysqli->query("SELECT COUNT(*) as count FROM app_config WHERE config_key LIKE '%gemini%' OR config_key LIKE '%ai%' OR config_key LIKE '%google%'");
if ($result->fetch_assoc()['count'] > 0) $hasConfig = true;

$result = $mysqli->query("SELECT COUNT(*) as count FROM api_configs WHERE service_name LIKE '%gemini%' OR service_name LIKE '%ai%' OR service_name LIKE '%google%'");
if ($result->fetch_assoc()['count'] > 0) $hasConfig = true;

if (!$hasConfig) {
    echo "  ⚠️ No Gemini/AI configuration found\n";
    echo "  🔧 Need to create configuration\n";
    $needsConfig = true;
} else {
    echo "  ✅ Configuration exists in database\n";
}

// 6. Create configuration if needed
if ($needsConfig) {
    echo "\n🔧 CREATING GEMINI CONFIGURATION:\n";
    
    // Insert into app_config
    $configs = [
        'gemini_project_id' => '',
        'gemini_api_key' => '',
        'gemini_model' => 'gemini-1.5-flash',
        'gemini_enabled' => 'true',
        'ai_service_provider' => 'google',
        'ai_enabled' => 'true'
    ];
    
    foreach ($configs as $key => $value) {
        $stmt = $mysqli->prepare("INSERT INTO app_config (config_key, config_value, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
        echo "  ✅ Created: $key\n";
    }
    
    // Insert into api_configs
    $stmt = $mysqli->prepare("INSERT INTO api_configs (service_name, api_key, api_endpoint, status, created_at) VALUES (?, ?, ?, ?, NOW())");
    $serviceName = 'gemini';
    $apiKey = '';
    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    $status = 'active';
    $stmt->bind_param('ssss', $serviceName, $apiKey, $endpoint, $status);
    $stmt->execute();
    echo "  ✅ Created API config for Gemini\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "  1. Update gemini_project_id in app_config table\n";
echo "  2. Update gemini_api_key in app_config table\n";
echo "  3. Set gemini_enabled to true in app_config\n";
echo "  4. Update api_key in api_configs table for Gemini service\n";
echo "  5. Restart VS Code after updating configurations\n";

echo "\n🏁 CONFIGURATION CHECK COMPLETE\n";

?>
