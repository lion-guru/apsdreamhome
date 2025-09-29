<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#0d6efd"> <!-- Update with your primary color -->
    <meta name="author" content="APS Dream Home">
    <meta name="robots" content="index, follow">
    
    <title><?php echo e($page_data['title'] ?? 'APS Dream Home'); ?></title>
    <meta name="description" content="<?php echo e($page_data['description'] ?? 'Find your dream home with APS Dream Home.'); ?>">
    <meta name="keywords" content="<?php echo e($page_data['keywords'] ?? 'real estate, property, home, buy, rent'); ?>">
    
    <!-- Canonical URL -->
    <?php if (!empty($page_data['canonical_url'])): ?>
    <link rel="canonical" href="<?php echo e($page_data['canonical_url']); ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo SITE_URL; ?>/assets/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?php echo SITE_URL; ?>/assets/favicon/safari-pinned-tab.svg" color="#0d6efd"> <!-- Update color -->
    <meta name="msapplication-TileColor" content="#0d6efd"> <!-- Update color -->
    <meta name="theme-color" content="#ffffff"> <!-- Or your main background color -->

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e($page_data['canonical_url'] ?? SITE_URL); ?>">
    <meta property="og:title" content="<?php echo e($page_data['title'] ?? 'APS Dream Home'); ?>">
    <meta property="og:description" content="<?php echo e($page_data['description'] ?? 'Find your dream home.'); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg"> <!-- Create and replace this image -->
    <meta property="og:site_name" content="APS Dream Home">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo e($page_data['canonical_url'] ?? SITE_URL); ?>">
    <meta name="twitter:title" content="<?php echo e($page_data['title'] ?? 'APS Dream Home'); ?>">
    <meta name="twitter:description" content="<?php echo e($page_data['description'] ?? 'Find your dream home.'); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/twitter-card.jpg"> <!-- Create and replace this image -->

    <!-- Preload critical resources -->
    <!-- Bootstrap 5.3.0 CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet" 
          onerror="this.onerror=null; this.href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Font Awesome 6.4.0 -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/plugins/font-awesome/css/all.min.css"
          onerror="this.onerror=null; this.href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'">

    <!-- Critical CSS (inlined) -->
    <style>
        <?php 
        $critical_css_path = INCLUDES_DIR . '/inline/critical.css';
        if (file_exists($critical_css_path)) {
            echo file_get_contents($critical_css_path);
        }
        ?>
    </style>
    
    <!-- Defer non-critical CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css"></noscript>

    <!-- No-JS fallback and JS enhancement class -->
    <script>
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
        window.siteUrl = '<?php echo SITE_URL; ?>'; // Make base URL available to JS
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/../../include/updated-common-header.php'; ?>
    <main id="main-content" class="flex-grow-1">
    <!-- Main page content will be injected here by index.new.php -->
