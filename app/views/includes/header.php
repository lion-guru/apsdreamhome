<?php
/**
 * Include Wrapper - Header
 * This file redirects to the actual layout header
 * Created to fix include path issues in views
 */

// Define base URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . '://' . $host . rtrim($base, '/') . '/');
}

// Include the actual header from layouts
$headerPath = __DIR__ . '/../layouts/header.php';
if (file_exists($headerPath)) {
    include $headerPath;
} else {
    // Fallback minimal header
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">APS Dream Home</a>
        </div>
    </nav>
    <div class="container mt-4">
<?php
}
