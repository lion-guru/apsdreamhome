<?php
// Simple test server - no database required
echo "<!DOCTYPE html>
<html>
<head>
    <title>? APS Dream Home - WORKING!</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; text-align: center; padding: 50px; }
        .success { color: green; font-size: 24px; }
        .info { color: blue; font-size: 18px; }
    </style>
</head>
<body>
    <h1 class='success'>?? WEBSITE IS WORKING!</h1>
    <p class='info'>Your APS Dream Home website is running successfully!</p>
    <p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    <p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>
    <hr>
    <h2>Next Steps:</h2>
    <p>1. Start MySQL in XAMPP Control Panel</p>
    <p>2. Clear browser cache (Ctrl+F5)</p>
    <p>3. Visit: <a href='index.php'>Full Homepage</a></p>
    <hr>
    <p><a href='index.php'>Go to Main Website</a></p>
</body>
</html>";
