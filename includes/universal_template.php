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
    public function renderHeader() {
        $this->outputHeader();
    }

    /**
     * Render only footer
     */
    public function renderFooter() {
        $this->outputFooter();
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
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-color) !important;
                font-size: 1.5rem;
            }

            .nav-link {
                color: #5a5c69 !important;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .nav-link:hover, .nav-link.active {
                color: var(--primary-color) !important;
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
        ?>
        <nav class="navbar navbar-expand-lg navbar-light fixed-top">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-home me-2"></i>APS Dream Home
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'properties.php') ? 'active' : ''; ?>" href="properties">Properties</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>" href="about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>" href="contact">Contact</a>
                        </li>
                        <?php if ($is_logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'customer_dashboard.php') ? 'active' : ''; ?>" href="customer_dashboard">Dashboard</a>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex align-items-center">
                        <?php if ($is_logged_in): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($user_name); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                                    <li><a class="dropdown-item" href="customer_dashboard"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="customer_login" class="btn btn-outline-primary me-2">Login</a>
                            <a href="customer_registration" class="btn btn-primary">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
        <?php
    }

    /**
     * Get main content style
     */
    private function getMainContentStyle() {
        $margin_top = $this->show_navigation ? '80px' : '0';
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

                // Add active class to current nav item
                $('.navbar-nav .nav-link').filter(function() {
                    return $(this).attr('href') === window.location.pathname.split('/').pop();
                }).addClass('active');

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

                // Form validation
                $('form.needs-validation').on('submit', function(e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    $(this).addClass('was-validated');
                });

                // Back to top button
                $(window).scroll(function() {
                    if ($(this).scrollTop() > 300) {
                        $('#backToTop').fadeIn();
                    } else {
                        $('#backToTop').fadeOut();
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
