<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['is_authenticated'] !== true) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - APS Dream Homes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .header {
            background: #fff;
            border-bottom: 1px solid #e3e3e3;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .user-menu {
            margin-left: auto;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-name {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header class="header py-3">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">APS Dream Homes</a>
                <div class="user-menu">
                    <div class="user-info">
                        <span class="user-name">
    <?php
        echo isset($_SESSION['admin_session']['username']) && $_SESSION['admin_session']['username']
            ? htmlspecialchars($_SESSION['admin_session']['username'])
            : 'Admin';
    ?>
</span>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Header spacing -->
    <div style="height: 70px;"></div>
