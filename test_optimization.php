<?php

echo "🚀 APS Dream Home - Optimization Test Suite\n";
echo "==========================================\n\n";

// Memory Usage Test
echo "📊 Memory Usage Test:\n";
$memory_before = memory_get_usage();
echo "Memory Before: " . round($memory_before / 1024 / 1024, 2) . " MB\n";

// Load test classes
require_once __DIR__ . '/app/Services/GeminiService.php';
require_once __DIR__ . '/app/Core/Database/Database.php';
require_once __DIR__ . '/app/Core/Config.php';

$memory_after = memory_get_usage();
echo "Memory After Loading: " . round($memory_after / 1024 / 1024, 2) . " MB\n";
echo "Memory Increase: " . round(($memory_after - $memory_before) / 1024 / 1024, 2) . " MB\n\n";

// Configuration Test
echo "⚙️ Configuration Test:\n";
$config_files = [
    '.env' => file_exists(__DIR__ . '/.env'),
    'config/gemini_config.php' => file_exists(__DIR__ . '/config/gemini_config.php'),
    'config/app_config.json' => file_exists(__DIR__ . '/config/app_config.json'),
    '.vscode/settings.json' => file_exists(__DIR__ . '/.vscode/settings.json'),
    '.vscode/extensions.json' => file_exists(__DIR__ . '/.vscode/extensions.json')
];

foreach ($config_files as $file => $exists) {
    echo sprintf("  %s: %s\n", $file, $exists ? '✅ Found' : '❌ Missing');
}

// Security Test
echo "\n🔒 Security Test:\n";
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $content = file_get_contents($env_file);
    $has_fake_keys = strpos($content, 'AIzaSyC1234567890') !== false;
    echo sprintf("  Fake API Keys: %s\n", $has_fake_keys ? '❌ Found' : '✅ Clean');

    $has_real_keys = strpos($content, 'YOUR_REAL_') !== false;
    echo sprintf("  Placeholder Keys: %s\n", $has_real_keys ? '⚠️ Need Real Keys' : '✅ Configured');
}

// Performance Test
echo "\n⚡ Performance Test:\n";
$start_time = microtime(true);

// Test database connection
try {
    $db = \App\Core\Database::getInstance();
    echo "  Database Connection: ✅ Connected\n";
} catch (Exception $e) {
    echo "  Database Connection: ❌ Failed - " . $e->getMessage() . "\n";
}

// Test Gemini service
try {
    $gemini = new \App\Services\GeminiService();
    $config = $gemini->isConfigured();
    echo sprintf("  Gemini Service: %s\n", $config ? '✅ Configured' : '⚠️ Needs API Key');
} catch (Exception $e) {
    echo "  Gemini Service: ❌ Error - " . $e->getMessage() . "\n";
}

$end_time = microtime(true);
$execution_time = round(($end_time - $start_time) * 1000, 2);
echo "  Execution Time: {$execution_time}ms\n";

// IDE Extensions Test
echo "\n🔌 IDE Extensions Status:\n";
$settings_file = __DIR__ . '/.vscode/settings.json';
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);

    $memory_optimized = ($settings['intelephense.maxMemory'] ?? 2048) <= 1024;
    echo sprintf("  Memory Optimized: %s\n", $memory_optimized ? '✅ Yes' : '❌ No');

    $ai_disabled = ($settings['codium.codeCompletion.enable'] ?? true) === false;
    echo sprintf("  AI Features Disabled: %s\n", $ai_disabled ? '✅ Yes' : '❌ No');

    $experimental_disabled = ($settings['editor.experimentalGpuAcceleration'] ?? 'on') === 'off';
    echo sprintf("  Experimental Features: %s\n", $experimental_disabled ? '✅ Disabled' : '❌ Enabled');
}

// Final Status
echo "\n🎯 Optimization Status:\n";
$memory_peak = memory_get_peak_usage();
echo "  Peak Memory: " . round($memory_peak / 1024 / 1024, 2) . " MB\n";
echo "  Current Memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "  PHP Version: " . PHP_VERSION . "\n";
echo "  OS: " . PHP_OS . "\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Optimization Test Complete!\n";
echo "📝 Next Steps:\n";
echo "   1. Add real Gemini API key to .env\n";
echo "   2. Restart VS Code for new settings\n";
echo "   3. Monitor RAM usage during development\n";
echo "   4. Test all project functionalities\n";
echo str_repeat("=", 50) . "\n";
