<?php
/**
 * Universal Template System for APS Dream Home
 * Combines the best of all existing designs into one flexible system
 */

// Prevent direct access - but allow when accessed via web server
if (!defined('ABSPATH') && !isset($_SERVER['HTTP_HOST'])) {
    exit('Direct access forbidden');
}

/**
 * Universal Template Manager
 * Handles different themes and layouts
 */
class UniversalTemplate {

    private $theme = 'default';
    private $layout = 'full';
    private $page_title = 'APS Dream Home';
    private $meta_description = 'Premium real estate platform for buying, selling, and renting properties';
    private $custom_css = '';
    private $custom_js = '';
    private $show_navigation = true;
    private $show_footer = true;

    // Theme configurations
    private $themes = [
        'default' => [
            'primary_color' => '#4e73df',
            'secondary_color' => '#1cc88a',
            'accent_color' => '#f6c23e',
            'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'card_style' => 'glass-morphism'
        ],
        'dashboard' => [
            'primary_color' => '#4e73df',
            'secondary_color' => '#1cc88a',
            'accent_color' => '#f6c23e',
            'background' => 'linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%)',
            'card_style' => 'modern-glass'
        ],
        'login' => [
            'primary_color' => '#28a745',
            'secondary_color' => '#20c997',
            'accent_color' => '#17a2b8',
            'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'card_style' => 'centered-login'
        ],
        'admin' => [
            'primary_color' => '#6f42c1',
            'secondary_color' => '#fd7e14',
            'accent_color' => '#20c997',
            'background' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
            'card_style' => 'admin-panel'
        ]
    ];

    /**
     * Set theme
     */
    public function setTheme($theme) {
        $this->theme = isset($this->themes[$theme]) ? $theme : 'default';
        return $this;
    }

    /**
     * Set layout
     */
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Set page title
     */
    public function setTitle($title) {
        $this->page_title = $title;
        return $this;
    }

    /**
     * Set meta description
     */
    public function setDescription($description) {
        $this->meta_description = $description;
        return $this;
    }

    /**
     * Add custom CSS
     */
    public function addCSS($css) {
        $this->custom_css .= $css . "\n";
        return $this;
    }

    /**
     * Add custom JavaScript
     */
    public function addJS($js) {
        $this->custom_js .= $js . "\n";
        return $this;
    }

    /**
     * Hide navigation
     */
    public function hideNavigation() {
        $this->show_navigation = false;
        return $this;
    }

    /**
     * Hide footer
     */
    public function hideFooter() {
        $this->show_footer = false;
        return $this;
    }

    /**
     * Render complete page
     */
    public function render($content) {
        $this->renderHTML($content);
    }

