<?php
// db_check.php - Safe DB connectivity check for APS Dream Home
// This script will:
// 1) Load .env if present (without throwing if missing)
// 2) Use defaults aligned with this project
// 3) Try connecting via PDO and mysqli

// Attempt to load .env via project loader only if .env exists to avoid exceptions
$projectRoot = __DIR__;
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    try {
        // The loader in app/config/env.php throws if .env is missing; we checked already
        require_once __DIR__ . '/app/config/env.php';
        echo "Loaded environment from .env via app/config/env.php<br>\n";
    } catch (Throwable $t) {
        echo "Warning: Failed to load .env via loader: " . htmlspecialchars($t->getMessage()) . "<br>\n";
    }
} else {
    echo ".env not found; proceeding with defaults/envvars<br>\n";
}

// Gather DB settings (prefer environment, fallback to project-friendly defaults)
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
// Prefer the main project DB name if not set in env
$DB_NAME = getenv('DB_NAME') ?: 'apsdreamhomefinal';
$DB_PORT = (int)(getenv('DB_PORT') ?: 3306);

// Display config (hide password)
echo "<h3>Database Settings</h3>\n";
echo "DB_HOST: " . htmlspecialchars($DB_HOST) . "<br>\n";
echo "DB_USER: " . htmlspecialchars($DB_USER) . "<br>\n";
echo "DB_NAME: " . htmlspecialchars($DB_NAME) . "<br>\n";
echo "DB_PORT: " . htmlspecialchars((string)$DB_PORT) . "<br>\n";

$pdoOk = false;
$mysqliOk = false;

// Test PDO connection
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;port=%d;charset=utf8mb4', $DB_HOST, $DB_NAME, $DB_PORT);
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "<p style='color:green'>PDO connection successful.</p>\n";
    // Simple query
    $stmt = $pdo->query('SELECT DATABASE() AS db, VERSION() AS version');
    if ($stmt) {
        $info = $stmt->fetch();
        echo "Connected DB: " . htmlspecialchars($info['db'] ?? '') . "<br>\n";
        echo "Server Version: " . htmlspecialchars($info['version'] ?? '') . "<br>\n";
    }
    $pdoOk = true;
} catch (Throwable $e) {
    echo "<p style='color:red'>PDO connection failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Test mysqli connection
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli && !$mysqli->connect_errno) {
    echo "<p style='color:green'>mysqli connection successful.</p>\n";
    $res = $mysqli->query('SELECT DATABASE() AS db, VERSION() AS version');
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Connected DB (mysqli): " . htmlspecialchars($row['db'] ?? '') . "<br>\n";
        echo "Server Version (mysqli): " . htmlspecialchars($row['version'] ?? '') . "<br>\n";
    }
    $mysqli->close();
    $mysqliOk = true;
} else {
    echo "<p style='color:red'>mysqli connection failed: " . htmlspecialchars($mysqli ? $mysqli->connect_error : 'Unknown error') . "</p>\n";
}

if ($pdoOk || $mysqliOk) {
    echo "<strong style='color:green'>Database connectivity verified.</strong>\n";
    exit(0);
}

http_response_code(500);
echo "<strong style='color:red'>All connection attempts failed. Check DB credentials and server status.</strong>\n";
exit(1);
