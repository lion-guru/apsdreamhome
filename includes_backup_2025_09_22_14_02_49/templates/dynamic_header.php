<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Cache-Control" content="public, max-age=31536000, immutable">
    
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'APS Dream Homes'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'APS Dream Homes - Your trusted partner in real estate'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <?php if (isset($additional_css)) echo $additional_css; ?>

    <!-- Cross-browser compatibility styles -->
    <style>
        :root {
            color-scheme: light;
        }
        html {
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -moz-text-size-adjust: 100%;
            tab-size: 4;
            -moz-tab-size: 4;
        }
        body {
            margin: 0;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body>
<a href="#main-content" class="visually-hidden-focusable skip-link">Skip to main content</a>
<?php
require_once __DIR__ . '/../../includes/db_settings.php';

// Default menu items if database settings are not available
$default_menu = [
    ['url' => '/apsdreamhomefinal/', 'text' => 'Home', 'icon' => 'fa-home', 'aria_label' => 'Home Page'],
    [
        'text' => 'Projects',
        'icon' => 'fa-building',
        'aria_label' => 'Our Projects',
        'submenu' => [
            ['url' => '/apsdreamhomefinal/properties.php?location=Gorakhpur', 'text' => 'Gorakhpur Projects', 'icon' => 'fa-map-marker-alt'],
            ['url' => '/apsdreamhomefinal/properties.php?location=Lucknow', 'text' => 'Lucknow Projects', 'icon' => 'fa-map-marker-alt']
        ]
    ],
    ['url' => '/apsdreamhomefinal/properties.php', 'text' => 'All Properties', 'icon' => 'fa-building', 'aria_label' => 'View all properties'],
    ['url' => '/apsdreamhomefinal/about.php', 'text' => 'About', 'icon' => 'fa-info-circle', 'aria_label' => 'About Us'],
    ['url' => '/apsdreamhomefinal/contact.php', 'text' => 'Contact', 'icon' => 'fa-envelope', 'aria_label' => 'Contact Us'],
    ['url' => '/apsdreamhomefinal/login.php', 'text' => 'Login', 'icon' => 'fa-sign-in-alt', 'aria_label' => 'Login to Your Account']
];

// Default settings
$settings = [
    'header_menu_items' => json_encode($default_menu),
    'site_logo' => '/apsdreamhomefinal/assets/images/logo.png',
    'header_styles' => json_encode([
        'background' => '#ffffff',
        'text_color' => '#333333',
        'hover_color' => '#007bff'
    ])
];

// Try to fetch settings from database
$conn = get_db_connection();

// Try to get settings from database if connection is successful
if ($conn) {
    $sql = "SELECT * FROM site_settings WHERE setting_name IN ('header_menu_items', 'site_logo', 'header_styles')";
    try {
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    } catch (Exception $e) {
        error_log('Header DB error: ' . $e->getMessage());
        // Using default settings, no need to show error to user
    }
}

// Parse the settings
$menu_items = json_decode($settings['header_menu_items'], true) ?: $default_menu;
// Default header styles
$default_header_styles = [
    'background' => '#ffffff',
    'text_color' => '#333333',
    'hover_color' => '#007bff'
];

// Merge with database settings, ensuring all keys exist
$header_styles = array_merge(
    $default_header_styles,
    json_decode($settings['header_styles'] ?? '{}', true) ?: []
);
$logo_path = $settings['site_logo'] ?: '/apsdreamhomefinal/assets/images/logo.png';

// Extract colors for easier access
$backgroundColor = $header_styles['background'];
$textColor = $header_styles['text_color'];
$hoverColor = $header_styles['hover_color'];
?>

<!-- Custom Header Styles -->
<style>
    /* Cross-browser compatibility */
    .site-header {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        background-color: <?php echo htmlspecialchars($backgroundColor); ?>;
        color: <?php echo htmlspecialchars($textColor); ?>;
        padding: 1rem 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        -webkit-box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1000;
    }
    .site-header a {
        color: <?php echo htmlspecialchars($textColor); ?>;
        text-decoration: none;
        transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        -moz-transition: all 0.3s ease;
        -o-transition: all 0.3s ease;
        display: inline-block;
        position: relative;
    }
    .site-header a:hover,
    .site-header a:focus {
        color: <?php echo htmlspecialchars($hoverColor); ?>;
        text-decoration: none;
        outline: none;
    }
    .site-header a:focus-visible {
        outline: 2px solid <?php echo htmlspecialchars($hoverColor); ?>;
        outline-offset: 2px;
    }
    .site-logo {
        display: inline-block;
        line-height: 1;
    }
    .site-logo img {
        display: block;
        max-height: 60px;
        width: auto;
        height: auto;
    }
    .nav-item {
        margin: 0 10px;
        position: relative;
    }
    .nav-item i {
        display: inline-block;
        margin-right: 5px;
        width: 1em;
        text-align: center;
        vertical-align: middle;
    }
    .nav-link {
        font-weight: 500;
        padding: 0.5rem 1rem;
        position: relative;
        display: inline-flex;
        align-items: center;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    .navbar-toggler {
        border: 1px solid <?php echo htmlspecialchars($textColor); ?>;
        padding: 0.5rem;
        background: transparent;
        transition: all 0.3s ease;
        -webkit-transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }
    .navbar-toggler:focus {
        outline: none;
        box-shadow: 0 0 0 2px <?php echo htmlspecialchars($hoverColor); ?>;
        -webkit-box-shadow: 0 0 0 2px <?php echo htmlspecialchars($hoverColor); ?>;
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='<?php echo urlencode($textColor); ?>' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        display: block;
        width: 1.5em;
        height: 1.5em;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100%;
    }
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background-color: <?php echo htmlspecialchars($backgroundColor); ?>;
            padding: 1rem;
            -webkit-box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    }
    .skip-link {
        position: absolute;
        left: -999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
        z-index: 10000;
        background: #007bff;
        color: #fff;
        padding: 8px 16px;
        border-radius: 4px;
    }
    .skip-link:focus {
        left: 16px;
        top: 16px;
        width: auto;
        height: auto;
        outline: 2px solid #fff;
        outline-offset: 2px;
    }
</style>
<header class="site-header" role="banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3">
                <a href="/apsdreamhomefinal/" class="site-logo" aria-label="APS Dream Homes - Return to Homepage">
                    <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="APS Dream Homes Logo" width="180" height="60">
                </a>
            </div>
            <div class="col-md-9">
                <nav class="navbar navbar-expand-lg" role="navigation" aria-label="Main Navigation">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation menu">
                        <span class="navbar-toggler-icon" aria-hidden="true"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <?php foreach ($menu_items as $item): ?>
                                <?php if (isset($item['submenu'])): ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="<?php echo strtolower(str_replace(' ', '-', $item['text'])); ?>-dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?php if (!empty($item['icon'])): ?>
                                                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($item['text']); ?></span>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="<?php echo strtolower(str_replace(' ', '-', $item['text'])); ?>-dropdown">
                                            <?php foreach ($item['submenu'] as $subitem): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?php echo htmlspecialchars($subitem['url']); ?>" aria-label="<?php echo htmlspecialchars($subitem['aria_label'] ?? $subitem['text']); ?>">
                                                        <?php if (!empty($subitem['icon'])): ?>
                                                            <i class="fas <?php echo htmlspecialchars($subitem['icon']); ?> me-2" aria-hidden="true"></i>
                                                        <?php endif; ?>
                                                        <?php echo htmlspecialchars($subitem['text']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="<?php echo htmlspecialchars($item['url']); ?>" aria-label="<?php echo htmlspecialchars($item['aria_label'] ?? $item['text']); ?>">
                                            <?php if (!empty($item['icon'])): ?>
                                                <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($item['text']); ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>
<main id="main-content" tabindex="-1">

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .nav-link:hover {
        color: <?php echo htmlspecialchars($header_styles['hover_color']); ?> !important;
    }
</style>