<?php
/**
 * Simple Development Template System
 * Easy-to-use templates for development phase
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

/**
 * Simple Header Template
 */
function simple_header($page_title = 'APS Dream Home', $show_nav = true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?></title>

        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <style>
            :root {
                --primary-color: #4e73df;
                --secondary-color: #1cc88a;
                --dark-color: #5a5c69;
                --light-color: #f8f9fc;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fc;
                color: #333;
            }

            .navbar {
                background: #fff !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-color) !important;
            }

            .nav-link {
                color: #5a5c69 !important;
                font-weight: 500;
            }

            .nav-link:hover {
                color: var(--primary-color) !important;
            }

            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }

            .btn-primary:hover {
                background-color: #2e59d9;
                border-color: #2653d4;
            }

            .card {
                border: none;
                border-radius: 0.5rem;
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
                margin-bottom: 1.5rem;
            }

            .hero-section {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 4rem 0;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php if ($show_nav): ?>
        <!-- Navigation -->
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
                        <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'customer_dashboard.php') ? 'active' : ''; ?>" href="customer_dashboard">Dashboard</a>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex align-items-center">
                        <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
                            <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'User'); ?>!</span>
                            <a href="logout.php" class="btn btn-outline-primary btn-sm">Logout</a>
                        <?php else: ?>
                            <a href="customer_login.php" class="btn btn-outline-primary me-2">Login</a>
                            <a href="customer_registration.php" class="btn btn-primary">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="main-content" style="<?php echo $show_nav ? 'margin-top: 80px;' : ''; ?>">
    <?php
}

/**
 * Simple Footer Template
 */
function simple_footer($show_footer = true) {
    ?>
        </main>

        <?php if ($show_footer): ?>
        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>APS Dream Home</h5>
                        <p>Your trusted partner in finding the perfect property.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
                        <p class="mb-0">
                            <a href="privacy.php" class="text-white-50 me-3">Privacy Policy</a>
                            <a href="terms.php" class="text-white-50">Terms of Service</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        <?php endif; ?>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // Simple development helpers
            $(document).ready(function() {
                // Add active class to current nav item
                $('.navbar-nav .nav-link').filter(function() {
                    return $(this).attr('href') === window.location.pathname.split('/').pop();
                }).addClass('active');

                // Simple form validation
                $('form').on('submit', function(e) {
                    if ($(this).hasClass('needs-validation')) {
                        if (!this.checkValidity()) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        $(this).addClass('was-validated');
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php
}

/**
 * Simple Page Template
 */
function simple_page($content, $page_title = 'APS Dream Home', $show_nav = true, $show_footer = true) {
    simple_header($page_title, $show_nav);
    echo $content;
    simple_footer($show_footer);
}

/**
 * Alert Messages
 */
function simple_alert($message, $type = 'info') {
    $types = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];

    $class = $types[$type] ?? 'alert-info';

    return "<div class='alert $class alert-dismissible fade show' role='alert'>
        $message
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

/**
 * Simple Card Component
 */
function simple_card($title, $content, $class = '') {
    return "<div class='card $class'>
        <div class='card-body'>
            <h5 class='card-title'>$title</h5>
            <div class='card-text'>$content</div>
        </div>
    </div>";
}

/**
 * Simple Button Component
 */
function simple_button($text, $url = '#', $class = 'btn-primary', $icon = '') {
    $icon_html = $icon ? "<i class='fas fa-$icon me-2'></i>" : '';
    return "<a href='$url' class='btn $class'>$icon_html$text</a>";
}

/**
 * Development Helper - Show Session Data
 */
function debug_session() {
    if (isset($_GET['debug']) && $_GET['debug'] === 'session') {
        echo "<div class='alert alert-warning'>";
        echo "<h6>Session Debug Info:</h6>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        echo "</div>";
    }
}

/**
 * Development Helper - Show POST Data
 */
function debug_post() {
    if (isset($_GET['debug']) && $_GET['debug'] === 'post') {
        echo "<div class='alert alert-info'>";
        echo "<h6>POST Debug Info:</h6>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        echo "</div>";
    }
}

/**
 * Development Helper - Show SQL Queries
 */
function debug_query($query, $params = []) {
    if (isset($_GET['debug']) && $_GET['debug'] === 'sql') {
        echo "<div class='alert alert-secondary'>";
        echo "<h6>SQL Debug Info:</h6>";
        echo "<p><strong>Query:</strong> $query</p>";
        if (!empty($params)) {
            echo "<p><strong>Parameters:</strong> " . implode(', ', $params) . "</p>";
        }
        echo "</div>";
    }
}
?>
