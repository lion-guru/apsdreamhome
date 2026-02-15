<?php
/**
 * APS Dream Home - Dynamic Template Integration
 * Example page showing how to use dynamic templates
 */

require_once 'includes/config.php';

// Ensure BASE_URL is available
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Include dynamic template classes
require_once 'templates/dynamic_header.php';
require_once 'templates/dynamic_footer.php';

// Initialize database connection
$conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;

// Page content
$pageTitle = 'Dynamic Template Demo - APS Dream Home';
$pageDescription = 'Demonstration of dynamic header and footer system';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($pageDescription) ?>">
    
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        .demo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin: 40px 0;
            border-radius: 10px;
        }
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .code-block {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php
// Render dynamic header
if ($conn) {
    $header = new DynamicHeader($conn, 'main');
    $header->render();
} else {
    echo "<div class='alert alert-warning'>Database connection not available. Using fallback header.</div>";
}
?>

<!-- Main Content -->
<main class="container">
    
    <!-- Hero Section -->
    <section class="demo-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">
                <i class="fas fa-rocket me-3"></i>Dynamic Template System
            </h1>
            <p class="lead mb-4">
                Experience the power of fully dynamic header and footer management
            </p>
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-cog fa-3x"></i>
                    </div>
                    <h4>100% Dynamic</h4>
                    <p>All header/footer content managed through database</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-palette fa-3x"></i>
                    </div>
                    <h4>Customizable</h4>
                    <p>Colors, logos, menus, and styles fully editable</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-bolt fa-3x"></i>
                    </div>
                    <h4>Real-time Updates</h4>
                    <p>Changes appear instantly across all pages</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <h2 class="text-center mb-5">Key Features</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-database me-2 text-primary"></i>Database-Driven</h4>
                    <p>All header and footer content stored in dynamic database tables for easy management and scalability.</p>
                    <div class="code-block">
                        <strong>Tables Created:</strong>
                        <ul class="mb-0">
                            <li>dynamic_headers</li>
                            <li>dynamic_footers</li>
                            <li>site_content</li>
                            <li>media_library</li>
                            <li>page_templates</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-paint-brush me-2 text-success"></i>Visual Editor</h4>
                    <p>Admin interface with live preview for managing header/footer content, colors, and styles.</p>
                    <div class="code-block">
                        <strong>Admin Features:</strong>
                        <ul class="mb-0">
                            <li>Live preview mode</li>
                            <li>Color picker</li>
                            <li>Menu management</li>
                            <li>Custom CSS/JS injection</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-mobile-alt me-2 text-warning"></i>Responsive Design</h4>
                    <p>Automatically adapts to different screen sizes and devices with Bootstrap 5 integration.</p>
                    <div class="code-block">
                        <strong>Responsive Features:</strong>
                        <ul class="mb-0">
                            <li>Mobile-first approach</li>
                            <li>Collapsible navigation</li>
                            <li>Adaptive layouts</li>
                            <li>Touch-friendly interface</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="feature-card">
                    <h4><i class="fas fa-shield-alt me-2 text-danger"></i>Secure & Fast</h4>
                    <p>Built with security best practices and optimized for performance with intelligent caching.</p>
                    <div class="code-block">
                        <strong>Performance Features:</strong>
                        <ul class="mb-0">
                            <li>Database optimization</li>
                            <li>Caching system</li>
                            <li>CSRF protection</li>
                            <li>SQL injection prevention</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Implementation Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Implementation Guide</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="fas fa-code me-2"></i>Step 1: Database Setup</h4>
                    <p>Run the database setup script to create required tables:</p>
                    <div class="code-block">
                        <code>php tools/setup_dynamic_database.php</code>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4><i class="fas fa-cog me-2"></i>Step 2: Template Integration</h4>
                    <p>Include dynamic templates in your pages:</p>
                    <div class="code-block">
                        <code>
                            require_once 'templates/dynamic_header.php';<br>
                            $header = new DynamicHeader($conn, 'main');<br>
                            $header->render();
                        </code>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4><i class="fas fa-user-shield me-2"></i>Step 3: Admin Access</h4>
                    <p>Access the dynamic content manager:</p>
                    <div class="code-block">
                        <code>/admin/dynamic_content_manager.php</code>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4><i class="fas fa-eye me-2"></i>Step 4: Live Preview</h4>
                    <p>See changes in real-time with preview mode:</p>
                    <div class="code-block">
                        <code>Preview tab in admin panel</code>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5">
        <div class="container text-center">
            <h2 class="mb-5">System Statistics</h2>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="feature-card text-center">
                        <h3 class="text-primary">5</h3>
                        <p class="mb-0">Database Tables</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center">
                        <h3 class="text-success">100%</h3>
                        <p class="mb-0">Dynamic Content</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center">
                        <h3 class="text-warning">0</h3>
                        <p class="mb-0">Hard-coded Elements</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center">
                        <h3 class="text-danger">∞</h3>
                        <p class="mb-0">Customization Options</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
// Render dynamic footer
if ($conn) {
    $footer = new DynamicFooter($conn, 'main');
    $footer->render();
} else {
    echo "<div class='alert alert-warning'>Database connection not available. Using fallback footer.</div>";
}
?>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
