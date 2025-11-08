<?php
/**
 * Test Script for Security Headers
 * 
 * This script tests the security headers and session configuration
 * to ensure they are properly set and working as expected.
 */

// Initialize security features FIRST (before any output)
require_once __DIR__ . '/includes/security_headers.php';

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "=== Security Headers Test ===\n\n";

// Get all response headers
$headers = [];
foreach (headers_list() as $header) {
    list($name, $value) = explode(':', $header, 2);
    $headers[trim($name)] = trim($value);
}

// Display all headers
echo "Response Headers:\n";
echo str_repeat("-", 80) . "\n";
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
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

// Check which required headers are present
$missingHeaders = [];
$presentHeaders = [];

foreach ($requiredHeaders as $header) {
    if (isset($headers[$header])) {
        $presentHeaders[$header] = $headers[$header];
    } else {
        $missingHeaders[] = $header;
    }
}

// Display results
echo "\nSecurity Header Check:\n";
echo str_repeat("-", 80) . "\n";

if (empty($missingHeaders)) {
    echo "âœ… All required security headers are present\n\n";
} else {
    echo "âŒ Missing security headers:\n";
    foreach ($missingHeaders as $header) {
        echo "- $header\n";
    }
    echo "\n";
}

// Display present headers
echo "Present Security Headers:\n";
foreach ($presentHeaders as $name => $value) {
    echo "- $name: $value\n";
}

// Test session security
echo "\nSession Security Check:\n";
echo str_repeat("-", 80) . "\n";

$sessionParams = session_get_cookie_params();
$sessionSecure = $sessionParams['secure'] ? 'âœ… Enabled' : 'âŒ Disabled';
$sessionHttpOnly = $sessionParams['httponly'] ? 'âœ… Enabled' : 'âŒ Disabled';
$sessionSameSite = $sessionParams['samesite'] ?? 'Not set';

// Check if session is using secure cookies
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $httpsStatus = $sessionParams['secure'] ? 'âœ… Secure' : 'âŒ Not secure';
} else {
    $httpsStatus = 'âš ï¸ Not using HTTPS';
}

echo "- Secure Flag: $sessionSecure\n";
echo "- HTTP Only: $sessionHttpOnly\n";
echo "- SameSite: $sessionSameSite\n";
echo "- HTTPS: $httpsStatus\n";

// Test CSRF protection
echo "\nCSRF Protection Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $csrfToken = getCsrfToken();
    $isValidToken = validateCsrfToken($csrfToken);

    if ($isValidToken) {
        echo "âœ… CSRF token generated and validated successfully\n";
        echo "- Token: " . substr($csrfToken, 0, 10) . "...\n";
    } else {
        echo "âŒ CSRF token validation failed\n";
    }
} catch (Exception $e) {
    echo "âŒ CSRF test failed: " . $e->getMessage() . "\n";
}

// Test rate limiting
echo "\nRate Limiting Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testKey = 'test_rate_limit';
    $attempts = 0;
    $allowed = true;

    // Test 5 attempts (should all be allowed)
    for ($i = 1; $i <= 5; $i++) {
        $allowed = checkRateLimit($testKey, 5, 60);
        if ($allowed) {
            $attempts++;
        }
    }

    if ($attempts === 5) {
        echo "âœ… Rate limiting test passed (5/5 attempts allowed)\n";
    } else {
        echo "âŒ Rate limiting test failed ($attempts/5 attempts allowed)\n";
    }

    // Test one more attempt (should be blocked)
    $blocked = !checkRateLimit($testKey, 5, 60);
    if ($blocked) {
        echo "âœ… Rate limiting blocking test passed (6th attempt blocked)\n";
    } else {
        echo "âŒ Rate limiting blocking test failed (6th attempt was not blocked)\n";
    }
} catch (Exception $e) {
    echo "âŒ Rate limiting test failed: " . $e->getMessage() . "\n";
}

// Test input sanitization
echo "\nInput Sanitization Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testInput = '<script>alert("XSS");</script> http://example.com';
    $sanitized = xssafe($testInput);

    if ($sanitized !== $testInput) {
        echo "âœ… Input sanitization test passed\n";
        echo "- Original: " . htmlspecialchars($testInput) . "\n";
        echo "- Sanitized: " . htmlspecialchars($sanitized) . "\n";
    } else {
        echo "âŒ Input sanitization test failed\n";
    }
} catch (Exception $e) {
    echo "âŒ Input sanitization test failed: " . $e->getMessage() . "\n";
}

// Test path sanitization
echo "\nPath Sanitization Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testPath = "../../../etc/passwd";
    $safePath = safePath($testPath);

    if (strpos($safePath, '..') === false) {
        echo "âœ… Path sanitization test passed\n";
        echo "- Original: $testPath\n";
        echo "- Sanitized: $safePath\n";
    } else {
        echo "âŒ Path sanitization test failed\n";
    }
} catch (Exception $e) {
    echo "âŒ Path sanitization test failed: " . $e->getMessage() . "\n";
}

