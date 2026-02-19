<?php
// DEPRECATED: This file is an unused template variation
// Modern header implementation not referenced anywhere in codebase
// Use includes/templates/header.php or includes/universal_template.php instead
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Your Trusted Real Estate Partner</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>public/css/header.css">

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
    </style>
</head>
<body>
    <!-- Loading Elements -->
    <div class="loading-bar" id="loadingBar"></div>
    <div class="page-transition" id="pageTransition"></div>

    <?php
    /**
     * Modern Header - APS Dream Home
     * Clean and responsive header with enhanced functionality
     */
    ?>

    <header class="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg p-0">
                <!-- Logo -->
                <a class="navbar-brand logo me-5" href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">
                    <i class="fas fa-home"></i>
                    <span>APS Dream Home</span>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Desktop Menu -->
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''); ?>"
                               href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>">
                                <i class="fas fa-home d-lg-none me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'properties') !== false ? 'active' : ''); ?>"
                               href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>properties">
                                <i class="fas fa-building d-lg-none me-2"></i>Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'about') !== false ? 'active' : ''); ?>"
                               href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>about">
                                <i class="fas fa-info-circle d-lg-none me-2"></i>About Us
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'contact') !== false ? 'active' : ''); ?>"
                               href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>contact">
                                <i class="fas fa-envelope d-lg-none me-2"></i>Contact
                            </a>
                        </li>
                    </ul>

                    <!-- Auth Buttons -->
                    <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>register" class="btn btn-header-cta">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Overlay -->
                <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

                <!-- Mobile Menu -->
                <div class="mobile-menu" id="mobileMenu">
                    <div class="mobile-menu-header">
                        <div class="logo">
                            <i class="fas fa-home"></i>
                            <span>APS Dream Home</span>
                        </div>
                        <button class="mobile-menu-close" id="mobileMenuClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <nav class="mobile-nav">
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>" class="mobile-nav-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>properties" class="mobile-nav-link">
                            <i class="fas fa-building"></i> Properties
                        </a>
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>about" class="mobile-nav-link">
                            <i class="fas fa-info-circle"></i> About Us
                        </a>
                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>contact" class="mobile-nav-link">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                        <div class="mobile-menu-cta">
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>login" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/'); ?>register" class="btn btn-header-cta w-100">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </div>
                    </nav>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const menuToggle = document.getElementById('mobileMenuToggle');
                        const mobileMenu = document.getElementById('mobileMenu');
                        const mobileMenuClose = document.getElementById('mobileMenuClose');
                        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
                        const body = document.body;
                        const header = document.querySelector('.header');
                        let lastScroll = 0;

                        function openMenu() {
                            mobileMenu.classList.add('active');
                            mobileMenuOverlay.classList.add('active');
                            body.style.overflow = 'hidden';
                        }

                        function closeMenu() {
                            mobileMenu.classList.remove('active');
                            mobileMenuOverlay.classList.remove('active');
                            body.style.overflow = '';
                        }

                        // Header scroll effect
                        window.addEventListener('scroll', function() {
                            const currentScroll = window.pageYOffset;

                            if (currentScroll <= 0) {
                                header.classList.remove('scrolled');
                                return;
                            }

                            if (currentScroll > lastScroll && currentScroll > 100) {
                                // Scrolling down
                                header.style.transform = 'translateY(-100%)';
                            } else {
                                // Scrolling up
                                header.style.transform = 'translateY(0)';
                                if (currentScroll > 50) {
                                    header.classList.add('scrolled');
                                } else {
                                    header.classList.remove('scrolled');
                                }
                            }

                            lastScroll = currentScroll;
                        });

                        // Event listeners
                        if (menuToggle) menuToggle.addEventListener('click', openMenu);
                        if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMenu);
                        if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMenu);

                        // Close menu when clicking on a mobile nav link
                        document.querySelectorAll('.mobile-nav-link').forEach(link => {
                            link.addEventListener('click', closeMenu);
                        });

                        // Close menu when window is resized to desktop
                        function handleResize() {
                            if (window.innerWidth >= 992) {
                                closeMenu();
                            }
                        }

                        window.addEventListener('resize', handleResize);
                    });
                </script>
            </nav>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div style="height: 80px;"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingBar = document.getElementById('loadingBar');
            const pageTransition = document.getElementById('pageTransition');
            
            // Handle page transitions
            document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Don't intercept anchor links or external links
                    if (href.startsWith('#') || 
                        href.startsWith('http') || 
                        href.startsWith('mailto:') || 
                        href.startsWith('tel:')) {
                        return;
                    }

                    e.preventDefault();
                    
                    // Show loading bar and transition
                    if (loadingBar) {
                        loadingBar.style.display = 'block';
                        loadingBar.classList.add('active');
                    }
                    if (pageTransition) {
                        pageTransition.classList.add('active');
                    }
                    
                    // Navigate after a short delay for the animation
                    setTimeout(() => {
                        window.location.href = href;
                    }, 500);
                });
            });

            // Hide loading when page is fully loaded
            window.addEventListener('load', function() {
                if (loadingBar) {
                    loadingBar.classList.remove('active');
                    // Hide after the fade out animation completes
                    setTimeout(() => {
                        loadingBar.style.display = 'none';
                        if (pageTransition) {
                            pageTransition.classList.remove('active');
                        }
                    }, 300);
                }
            });
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
