<?php
/**
 * APS Dream Home - Admin Test Script
 * This script tests admin pages and functions
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/url_fix.php';
require_once __DIR__ . '/includes/security_functions.php';

// Set up error reporting for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test results array
$test_results = [];

// Function to add test result
function add_test_result($test_name, $result, $details = '') {
    global $test_results;
    $test_results[] = [
        'test' => $test_name,
        'result' => $result ? 'PASS' : 'FAIL',
        'details' => $details
    ];
}

// Test 1: Check if URL fix functions exist
$test_name = "URL Fix Functions";
if (function_exists('generate_admin_url') && function_exists('fix_admin_urls')) {
    add_test_result($test_name, true, "URL fix functions are properly loaded");
} else {
    add_test_result($test_name, false, "URL fix functions are not available");
}

// Test 2: Check if security functions exist
$test_name = "Security Functions";
$security_functions = [
    'secure_input',
    'generate_csrf_token',
    'validate_csrf_token',
    'secure_redirect',
    'validate_file_upload',
    'generate_secure_token',
    'log_security_event'
];
$missing_functions = [];
foreach ($security_functions as $func) {
    if (!function_exists($func)) {
        $missing_functions[] = $func;
    }
}
if (empty($missing_functions)) {
    add_test_result($test_name, true, "All security functions are properly loaded");
} else {
    add_test_result($test_name, false, "Missing security functions: " . implode(', ', $missing_functions));
}

// Test 3: Test URL fix with sample URL
$test_name = "URL Fix Functionality";
$test_url = "admin/dashboard.php?id=1";
$fixed_url = generate_admin_url($test_url);
if (strpos($fixed_url, 'htmlspecialchars') === false) {
    add_test_result($test_name, true, "URL properly generated: $fixed_url");
} else {
    add_test_result($test_name, false, "URL still contains unprocessed PHP: $fixed_url");
}

// Test 4: Test CSRF token generation and validation
$test_name = "CSRF Protection";
$token = generate_csrf_token();
$is_valid = validate_csrf_token($token);
add_test_result($test_name, $is_valid, "Token generation and validation " . ($is_valid ? "works correctly" : "failed"));

// Test 5: Test secure input function
$test_name = "Input Sanitization";
$test_input = "<script>alert('XSS');</script>";
$sanitized = secure_input($test_input, 'html');
$is_sanitized = ($sanitized !== $test_input && !preg_match('/<script>/i', $sanitized));
add_test_result($test_name, $is_sanitized, "Input sanitization " . ($is_sanitized ? "works correctly" : "failed"));

// Display test results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Admin Test Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .test-result { margin-bottom: 10px; padding: 10px; border-radius: 5px; }
        .pass { background-color: #d4edda; }
        .fail { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Admin Functionality Test Results</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2>Test Summary</h2>
            </div>
            <div class="card-body">
                <?php
                $total_tests = count($test_results);
                $passed_tests = count(array_filter($test_results, function($test) {
                    return $test['result'] === 'PASS';
                }));
                $success_rate = ($total_tests > 0) ? round(($passed_tests / $total_tests) * 100) : 0;
                ?>
                <p>Total Tests: <?php echo $total_tests; ?></p>
                <p>Passed Tests: <?php echo $passed_tests; ?></p>
                <p>Success Rate: <?php echo $success_rate; ?>%</p>
                
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $success_rate; ?>%" 
                         aria-valuenow="<?php echo $success_rate; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $success_rate; ?>%
                    </div>
                </div>
            </div>
        </div>
        
        <h2>Detailed Results</h2>
        <?php foreach ($test_results as $result): ?>
            <div class="test-result <?php echo strtolower($result['result']); ?>">
                <h4><?php echo htmlspecialchars($result['test']); ?>: <?php echo $result['result']; ?></h4>
                <p><?php echo htmlspecialchars($result['details']); ?></p>
            </div>
        <?php endforeach; ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Back to Home</a>
            <a href="admin.php" class="btn btn-secondary">Go to Admin</a>
        </div>
    </div>
</body>
</html>