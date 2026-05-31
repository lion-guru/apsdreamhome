<?php
/**
 * Update all Gemini configurations in database and files
 */

$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "=== UPDATING GEMINI CONFIGURATIONS ===\n\n";

// Get current configuration from database
echo "🔍 READING CURRENT CONFIGURATION:\n";
$result = $mysqli->query("SELECT config_key, config_value FROM app_config WHERE config_key LIKE '%gemini%' OR config_key LIKE '%ai%' ORDER BY config_key");

$currentConfig = [];
while ($row = $result->fetch_assoc()) {
    $currentConfig[$row['config_key']] = $row['config_value'];
    echo "  📋 {$row['config_key']}: " . ($row['config_key'] && strpos($row['config_key'], 'key') !== false ? '***SET***' : $row['config_value']) . "\n";
}

// Update config files
echo "\n🔧 UPDATING CONFIGURATION FILES:\n";

// 1. Update config/gemini_config.php
$geminiConfigPath = __DIR__ . '/config/gemini_config.php';
$geminiConfigContent = '<?php

// Function to parse a .env file and set environment variables
function loadEnv($filePath) {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), \'#\') === 0) {
                continue;
            }
            list($name, $value) = explode(\'=\', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$projectRoot = __DIR__ . \'/..\';
loadEnv($projectRoot . \'/.env\');

// Gemini AI Configuration - Updated from database
return [
    \'api_key\' => $_ENV[\'GEMINI_API_KEY\'] ?? \'' . ($currentConfig['gemini_api_key'] ?? '') . '\',
    \'api_url\' => \'https://generativelanguage.googleapis.com/v1beta/models/' . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . ':generateContent\',
    \'project_id\' => \'' . ($currentConfig['gemini_project_id'] ?? '') . '\',
    \'model\' => \'' . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . '\',
    \'temperature\' => ' . ($currentConfig['gemini_temperature'] ?? '0.7') . ',
    \'max_tokens\' => ' . ($currentConfig['gemini_max_tokens'] ?? '8192') . ',
    \'enabled\' => ' . ($currentConfig['gemini_enabled'] === 'true' ? 'true' : 'false') . '
];

';

file_put_contents($geminiConfigPath, $geminiConfigContent);
echo "  ✅ Updated: config/gemini_config.php\n";

// 2. Update app/config/gemini_config.php
$appGeminiConfigPath = __DIR__ . '/app/config/gemini_config.php';
$appGeminiConfigContent = '<?php

// Gemini AI Configuration - Updated from database
return [
    \'api_url\' => \'https://generativelanguage.googleapis.com/v1beta/models/' . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . ':generateContent\',
    \'model\' => \'' . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . '\',
    \'project_id\' => \'' . ($currentConfig['gemini_project_id'] ?? '') . '\',
    \'api_key\' => \'' . ($currentConfig['gemini_api_key'] ?? '') . '\',
    \'temperature\' => ' . ($currentConfig['gemini_temperature'] ?? '0.7') . ',
    \'max_tokens\' => ' . ($currentConfig['gemini_max_tokens'] ?? '8192') . ',
    \'enabled\' => ' . ($currentConfig['gemini_enabled'] === 'true' ? 'true' : 'false') . '
];

';

file_put_contents($appGeminiConfigPath, $appGeminiConfigContent);
echo "  ✅ Updated: app/config/gemini_config.php\n";

// 3. Update .env file
$envPath = __DIR__ . '/.env';
$envContent = '';

// Read existing .env if it exists
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Remove existing Gemini config lines
    $lines = explode("\n", $envContent);
    $filteredLines = [];
    foreach ($lines as $line) {
        if (strpos($line, 'GEMINI_') === 0 || strpos($line, 'AI_') === 0) {
            continue; // Skip existing Gemini/AI lines
        }
        $filteredLines[] = $line;
    }
    $envContent = implode("\n", $filteredLines);
}

// Add new Gemini configuration
$envContent .= "\n# Gemini AI Configuration - Auto-generated from database\n";
$envContent .= "GEMINI_PROJECT_ID=" . ($currentConfig['gemini_project_id'] ?? '') . "\n";
$envContent .= "GEMINI_API_KEY=" . ($currentConfig['gemini_api_key'] ?? '') . "\n";
$envContent .= "GEMINI_MODEL=" . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . "\n";
$envContent .= "GEMINI_TEMPERATURE=" . ($currentConfig['gemini_temperature'] ?? '0.7') . "\n";
$envContent .= "GEMINI_MAX_TOKENS=" . ($currentConfig['gemini_max_tokens'] ?? '8192') . "\n";
$envContent .= "GEMINI_ENABLED=" . ($currentConfig['gemini_enabled'] ?? 'false') . "\n";
$envContent .= "AI_SERVICE_PROVIDER=" . ($currentConfig['ai_service_provider'] ?? 'google') . "\n";
$envContent .= "AI_ENABLED=" . ($currentConfig['ai_enabled'] ?? 'false') . "\n";

file_put_contents($envPath, $envContent);
echo "  ✅ Updated: .env file\n";

// 4. Update VS Code settings
$vscodeSettingsPath = __DIR__ . '/.vscode/settings.json';
if (file_exists($vscodeSettingsPath)) {
    $settingsContent = file_get_contents($vscodeSettingsPath);
    
    // Update Gemini settings
    $settingsContent = preg_replace(
        '/"google\.gemini\.projectId":\s*"[^"]*"/',
        '"google.gemini.projectId": "' . ($currentConfig['gemini_project_id'] ?? '') . '"',
        $settingsContent
    );
    
    $settingsContent = preg_replace(
        '/"google\.gemini\.apiKey":\s*"[^"]*"/',
        '"google.gemini.apiKey": "' . ($currentConfig['gemini_api_key'] ?? '') . '"',
        $settingsContent
    );
    
    $settingsContent = preg_replace(
        '/"gemini-code-assist\.projectId":\s*"[^"]*"/',
        '"gemini-code-assist.projectId": "' . ($currentConfig['gemini_project_id'] ?? '') . '"',
        $settingsContent
    );
    
    $settingsContent = preg_replace(
        '/"gemini-code-assist\.apiKey":\s*"[^"]*"/',
        '"gemini-code-assist.apiKey": "' . ($currentConfig['gemini_api_key'] ?? '') . '"',
        $settingsContent
    );
    
    file_put_contents($vscodeSettingsPath, $settingsContent);
    echo "  ✅ Updated: .vscode/settings.json\n";
}

// 5. Update MCP configuration
$mcpConfigPath = __DIR__ . '/.windsurf/mcp_config.env';
if (file_exists($mcpConfigPath)) {
    $mcpContent = file_get_contents($mcpConfigPath);
    
    // Update AI-related MCP settings
    $mcpContent = preg_replace(
        '/AI_VALUATION_API_KEY=[^\n]*/',
        'AI_VALUATION_API_KEY=' . ($currentConfig['gemini_api_key'] ?? ''),
        $mcpContent
    );
    
    $mcpContent = preg_replace(
        '/AI_ANALYTICS_API_KEY=[^\n]*/',
        'AI_ANALYTICS_API_KEY=' . ($currentConfig['gemini_api_key'] ?? ''),
        $mcpContent
    );
    
    file_put_contents($mcpConfigPath, $mcpContent);
    echo "  ✅ Updated: .windsurf/mcp_config.env\n";
}

echo "\n📋 CONFIGURATION SUMMARY:\n";
echo "  📊 Database Config: app_config table\n";
echo "  📄 PHP Config: config/gemini_config.php\n";
echo "  📄 App Config: app/config/gemini_config.php\n";
echo "  📄 Environment: .env file\n";
echo "  📄 VS Code: .vscode/settings.json\n";
echo "  📄 MCP Config: .windsurf/mcp_config.env\n";

echo "\n🎯 CURRENT STATUS:\n";
if (!empty($currentConfig['gemini_project_id']) && !empty($currentConfig['gemini_api_key'])) {
    echo "  ✅ Gemini configuration is ready\n";
    echo "  ✅ Project ID: " . $currentConfig['gemini_project_id'] . "\n";
    echo "  ✅ API Key: ***SET***\n";
    echo "  ✅ Model: " . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . "\n";
    echo "  ✅ Status: " . ($currentConfig['gemini_enabled'] === 'true' ? 'ENABLED' : 'DISABLED') . "\n";
} else {
    echo "  ⚠️ Gemini configuration needs setup\n";
    echo "  ❌ Project ID: " . (empty($currentConfig['gemini_project_id']) ? 'NOT SET' : 'SET') . "\n";
    echo "  ❌ API Key: " . (empty($currentConfig['gemini_api_key']) ? 'NOT SET' : 'SET') . "\n";
    echo "  ✅ Model: " . ($currentConfig['gemini_model'] ?? 'gemini-1.5-flash') . "\n";
    echo "  ❌ Status: " . ($currentConfig['gemini_enabled'] === 'true' ? 'ENABLED' : 'DISABLED') . "\n";
}

echo "\n🔧 NEXT STEPS:\n";
if (empty($currentConfig['gemini_project_id']) || empty($currentConfig['gemini_api_key'])) {
    echo "  1. Get Google Cloud Project ID\n";
    echo "  2. Get Gemini API Key\n";
    echo "  3. Update database configuration:\n";
    echo "     UPDATE app_config SET config_value = 'YOUR_PROJECT_ID' WHERE config_key = 'gemini_project_id';\n";
    echo "     UPDATE app_config SET config_value = 'YOUR_API_KEY' WHERE config_key = 'gemini_api_key';\n";
    echo "     UPDATE app_config SET config_value = 'true' WHERE config_key = 'gemini_enabled';\n";
    echo "  4. Run this script again to sync all configurations\n";
} else {
    echo "  1. Restart VS Code\n";
    echo "  2. Test Gemini Code Assist\n";
    echo "  3. Verify AI suggestions work\n";
    echo "  4. Enjoy AI-powered coding!\n";
}

echo "\n🏁 CONFIGURATION UPDATE COMPLETE\n";

?>
