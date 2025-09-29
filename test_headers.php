<?php
/**
 * Test script to verify security headers
 */

// Include the security headers
require_once __DIR__ . '/includes/security_headers.php';

// Set content type
header('Content-Type: text/plain');

echo "Security Headers Test\n";
echo "====================\n\n";

// Get all response headers
$headers = headers_list();

// Display all headers
echo "Current Headers:\n";
foreach ($headers as $header) {
    echo "- $header\n";
}

// Check for required security headers
$requiredHeaders = [
    'X-Content-Type-Options',
    'X-Frame-Options',
    'X-XSS-Protection',
    'Content-Security-Policy',
    'Referrer-Policy',
    'Permissions-Policy',
    'Strict-Transport-Security'
];

// Check which required headers are missing
$missingHeaders = [];
foreach ($requiredHeaders as $header) {
    $found = false;
    foreach ($headers as $sentHeader) {
        if (stripos($sentHeader, $header) === 0) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $missingHeaders[] = $header;
    }
}

// Display results
echo "\nSecurity Header Check:\n";
if (empty($missingHeaders)) {
    echo "✅ All required security headers are present\n";
} else {
    echo "❌ Missing security headers:\n";
    foreach ($missingHeaders as $header) {
        echo "- $header\n";
    }
}

// Test CSRF token generation
echo "\nCSRF Token Test:\n";
$token = getCsrfToken();
echo "- Generated CSRF Token: $token\n";
echo "- Token Validation: " . (verifyCsrfToken($token) ? '✅ Valid' : '❌ Invalid') . "\n";

// Test input sanitization
echo "\nInput Sanitization Test:\n";
$testInput = "<script>alert('XSS');</script>";
$sanitized = xssafe($testInput);
echo "- Original: $testInput\n";
echo "- Sanitized: $sanitized\n";

// Test path sanitization
echo "\nPath Sanitization Test:\n";
$testPath = "../../../etc/passwd";
$safePath = safePath($testPath);
echo "- Original: $testPath\n";
echo "- Sanitized: $safePath\n";

// Test password hashing
echo "\nPassword Hashing Test:\n";
$password = "mySecurePassword123";
$hash = hashPassword($password);
echo "- Password: $password\n";
echo "- Hash: $hash\n";
echo "- Verification: " . (verifyPassword($password, $hash) ? '✅ Success' : '❌ Failed') . "\n";

// Test security event logging
echo "\nSecurity Event Logging Test:\
";
$testEvent = "test_security_event";
$testDetails = ["ip" => "127.0.0.1", "user_agent" => "Test Agent"];
logSecurityEvent($testEvent, $testDetails);
$logFile = __DIR__ . '/logs/security.log';
if (file_exists($logFile)) {
    $lastLine = `tail -n 1 "$logFile"`;
    echo "- Last log entry: " . trim($lastLine) . "\n";
    echo "✅ Security event logged successfully\n";
} else {
    echo "❌ Security log file not found or not writable\n";
}

echo "\nTest completed. Check your browser's developer tools (Network tab) to verify all headers.\n";
