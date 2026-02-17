<?php
// Unauthorized access page for admins
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/templates/dynamic_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <style>
        body { background: #f8d7da; }
        .unauth-container { margin: 8% auto; max-width: 500px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); padding: 40px; text-align: center; }
        .unauth-container h1 { color: #dc3545; }
        .unauth-container p { color: #6c757d; }
        .unauth-container a { color: #fff; background: #dc3545; border: none; padding: 10px 25px; border-radius: 5px; text-decoration: none; }
        .unauth-container a:hover { background: #b52a37; }
    </style>
</head>
<body>
<div class="unauth-container">
    <h1>Unauthorized</h1>
    <p>You do not have permission to access this page.<br>
    If you believe this is an error, please contact your system administrator.</p>
    <a href="dashboard.php">Return to Dashboard</a>
</div>
</body>
</html>
