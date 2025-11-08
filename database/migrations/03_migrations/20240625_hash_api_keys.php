<?php
/**
 * Migration: Hash existing plain-text API keys to SHA-256
 * -------------------------------------------------------
 * This script converts all API keys stored in plain text in the `api_keys`
 * table to a secure SHA-256 hash (lower-case hex), matching the updated
 * validation logic in `api/auth/api_keys.php` and `includes/ApiKeyManager.php`.
 *
 * Usage (CLI):
 *   php 03_migrations/20240625_hash_api_keys.php
 *
 * Make sure the DB constants (DB_HOST, DB_USER, DB_PASS/DB_PASSWORD, DB_NAME)
 * are defined. They are typically loaded by including one of the central
 * configuration files.
 */

// Try to load central DB config – adjust the path if your project uses a
// different location.
$possibleConfigs = [
    __DIR__ . '/../includes/config.php',            // legacy global config
    __DIR__ . '/../includes/db_settings.php',       // settings stub
    __DIR__ . '/../includes/db_config.php',         // another variant
];

foreach ($possibleConfigs as $cfg) {
    if (file_exists($cfg)) {
        require_once $cfg;
        break;
    }
}

// Fallback env vars if constants not defined
if (!defined('DB_HOST'))     define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER'))     define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASSWORD') && defined('DB_PASS')) {
    define('DB_PASSWORD', DB_PASS);
} elseif (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
}
if (!defined('DB_NAME'))     define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhome');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    fwrite(STDERR, "[Error] Database connection failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

// Step 1: Find candidate records (api_key length != 64 or non-hex chars)
$query = "SELECT id, api_key FROM api_keys WHERE CHAR_LENGTH(api_key) <> 64 OR api_key REGEXP('[^0-9A-Fa-f]')";
$result = $mysqli->query($query);
if ($result === false) {
    fwrite(STDERR, "[Error] Failed to fetch API keys: {$mysqli->error}\n");
    exit(1);
}

$total   = 0;
$updated = 0;
while ($row = $result->fetch_assoc()) {
    $total++;
    $id       = (int)$row['id'];
    $plainKey = $row['api_key'];
    $hashed   = hash('sha256', $plainKey);

    // Use prepared statement to avoid SQL injection (even though data is internal)
    $stmt = $mysqli->prepare("UPDATE api_keys SET api_key = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $hashed, $id);
        if ($stmt->execute()) {
            $updated += $stmt->affected_rows;
        } else {
            fwrite(STDERR, "[Warning] Failed to update id {$id}: {$stmt->error}\n");
        }
        $stmt->close();
    }
}
$result->free();

// Optionally ensure UNIQUE constraint collisions did not occur
if ($mysqli->errno === 1062) { // duplicate entry
    fwrite(STDERR, "[Error] Duplicate API key hash detected. Please resolve manually.\n");
    exit(1);
}

echo "Migration completed. {$updated} of {$total} API keys hashed successfully.\n";

$mysqli->close();
?>