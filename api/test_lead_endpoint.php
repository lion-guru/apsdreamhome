<?php
/**
 * Test script for get_lead_details.php
 * 
 * This script tests the get_lead_details.php endpoint functionality
 * without requiring a web server.
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up server variables for testing
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/api/test_lead_endpoint.php';

// Include necessary files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/../includes/Database.php'; // Include the database class

// Set up database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Test the connection
    $conn->query('SELECT 1');
    
    // Store connection in a global variable for use in other scripts
    $GLOBALS['conn'] = $conn;
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Start output buffering to prevent header issues
ob_start();

// Set up server variables for testing
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/api/test_lead_endpoint.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set test user session (using Super Admin user from database)
$_SESSION['user_id'] = 1; // Super Admin user ID
$_SESSION['user_role'] = 'admin'; // This should match the 'type' in the users table
$_SESSION['user_email'] = 'superadmin@dreamhome.com';
$_SESSION['user_name'] = 'Super Admin';
$_SESSION['type'] = 'admin'; // Ensure this matches the database 'type' column

// Set up HTML output
$htmlOutput = '';

// Debug: Show session data
$htmlOutput .= "<h3>Session Data:</h3>";
$htmlOutput .= "<pre>";
$htmlOutput .= htmlspecialchars(print_r($_SESSION, true));
$htmlOutput .= "</pre>";

// Debug: Show database connection info
$htmlOutput .= "<h3>Database Connection Info:</h3>";
$htmlOutput .= "<pre>";
$htmlOutput .= "DB_HOST: " . htmlspecialchars(DB_HOST) . "\n";
$htmlOutput .= "DB_USER: " . htmlspecialchars(DB_USER) . "\n";
$htmlOutput .= "DB_NAME: " . htmlspecialchars(DB_NAME) . "\n";
$htmlOutput .= "</pre>";

// Test data
$testCases = [
    [
        'name' => 'Get all leads',
        'params' => ['action' => 'getall', 'limit' => 5],
        'expected' => ['success' => true]
    ],
    [
        'name' => 'Get single lead with details',
        'params' => ['action' => 'get', 'id' => 1, 'include' => 'all'],
        'expected' => ['success' => true]
    ],
    [
        'name' => 'Get lead stats',
        'params' => ['action' => 'stats', 'id' => 1],
        'expected' => ['success' => true]
    ],
    [
        'name' => 'Search leads',
        'params' => ['action' => 'search', 'q' => 'Lead 1'],
        'expected' => ['success' => true]
    ],
    [
        'name' => 'Count leads',
        'params' => ['action' => 'count'],
        'expected' => ['success' => true]
    ]
];

// Function to run tests
function runTests($testCases, &$htmlOutput) {
    $results = [];
    
    foreach ($testCases as $test) {
        // Reset output buffer for each test
        ob_start();
        
        // Set up request parameters
        $_GET = $test['params'];
        
        // Debug output
        $htmlOutput .= "<h3>Running Test: " . htmlspecialchars($test['name']) . "</h3>";
        $htmlOutput .= "<h4>Parameters:</h4><pre>" . htmlspecialchars(print_r($test['params'], true)) . "</pre>";
        // Set up test parameters
        $params = http_build_query($test['params']);
        
        // Start a new output buffer for this test
                ob_start();
                
                // Set up the request
                $_GET = $test['params'];
                
                // Debug: Show request parameters
                $htmlOutput .= "<h4>Test: " . htmlspecialchars($test['name']) . "</h4>";
                $htmlOutput .= "<p>Parameters: ";
                $htmlOutput .= htmlspecialchars(print_r($test['params'], true));
                $htmlOutput .= "</p>";
                
                // Debug: Show current working directory and file path
                $htmlOutput .= "<p>Current directory: " . htmlspecialchars(__DIR__) . "</p>";
                $htmlOutput .= "<p>Including file: " . htmlspecialchars(__DIR__ . '/get_lead_details.php') . "</p>";
                
                // Include the endpoint
                try {
                    // Check if file exists
                    if (!file_exists(__DIR__ . '/get_lead_details.php')) {
                        throw new Exception("File not found: " . __DIR__ . '/get_lead_details.php');
                    }
                    
                    // Set up the GET parameters
                    parse_str(http_build_query($test['params']), $_GET);
                    
                    // Debug: Show GET parameters
                    $htmlOutput .= "<h5>GET Parameters:</h5><pre>" . htmlspecialchars(print_r($_GET, true)) . "</pre>";
                    
                    // Start a new output buffer for the included file
                    ob_start();
                    
                    // Include the file
                    include __DIR__ . '/get_lead_details.php';
                    
                    // Get the output and clean the buffer
                    $apiOutput = ob_get_clean();
                    
                    // Always show the raw output for debugging
                    $htmlOutput .= "<h5>API Response (Raw):</h5><pre>" . htmlspecialchars($apiOutput) . "</pre>";
                    
                    // Try to decode the JSON to check if it's valid
                    $jsonData = json_decode($apiOutput, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $formattedOutput = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        $htmlOutput .= "<h5>API Response (Formatted):</h5><pre>" . htmlspecialchars($formattedOutput) . "</pre>";
                    }
                    
                    $output = $apiOutput;
                    
                } catch (Exception $e) {
                    $error = "<div style='color:red;'><strong>Error including file:</strong> " . htmlspecialchars($e->getMessage());
                    $error .= "<br>File: " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")";
                    $error .= "<br>Trace: " . nl2br(htmlspecialchars($e->getTraceAsString())) . "</div>";
                    $htmlOutput .= $error;
                    $output = '';
                }
                
                ob_end_clean();
                
                // Capture any errors that might have occurred
                $errors = error_get_last();
                if ($errors) {
                    $htmlOutput .= "<h5>PHP Errors:</h5><pre>" . htmlspecialchars(print_r($errors, true)) . "</pre>";
                }
                
                try {
                    // Include the endpoint file
                    include __DIR__ . '/get_lead_details.php';
                    
                    // Get the output
                    $output = ob_get_clean();
                    
                    // Debug output
                    $htmlOutput .= "<h4>Raw Output:</h4><pre>" . htmlspecialchars($output) . "</pre>";
                    
                    // Parse JSON response
                    $response = json_decode($output, true);
                    
                    // Check if JSON is valid
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorMsg = 'Invalid JSON response: ' . json_last_error_msg();
                        $htmlOutput .= "<div style='color:red;'><strong>Error:</strong> $errorMsg</div>";
                        
                        $results[] = [
                            'name' => $test['name'],
                            'status' => 'ERROR',
                            'message' => $errorMsg,
                            'output' => $output
                        ];
                        continue;
                    }
                } catch (Exception $e) {
                    $errorMsg = 'Exception: ' . $e->getMessage();
                    $htmlOutput .= "<div style='color:red;'><strong>Exception:</strong> $errorMsg</div>";
                    
                    $results[] = [
                        'name' => $test['name'],
                        'status' => 'ERROR',
                        'message' => $errorMsg,
                        'output' => $e->getTraceAsString()
                    ];
                    continue;
                }
                
                // Check if response matches expected structure
                $passed = true;
                $message = '';
                
                foreach ($test['expected'] as $key => $value) {
                    if (!isset($response[$key]) || $response[$key] !== $value) {
                        $passed = false;
                        $message = sprintf("Expected '%s' to be '%s', got '%s'", 
                            $key, 
                            json_encode($value), 
                            json_encode($response[$key] ?? 'not set')
                        );
                        break;
                    }
                }
        $message = '';
        
        foreach ($test['expected'] as $key => $value) {
            if (!isset($response[$key]) || $response[$key] !== $value) {
                $passed = false;
                $message = sprintf("Expected '%s' to be '%s', got '%s'", 
                    $key, 
                    json_encode($value), 
                    json_encode($response[$key] ?? 'not set')
                );
                break;
            }
        }
        
        $results[] = [
            'test' => $test['name'],
            'status' => $passed ? 'PASS' : 'FAIL',
            'message' => $passed ? 'Test passed' : $message,
            'response' => $response
        ];
    }
    
    return $results;
}

// Run tests and display results
$results = runTests($testCases, $htmlOutput);

// Save the output to a file
$outputFile = __DIR__ . '/test_output.html';
file_put_contents($outputFile, $htmlOutput);
echo "Test output saved to: $outputFile\n";

// Display test results
$htmlOutput .= "<h2>Test Results</h2>";
$htmlOutput .= "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
$htmlOutput .= "<tr style='background-color: #f2f2f2;'><th>Test</th><th>Status</th><th>Message</th><th>Details</th></tr>";

foreach ($results as $result) {
    $details = json_encode($result['response'] ?? [], JSON_PRETTY_PRINT);
    $statusColor = ($result['status'] === 'PASS') ? 'green' : 'red';
    
    $htmlOutput .= "<tr>";
    $htmlOutput .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($result['test']) . "</td>";
    $htmlOutput .= "<td style='padding: 8px; border: 1px solid #ddd; color: $statusColor; font-weight: bold;'>" . 
                  htmlspecialchars($result['status']) . "</td>";
    $htmlOutput .= "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($result['message']) . "</td>";
    $htmlOutput .= "<td style='padding: 8px; border: 1px solid #ddd;'><pre style='white-space: pre-wrap; margin: 0;'>" . 
                  htmlspecialchars(substr($details, 0, 500)) . 
                  (strlen($details) > 500 ? '...' : '') . "</pre></td>";
    $htmlOutput .= "</tr>";
}

$htmlOutput .= "</table>";

// Display any PHP errors that might have occurred
$errors = error_get_last();
if ($errors) {
    $htmlOutput .= "<h3>PHP Errors:</h3><pre>" . htmlspecialchars(print_r($errors, true)) . "</pre>";
}

// Output the complete HTML
$html = "<!DOCTYPE html>
<html>
<head>
    <title>Lead API Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { margin: 20px 0; width: 100%; border-collapse: collapse; }
        th { background-color: #4CAF50; color: white; text-align: left; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Lead API Test Results</h1>
    " . $htmlOutput . "
</body>
</html>";

// Clear any previous output and send the HTML
ob_end_clean();
echo $html;
?>