    /**
     * Render only header
     */
    public function outputHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->page_title); ?></title>
            <meta name="description" content="<?php echo htmlspecialchars($this->meta_description); ?>">

            <!-- Bootstrap 5 -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <!-- Google Fonts -->
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

            <?php $this->outputStyles(); ?>
        </head>
        <body>
            <?php if ($this->show_navigation): ?>
                <?php $this->outputNavigation(); ?>
            <?php endif; ?>
        <?php
    }

    /**
     * Render only footer
     */
    public function outputFooter() {
        ?>
        </body>
        </html>
        <?php
    }

    /**
     * Output complete HTML
     */
    private function renderHTML($content) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->page_title); ?></title>
            <meta name="description" content="<?php echo htmlspecialchars($this->meta_description); ?>">

            <!-- Bootstrap 5 -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <!-- Google Fonts -->
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

            <?php $this->outputStyles(); ?>
        </head>
        <body>
            <?php if ($this->show_navigation): ?>
                <?php $this->outputNavigation(); ?>
            <?php endif; ?>

            <main class="main-content" style="<?php echo $this->getMainContentStyle(); ?>">
                <?php echo $content; ?>
            </main>

            <?php if ($this->show_footer): ?>
                <?php $this->outputFooter(); ?>
            <?php endif; ?>

            <?php $this->outputScripts(); ?>
        </body>
        </html>
        <?php
    }

    /**
     * Output CSS styles
     */
    private function outputStyles() {
        $theme = $this->themes[$this->theme];
        ?>
        <style>
            :root {
                --primary-color: <?php echo $theme['primary_color']; ?>;
                --secondary-color: <?php echo $theme['secondary_color']; ?>;
                --accent-color: <?php echo $theme['accent_color']; ?>;
                --dark-color: #5a5c69;
                --light-color: #f8f9fc;
                --danger-color: #e74a3b;
                --success-color: #1cc88a;
                --warning-color: #f6c23e;
                --info-color: #36b9cc;
            }

            body {
                font-family: 'Inter', sans-serif;
                background: <?php echo $theme['background']; ?>;
                color: #333;
                margin: 0;
                padding: 0;
                min-height: 100vh;
            }

            <?php $this->outputThemeSpecificStyles(); ?>

            /* Custom CSS */
            <?php echo $this->custom_css; ?>

            /* Universal Components */
            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                background-color: <?php echo $this->darkenColor($theme['primary_color'], 20); ?>;
                border-color: <?php echo $this->darkenColor($theme['primary_color'], 25); ?>;
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            }

            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                margin-bottom: 1.5rem;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .hero-section {
                background: <?php echo $theme['background']; ?>;
                color: white;
                padding: 6rem 0;
                text-align: center;
            }

            .section-title {
                font-size: 2rem;
                font-weight: 600;
                color: var(--primary-color);
                margin-bottom: 2rem;
                text-align: center;
            }

            .alert {
                border-radius: 10px;
                border: none;
            }

            .navbar {
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(10px);
                box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
                padding: 0.25rem 0;
                transition: all 0.3s ease;
            }

            .navbar.scrolled {
                background: rgba(255, 255, 255, 0.98) !important;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-color) !important;
                font-size: 1.5rem;
                display: flex;
                align-items: center;
                transition: transform 0.3s ease;
                padding: 0.5rem 0;
            }

            .navbar-brand:hover {
                transform: scale(1.02);
                color: var(--primary-color) !important;
            }

            .logo-icon {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                border-radius: 8px;
                padding: 6px;
                color: white;
                box-shadow: 0 2px 10px rgba(78, 115, 223, 0.3);
            }

            .logo-text h6 {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .logo-text small {
                font-size: 0.7rem;
                color: #6c757d;
                font-weight: 400;
            }

            .nav-link {
                color: #5a5c69 !important;
                font-weight: 500;
                transition: all 0.3s ease;
                padding: 0.5rem 0.75rem !important;
                border-radius: 6px;
                margin: 0 0.125rem;
                position: relative;
                font-size: 0.9rem;
            }

            .nav-link:hover, .nav-link.active {
                color: var(--primary-color) !important;
                background: rgba(78, 115, 223, 0.1);
                transform: translateY(-1px);
            }

            .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -2px;
                left: 50%;
                transform: translateX(-50%);
                width: 20px;
                height: 2px;
                background: var(--primary-color);
                border-radius: 1px;
            }

            .dropdown-menu {
                border: none;
                border-radius: 8px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                padding: 0.25rem 0;
                margin-top: 0.25rem;
                min-width: 200px;
            }

            .dropdown-item {
                padding: 0.5rem 1rem;
                font-weight: 500;
                transition: all 0.3s ease;
                border-radius: 4px;
                margin: 0 0.125rem;
                font-size: 0.875rem;
            }

            .dropdown-item:hover {
                background: rgba(78, 115, 223, 0.1);
                color: var(--primary-color);
                transform: translateX(3px);
            }

            /* Top Bar Styles */
            .top-bar {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                font-size: 0.8rem;
                padding: 0.4rem 0;
            }

            .top-bar .hover-primary:hover {
                color: var(--primary-color) !important;
                transition: color 0.3s ease;
            }

            /* Search Modal Styles */
            .search-form .form-label {
                font-weight: 600;
                color: var(--dark-color);
                margin-bottom: 0.5rem;
            }

            .search-form .form-select,
            .search-form .form-control {
                border-radius: 6px;
                border: 1px solid #dee2e6;
                padding: 0.5rem 0.75rem;
                transition: all 0.3s ease;
                font-size: 0.875rem;
            }

            .search-form .form-select:focus,
            .search-form .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.25);
            }

            /* Back to Top Button */
            .back-to-top {
                position: fixed;
                bottom: 1rem;
                right: 1rem;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                border: none;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                box-shadow: 0 3px 12px rgba(78, 115, 223, 0.4);
                transition: all 0.3s ease;
                z-index: 1000;
                opacity: 0;
                visibility: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .back-to-top.show {
                opacity: 1;
                visibility: visible;
            }

            .back-to-top:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(78, 115, 223, 0.6);
            }

            /* User Dropdown Enhancements */
            .user-avatar {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 0.875rem;
            }

            /* Mobile Responsive Improvements */
            @media (max-width: 992px) {
                .navbar-nav {
                    background: rgba(255, 255, 255, 0.98);
                    border-radius: 8px;
                    padding: 1rem;
                    margin: 0.5rem 0;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                }

                .navbar-nav .nav-item {
                    margin-bottom: 0.25rem;
                }

                .nav-link {
                    padding: 0.75rem 1rem !important;
                    border-radius: 6px !important;
                    font-size: 1rem;
                }

                .dropdown-menu {
                    position: static;
                    float: none;
                    width: auto;
                    margin-top: 0;
                    background-color: transparent;
                    border: 0;
                    box-shadow: none;
                    padding-left: 1rem;
                }

                .dropdown-item {
                    padding: 0.5rem 0.75rem;
                    background: rgba(78, 115, 223, 0.05);
                    margin: 0.125rem 0;
                    border-radius: 4px;
                }

                .dropdown-item:hover {
                    background: rgba(78, 115, 223, 0.1);
                    transform: translateX(0);
                }
            }

            @media (max-width: 768px) {
                .top-bar {
                    font-size: 0.7rem;
                    padding: 0.3rem 0;
                }

                .top-bar .d-flex {
                    flex-direction: column;
                    gap: 0.25rem;
                    text-align: center;
                }

                .navbar-brand .logo-text h6 {
                    font-size: 1rem;
                }

                .navbar-brand .logo-text small {
                    font-size: 0.65rem;
                }

                .nav-link {
                    padding: 0.5rem 0.75rem !important;
                    margin: 0.125rem 0;
                    font-size: 0.9rem;
                }

                .back-to-top {
                    bottom: 0.75rem;
                    right: 0.75rem;
                    width: 40px;
                    height: 40px;
                }

                /* Mobile dropdown fixes */
                .dropdown-menu {
                    background: rgba(255, 255, 255, 0.95) !important;
                    backdrop-filter: blur(5px);
                    border-radius: 8px !important;
                    border: 1px solid rgba(0, 0, 0, 0.1) !important;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
                    transform: none !important;
                    position: relative !important;
                }

                .dropdown-item {
                    background: transparent !important;
                    transform: none !important;
                    font-size: 0.875rem;
                }
            }

            @media (max-width: 576px) {
                .container-fluid {
                    padding-left: 0.75rem;
                    padding-right: 0.75rem;
                }

                .navbar-brand {
                    padding: 0.25rem 0;
                }

                .logo-icon {
                    padding: 4px;
                }

                .logo-text h6 {
                    font-size: 0.9rem;
                }

                .btn-sm {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.8rem;
                }
            }

            /* Enhanced Animations */
            .navbar {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .nav-link {
                position: relative;
                overflow: hidden;
            }

            .nav-link::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(78, 115, 223, 0.1), transparent);
                transition: left 0.5s ease;
            }

            .nav-link:hover::before {
                left: 100%;
            }

            /* Dropdown animations */
            .dropdown-menu {
                animation: dropdownFadeIn 0.2s ease-out;
            }

            @keyframes dropdownFadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Loading States */
            .btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            /* Enhanced Focus States */
            .btn:focus,
            .form-control:focus,
            .form-select:focus {
                box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.25);
                border-color: var(--primary-color);
            }

            /* Smooth Scrolling */
            html {
                scroll-behavior: smooth;
            }

            footer {
                background: rgba(0, 0, 0, 0.9) !important;
                color: white;
                padding: 3rem 0 2rem;
                margin-top: 4rem;
            }

            footer a {
                color: rgba(255, 255, 255, 0.8);
                text-decoration: none;
                transition: color 0.3s ease;
            }

            footer a:hover {
                color: white;
            }

            .loading-spinner {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 50px;
                height: 50px;
                border: 4px solid rgba(255, 255, 255, 0.3);
                border-top: 4px solid var(--primary-color);
                border-radius: 50%;
                animation: spin 1s linear infinite;
                z-index: 9999;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in {
                animation: fadeInUp 0.6s ease-out;
            }
        </style>
        <?php
    }

    /**
     * Output theme-specific styles
     */
    private function outputThemeSpecificStyles() {
        $theme = $this->themes[$this->theme];

        switch($this->theme) {
            case 'dashboard':
                echo "
                .dashboard-container {
                    max-width: 1400px;
                    margin: 0 auto;
                    padding: 20px;
                }

                .welcome-section {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    border-radius: 25px;
                    padding: 3rem;
                    margin-bottom: 2rem;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                }

                .floating-elements {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    pointer-events: none;
                    z-index: -1;
                }

                .floating-element {
                    position: absolute;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                    animation: float 6s ease-in-out infinite;
                }

                @keyframes float {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    50% { transform: translateY(-20px) rotate(180deg); }
                }
                ";
                break;

            case 'login':
                echo "
                .login-container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                    max-width: 500px;
                    width: 100%;
                }

                .login-header {
                    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                    color: white;
                    padding: 3rem 2rem 2rem;
                    text-align: center;
                }

                .form-control:focus {
                    border-color: var(--primary-color);
                    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
                }
                ";
                break;

            case 'admin':
                echo "
                .admin-container {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 15px;
                    padding: 2rem;
                    margin: 2rem auto;
                    max-width: 1200px;
                }

                .admin-sidebar {
                    background: rgba(111, 66, 193, 0.1);
                    border-radius: 15px;
                    padding: 1.5rem;
                    height: fit-content;
                }

                .admin-nav-link {
                    color: var(--primary-color) !important;
                    padding: 0.75rem 1rem;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .admin-nav-link:hover, .admin-nav-link.active {
                    background: var(--primary-color);
                    color: white !important;
                }
                ";
                break;
        }
    }

    /**
     * Output navigation
     */
    private function outputNavigation() {
        $is_logged_in = isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'];
        $user_name = $is_logged_in ? ($_SESSION['customer_name'] ?? 'User') : '';
        $current_page = basename($_SERVER['PHP_SELF']);
        $theme = $this->themes[$this->theme];
        ?>
        <!-- Modern Professional Header -->
        <header class="header-section">
            <!-- Top Bar -->
            <div class="top-bar bg-dark text-white py-2" style="font-size: 0.875rem;">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <span><i class="fas fa-phone-alt me-2"></i>+91 98765 43210</span>
                                <span><i class="fas fa-envelope me-2"></i>info@apsdreamhome.com</span>
                                <span><i class="fas fa-clock me-2"></i>Mon-Sat: 9:00 AM - 8:00 PM</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="social-links d-flex justify-content-end gap-2">
                                <a href="#" class="text-white-50 hover-primary" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a href="#" class="text-white-50 hover-primary" title="Twitter"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="text-white-50 hover-primary" title="Instagram"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="text-white-50 hover-primary" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#" class="text-white-50 hover-primary" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light sticky-top main-navbar">
                <div class="container-fluid px-3">
                    <!-- Logo -->
                    <a class="navbar-brand d-flex align-items-center" href="/">
                        <div class="logo-icon me-2">
                            <i class="fas fa-home text-primary" style="font-size: 1.8rem;"></i>
                        </div>
                        <div class="logo-text">
                            <h6 class="mb-0 fw-bold text-primary">APS Dream Home</h6>
                            <small class="text-muted d-none d-lg-block">Your Dream Property Partner</small>
                        </div>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Navigation Menu -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link px-2 py-2 <?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>" href="/">
                                    <i class="fas fa-home me-1"></i><span class="d-inline d-lg-none">Home</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link px-2 py-2 dropdown-toggle" href="#" id="propertiesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-building me-1"></i><span class="d-inline d-lg-none">Properties</span>
                                    <span class="d-none d-lg-inline">Properties</span>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="propertiesDropdown">
                                    <li><a class="dropdown-item" href="properties">All Properties</a></li>
                                    <li><a class="dropdown-item" href="properties?type=apartment">Apartments</a></li>
                                    <li><a class="dropdown-item" href="properties?type=villa">Villas</a></li>
                                    <li><a class="dropdown-item" href="properties?type=plot">Plots</a></li>
                                    <li><a class="dropdown-item" href="properties?type=commercial">Commercial</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="property-favorites">My Favorites</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link px-2 py-2 dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-concierge-bell me-1"></i><span class="d-inline d-lg-none">Services</span>
                                    <span class="d-none d-lg-inline">Services</span>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                                    <li><a class="dropdown-item" href="legal-services">Legal Services</a></li>
                                    <li><a class="dropdown-item" href="financial-services">Financial Services</a></li>
                                    <li><a class="dropdown-item" href="interior-design">Interior Design</a></li>
                                    <li><a class="dropdown-item" href="property-management">Property Management</a></li>
                                </ul>
                            </li>
                            <li class="nav-item d-none d-lg-block">
                                <a class="nav-link px-2 py-2 <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about">
                                    <i class="fas fa-info-circle me-1"></i>About Us
                                </a>
                            </li>
                            <li class="nav-item d-none d-lg-block">
                                <a class="nav-link px-2 py-2 <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact">
                                    <i class="fas fa-phone me-1"></i>Contact
                                </a>
                            </li>
                            <?php if ($is_logged_in): ?>
                            <li class="nav-item dropdown d-none d-lg-block">
                                <a class="nav-link px-2 py-2 dropdown-toggle" href="#" id="dashboardDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dashboardDropdown">
                                    <li><a class="dropdown-item" href="customer_dashboard">My Dashboard</a></li>
                                    <li><a class="dropdown-item" href="property-favorites">Favorite Properties</a></li>
                                    <li><a class="dropdown-item" href="profile">My Profile</a></li>
                                    <li><a class="dropdown-item" href="inquiries">My Inquiries</a></li>
                                </ul>
                            </li>
                            <?php endif; ?>
                        </ul>

                        <!-- Right Side Actions - Compact Design -->
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <!-- Search Button -->
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search Properties">
                                <i class="fas fa-search"></i>
                                <span class="d-none d-lg-inline ms-1">Search</span>
                            </button>

                            <?php if ($is_logged_in): ?>
                                <!-- User Menu - Compact -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="max-width: 120px;">
                                        <div class="user-avatar me-1">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <span class="d-none d-md-inline text-truncate" title="<?php echo htmlspecialchars($user_name); ?>">
                                            <?php echo htmlspecialchars(substr($user_name, 0, 8)); ?><?php echo strlen($user_name) > 8 ? '...' : ''; ?>
                                        </span>
                                        <i class="fas fa-chevron-down ms-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                                        <li class="px-3 py-2 border-bottom d-none d-lg-block">
                                            <div class="fw-bold text-primary"><?php echo htmlspecialchars($user_name); ?></div>
                                            <small class="text-muted">Customer Account</small>
                                        </li>
                                        <li><a class="dropdown-item" href="profile"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                                        <li><a class="dropdown-item" href="customer_dashboard"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                        <li><a class="dropdown-item" href="property-favorites"><i class="fas fa-heart me-2"></i>Favorites</a></li>
                                        <li><a class="dropdown-item" href="inquiries"><i class="fas fa-envelope me-2"></i>Inquiries</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <!-- Login/Register Buttons - Compact -->
                                <a href="customer_login" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-sign-in-alt me-1 d-none d-lg-inline"></i>Login
                                </a>
                                <a href="customer_registration" class="btn btn-sm btn-primary">
                                    <i class="fas fa-user-plus me-1 d-none d-lg-inline"></i>Register
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Search Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel">Search Properties</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="properties" method="GET" class="search-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Property Type</label>
                                    <select name="type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="apartment">Apartment</option>
                                        <option value="villa">Villa</option>
                                        <option value="plot">Plot</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" placeholder="Enter city or area">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Min Price</label>
                                    <input type="number" name="min_price" class="form-control" placeholder="Min Price">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Price</label>
                                    <input type="number" name="max_price" class="form-control" placeholder="Max Price">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bedrooms</label>
                                    <select name="bedrooms" class="form-select">
                                        <option value="">Any</option>
                                        <option value="1">1+</option>
                                        <option value="2">2+</option>
                                        <option value="3">3+</option>
                                        <option value="4">4+</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Search Properties
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Top Button -->
        <button id="backToTop" class="btn btn-primary back-to-top" title="Back to Top">
            <i class="fas fa-chevron-up"></i>
        </button>
        <?php
    }

    /**
     * Get main content style
     */
    private function getMainContentStyle() {
        $margin_top = $this->show_navigation ? '120px' : '0';
        return "margin-top: $margin_top; min-height: calc(100vh - $margin_top);";
    }

    /**
     * Output footer
     */
    private function outputFooter() {
        ?>
        <footer class="bg-dark text-white">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <h5>APS Dream Home</h5>
                        <p>Your trusted partner in finding the perfect property. We offer comprehensive real estate solutions.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-4">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="/" class="text-white-50">Home</a></li>
                            <li class="mb-2"><a href="properties" class="text-white-50">Properties</a></li>
                            <li class="mb-2"><a href="about" class="text-white-50">About Us</a></li>
                            <li class="mb-2"><a href="contact" class="text-white-50">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5>Contact Info</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Property Street, Gorakhpur</li>
                            <li class="mb-2"><i class="fas fa-phone-alt me-2"></i> +91 98765 43210</li>
                            <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@apsdreamhome.com</li>
                            <li><i class="fas fa-clock me-2"></i> Mon-Sat: 9:00 AM - 8:00 PM</li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <h5>Newsletter</h5>
                        <p>Subscribe for latest property updates</p>
                        <form class="mb-3">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="my-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="privacy" class="text-white-50 me-3">Privacy Policy</a>
                        <a href="terms" class="text-white-50">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
        <?php
    }

    /**
     * Output JavaScript
     */
    private function outputScripts() {
        ?>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                // Auto-hide alerts
                setTimeout(() => {
                    $('.alert').fadeOut();
                }, 5000);

                // Enhanced dropdown functionality
                $('.dropdown-toggle').on('click', function(e) {
                    e.preventDefault();
                    const dropdown = $(this).closest('.dropdown');
                    const menu = dropdown.find('.dropdown-menu');

                    // Close other dropdowns
                    $('.dropdown').not(dropdown).removeClass('show').find('.dropdown-menu').removeClass('show');

                    // Toggle current dropdown
                    dropdown.toggleClass('show');
                    menu.toggleClass('show');

                    // Prevent event bubbling
                    return false;
                });

                // Close dropdowns when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.dropdown').length) {
                        $('.dropdown').removeClass('show').find('.dropdown-menu').removeClass('show');
                    }
                });

                // Smooth scroll for anchor links
                $('a[href^="#"]').on('click', function(e) {
                    e.preventDefault();
                    const target = $($(this).attr('href'));
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: target.offset().top - 80
                        }, 800);
                    }
                });

                // Enhanced mobile menu functionality
                $('.navbar-toggler').on('click', function() {
                    const navbarCollapse = $('.navbar-collapse');
                    const isOpen = navbarCollapse.hasClass('show');

                    if (isOpen) {
                        navbarCollapse.removeClass('show');
                        $(this).removeClass('active');
                    } else {
                        navbarCollapse.addClass('show');
                        $(this).addClass('active');
                    }

                });

                // Close mobile menu when clicking nav links
                $('.navbar-nav .nav-link').on('click', function() {
                    if ($(window).width() < 992) {
                        $('.navbar-collapse').removeClass('show');
                        $('.navbar-toggler').removeClass('active');
                    }
                });

                // Back to top button functionality
                $(window).scroll(function() {
                    if ($(this).scrollTop() > 300) {
                        $('#backToTop').addClass('show');
                    } else {
                        $('#backToTop').removeClass('show');
                    }

                    // Navbar scroll effect
                    if ($(this).scrollTop() > 50) {
                        $('.main-navbar').addClass('scrolled');
                    } else {
                        $('.main-navbar').removeClass('scrolled');
                    }
                });

                $('#backToTop').on('click', function(e) {
                    e.preventDefault();
                    $('html, body').animate({scrollTop: 0}, 800);
                });
            });

            // Custom JavaScript
            <?php echo $this->custom_js; ?>
        </script>
        <?php
    }

    /**
     * Darken color utility
     */
    private function darkenColor($hex, $percent) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}

/**
 * Global Template Instance
 */
global $template;
$template = new UniversalTemplate();

/**
 * Convenience Functions
 */
function template($theme = 'default') {
    global $template;
    return $template->setTheme($theme);
}

function page($content, $title = 'APS Dream Home', $theme = 'default') {
    global $template;
    $template->setTheme($theme)->setTitle($title)->render($content);
}

function dashboard_page($content, $title = 'Dashboard') {
    page($content, $title, 'dashboard');
}

function login_page($content, $title = 'Login') {
    global $template;
    $template->setTheme('login')->hideNavigation()->setTitle($title)->render($content);
}

function admin_page($content, $title = 'Admin Panel') {
    page($content, $title, 'admin');
}
?>
