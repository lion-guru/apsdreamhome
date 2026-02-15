<?php
/**
 * Direct Security Headers Test
 */

// Set security headers FIRST (before any output)
function setSecurityHeaders() {
    // Remove X-Powered-By header if not already removed
    if (function_exists('header_remove')) {
        header_remove('X-Powered-By');
    } else {
        @ini_set('expose_php', 'off');
    }

    // Set security headers
    $headers = [
        // Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',

        // Prevent clickjacking
        'X-Frame-Options' => 'SAMEORIGIN',

        // Enable XSS protection
        'X-XSS-Protection' => '1; mode=block',

        // Content Security Policy
        'Content-Security-Policy' => "default-src 'self'; " .
                                   "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
                                   "style-src 'self' 'unsafe-inline' https:; " .
                                   "img-src 'self' data: https:; " .
                                   "font-src 'self' https: data:; " .
                                   "connect-src 'self' https:;",

        // Referrer Policy
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Feature Policy (now Permissions-Policy)
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',

        // HTTP Strict Transport Security
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
    ];

    // Set each header
    foreach ($headers as $header => $value) {
        header("$header: $value");
    }
}

// Call the function FIRST
setSecurityHeaders();

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "=== Direct Security Headers Test ===\n\n";

// Get all response headers
$headers = [];
foreach (headers_list() as $header) {
    list($name, $value) = explode(':', $header, 2);
    $headers[trim($name)] = trim($value);
}

echo "Response Headers:\n";
echo str_repeat("-", 80) . "\n";
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}

echo "\nTest completed!\n";
