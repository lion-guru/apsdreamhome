<?php
/**
 * APS Dream Home - Input Security Testing Script
 */

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Security/InputValidator.php';
require_once APP_PATH . '/Security/InputSanitizer.php';

echo '🛡️ APS DREAM HOME - INPUT SECURITY TESTING\n';
echo '==========================================\n\n';

$validator = App\Security\InputValidator::getInstance();
$sanitizer = App\Security\InputSanitizer::getInstance();

// Test cases
$testCases = [
    'valid_email' => ['email' => 'test@example.com'],
    'invalid_email' => ['email' => 'invalid-email'],
    'xss_attempt' => ['message' => '<script>alert("xss")</script>'],
    'sql_injection' => ['query' => "SELECT * FROM users WHERE id = 1; DROP TABLE users;--"],
    'valid_name' => ['name' => 'John Doe'],
    'invalid_name' => ['name' => 'John123'],
    'valid_phone' => ['phone' => '+91-98765-43210'],
    'invalid_phone' => ['phone' => 'abc123']
];

echo '🔍 Testing Input Validation:\n';
foreach ($testCases as $testName => $data) {
    echo "Testing $testName...\n";
    
    // Sanitize first
    $sanitized = $sanitizer->sanitize($data);
    echo "  Sanitized: " . json_encode($sanitized) . "\n";
    
    // Validate
    $isValid = $validator->validate($sanitized);
    $status = $isValid ? '✅ PASSED' : '❌ FAILED';
    echo "  Validation: $status\n";
    
    if (!$isValid) {
        $errors = $validator->getErrors();
        echo "  Errors: " . implode(', ', $errors) . "\n";
    }
    echo "\n";
}

echo '🎉 Input security testing completed!\n';
