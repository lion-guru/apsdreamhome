<?php
/**
 * Test Error Page - 404 Error Test
 * This page is used to test the error handling system
 */

// Set 404 headers
http_response_code(404);

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found (Test)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
        }
        .error-title {
            font-size: 24px;
            color: #333;
            margin: 10px 0;
        }
        .error-message {
            color: #666;
            margin: 20px 0;
            line-height: 1.6;
        }
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #2980b9;
        }
        .test-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">
            The page you are looking for doesn't exist or has been moved.
            Please check the URL or return to the homepage.
        </p>
        <a href="/" class="back-button">Go to Homepage</a>
        
        <div class="test-info">
            <strong>Test Information:</strong><br>
            This is a test error page for the 404 error handling system.<br>
            Requested URL: <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?><br>
            Server Time: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>