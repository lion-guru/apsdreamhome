<?php

/**
 * Migration: Add Email Verification Columns to Users Table
 *
 * verifyEmailPost() reads verify_token + verify_sent_at, but no code path
 * ever wrote them (columns did not exist) — the whole verify-email flow
 * was dead. This adds the columns; UserRegistrationService populates them.
 * Idempotent: safe to re-run.
 */

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=apsdreamhome',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Adding email verification columns to users table...\n";

    $exists = $pdo->query("SHOW COLUMNS FROM users LIKE 'verify_token'")->rowCount() > 0;
    if (!$exists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verify_token VARCHAR(64) NULL DEFAULT NULL AFTER remember_token");
        $pdo->exec("ALTER TABLE users ADD INDEX idx_verify_token (verify_token)");
        echo "ADDED verify_token column\n";
    } else {
        echo "SKIP verify_token column already exists\n";
    }

    $exists = $pdo->query("SHOW COLUMNS FROM users LIKE 'verify_sent_at'")->rowCount() > 0;
    if (!$exists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verify_sent_at DATETIME NULL DEFAULT NULL AFTER verify_token");
        echo "ADDED verify_sent_at column\n";
    } else {
        echo "SKIP verify_sent_at column already exists\n";
    }

    echo "Migration complete.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
