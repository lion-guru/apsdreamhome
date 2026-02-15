<?php
/**
 * Error Handler Test File
 * This file tests various error scenarios to verify our error handling system
 */

// Define security constant
define('SECURE_CONSTANT', true);

// Include configuration
require_once 'includes/config.php';

// Set page title
$page_title = "Error Handler Test";

// Function to display test result
function display_test_result($test_name, $success) {
    echo "<div style='margin: 10px 0; padding: 10px; border-radius: 5px; " . 
         "background-color: " . ($success ? "#d4edda" : "#f8d7da") . "; " .
         "color: " . ($success ? "#155724" : "#721c24") . ";'>";
    echo "<strong>" . ($success ? "✓ PASS: " : "✗ FAIL: ") . "</strong>";
    echo htmlspecialchars($test_name);
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h3 mb-0">Error Handler Test</h1>
            </div>
            <div class="card-body">
                <p class="lead">This page tests the error handling system. Click the buttons below to trigger different types of errors.</p>
                
                <div class="alert alert-warning">
                    <strong>Note:</strong> These tests are meant to generate errors intentionally to verify the error handling system.
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">Error Types</div>
                            <div class="card-body">
                                <a href="?test=warning" class="btn btn-warning mb-2 w-100">Trigger Warning</a>
                                <a href="?test=notice" class="btn btn-info mb-2 w-100">Trigger Notice</a>
                                <a href="?test=error" class="btn btn-danger mb-2 w-100">Trigger Error</a>
                                <a href="?test=exception" class="btn btn-dark mb-2 w-100">Trigger Exception</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Test Results</div>
                            <div class="card-body">
                                <?php
                                // Process test requests
                                if (isset($_GET['test'])) {
                                    switch ($_GET['test']) {
                                        case 'warning':
                                            // Test warning
                                            display_test_result("Testing Warning", true);
                                            // Use custom error handler
                                            error_log("This is a test warning", 0);
                                            trigger_error("This is a test warning", E_USER_WARNING);
                                            break;
                                            
                                        case 'notice':
                                            // Test notice
                                            display_test_result("Testing Notice", true);
                                            // Use custom error handler
                                            error_log("This is a test notice", 0);
                                            trigger_error("This is a test notice", E_USER_NOTICE);
                                            break;
                                            
                                        case 'error':
                                            // Test error
                                            display_test_result("Testing Error", true);
                                            // Use custom error handler
                                            error_log("This is a test error", 0);
                                            trigger_error("This is a test error", E_USER_ERROR);
                                            break;
                                            
                                        case 'exception':
                                            // Test exception
                                            display_test_result("Testing Exception", true);
                                            throw new Exception("This is a test exception");
                                            break;
                                            
                                        default:
                                            display_test_result("Unknown test type", false);
                                    }
                                } else {
                                    echo "<p>No test selected. Click a button to run a test.</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="/" class="btn btn-primary">Return to Homepage</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>