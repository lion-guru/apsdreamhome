<?php
// Quick CLI test: generate a new API key, then immediately validate it using updated hashing logic.
// Usage: php 06_tools/test_api_key_hashing.php

require_once __DIR__ . '/../includes/db_settings.php'; // adjust if needed

// Initialize database connection for global usage in api_keys.php
$conn = get_db_connection();
if (!$conn) {
    echo "❌ Unable to connect to database.\n";
    exit(1);
}

require_once __DIR__ . '/../api/auth/api_keys.php';    // functions generateApiKey, validateApiKey

// Use a valid user_id from the users table (Super Admin)
$userId = 1; // Super Admin user
$name   = 'CLI Test Key';
$permissions = ['*']; // full access for test
$rateLimit   = 100;

echo "Generating new API key for user ID {$userId}...\n";
$newKey = generateApiKey($userId, $name, $permissions, $rateLimit);
if (!$newKey) {
    echo "❌ Failed to generate API key. Check if user_id {$userId} exists and has proper permissions.\n";
    echo "MySQL Error: " . ($conn->error ?: 'Unknown error') . "\n";
    exit(1);
}

echo "✅ New API key: {$newKey}\n";

// Attempt to validate the new key for a dummy endpoint
$endpoint = '/test/endpoint';

// Mock $_SERVER variables for CLI environment
if (php_sapi_name() === 'cli') {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTP_USER_AGENT'] = 'CLI-Test-Script';
}

// Debug: check what permissions were stored
$keyInfo = getApiKeyInfo($newKey);
if ($keyInfo) {
    echo "🔍 Stored permissions: " . $keyInfo['permissions'] . "\n";
    $decodedPermissions = json_decode($keyInfo['permissions'], true);
    echo "🔍 Decoded permissions: " . print_r($decodedPermissions, true) . "\n";
}

$validation = validateApiKey($newKey, $endpoint);

if ($validation['valid']) {
    echo "🎉 Validation succeeded. Rate limit remaining: {$validation['requests_remaining']}\n";
    
    // Additional verification: check if the key was stored as hash
    $result = $conn->query("SELECT api_key FROM api_keys WHERE api_key = SHA2('{$newKey}', 256)");
    if ($result && $result->num_rows > 0) {
        echo "✅ API key successfully stored as SHA-256 hash in database\n";
    } else {
        echo "⚠️ API key may not be stored as expected hash format\n";
    }
    
    exit(0);
} else {
    echo "⚠️ Validation failed: " . ($validation['message'] ?? 'Unknown error') . "\n";
    exit(1);
}
?>