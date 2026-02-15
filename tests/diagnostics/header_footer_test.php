<?php
/**
 * APS Dream Home - Header/Footer Test Page
 * This is a simple test page to verify the new unified header and footer work correctly
 */

// Define BASE_URL for testing
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Start session for testing
session_start();

// Set a test flash message
$_SESSION['flash_messages'] = [
    [
        'type' => 'success',
        'text' => 'Header and Footer have been successfully unified!'
    ]
];

// Include the new header
include __DIR__ . '/app/views/layouts/header_unified.php';
?>

<!-- Test Content -->
<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-4">🎉 Header & Footer Test</h1>
            <p class="lead mb-4">This page tests the new unified header and footer components.</p>

            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Header Fixed</h5>
                            <p class="card-text">The malformed header_unified.php has been cleaned and restructured with proper PHP code and HTML structure.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Footer Updated</h5>
                            <p class="card-text">The footer_unified.php now properly closes the HTML structure opened by the header, ensuring valid markup.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="card-title">CSS Enhanced</h5>
                            <p class="card-text">Complete CSS styles have been added for responsive design, animations, and modern UI components.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <h3>Test Features:</h3>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>✅ Navigation Menu</h5>
                        <ul class="list-unstyled">
                            <li>• Active page highlighting</li>
                            <li>• Responsive mobile menu</li>
                            <li>• Smooth hover effects</li>
                        </ul>

                        <h5 class="mt-4">✅ User Authentication</h5>
                        <ul class="list-unstyled">
                            <li>• Login/Register buttons for guests</li>
                            <li>• User dropdown for authenticated users</li>
                            <li>• Role-based navigation (Employee/Customer/Associate)</li>
                        </ul>
                    </div>

                    <div class="col-md-6">
                        <h5>✅ Footer Components</h5>
                        <ul class="list-unstyled">
                            <li>• Company information</li>
                            <li>• Quick navigation links</li>
                            <li>• Contact information</li>
                            <li>• Newsletter subscription</li>
                            <li>• Social media links</li>
                        </ul>

                        <h5 class="mt-4">✅ Interactive Elements</h5>
                        <ul class="list-unstyled">
                            <li>• Back to top button</li>
                            <li>• Mobile menu toggle</li>
                            <li>• Flash message alerts</li>
                            <li>• Form validations</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
                <a href="<?php echo BASE_URL; ?>properties.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-building me-2"></i>View Properties
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include __DIR__ . '/app/views/layouts/footer_unified.php';
?>
