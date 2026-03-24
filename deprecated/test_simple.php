<?php

echo "🚀 APS Dream Home - Simple Optimization Test\n";
echo "==========================================\n\n";

// Memory Usage Test
echo "📊 Memory Usage:\n";
$memory_before = memory_get_usage();
echo "  Memory Before: " . round($memory_before / 1024 / 1024, 2) . " MB\n";

// Configuration Test
echo "\n⚙️ Configuration Files:\n";
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
echo "\n🔒 Security Check:\n";
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $content = file_get_contents($env_file);
    $has_fake_keys = strpos($content, 'AIzaSyC1234567890') !== false;
    echo sprintf("  Fake API Keys: %s\n", $has_fake_keys ? '❌ Found' : '✅ Clean');
    
    $has_real_keys = strpos($content, 'YOUR_REAL_') !== false;
    echo sprintf("  Placeholder Keys: %s\n", $has_real_keys ? '⚠️ Need Real Keys' : '✅ Configured');
}

// IDE Settings Test
echo "\n🔌 IDE Settings:\n";
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

$memory_after = memory_get_usage();
echo "\n📊 Final Memory:\n";
echo "  Memory After: " . round($memory_after / 1024 / 1024, 2) . " MB\n";
echo "  Memory Increase: " . round(($memory_after - $memory_before) / 1024 / 1024, 2) . " MB\n";
echo "  Peak Memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Simple Test Complete!\n";
echo "📝 Recommendations:\n";
echo "   1. Restart VS Code for new settings\n";
echo "   2. Add real Gemini API key to .env\n";
echo "   3. Monitor RAM usage during development\n";
echo "   4. Remove unused VS Code extensions\n";
echo str_repeat("=", 50) . "\n";
?>