// Log a test security event
echo "\nSecurity Logging Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testEvent = "test_security_event";
    $testDetails = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'none',
        'test' => true
    ];

    logSecurityEvent($testEvent, $testDetails);
    $logFile = __DIR__ . '/logs/security.log';

    if (file_exists($logFile)) {
        $lastLine = `tail -n 1 "$logFile"`;
        if (strpos($lastLine, $testEvent) !== false) {
            echo "âœ… Security logging test passed\n";
            echo "- Log entry: " . trim($lastLine) . "\n";
        } else {
            echo "âš ï¸ Security logging test may have failed (check log file)\n";
        }
    } else {
        echo "âš ï¸ Security log file not found or not writable\n";
    }
} catch (Exception $e) {
    echo "âŒ Security logging test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Check your browser's developer tools (Network tab) to verify all security headers are present.\n";

// Prevent caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>

// Get all response headers
$headers = [];
foreach (headers_list() as $header) {
    list($name, $value) = explode(':', $header, 2);
    $headers[trim($name)] = trim($value);
}

// Display all headers
echo "Response Headers:\n";
echo str_repeat("-", 80) . "\n";
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
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

// Check which required headers are present
$missingHeaders = [];
$presentHeaders = [];

foreach ($requiredHeaders as $header) {
    if (isset($headers[$header])) {
        $presentHeaders[$header] = $headers[$header];
    } else {
        $missingHeaders[] = $header;
    }
}

// Display results
echo "\nSecurity Header Check:\n";
echo str_repeat("-", 80) . "\n";

if (empty($missingHeaders)) {
    echo "âœ… All required security headers are present\n\n";
} else {
    echo "âŒ Missing security headers:\n";
    foreach ($missingHeaders as $header) {
        echo "- $header\n";
    }
    echo "\n";
}

// Display present headers
echo "Present Security Headers:\n";
foreach ($presentHeaders as $name => $value) {
    echo "- $name: $value\n";
}

// Test session security
echo "\nSession Security Check:\n";
echo str_repeat("-", 80) . "\n";

$sessionParams = session_get_cookie_params();
$sessionSecure = $sessionParams['secure'] ? 'âœ… Enabled' : 'âŒ Disabled';
$sessionHttpOnly = $sessionParams['httponly'] ? 'âœ… Enabled' : 'âŒ Disabled';
$sessionSameSite = $sessionParams['samesite'] ?? 'Not set';

// Check if session is using secure cookies
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $httpsStatus = $sessionParams['secure'] ? 'âœ… Secure' : 'âŒ Not secure';
} else {
    $httpsStatus = 'âš ï¸ Not using HTTPS';
}

echo "- Secure Flag: $sessionSecure\n";
echo "- HTTP Only: $sessionHttpOnly\n";
echo "- SameSite: $sessionSameSite\n";
echo "- HTTPS: $httpsStatus\n";

// Test CSRF protection
echo "\nCSRF Protection Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $csrfToken = getCsrfToken();
    $isValidToken = validateCsrfToken($csrfToken);

    if ($isValidToken) {
        echo "âœ… CSRF token generated and validated successfully\n";
        echo "- Token: " . substr($csrfToken, 0, 10) . "...\n";
    } else {
        echo "âŒ CSRF token validation failed\n";
    }
} catch (Exception $e) {
    echo "âŒ CSRF test failed: " . $e->getMessage() . "\n";
}

// Test rate limiting
echo "\nRate Limiting Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testKey = 'test_rate_limit';
    $attempts = 0;
    $allowed = true;

    // Test 5 attempts (should all be allowed)
    for ($i = 1; $i <= 5; $i++) {
        $allowed = checkRateLimit($testKey, 5, 60);
        if ($allowed) {
            $attempts++;
        }
    }

    if ($attempts === 5) {
        echo "âœ… Rate limiting test passed (5/5 attempts allowed)\n";
    } else {
        echo "âŒ Rate limiting test failed ($attempts/5 attempts allowed)\n";
    }

    // Test one more attempt (should be blocked)
    $blocked = !checkRateLimit($testKey, 5, 60);
    if ($blocked) {
        echo "âœ… Rate limiting blocking test passed (6th attempt blocked)\n";
    } else {
        echo "âŒ Rate limiting blocking test failed (6th attempt was not blocked)\n";
    }
} catch (Exception $e) {
    echo "âŒ Rate limiting test failed: " . $e->getMessage() . "\n";
}

// Test input sanitization
echo "\nInput Sanitization Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testInput = '<script>alert("XSS");</script> http://example.com';
    $sanitized = xssafe($testInput);

    if ($sanitized !== $testInput) {
        echo "âœ… Input sanitization test passed\n";
        echo "- Original: " . htmlspecialchars($testInput) . "\n";
        echo "- Sanitized: " . htmlspecialchars($sanitized) . "\n";
    } else {
        echo "âŒ Input sanitization test failed\n";
    }
} catch (Exception $e) {
    echo "âŒ Input sanitization test failed: " . $e->getMessage() . "\n";
}

// Test path sanitization
echo "\nPath Sanitization Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testPath = "../../../etc/passwd";
    $safePath = safePath($testPath);

    if (strpos($safePath, '..') === false) {
        echo "âœ… Path sanitization test passed\n";
        echo "- Original: $testPath\n";
        echo "- Sanitized: $safePath\n";
    } else {
        echo "âŒ Path sanitization test failed\n";
    }
} catch (Exception $e) {
    echo "âŒ Path sanitization test failed: " . $e->getMessage() . "\n";
}

// Log a test security event
echo "\nSecurity Logging Test:\n";
echo str_repeat("-", 80) . "\n";

try {
    $testEvent = "test_security_event";
    $testDetails = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'none',
        'test' => true
    ];

    logSecurityEvent($testEvent, $testDetails);
    $logFile = __DIR__ . '/logs/security.log';

    if (file_exists($logFile)) {
        $lastLine = `tail -n 1 "$logFile"`;
        if (strpos($lastLine, $testEvent) !== false) {
            echo "âœ… Security logging test passed\n";
            echo "- Log entry: " . trim($lastLine) . "\n";
        } else {
            echo "âš ï¸ Security logging test may have failed (check log file)\n";
        }
    } else {
        echo "âš ï¸ Security log file not found or not writable\n";
    }
} catch (Exception $e) {
    echo "âŒ Security logging test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Check your browser's developer tools (Network tab) to verify all security headers are present.\n";

// Prevent caching of this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>

