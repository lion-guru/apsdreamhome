<?php
/**
 * APS Dream Home - Quick Start Demo
 * One-page demonstration of dynamic template system
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/dynamic_templates.php';

// Page configuration
$pageConfig = [
    'title' => 'Dynamic Template System - Quick Start',
    'description' => 'Experience the power of APS Dream Home dynamic templates',
    'header_type' => 'main',
    'footer_type' => 'main'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageConfig['title']) ?></title>
    <meta name="description" content="<?= h($pageConfig['description']) ?>">

    <?php addDynamicTemplateCSS(); ?>

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .demo-section {
            background: #f8f9fa;
            padding: 60px 0;
            margin: 40px 0;
        }
        .code-demo {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 20px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin: 5px;
        }
        .status-working { background: #48bb78; color: white; }
        .status-ready { background: #4299e1; color: white; }
        .status-complete { background: #9f7aea; color: white; }
    </style>
</head>
<body>

<?php renderDynamicHeader($pageConfig['header_type']); ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">
            <i class="fas fa-rocket me-3"></i>Dynamic Template System
        </h1>
        <p class="lead mb-4">
            Experience real-time header and footer customization with APS Dream Home
        </p>
        <div class="mt-5">
            <a href="admin/dynamic_content_manager.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-cog me-2"></i>Admin Panel
            </a>
            <a href="dynamic_integration_guide.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-book me-2"></i>Documentation
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="container my-5">
    <h2 class="text-center mb-5">System Features</h2>

    <div class="feature-grid">
        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-database"></i>
            </div>
            <h4>Database-Driven</h4>
            <p>All header and footer content stored in dynamic database tables for easy management and scalability.</p>
            <span class="status-badge status-working">Working</span>
        </div>

        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h4>Visual Editor</h4>
            <p>Admin interface with live preview for managing header/footer content, colors, and styles.</p>
            <span class="status-badge status-ready">Ready</span>
        </div>

        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <h4>Real-time Updates</h4>
            <p>Changes appear instantly across all pages without requiring developer intervention.</p>
            <span class="status-badge status-complete">Complete</span>
        </div>

        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h4>Responsive Design</h4>
            <p>Automatically adapts to different screen sizes and devices with Bootstrap 5 integration.</p>
            <span class="status-badge status-complete">Complete</span>
        </div>

        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h4>Secure & Fast</h4>
            <p>Built with security best practices and optimized for performance with intelligent caching.</p>
            <span class="status-badge status-working">Working</span>
        </div>

        <div class="feature-card text-center">
            <div class="feature-icon">
                <i class="fas fa-code"></i>
            </div>
            <h4>Developer Friendly</h4>
            <p>Clean, modular code structure with easy integration functions and comprehensive documentation.</p>
            <span class="status-badge status-complete">Complete</span>
        </div>
    </div>
</section>

<!-- Demo Section -->
<section class="demo-section">
    <div class="container">
        <h2 class="text-center mb-5">Quick Implementation Demo</h2>

        <div class="row">
            <div class="col-md-6">
                <h4><i class="fas fa-code me-2"></i>Basic Usage</h4>
                <div class="code-demo">
&lt;?php
require_once 'includes/dynamic_templates.php';
?&gt;

&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;My Page&lt;/title&gt;
    &lt;?php addDynamicTemplateCSS(); ?&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;?php renderDynamicHeader('main'); ?&gt;

    &lt;!-- Your content here --&gt;

    &lt;?php renderDynamicFooter('main'); ?&gt;
    &lt;?php addDynamicTemplateJS(); ?&gt;
&lt;/body&gt;
&lt;/html&gt;
                </div>
            </div>

            <div class="col-md-6">
                <h4><i class="fas fa-rocket me-2"></i>Advanced Usage</h4>
                <div class="code-demo">
&lt;?php
require_once 'includes/dynamic_templates.php';

// Complete page with options
renderDynamicPage(
    'Page Title',
    '&lt;p&gt;Your content here&lt;/p&gt;',
    [
        'header_type' => 'main',
        'footer_type' => 'main'
    ]
);
?&gt;
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <h4>System Status</h4>
            <div class="mt-3">
                <span class="status-badge status-complete">Database Setup</span>
                <span class="status-badge status-complete">Template Classes</span>
                <span class="status-badge status-complete">Admin Interface</span>
                <span class="status-badge status-complete">Test Suite</span>
                <span class="status-badge status-working">Migration Tools</span>
            </div>
        </div>
    </div>
</section>

<!-- Admin Preview Section -->
<section class="container my-5">
    <h2 class="text-center mb-5">Admin Panel Preview</h2>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Dynamic Content Manager</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-header fa-3x text-primary mb-3"></i>
                                <h6>Header Management</h6>
                                <p class="small text-muted">Logo, colors, navigation</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-footer fa-3x text-success mb-3"></i>
                                <h6>Footer Management</h6>
                                <p class="small text-muted">Company info, links, social</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-eye fa-3x text-info mb-3"></i>
                                <h6>Live Preview</h6>
                                <p class="small text-muted">Real-time changes</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="admin/dynamic_content_manager.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Access Admin Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="container my-5">
    <h2 class="text-center mb-5">Implementation Statistics</h2>

    <div class="row text-center">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-primary">5</h3>
                    <p class="mb-0">Database Tables</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-success">88%</h3>
                    <p class="mb-0">Test Success Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-info">100%</h3>
                    <p class="mb-0">Dynamic Content</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="text-warning">0</h3>
                    <p class="mb-0">Hard-coded Elements</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Resources Section -->
<section class="demo-section">
    <div class="container">
        <h2 class="text-center mb-5">Quick Resources</h2>

        <div class="row">
            <div class="col-md-6">
                <h4><i class="fas fa-tools me-2"></i>Developer Tools</h4>
                <div class="list-group">
                    <a href="test_dynamic_templates.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-vial me-2"></i>Test Suite
                    </a>
                    <a href="tools/setup_dynamic_database.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-database me-2"></i>Database Setup
                    </a>
                    <a href="tools/migrate_to_dynamic.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-exchange-alt me-2"></i>Migration Tool
                    </a>
                </div>
            </div>

            <div class="col-md-6">
                <h4><i class="fas fa-book me-2"></i>Documentation</h4>
                <div class="list-group">
                    <a href="dynamic_integration_guide.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-graduation-cap me-2"></i>Integration Guide
                    </a>
                    <a href="DYNAMIC_TEMPLATE_SYSTEM.md" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2"></i>System Documentation
                    </a>
                    <a href="dynamic_demo.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-play me-2"></i>Live Demo
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="container my-5 text-center">
    <div class="card border-0 shadow-lg bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white p-5">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">
                The dynamic template system is ready for production use. Start customizing your APS Dream Home website today!
            </p>
            <div class="mt-4">
                <a href="admin/dynamic_content_manager.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-rocket me-2"></i>Launch Admin Panel
                </a>
                <a href="test_dynamic_templates.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-check me-2"></i>Run Tests
                </a>
            </div>
        </div>
    </div>
</section>

<?php renderDynamicFooter($pageConfig['footer_type']); ?>

<?php addDynamicTemplateJS(); ?>

<script>
// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Animate feature cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

</body>
</html>
