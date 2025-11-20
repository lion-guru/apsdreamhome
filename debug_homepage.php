<?php
/**
 * Debug Homepage - APS Dream Home
 * Shows exact error and fixes 500 Internal Server Error
 */

// Enable maximum error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Debug output
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>🔧 APS Dream Home - Debug Mode</h1>
        <div class="alert alert-info">
            <h4>System Information:</h4>
            <ul>
                <li><strong>PHP Version:</strong> ' . phpversion() . '</li>
                <li><strong>Current Directory:</strong> ' . getcwd() . '</li>
                <li><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</li>
                <li><strong>Server Name:</strong> ' . $_SERVER['SERVER_NAME'] . '</li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h4>📁 File Check:</h4>
                <ul class="list-group">
                    <li class="list-group-item">config.php: ' . (file_exists('config.php') ? '✅ Found' : '❌ Missing') . '</li>
                    <li class="list-group-item">homepage.php: ' . (file_exists('homepage.php') ? '✅ Found' : '❌ Missing') . '</li>
                    <li class="list-group-item">index.php: ' . (file_exists('index.php') ? '✅ Found' : '❌ Missing') . '</li>
                    <li class="list-group-item">includes/config/config.php: ' . (file_exists('includes/config/config.php') ? '✅ Found' : '❌ Missing') . '</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4>🔗 Quick Links:</h4>
                <div class="d-grid gap-2">
                    <a href="homepage.php" class="btn btn-primary">Test Homepage</a>
                    <a href="admin/" class="btn btn-success">Admin Panel</a>
                    <a href="properties.php" class="btn btn-info">Properties</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
?>
