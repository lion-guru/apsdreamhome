<?php
require_once __DIR__ . '/../config/base_url.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/updated-config-paths.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'APS Dream Homes - Your Trusted Real Estate Partner'; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/custom-fonts.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/custom-styles.css">
    <?php echo isset($additional_css) ? $additional_css : ''; ?>

    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/popper.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/owl.carousel.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/wow.min.js"></script>
    <?php echo isset($additional_js) ? $additional_js : ''; ?>
    
    <!-- AI Chatbot Widget Loader -->
    <script src="<?php echo $base_url; ?>assets/js/ai_chatbot.js"></script>
</head>
<body>
    <header>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="<?php echo $base_url; ?>">
                    <img src="<?php echo $base_url; ?>assets/images/aps-logo.png" alt="APS Dream Homes" height="50">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>properties.php">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>plots-availability.php">Plots Availability</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>contact.php">Contact</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>logout.php">Logout</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>login.php">Login</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
