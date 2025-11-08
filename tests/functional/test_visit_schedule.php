<?php
/**
 * Test Visit Scheduling
 * 
 * This script tests the visit scheduling functionality by submitting a test visit request.
 */

// Start session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration and functions
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Set page title
$pageTitle = 'Test Visit Scheduling';

// Check if we're submitting the test form
$testResult = null;
$testError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get a random published property for testing
        $db = DatabaseConfig::getConnection();
        $query = "SELECT id, title FROM properties WHERE status = 'published' ORDER BY RAND() LIMIT 1";
        $result = $db->query($query);
        
        if ($result->num_rows === 0) {
            throw new Exception('No published properties found. Please add properties first.');
        }
        
        $property = $result->fetch_assoc();
        $propertyId = $property['id'];
        
        // Prepare test data
        $testData = [
            'property_id' => $propertyId,
            'visit_date' => date('Y-m-d', strtotime('+2 days')),
            'visit_time' => '14:00',
            'visitor_name' => 'Test User ' . rand(100, 999),
            'visitor_email' => 'test' . rand(100, 999) . '@example.com',
            'visitor_phone' => '123-456-' . rand(1000, 9999),
            'visit_notes' => 'This is a test visit request',
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ];
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/process_visit.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for errors
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        // Close cURL
        curl_close($ch);
        
        // Decode the response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $testResult = [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'No message returned',
                'visit_id' => $result['visit_id'] ?? null,
                'property_title' => $property['title']
            ];
            
            if (isset($result['errors'])) {
                $testResult['errors'] = $result['errors'];
            }
        } else {
            throw new Exception('Request failed with HTTP code ' . $httpCode . ': ' . ($result['message'] ?? 'Unknown error'));
        }
        
    } catch (Exception $e) {
        $testError = $e->getMessage();
    }
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { padding: 2rem 0; background-color: #f8f9fa; }
        .test-container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .test-section { margin-bottom: 2rem; padding: 1.5rem; border: 1px solid #dee2e6; border-radius: 0.25rem; }
        .test-result { margin-top: 1rem; padding: 1rem; border-radius: 0.25rem; }
        .success { background-color: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .warning { background-color: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 0.25rem; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-container">
            <h1 class="mb-4">
                <i class="bi bi-calendar-check me-2"></i>
                Test Visit Scheduling
            </h1>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                This page helps you test the visit scheduling functionality by submitting a test visit request.
            </div>
            
            <?php if ($testResult !== null): ?>
                <div class="test-section">
                    <h2 class="h4 mb-3">Test Results</h2>
                    
                    <?php if ($testResult['success']): ?>
                        <div class="test-result success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Success!</strong> <?php echo htmlspecialchars($testResult['message']); ?>
                            
                            <?php if (isset($testResult['visit_id'])): ?>
                                <div class="mt-2">
                                    <strong>Visit ID:</strong> <?php echo htmlspecialchars($testResult['visit_id']); ?><br>
                                    <strong>Property:</strong> <?php echo htmlspecialchars($testResult['property_title']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <a href="test_properties.php" class="btn btn-primary me-2">
                                <i class="bi bi-house me-1"></i> Back to Properties Test
                            </a>
                            <a href="test_visit_schedule.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-repeat me-1"></i> Run Another Test
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="test-result error">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <strong>Error:</strong> <?php echo htmlspecialchars($testResult['message']); ?>
                            
                            <?php if (!empty($testResult['errors'])): ?>
                                <div class="mt-2">
                                    <strong>Validation Errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($testResult['errors'] as $field => $error): ?>
                                            <li><strong><?php echo htmlspecialchars($field); ?>:</strong> <?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <a href="test_visit_schedule.php" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-1"></i> Try Again
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($testError !== null): ?>
                <div class="test-section">
                    <h2 class="h4 mb-3">Test Failed</h2>
                    
                    <div class="test-result error">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        <strong>Error:</strong> <?php echo htmlspecialchars($testError); ?>
                    </div>
                    
                    <div class="mt-3">
                        <a href="test_visit_schedule.php" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i> Try Again
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="test-section">
                    <h2 class="h4 mb-3">Run Test</h2>
                    
                    <p>This test will:</p>
                    <ol>
                        <li>Find a random published property</li>
                        <li>Submit a test visit request for that property</li>
                        <li>Display the result of the operation</li>
                    </ol>
                    
                    <form method="post" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            This will create a test visit record in the database.
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-play-fill me-1"></i> Run Test
                            </button>
                            <a href="test_properties.php" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-left me-1"></i> Back to Tests
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="test-section">
                <h2 class="h4 mb-3">Test Details</h2>
                
                <div class="accordion" id="testDetails">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                What is being tested?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#testDetails">
                            <div class="accordion-body">
                                <p>This test verifies that:</p>
                                <ul>
                                    <li>The database connection is working</li>
                                    <li>There are published properties available</li>
                                    <li>The visit scheduling endpoint (<code>/process_visit.php</code>) is accessible</li>
                                    <li>The visit scheduling form data is processed correctly</li>
                                    <li>The response from the server is as expected</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Troubleshooting
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#testDetails">
                            <div class="accordion-body">
                                <h5>Common Issues:</h5>
                                <ul>
                                    <li><strong>No published properties found</strong> - Make sure you have published properties in the database</li>
                                    <li><strong>CSRF token validation failed</strong> - Ensure sessions are working correctly</li>
                                    <li><strong>Database connection error</strong> - Check your database configuration</li>
                                    <li><strong>Endpoint not found</strong> - Make sure the <code>process_visit.php</code> file exists in the root directory</li>
                                </ul>
                                
                                <h5 class="mt-3">Next Steps:</h5>
                                <ol>
                                    <li>Check the <a href="test_properties.php">Properties Test</a> page to verify properties exist</li>
                                    <li>Check the server error logs for any PHP errors</li>
                                    <li>Verify the database tables exist and are properly structured</li>
                                    <li>Check file permissions on the server</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
