<?php
/**
 * Test bootstrap file
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set the default timezone
date_default_timezone_set('UTC');

// Define application paths
define('APP_ROOT', dirname(__DIR__));

// Load environment variables from .env.testing if it exists
if (file_exists(APP_ROOT . '/.env.testing')) {
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(APP_ROOT, '.env.testing');
        $dotenv->load();
    }
}

// Define test database constants
define('TEST_DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('TEST_DB_NAME', getenv('DB_DATABASE') ?: 'apsdreamhome_test');
define('TEST_DB_USER', getenv('DB_USERNAME') ?: 'root');
define('TEST_DB_PASS', getenv('DB_PASSWORD') ?: '');

// Load the composer autoloader
if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
    require_once APP_ROOT . '/vendor/autoload.php';
}

// Load test configuration
if (file_exists(APP_ROOT . '/tests/config/database.php')) {
    $testDbConfig = require APP_ROOT . '/tests/config/database.php';
    
    // Override with environment variables if they exist
    if (getenv('DB_HOST')) {
        $testDbConfig['host'] = getenv('DB_HOST');
    }
    if (getenv('DB_DATABASE')) {
        $testDbConfig['database'] = getenv('DB_DATABASE');
    }
    if (getenv('DB_USERNAME')) {
        $testDbConfig['username'] = getenv('DB_USERNAME');
    }
    if (getenv('DB_PASSWORD')) {
        $testDbConfig['password'] = getenv('DB_PASSWORD');
    }
}

// Set up the test database connection
function getTestDbConnection() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                "mysql:host=" . TEST_DB_HOST . ";dbname=" . TEST_DB_NAME . ";charset=utf8mb4",
                TEST_DB_USER,
                TEST_DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Test database connection failed: " . $e->getMessage());
        }
    }
    
    return $db;
}

// Set up the test database connection (mysqli version for migrations)
function getTestMysqliConnection() {
    static $mysqli = null;
    
    if ($mysqli === null) {
        $mysqli = new mysqli(
            TEST_DB_HOST,
            TEST_DB_USER,
            TEST_DB_PASS,
            TEST_DB_NAME
        );
        
        if ($mysqli->connect_error) {
            die("Test database connection failed: " . $mysqli->connect_error);
        }
        
        $mysqli->set_charset('utf8mb4');
    }
    
    return $mysqli;
}

// Run migrations before tests
function runTestMigrations() {
    if (getenv('RUN_MIGRATIONS') !== 'false') {
        $mysqli = getTestMysqliConnection();
        $migrationManager = new Tests\Database\MigrationManager(
            $mysqli,
            __DIR__ . '/Database/Migrations'
        );
        $migrationManager->migrate();
    }
}

// Include the test helper
require_once __DIR__ . '/TestHelper.php';

// Run migrations and seed the test database when this file is included
if (php_sapi_name() !== 'cli' || (isset($argv[0]) && basename($argv[0]) === 'phpunit')) {
    try {
        // Create a PDO connection for the seeder
        $pdo = new PDO(
            "mysql:host=" . TEST_DB_HOST . ";charset=utf8mb4",
            TEST_DB_USER,
            TEST_DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        // Run the test database seeder
        require_once __DIR__ . '/Database/Seeders/TestDatabaseSeeder.php';
        Tests\Database\Seeders\TestDatabaseSeeder::run($pdo);
        
        // Close the connection
        $pdo = null;
    } catch (PDOException $e) {
        die("Failed to seed test database: " . $e->getMessage());
    }
}

// Helper function to get the base URL for API requests
function getApiBaseUrl() {
    return getenv('API_BASE_URL') ?: 'http://localhost/apsdreamhome/api';
}

// Helper function to make API requests
function makeApiRequest($endpoint, $method = 'GET', $data = null) {
    $url = rtrim(getApiBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
    
    $ch = curl_init($url);
    
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ];
    
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method !== 'GET') {
        $options[CURLOPT_CUSTOMREQUEST] = $method;
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($data !== null) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
        curl_setopt($ch, CURLOPT_URL, $url);
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    curl_close($ch);
    
    if ($error) {
        throw new \RuntimeException("cURL Error: " . $error);
    }
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'code' => $httpCode,
        'headers' => parseHeaders($headers),
        'body' => json_decode($body, true) ?: $body
    ];
}

// Helper function to parse headers
function parseHeaders(string $headers): array
{
    $headersArray = [];
    $headers = explode("\r\n", $headers);
    
    foreach ($headers as $header) {
        if (strpos($header, ':') !== false) {
            list($key, $value) = explode(':', $header, 2);
            $headersArray[trim($key)] = trim($value);
        }
    }
    
    return $headersArray;
}
