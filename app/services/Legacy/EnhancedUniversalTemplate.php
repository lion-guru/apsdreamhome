<?php

namespace App\Services\Legacy;
/**
 * EnhancedUniversalTemplate
 * A unified template system for APS Dream Home SaaS Platform.
 */

class EnhancedUniversalTemplate {
    protected $title = 'APS Dream Home';
    protected $additional_css = [];
    protected $additional_js = [];
    protected $user = null;

    public function __construct() {
        // require_once __DIR__ . '/session_helpers.php';
        if (function_exists('ensureSessionStarted')) {
            ensureSessionStarted();
        } elseif (!isset($_SESSION)) {
            session_start();
        }
        $this->user = $_SESSION['user'] ?? null;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function addCss($path) {
        $this->additional_css[] = $path;
        return $this;
    }

    public function addJs($path) {
        $this->additional_js[] = $path;
        return $this;
    }

    public function renderHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php if (function_exists('getCsrfToken')): ?>
                <meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
            <?php endif; ?>
            <title><?php echo h($this->title); ?></title>

            <!-- Core Assets -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

            <!-- Local Design System & Styles -->
            <?php if (defined('ASSETS_URL')): ?>
                <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/modern-design-system.css">
                <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
                <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/property-cards.css">
            <?php else: ?>
                <link rel="stylesheet" href="/apsdreamhome/public/assets/css/modern-design-system.css">
                <link rel="stylesheet" href="/apsdreamhome/public/assets/css/style.css">
                <link rel="stylesheet" href="/apsdreamhome/public/assets/css/property-cards.css">
            <?php endif; ?>

        <style>
            :root {
                --primary-color: #2563eb;
                --primary-hover: #1d4ed8;
                --secondary-color: #64748b;
                --dark-color: #0f172a;
                --light-bg: #f8fafc;
                --white: #ffffff;
                --glass-bg: rgba(255, 255, 255, 0.85);
                --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                --shadow-premium: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 18px -2px rgba(0, 0, 0, 0.05);
            }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: var(--light-bg);
                color: var(--dark-color);
                line-height: 1.6;
            }

            .navbar {
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                background: var(--glass-bg);
                padding: 1rem 0;
                transition: var(--transition);
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }

            .navbar-brand {
                font-size: 1.5rem;
                letter-spacing: -0.5px;
            }

            .nav-link {
                font-weight: 500;
                color: var(--dark-color) !important;
                padding: 0.5rem 1.2rem !important;
                transition: var(--transition);
            }

            .nav-link:hover {
                color: var(--primary-color) !important;
                transform: translateY(-1px);
            }

            .btn-premium {
                background: var(--primary-color);
                color: white;
                border: none;
                padding: 0.8rem 2rem;
                border-radius: 50px;
                font-weight: 600;
                transition: var(--transition);
                box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
            }

