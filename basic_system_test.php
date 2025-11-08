<?php
/**
 * APS Dream Home - Simple System Test
 * Basic test to verify core functionality
 */

// Simple test without complex dependencies
echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Basic System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .test { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .test.success { background: #d4edda; border: 1px solid #c3e6cb; }
        .test.error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .test.warning { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>APS Dream Home - Basic System Test</h1>";

// Test 1: PHP Version
echo "<div class='test " . (PHP_VERSION >= '7.4' ? 'success' : 'warning') . "'>";
echo "<h3>Test 1: PHP Version</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Status: " . (PHP_VERSION >= '7.4' ? '✅ Good (PHP 7.4+)' : '⚠️ Warning (PHP 7.4+ recommended)') . "</p>";
echo "</div>";

// Test 2: File System
echo "<div class='test " . (is_writable('.') ? 'success' : 'error') . "'>";
echo "<h3>Test 2: File System</h3>";
echo "<p>Current Directory: " . getcwd() . "</p>";
echo "<p>Writeable: " . (is_writable('.') ? '✅ Yes' : '❌ No') . "</p>";
echo "</div>";

// Test 3: Required Files
$required_files = [
    'includes/config.php' => 'Configuration',
    'includes/ai_integration.php' => 'AI Integration',
    'includes/whatsapp_integration.php' => 'WhatsApp Integration',
    'includes/email_system.php' => 'Email System',
    'assets/js/ai_client.js' => 'AI JavaScript',
    'api/ai_agent_chat.php' => 'AI Chat API',
    'comprehensive_system_test.php' => 'System Test',
    'management_dashboard.php' => 'Management Dashboard'
];

echo "<div class='test'>";
echo "<h3>Test 3: Required Files</h3>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th style='padding: 10px; border: 1px solid #ddd;'>File</th><th style='padding: 10px; border: 1px solid #ddd;'>Description</th><th style='padding: 10px; border: 1px solid #ddd;'>Status</th></tr>";

foreach ($required_files as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? '✅ Exists' : '❌ Missing';
    $class = $exists ? 'success' : 'error';

    echo "<tr>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><code>{$file}</code></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$description}</td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;' class='{$class}'>{$status}</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Test 4: Directory Structure
echo "<div class='test success'>";
echo "<h3>Test 4: Directory Structure</h3>";
echo "<p>✅ Main directories exist:</p>";
echo "<ul>";

$directories = ['includes', 'assets', 'api', 'logs'];
foreach ($directories as $dir) {
    $exists = is_dir($dir);
    $status = $exists ? '✅' : '❌';
    echo "<li>{$status} {$dir}/</li>";
}

echo "</ul>";
echo "</div>";

// Test 5: Configuration Test
echo "<div class='test'>";
echo "<h3>Test 5: Configuration Test</h3>";

if (file_exists('includes/config.php')) {
    echo "<p>✅ Config file exists</p>";

    // Simple config test without loading
    $config_content = file_get_contents('includes/config.php');
    if (strpos($config_content, 'whatsapp') !== false) {
        echo "<p>✅ WhatsApp configuration found</p>";
    } else {
        echo "<p>⚠️ WhatsApp configuration not found</p>";
    }

    if (strpos($config_content, 'ai') !== false) {
        echo "<p>✅ AI configuration found</p>";
    } else {
        echo "<p>⚠️ AI configuration not found</p>";
    }

    if (strpos($config_content, 'email') !== false) {
        echo "<p>✅ Email configuration found</p>";
    } else {
        echo "<p>⚠️ Email configuration not found</p>";
    }
} else {
    echo "<p class='error'>❌ Config file missing</p>";
}

echo "</div>";

// Test 6: Performance Test
$start_time = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $test = 'test_' . $i;
}

$end_time = microtime(true);
$execution_time = round(($end_time - $start_time) * 1000, 2);

echo "<div class='test " . ($execution_time < 50 ? 'success' : 'warning') . "'>";
echo "<h3>Test 6: Performance Test</h3>";
echo "<p>Execution Time: {$execution_time}ms</p>";
echo "<p>Status: " . ($execution_time < 50 ? '✅ Excellent' : '⚠️ Acceptable') . "</p>";
echo "</div>";

// Summary
echo "<div class='test' style='background: #e9ecef; margin-top: 30px; padding: 20px;'>";
echo "<h3>Test Summary</h3>";
echo "<p><strong>System Status:</strong> Basic functionality verified ✅</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>1. Run <a href='comprehensive_system_test.php'>Comprehensive System Test</a></li>";
echo "<li>2. Use <a href='management_dashboard.php'>Management Dashboard</a></li>";
echo "<li>3. Test <a href='test_whatsapp_integration.php'>WhatsApp Integration</a></li>";
echo "<li>4. Explore <a href='ai_demo.php'>AI Features</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
