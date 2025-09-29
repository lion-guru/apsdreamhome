<?php
/**
 * Enhanced Header for APS Dream Home
 * Includes dynamic database-driven content with fallback
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamic header with fallback
$dynamicError = false;
try {
    // Try to load dynamic content from database
    if (file_exists('../admin/config.php')) {
        require_once '../admin/config.php';
    }
    if (file_exists('../includes/db_config.php')) {
        require_once '../includes/db_config.php';
    }

    $conn = function_exists('getDbConnection') ? getDbConnection() : null;

    // Get site settings from database if available
    if ($conn) {
        $sql = "SELECT setting_name, setting_value FROM site_settings WHERE setting_name IN ('site_title', 'site_logo', 'header_background', 'header_text_color')";
        $result = $conn->query($sql);
        $settings = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['setting_value'];
            }
        }

        $page_title = $settings['site_title'] ?? 'APS Dream Home - Find Your Dream Property';
        $logo_url = $settings['site_logo'] ?? '/assets/images/logo.png';
        $header_bg = $settings['header_background'] ?? '#1e3c72';
        $header_text = $settings['header_text_color'] ?? '#ffffff';
    } else {
        $page_title = 'APS Dream Home - Find Your Dream Property';
        $logo_url = '/assets/images/logo.png';
        $header_bg = '#1e3c72';
        $header_text = '#ffffff';
    }
} catch (Throwable $e) {
    $dynamicError = true;
    $page_title = 'APS Dream Home - Find Your Dream Property';
    $logo_url = '/assets/images/logo.png';
    $header_bg = '#1e3c72';
    $header_text = '#ffffff';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .navbar-dark {
            background-color: <?php echo $header_bg; ?> !important;
        }
        .navbar-dark .navbar-brand,
        .navbar-dark .nav-link {
            color: <?php echo $header_text; ?> !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="<?php echo $logo_url; ?>" alt="APS Dream Home" style="height:40px; margin-right:10px;">
                APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/properties">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/dashboard" class="btn btn-outline-light me-2">Dashboard</a>
                        <a href="/auth/logout" class="btn btn-danger">Logout</a>
                    <?php else: ?>
                        <a href="/auth/login" class="btn btn-outline-light me-2">Login</a>
                        <a href="/auth/register" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
