<?php
// Access Denied Page
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            padding: 50px;
            background-color: #f8f9fa;
        }
        .access-denied {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .access-denied h1 {
            color: #dc3545;
        }
        .access-denied .icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="access-denied">
        <div class="icon">⚠️</div>
        <h1>Access Denied</h1>
        <p class="lead">You do not have permission to access this page.</p>
        <p>Please contact your administrator if you believe this is an error.</p>
        <div class="mt-4">
            <a href="/apsdreamhome/admin/dashboard.php" class="btn btn-primary">Return to Dashboard</a>
            <a href="/apsdreamhome/admin/logout.php" class="btn btn-secondary ms-2">Logout</a>
        </div>
    </div>
</body>
</html>