            .btn-premium:hover {
                background: var(--primary-hover);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.23);
                color: white;
            }

            .btn-outline-premium {
                border: 2px solid var(--primary-color);
                color: var(--primary-color);
                padding: 0.7rem 1.8rem;
                border-radius: 50px;
                font-weight: 600;
                transition: var(--transition);
            }

            .btn-outline-premium:hover {
                background: var(--primary-color);
                color: white;
                transform: translateY(-2px);
            }

            footer {
                background: var(--dark-color);
                color: #94a3b8;
                padding: 80px 0 40px;
            }

            footer h4, footer h6 {
                color: white;
                letter-spacing: 0.5px;
            }

            footer a {
                color: #94a3b8;
                text-decoration: none;
                transition: var(--transition);
                display: inline-block;
            }

            footer a:hover {
                color: white;
                transform: translateX(5px);
            }

            .social-links a {
                width: 40px;
                height: 40px;
                background: rgba(255,255,255,0.05);
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-right: 10px;
            }

            .social-links a:hover {
                background: var(--primary-color);
                transform: translateY(-3px);
            }

            /* Back to Top */
            .back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                background: var(--primary-color);
                color: #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                z-index: 1000;
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }

            .back-to-top.visible {
                opacity: 1;
                visibility: visible;
            }

            .back-to-top:hover {
                background: var(--primary-hover);
                color: #fff;
                transform: translateY(-3px);
            }

            /* Responsive Design */
            @media (max-width: 991.98px) {
                footer {
                    text-align: center;
                }

                .social-links {
                    justify-content: center;
                    margin-bottom: 20px;
                }

                .footer-links,
                .contact-info {
                    margin-bottom: 30px;
                }
            }

            @media (max-width: 767.98px) {
                footer {
                    padding: 60px 0 20px;
                }
            }
        </style>

        <?php foreach ($this->additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg sticky-top navbar-light">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>">
                    <i class="fas fa-home me-2"></i>APS Dream Home
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'properties'; ?>">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'projects'; ?>">Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'services'; ?>">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'news'; ?>">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'about'; ?>">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="<?php echo BASE_URL . 'contact'; ?>">Contact</a>
                        </li>
                        <?php if ($this->user): ?>
                            <li class="nav-item dropdown ms-lg-3">
                                <a class="btn btn-premium px-4 dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i>Account
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" aria-labelledby="userDropdown" style="border-radius: 15px; margin-top: 15px;">
                                    <li><a class="dropdown-item py-2 px-4" href="<?php echo BASE_URL . 'dashboard'; ?>"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item py-2 px-4" href="<?php echo BASE_URL . 'profile'; ?>"><i class="fas fa-user-edit me-2 text-info"></i>Profile Settings</a></li>
                                    <li><hr class="dropdown-divider mx-3"></li>
                                    <li><a class="dropdown-item py-2 px-4 text-danger" href="<?php echo BASE_URL . 'logout'; ?>"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item ms-lg-3">
                                <a class="btn btn-outline-premium px-4 me-2" href="<?php echo BASE_URL . 'login'; ?>">Login</a>
                                <a class="btn btn-premium px-4" href="<?php echo BASE_URL . 'register'; ?>">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <?php
    }

    public function renderFooter() {
        ?>
            <!-- Footer -->
            <footer class="mt-5">
                <div class="container">
                    <div class="row g-4 mb-5">
                        <div class="col-lg-4">
                            <h4 class="fw-bold text-white mb-4">APS Dream Home</h4>
                            <p class="text-muted">Your trusted partner in finding the perfect property in Gorakhpur and beyond. We provide premium real estate solutions tailored to your needs.</p>
                            <div class="d-flex gap-3 mt-4">
                                <a href="#" class="fs-5"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="fs-5"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="fs-5"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="fs-5"><i class="fab fa-linkedin"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <h6 class="fw-bold text-white mb-4">Quick Links</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'properties'; ?>">Properties</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'projects'; ?>">Projects</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'services'; ?>">Services</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'news'; ?>">Blog</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'about'; ?>">About Us</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'contact'; ?>">Contact</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <h6 class="fw-bold text-white mb-4">Support</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'privacy-policy'; ?>">Privacy Policy</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'terms'; ?>">Terms of Service</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'faq'; ?>">FAQ</a></li>
                                <li class="mb-2"><a href="<?php echo BASE_URL . 'help'; ?>">Help Center</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <h6 class="fw-bold text-white mb-4">Contact Info</h6>
                            <ul class="list-unstyled text-muted">
                                <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> 123 Main Street, Gorakhpur, UP</li>
                                <li class="mb-3"><i class="fas fa-phone me-2"></i> +91 123 456 7890</li>
                                <li class="mb-3"><i class="fas fa-envelope me-2"></i> info@apsdreamhome.com</li>
                            </ul>
                        </div>
                    </div>
                    <hr class="bg-secondary">
                    <div class="text-center pt-3">
                        <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                    </div>
                </div>
            </footer>

            <!-- Back to Top -->
            <a href="#" class="back-to-top" id="backToTop">
                <i class="fas fa-arrow-up"></i>
            </a>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <!-- Local Scripts -->
            <?php if (defined('ASSETS_URL')): ?>
                <script src="<?php echo ASSETS_URL; ?>js/script.js"></script>
            <?php else: ?>
                <script src="/apsdreamhome/public/assets/js/script.js"></script>
            <?php endif; ?>

            <script>
                // Global AJAX setup for CSRF
                $(function() {
                    const token = $('meta[name="csrf-token"]').attr('content');
                    if (token) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-Token': token
                            }
                        });
                    }
                });

                // Back to Top Button Logic
                $(window).scroll(function() {
                    if ($(this).scrollTop() > 300) {
                        $('#backToTop').addClass('visible');
                    } else {
                        $('#backToTop').removeClass('visible');
                    }
                });

                $('#backToTop').click(function(e) {
                    e.preventDefault();
                    $('html, body').animate({scrollTop: 0}, 600);
                });
            </script>
            <?php foreach ($this->additional_js as $js): ?>
                <script src="<?php echo $js; ?>"></script>
            <?php endforeach; ?>
        </body>
        </html>
        <?php
    }
}
