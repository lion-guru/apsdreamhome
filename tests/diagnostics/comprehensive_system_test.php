<?php
/**
 * APS Dream Home - Comprehensive System Test
 * Tests all integrations: AI, WhatsApp, Email, Database
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

require_once 'includes/config.php';
require_once 'includes/ai_integration.php';
require_once 'includes/whatsapp_integration.php';
require_once 'includes/email_system.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>APS Dream Home - System Integration Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .test-card { margin: 20px 0; border-radius: 10px; }
        .success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .warning { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .info { background: linear-gradient(135deg, #17a2b8, #007bff); color: white; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .status-icon { font-size: 1.5em; margin-right: 10px; }
        .log-output { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9em; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class='container-fluid py-4'>
        <div class='row'>
            <div class='col-12'>
                <div class='card test-card info'>
                    <div class='card-body text-center'>
                        <h1><i class='fas fa-cogs me-3'></i>APS Dream Home - System Integration Test</h1>
                        <p class='mb-0'>Testing AI, WhatsApp, Email, and Database Integrations</p>
                        <small>Generated: " . date('Y-m-d H:i:s') . "</small>
                    </div>
                </div>
            </div>
        </div>";

$test_results = [];
$test_logs = [];

// Test 1: Database Connection
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-database me-2'></i>Database Connection Test</h3></div>";
echo "<div class='card-body'>";

try {
    global $config;
    $host = $config['database']['host'] ?? 'localhost';
    $dbname = $config['database']['database'] ?? 'apsdreamhome';
    $username = $config['database']['username'] ?? 'root';
    $password = $config['database']['password'] ?? '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
    echo "✅ Database Connected Successfully<br>";
    echo "<small>Host: $host | Database: $dbname</small>";
    echo "</div>";

    $test_results['database'] = ['status' => 'success', 'message' => 'Database connected successfully'];

} catch (Exception $e) {
    echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
    echo "❌ Database Connection Failed: " . $e->getMessage();
    echo "</div>";
    $test_results['database'] = ['status' => 'error', 'message' => $e->getMessage()];
}

echo "</div></div></div></div>";

// Test 2: AI Integration
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-robot me-2'></i>AI Integration Test</h3></div>";
echo "<div class='card-body'>";

try {
    $ai_integration = new AIDreamHome();
    echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
    echo "✅ AI Integration Class Loaded Successfully<br>";
    echo "<small>Provider: " . ($config['ai']['provider'] ?? 'Not configured') . " | Model: " . ($config['ai']['model'] ?? 'Not configured') . "</small>";
    echo "</div>";

    // Test AI Response
    $test_prompt = "Hello, can you help me with a simple PHP function?";
    $ai_response = $ai_integration->generateChatbotResponse($test_prompt);

    if ($ai_response && isset($ai_response['success']) && strlen($ai_response['success']) > 0) {
        echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
        echo "✅ AI Response Generated Successfully<br>";
        echo "<small>Response length: " . strlen($ai_response['success']) . " characters</small>";
        echo "</div>";
        $test_results['ai_response'] = ['status' => 'success', 'message' => 'AI response generated'];
    } else {
        echo "<div class='test-result warning'><i class='fas fa-exclamation-triangle status-icon'></i>";
        echo "⚠️ AI Response Empty or Failed";
        echo "</div>";
        $test_results['ai_response'] = ['status' => 'warning', 'message' => 'AI response empty'];
    }

} catch (Exception $e) {
    echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
    echo "❌ AI Integration Failed: " . $e->getMessage();
    echo "</div>";
    $test_results['ai'] = ['status' => 'error', 'message' => $e->getMessage()];
}

echo "</div></div></div></div>";

// Test 3: WhatsApp Integration
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-mobile-alt me-2'></i>WhatsApp Integration Test</h3></div>";
echo "<div class='card-body'>";

try {
    $whatsapp = new WhatsAppIntegration();

    echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
    echo "✅ WhatsApp Integration Class Loaded Successfully<br>";
    echo "<small>Phone: " . ($config['whatsapp']['phone_number'] ?? 'Not configured') . " | Provider: " . ($config['whatsapp']['api_provider'] ?? 'Not configured') . "</small>";
    echo "</div>";

    // Test WhatsApp Statistics
    $stats = $whatsapp->getWhatsAppStats();
    echo "<div class='test-result info'><i class='fas fa-chart-bar status-icon'></i>";
    echo "📊 WhatsApp Statistics:<br>";
    echo "<small>Total Sent: " . ($stats['total_sent'] ?? 0) . " | Success Rate: " . ($stats['success_rate'] ?? 0) . "%</small>";
    echo "</div>";

    $test_results['whatsapp'] = ['status' => 'success', 'message' => 'WhatsApp integration loaded'];

} catch (Exception $e) {
    echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
    echo "❌ WhatsApp Integration Failed: " . $e->getMessage();
    echo "</div>";
    $test_results['whatsapp'] = ['status' => 'error', 'message' => $e->getMessage()];
}

echo "</div></div></div></div>";

// Test 4: Email Integration
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-envelope me-2'></i>Email Integration Test</h3></div>";
echo "<div class='card-body'>";

try {
    $email_system = new EmailSystem();

    echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
    echo "✅ Email System Class Loaded Successfully<br>";
    echo "<small>SMTP: " . ($config['email']['smtp_host'] ?? 'Not configured') . " | From: " . ($config['email']['from_email'] ?? 'Not configured') . "</small>";
    echo "</div>";

    // Test Email Configuration
    $email_configured = !empty($config['email']['smtp_host']) && !empty($config['email']['from_email']);
    if ($email_configured) {
        echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
        echo "✅ Email Configuration Valid";
        echo "</div>";
        $test_results['email'] = ['status' => 'success', 'message' => 'Email system configured'];
    } else {
        echo "<div class='test-result warning'><i class='fas fa-exclamation-triangle status-icon'></i>";
        echo "⚠️ Email Configuration Incomplete";
        echo "</div>";
        $test_results['email'] = ['status' => 'warning', 'message' => 'Email configuration incomplete'];
    }

} catch (Exception $e) {
    echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
    echo "❌ Email Integration Failed: " . $e->getMessage();
    echo "</div>";
    $test_results['email'] = ['status' => 'error', 'message' => $e->getMessage()];
}

echo "</div></div></div></div>";

// Test 5: File System Check
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-folder me-2'></i>File System Check</h3></div>";
echo "<div class='card-body'>";

$required_files = [
    'includes/config.php' => 'Configuration File',
    'includes/ai_integration.php' => 'AI Integration',
    'includes/whatsapp_integration.php' => 'WhatsApp Integration',
    'includes/email_system.php' => 'Email System',
    'assets/js/ai_client.js' => 'AI JavaScript Client',
    'api/ai_agent_chat.php' => 'AI Chat API',
    'ai_demo.php' => 'AI Demo Page',
    'test_whatsapp_integration.php' => 'WhatsApp Test Page'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
        echo "✅ $description - <code>$file</code>";
        echo "</div>";
        $test_results['files'][$file] = 'success';
    } else {
        echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
        echo "❌ $description Missing - <code>$file</code>";
        echo "</div>";
        $test_results['files'][$file] = 'error';
    }
}

echo "</div></div></div></div>";

// Test 6: Configuration Validation
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-cog me-2'></i>Configuration Validation</h3></div>";
echo "<div class='card-body'>";

$config_checks = [
    'ai' => ['enabled' => 'AI System', 'api_key' => 'OpenRouter API Key', 'model' => 'AI Model'],
    'whatsapp' => ['enabled' => 'WhatsApp Integration', 'phone_number' => 'WhatsApp Number'],
    'email' => ['enabled' => 'Email System', 'smtp_host' => 'SMTP Host', 'from_email' => 'From Email']
];

foreach ($config_checks as $section => $checks) {
    foreach ($checks as $key => $description) {
        $value = $config[$section][$key] ?? null;
        if ($value) {
            echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
            echo "✅ $description Configured";
            echo "</div>";
        } else {
            echo "<div class='test-result warning'><i class='fas fa-exclamation-triangle status-icon'></i>";
            echo "⚠️ $description Not Configured";
            echo "</div>";
        }
    }
}

echo "</div></div></div></div>";

// Test 7: Performance Check
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-tachometer-alt me-2'></i>Performance Check</h3></div>";
echo "<div class='card-body'>";

$start_time = microtime(true);

// Test PHP execution time
$test_data = [];
for ($i = 0; $i < 1000; $i++) {
    $test_data[] = ['id' => $i, 'name' => 'Test Data ' . $i];
}

$end_time = microtime(true);
$execution_time = round(($end_time - $start_time) * 1000, 2);

if ($execution_time < 100) {
    echo "<div class='test-result success'><i class='fas fa-check-circle status-icon'></i>";
    echo "✅ PHP Performance: {$execution_time}ms (Good)";
    echo "</div>";
} elseif ($execution_time < 500) {
    echo "<div class='test-result warning'><i class='fas fa-exclamation-triangle status-icon'></i>";
    echo "⚠️ PHP Performance: {$execution_time}ms (Acceptable)";
    echo "</div>";
} else {
    echo "<div class='test-result danger'><i class='fas fa-times-circle status-icon'></i>";
    echo "❌ PHP Performance: {$execution_time}ms (Slow)";
    echo "</div>";
}

$test_results['performance'] = ['status' => $execution_time < 100 ? 'success' : 'warning', 'time' => $execution_time];

echo "</div></div></div></div>";

// Test Summary
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-clipboard-check me-2'></i>Test Summary</h3></div>";
echo "<div class='card-body'>";

$success_count = 0;
$error_count = 0;
$warning_count = 0;

foreach ($test_results as $test => $result) {
    if (is_array($result)) {
        if (isset($result['status'])) {
            switch ($result['status']) {
                case 'success': $success_count++; break;
                case 'error': $error_count++; break;
                case 'warning': $warning_count++; break;
            }
        } elseif (isset($result[0])) {
            foreach ($result as $sub_result) {
                if ($sub_result === 'success') $success_count++;
                elseif ($sub_result === 'error') $error_count++;
                elseif ($sub_result === 'warning') $warning_count++;
            }
        }
    }
}

$total_tests = $success_count + $error_count + $warning_count;

echo "<div class='row text-center'>";
echo "<div class='col-md-4'><div class='test-result success'><h2>{$success_count}</h2><p>Passed</p></div></div>";
echo "<div class='col-md-4'><div class='test-result warning'><h2>{$warning_count}</h2><p>Warnings</p></div></div>";
echo "<div class='col-md-4'><div class='test-result danger'><h2>{$error_count}</h2><p>Failed</p></div></div>";
echo "</div>";

$overall_status = $error_count === 0 ? ($warning_count === 0 ? 'success' : 'warning') : 'danger';
$status_icon = $error_count === 0 ? ($warning_count === 0 ? 'check-circle' : 'exclamation-triangle') : 'times-circle';
$status_text = $error_count === 0 ? ($warning_count === 0 ? 'All Tests Passed!' : 'Tests Passed with Warnings') : 'Some Tests Failed';

echo "<div class='test-result {$overall_status} mt-3'>";
echo "<i class='fas fa-{$status_icon} status-icon'></i>";
echo "<h4>{$status_text}</h4>";
echo "<p>Total Tests: {$total_tests} | Success Rate: " . round(($success_count / $total_tests) * 100, 1) . "%</p>";
echo "</div>";

echo "</div></div></div></div>";

// Quick Actions
echo "<div class='row'><div class='col-12'><div class='card test-card'>";
echo "<div class='card-header'><h3><i class='fas fa-rocket me-2'></i>Quick Actions</h3></div>";
echo "<div class='card-body text-center'>";
echo "<a href='ai_demo.php' class='btn btn-primary me-2'><i class='fas fa-robot me-2'></i>Test AI Features</a>";
echo "<a href='test_whatsapp_integration.php' class='btn btn-success me-2'><i class='fas fa-mobile-alt me-2'></i>Test WhatsApp</a>";
echo "<a href='test_email_system.php' class='btn btn-info me-2'><i class='fas fa-envelope me-2'></i>Test Email</a>";
echo "<a href='ai_agent_dashboard.php' class='btn btn-warning me-2'><i class='fas fa-cogs me-2'></i>AI Dashboard</a>";
echo "<button onclick='location.reload()' class='btn btn-secondary'><i class='fas fa-redo me-2'></i>Re-run Tests</button>";
echo "</div></div></div></div>";

echo "</div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";

$test_logs[] = "System test completed at " . date('Y-m-d H:i:s');
$test_logs[] = "Results: {$success_count} passed, {$warning_count} warnings, {$error_count} failed";
?>
