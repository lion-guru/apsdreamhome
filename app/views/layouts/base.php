<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Your Trusted Real Estate Partner'; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $page_description ?? 'Find your dream home with APS Dream Home. Premium properties in Gorakhpur, Lucknow & UP. Expert real estate services with modern technology.'; ?>">
    <meta name="keywords" content="<?php echo $page_keywords ?? 'real estate Gorakhpur, property for sale, buy house, apartments Lucknow, real estate UP, dream home'; ?>">
    <meta name="author" content="APS Dream Home">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL ?? '/'; ?>">
    <meta property="og:title" content="APS Dream Home - Premium Real Estate in UP">
    <meta property="og:description" content="Discover exclusive properties with the most trusted real estate platform in Uttar Pradesh.">
    <meta property="og:image" content="<?php echo BASE_URL; ?>assets/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo BASE_URL ?? '/'; ?>">
    <meta property="twitter:title" content="APS Dream Home - Premium Real Estate">
    <meta property="twitter:description" content="Find your dream home with APS Dream Home - Premium properties in UP">
    <meta property="twitter:image" content="<?php echo BASE_URL; ?>assets/images/twitter-card.jpg">
    
    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/header.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            --secondary-gradient: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(0, 0, 0, 0.1);
            --text-dark: #1e293b;
            --text-light: #64748b;
            --accent-color: #d97706;
            --primary-color: #1e40af;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .header-spacer {
            height: 80px;
        }
    </style>
</head>
<body>
    <!-- Loading Elements -->
    <div class="loading-bar" id="loadingBar"></div>
    <div class="page-transition" id="pageTransition"></div>

    <?php include __DIR__ . '/header_unified.php'; ?>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php echo $content ?? ''; ?>
    </main>

    <?php include __DIR__ . '/footer_unified.php'; ?>
</body>
</html>
