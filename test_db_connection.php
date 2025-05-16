<?php
// Simple script to test DB connection using .env settings
function parseEnv($file) {
    $vars = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $val) = explode('=', $line, 2);
        $vars[trim($key)] = trim(trim($val), '"\'');
    }
    return $vars;
}

$envPath = __DIR__ . '/includes/config/.env';
if (!file_exists($envPath)) die('No .env file found.');
$env = parseEnv($envPath);

$host = $env['DB_HOST'] ?? 'localhost';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$db   = $env['DB_NAME'] ?? '';

$conn = @new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo '<b>Database connection failed:</b> ' . htmlspecialchars($conn->connect_error);
} else {
    echo '<b>Database connection successful!</b>';
    $res = $conn->query('SHOW TABLES');
    if ($res) {
        echo '<br>Tables in database:<ul>';
        while ($row = $res->fetch_array()) {
            echo '<li>' . htmlspecialchars($row[0]) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<br>No tables found or error fetching tables.';
    }
}
$conn->close();
?>
