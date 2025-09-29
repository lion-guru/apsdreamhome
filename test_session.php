<?php
/**
 * Session Test Script
 * Verifies that PHP sessions are working correctly
 */

// Start session with our custom settings
require_once 'includes/session_manager.php';

// Set content type
header('Content-Type: text/html; charset=utf-8');

// Simple HTML template
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Test - APS Dream Home<?php echo date('Y-m-d H:i:s'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .test-container { max-width: 800px; margin: 0 auto; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="mb-4">Session Test Page</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Session Information</h5>
            </div>
            <div class="card-body">
                <pre>Session ID: <?php echo session_id(); ?>
Session Status: <?php echo session_status(); ?> (2 = PHP_SESSION_ACTIVE)
Session Name: <?php echo session_name(); ?>

Session Data:
<?php 
if (empty($_SESSION)) {
    echo "No session data found.\n";
} else {
    print_r($_SESSION); 
}
?>

Cookie Data:
<?php print_r($_COOKIE); ?>
                </pre>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Session Test</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['test'])): ?>
                    <div class="alert alert-success">
                        Session write test successful!
                        <p class="mb-0 mt-2">Test value was saved to the session.</p>
                    </div>
                    <a href="test_session.php" class="btn btn-primary">Test Again</a>
                <?php else: ?>
                    <?php 
                    // Set a test value in the session
                    $_SESSION['test_time'] = date('Y-m-d H:i:s');
                    $_SESSION['test_random'] = bin2hex(random_bytes(8));
                    ?>
                    <p>Click the button below to test if session data is being saved:</p>
                    <a href="test_session.php?test=1" class="btn btn-primary">Test Session Write</a>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="customer_dashboard.php" class="btn btn-outline-secondary">Go to Dashboard</a>
                    <?php if (isset($_SESSION['customer_logged_in'])): ?>
                        <a href="customer_logout.php" class="btn btn-outline-danger float-end">Logout</a>
                    <?php else: ?>
                        <a href="customer_login.php" class="btn btn-outline-primary float-end">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
