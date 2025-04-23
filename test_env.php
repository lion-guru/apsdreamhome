<?php
// Load environment variables from .env if available (for local development)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

// Gather config
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_username = getenv('DB_USER') ?: 'root';
$db_password = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'apsdreamhomes';
$db_port = getenv('DB_PORT') ?: '3306';
$OPENAI_API_KEY = getenv('OPENAI_API_KEY');

// Output config (do not show sensitive values)
echo "<h2>Loaded Configuration</h2>";
echo "DB_HOST: $db_host<br>";
echo "DB_USER: $db_username<br>";
echo "DB_NAME: $db_name<br>";
echo "DB_PORT: $db_port<br>";
echo "OPENAI_API_KEY is " . ($OPENAI_API_KEY ? 'set' : 'NOT set') . "<br>";

// Test database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    echo "<p style='color:green'>Database connection successful!</p>";
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color:red'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
