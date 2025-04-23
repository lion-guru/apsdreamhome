<?php
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../config/base_url.php';
    // require_once __DIR__ . '/../functions/asset_helper.php'; // Deprecated, use get_asset_url() from common-functions.php or updated-config-paths.php instead
    require_once __DIR__ . '/../db_config.php';

    // Ensure $conn is initialized
    if (!isset($conn)) {
        $conn = getDbConnection();
    }

    // Load header settings from database
    $menu_db_error = false;
    $settings = [];
    try {
        $sql = "SELECT * FROM site_settings WHERE setting_name IN ('header_menu_items', 'site_logo', 'header_styles')";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = json_decode($row['value'], true);
        }
    } catch (Throwable $e) {
        $menu_db_error = true;
    }

    // Fallback default menu (if DB fails or menu JSON is invalid)
    $default_menu = [
        ['text' => 'Home', 'url' => $base_url . 'index.php', 'icon' => 'fa-home'],
        ['text' => 'Properties', 'url' => $base_url . 'property-listings.php', 'icon' => 'fa-building'],
        ['text' => 'About', 'url' => $base_url . 'about.php', 'icon' => 'fa-info-circle'],
        ['text' => 'Contact', 'url' => $base_url . 'contact.php', 'icon' => 'fa-envelope'],
        ['text' => 'News', 'url' => $base_url . 'news.php', 'icon' => 'fa-newspaper'],
        ['text' => 'Feedback', 'url' => $base_url . 'submit_feedback.php', 'icon' => 'fa-comments'],
        ['text' => 'Register', 'url' => $base_url . 'register.php', 'icon' => 'fa-user-plus'],
        ['text' => 'Login', 'url' => $base_url . 'login.php', 'icon' => 'fa-sign-in-alt'],
        ['text' => 'Dashboard', 'url' => $base_url . 'user_dashboard.php', 'icon' => 'fa-tachometer-alt'],
        ['text' => 'Logout', 'url' => $base_url . 'logout.php', 'icon' => 'fa-sign-out-alt']
    ];

    // Use menu from DB if available and valid, else fallback
    $menu_items = $default_menu;
    if (!$menu_db_error && isset($settings['header_menu_items']) && is_array($settings['header_menu_items']) && count($settings['header_menu_items']) > 0) {
        $menu_items = $settings['header_menu_items'];
    }

    // Set default values if not found in database
    if (!isset($settings['site_logo'])) {
        $settings['site_logo'] = [
            'url' => $base_url . 'assets/images/logo/aps1.png'
        ];
    }

    $header_styles = $settings['header_styles'] ?? [
        'background' => '#1e3c72',
        'text_color' => '#ffffff'
    ];

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($page_title) ? $page_title : 'APS Dream Homes - Your Trusted Real Estate Partner'; ?></title>
        <meta name="description" content="<?php echo isset($meta_description) ? $meta_description : 'APS Dream Homes offers premium real estate services in Gorakhpur, Lucknow and across UP. Find your dream property with our expert guidance.'; ?>">
        <meta name="keywords" content="real estate, property, APS Dream Homes, Gorakhpur property, Lucknow property, residential plots, commercial property, property investment, real estate agency UP">
        <meta name="author" content="APS Dream Homes">
        <meta name="robots" content="index, follow">
        
        <!-- SEO & Social Meta Tags -->
        <meta name="description" content="APS Dream Homes - Premium properties, expert guidance, and trusted service in Gorakhpur, Lucknow & beyond.">
        <meta name="keywords" content="real estate, property, buy home, Gorakhpur, Lucknow, APS Dream Homes, flats, plots, houses">
        <meta property="og:title" content="APS Dream Homes - Find Your Dream Home">
        <meta property="og:description" content="Premium properties, expert guidance, and trusted service in Gorakhpur, Lucknow & beyond.">
        <meta property="og:image" content="<?php echo $base_url; ?>assets/images/banner/ban1.jpg">
        <meta property="og:url" content="<?php echo $base_url; ?>">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="APS Dream Homes - Find Your Dream Home">
        <meta name="twitter:description" content="Premium properties, expert guidance, and trusted service in Gorakhpur, Lucknow & beyond.">
        <meta name="twitter:image" content="<?php echo $base_url; ?>assets/images/banner/ban1.jpg">
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=1.0" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css?v=1.0">
        <!-- Custom CSS Fallback -->
        <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/home.css?v=1.0">
        <!-- If get_asset_url fails, fallback above will ensure styling -->
        <style>
            .header-desktop { background: #1e3c72; color: #fff; }
            .header-nav { display: flex; justify-content: space-between; align-items: center; }
            .logo img { height: 42px; margin-right: 10px; }
            .logo-text { font-size: 1.25rem; font-weight: 600; }
            .nav-menu { display: flex; gap: 1.3rem; align-items: center; list-style: none; }
            .nav-item a { color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 5px; transition: color 0.3s; }
            .nav-item a:hover, .nav-item.active a { color: #e74c3c; background: #fbeeee; }
            @media (max-width: 991px) { .nav-menu { flex-direction: column; } }
        </style>
        <!-- Sticky Navigation Enhancement -->
        <style>
        .sticky-nav {
            position: sticky;
            top: 0;
            z-index: 1050;
            box-shadow: 0 2px 16px rgba(42,82,152,0.07);
            background: #fff;
            transition: box-shadow 0.2s, background 0.2s;
        }
        .sticky-nav.scrolled {
            box-shadow: 0 4px 24px rgba(42,82,152,0.16);
            background: #f6f9fc;
        }
        </style>
        <!-- Dark Mode Toggle Styles -->
        <style>
        :root {
          --primary-bg: #fff;
          --primary-text: #222;
          --secondary-bg: #f6f9fc;
          --card-bg: #fff;
        }
        body.dark-mode {
          --primary-bg: #141d2b;
          --primary-text: #eaeaea;
          --secondary-bg: #1e293b;
          --card-bg: #212c3b;
          background: var(--primary-bg) !important;
          color: var(--primary-text) !important;
        }
        body.dark-mode .bg-light { background-color: var(--secondary-bg) !important; }
        body.dark-mode .bg-white { background-color: var(--card-bg) !important; }
        body.dark-mode .card, body.dark-mode .feature-card { background-color: var(--card-bg) !important; color: var(--primary-text) !important; }
        body.dark-mode .navbar, body.dark-mode .sticky-nav { background: var(--primary-bg) !important; }
        body.dark-mode .text-dark { color: #f9fafb !important; }
        body.dark-mode .text-secondary { color: #cbd5e1 !important; }
        body.dark-mode .btn-outline-primary { border-color: #eaeaea; color: #eaeaea; }
        body.dark-mode .btn-outline-primary:hover { background: #eaeaea; color: #222; }
        .dark-toggle-btn {
          position: fixed;
          bottom: 100px;
          right: 24px;
          z-index: 9999;
          background: #222;
          color: #fff;
          border: none;
          border-radius: 50%;
          width: 48px;
          height: 48px;
          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 4px 16px rgba(0,0,0,0.18);
          font-size: 1.4rem;
          cursor: pointer;
          transition: background 0.2s;
        }
        .dark-toggle-btn.active { background: #ffc107; color: #222; }
        </style>
        <?php if(isset($additional_css)) echo $additional_css; ?>
    </head>
    <body>
        <?php if (isset($additional_js)) echo $additional_js; ?>
        <header class="header-desktop sticky-nav" role="banner">
            <nav class="navbar navbar-expand-lg navbar-light" aria-label="Main navigation" role="navigation">
                <div class="container">
                    <a href="<?php echo $base_url; ?>" class="logo navbar-brand" aria-label="Home">
                        <img src="<?php echo $base_url; ?>assets/images/logo/aps1.png" alt="Logo" style="max-height:60px;object-fit:contain;display:block;">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="mainNavbar">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0" role="menubar">
                        <?php
                        function render_menu_items($items) {
                            foreach ($items as $item) {
                                $has_children = isset($item['children']) && is_array($item['children']) && count($item['children']) > 0;
                                if ($has_children) {
                                    echo '<li class="nav-item dropdown" role="none">';
                                    echo '<a class="nav-link dropdown-toggle" href="#" id="dropdown'.md5($item['text']).'" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                                    if (!empty($item['icon'])) echo '<i class="fa '.$item['icon'].'"></i> ';
                                    echo htmlspecialchars($item['text']);
                                    echo '</a>';
                                    echo '<ul class="dropdown-menu" aria-labelledby="dropdown'.md5($item['text']).'">';
                                    foreach ($item['children'] as $child) {
                                        echo '<li><a class="dropdown-item" href="'.htmlspecialchars($child['url']).'">';
                                        if (!empty($child['icon'])) echo '<i class="fa '.$child['icon'].'"></i> ';
                                        echo htmlspecialchars($child['text']);
                                        echo '</a></li>';
                                    }
                                    echo '</ul>';
                                    echo '</li>';
                                } else {
                                    echo '<li class="nav-item" role="none">';
                                    echo '<a class="nav-link" href="'.htmlspecialchars($item['url']).'">';
                                    if (!empty($item['icon'])) echo '<i class="fa '.$item['icon'].'"></i> ';
                                    echo htmlspecialchars($item['text']);
                                    echo '</a>';
                                    echo '</li>';
                                }
                            }
                        }
                        // Filter for login/logout/dashboard/register etc based on session
                        $filtered_menu = [];
                        foreach ($menu_items as $item) {
                            if (in_array($item['text'], ['Login','Register']) && isset($_SESSION['user_id'])) continue;
                            if (in_array($item['text'], ['Dashboard','Logout']) && !isset($_SESSION['user_id'])) continue;
                            $filtered_menu[] = $item;
                        }
                        render_menu_items($filtered_menu);
                        ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <!-- Dark Mode Toggle Button -->
        <button class="dark-toggle-btn" id="darkModeToggle" title="Toggle dark mode"><i class="fas fa-moon"></i></button>
        <script>
        // Dark mode toggle logic
        const darkToggle = document.getElementById('darkModeToggle');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        function setDarkMode(on) {
          document.body.classList.toggle('dark-mode', on);
          darkToggle.classList.toggle('active', on);
          localStorage.setItem('aps_dark_mode', on ? '1' : '0');
          darkToggle.innerHTML = on ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        }
        darkToggle.addEventListener('click', function() {
          setDarkMode(!document.body.classList.contains('dark-mode'));
        });
        // On load
        const saved = localStorage.getItem('aps_dark_mode');
        if (saved === '1' || (saved === null && prefersDark)) setDarkMode(true);
        </script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nav = document.querySelector('header, .main-header, .header-desktop');
            if (nav) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 30) {
                        nav.classList.add('scrolled');
                    } else {
                        nav.classList.remove('scrolled');
                    }
                });
            }
        });
        </script>
    <?php } catch (Throwable $e) {
        // Fallback to static header on error
        include __DIR__ . '/static_header.php';
    }
    ?>