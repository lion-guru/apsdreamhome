<?php
/**
 * APS Dream Home - Dynamic Template Integration Guide
 * Complete implementation and usage documentation
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Template Integration Guide - APS Dream Home</title>
    
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        .guide-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .code-block {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }
        .step-number {
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .feature-icon {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-complete { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-optional { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

<?php
// Render dynamic header for demonstration
require_once 'includes/dynamic_templates.php';
renderDynamicHeader('main');
?>

<main class="container">
    
    <!-- Header Section -->
    <section class="guide-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">
                <i class="fas fa-book me-3"></i>Dynamic Template Integration Guide
            </h1>
            <p class="lead mb-4">
                Complete documentation for implementing and managing dynamic templates in APS Dream Home
            </p>
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="step-number mx-auto">1</div>
                    <h4>Setup Database</h4>
                    <p>Create dynamic tables and default data</p>
                </div>
                <div class="col-md-4">
                    <div class="step-number mx-auto">2</div>
                    <h4>Integrate Templates</h4>
                    <p>Add dynamic headers and footers to pages</p>
                </div>
                <div class="col-md-4">
                    <div class="step-number mx-auto">3</div>
                    <h4>Manage Content</h4>
                    <p>Use admin panel to customize appearance</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview Section -->
    <section class="section-card">
        <h2><i class="fas fa-info-circle me-2"></i>System Overview</h2>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h4><i class="fas fa-database feature-icon"></i>Database Architecture</h4>
                <p>The dynamic template system uses 5 core database tables:</p>
                <ul>
                    <li><strong>dynamic_headers:</strong> Header configurations and styling</li>
                    <li><strong>dynamic_footers:</strong> Footer content and links</li>
                    <li><strong>site_content:</strong> Page content and meta information</li>
                    <li><strong>media_library:</strong> File and image management</li>
                    <li><strong>page_templates:</strong> Custom template definitions</li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h4><i class="fas fa-cogs feature-icon"></i>Template Classes</h4>
                <p>Object-oriented PHP classes provide clean separation:</p>
                <ul>
                    <li><strong>DynamicHeader:</strong> Renders dynamic headers</li>
                    <li><strong>DynamicFooter:</strong> Renders dynamic footers</li>
                    <li><strong>DynamicContentManager:</strong> Admin interface</li>
                    <li><strong>Helper Functions:</strong> Easy integration utilities</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Implementation Steps -->
    <section class="section-card">
        <h2><i class="fas fa-tasks me-2"></i>Implementation Steps</h2>
        
        <div class="accordion" id="implementationSteps">
            
            <!-- Step 1 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="step1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                        <span class="status-badge status-complete me-3">COMPLETE</span>
                        Step 1: Database Setup
                    </button>
                </h2>
                <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#implementationSteps">
                    <div class="accordion-body">
                        <h4>Run Database Setup Script</h4>
                        <div class="code-block">
                            php tools/setup_dynamic_database.php
                        </div>
                        
                        <p>This script creates all necessary tables and inserts default data including:</p>
                        <ul>
                            <li>Default header with APS Dream Homes branding</li>
                            <li>Default footer with company information</li>
                            <li>Sample site content and meta tags</li>
                        </ul>
                        
                        <h5>Verification:</h5>
                        <div class="code-block">
                            # Check if tables exist
                            php test_dynamic_templates.php
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="step2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                        <span class="status-badge status-complete me-3">COMPLETE</span>
                        Step 2: Basic Integration
                    </button>
                </h2>
                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#implementationSteps">
                    <div class="accordion-body">
                        <h4>Add Dynamic Templates to Pages</h4>
                        
                        <h5>Simple Integration Method:</h5>
                        <div class="code-block">
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
    
    &lt;!-- Your page content here --&gt;
    &lt;main&gt;Content&lt;/main&gt;
    
    &lt;?php renderDynamicFooter('main'); ?&gt;
    &lt;?php addDynamicTemplateJS(); ?&gt;
&lt;/body&gt;
&lt;/html&gt;
                        </div>
                        
                        <h5>Advanced Integration Method:</h5>
                        <div class="code-block">
&lt;?php
require_once 'includes/dynamic_templates.php';

// Render complete page
renderDynamicPage('Page Title', '&lt;p&gt;Page content&lt;/p&gt;', [
    'header_type' => 'main',
    'footer_type' => 'main'
]);
?&gt;
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="step3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                        <span class="status-badge status-complete me-3">COMPLETE</span>
                        Step 3: Admin Configuration
                    </button>
                </h2>
                <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#implementationSteps">
                    <div class="accordion-body">
                        <h4>Access Admin Panel</h4>
                        <p>Navigate to: <strong>/admin/dynamic_content_manager.php</strong></p>
                        
                        <h5>Admin Features:</h5>
                        <ul>
                            <li><strong>Headers Tab:</strong> Customize logo, colors, navigation menu</li>
                            <li><strong>Footers Tab:</strong> Edit company info, links, social media</li>
                            <li><strong>Live Preview:</strong> See changes in real-time</li>
                            <li><strong>Custom CSS/JS:</strong> Add custom styling and scripts</li>
                        </ul>
                        
                        <h5>Configuration Options:</h5>
                        <ul>
                            <li>Logo URL and alt text</li>
                            <li>Background and text colors</li>
                            <li>Navigation menu items (JSON format)</li>
                            <li>Company information and contact details</li>
                            <li>Social media links</li>
                            <li>Custom CSS and JavaScript</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="step4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                        <span class="status-badge status-pending me-3">PENDING</span>
                        Step 4: Migration of Existing Pages
                    </button>
                </h2>
                <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#implementationSteps">
                    <div class="accordion-body">
                        <h4>Run Migration Script</h4>
                        <div class="code-block">
                            php tools/migrate_to_dynamic.php
                        </div>
                        
                        <p>This script will:</p>
                        <ul>
                            <li>Backup existing template files</li>
                            <li>Convert main pages to use dynamic templates</li>
                            <li>Update admin pages accordingly</li>
                            <li>Create migration report</li>
                        </ul>
                        
                        <h5>Manual Migration (Optional):</h5>
                        <p>If you prefer manual migration, replace existing header/footer includes with:</p>
                        <div class="code-block">
// Replace this:
&lt;?php include 'includes/header.php'; ?&gt;

// With this:
&lt;?php renderDynamicHeader('main'); ?&gt;
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Step 5 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="step5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                        <span class="status-badge status-optional me-3">OPTIONAL</span>
                        Step 5: Advanced Customization
                    </button>
                </h2>
                <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#implementationSteps">
                    <div class="accordion-body">
                        <h4>Custom Header/Footer Types</h4>
                        <div class="code-block">
// Different header types for different pages
renderDynamicHeader('admin');    // Admin panel
renderDynamicHeader('user');     // User dashboard  
renderDynamicHeader('mobile');   // Mobile-optimized
                        </div>
                        
                        <h4>Dynamic Content Retrieval</h4>
                        <div class="code-block">
// Get dynamic content from database
$title = getDynamicContent('meta', 'site_title');
$description = getDynamicContent('meta', 'site_description');
                        </div>
                        
                        <h4>Custom Styling</h4>
                        <div class="code-block">
// Add custom CSS in admin panel or programmatically
&lt;style&gt;
.dynamic-header.header-main {
    background: linear-gradient(45deg, #667eea, #764ba2);
}
&lt;/style&gt;
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Code Examples -->
    <section class="section-card">
        <h2><i class="fas fa-code me-2"></i>Code Examples</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Basic Page Template</h4>
                <div class="code-block">
&lt;?php
require_once 'includes/dynamic_templates.php';
?&gt;

&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;&lt;?= getDynamicContent('meta', 'site_title') ?&gt;&lt;/title&gt;
    &lt;?php addDynamicTemplateCSS(); ?&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;?php renderDynamicHeader('main'); ?&gt;
    
    &lt;main class="container my-5"&gt;
        &lt;h1&gt;Welcome to APS Dream Homes&lt;/h1&gt;
        &lt;p&gt;Your dynamic content here&lt;/p&gt;
    &lt;/main&gt;
    
    &lt;?php renderDynamicFooter('main'); ?&gt;
    &lt;?php addDynamicTemplateJS(); ?&gt;
&lt;/body&gt;
&lt;/html&gt;
                </div>
            </div>
            
            <div class="col-md-6">
                <h4>Advanced Page with Options</h4>
                <div class="code-block">
&lt;?php
require_once 'includes/dynamic_templates.php';

// Custom page with options
$options = [
    'header_type' => 'main',
    'footer_type' => 'main',
    'title' => 'Custom Page Title'
];

$content = '
&lt;div class="row"&gt;
    &lt;div class="col-12"&gt;
        &lt;h1&gt;Custom Page&lt;/h1&gt;
        &lt;p&gt;This is a dynamically rendered page.&lt;/p&gt;
    &lt;/div&gt;
&lt;/div&gt;
';

renderDynamicPage('Custom Page', $content, $options);
?&gt;
                </div>
            </div>
        </div>
    </section>

    <!-- Troubleshooting -->
    <section class="section-card">
        <h2><i class="fas fa-wrench me-2"></i>Troubleshooting</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>Common Issues</h4>
                
                <div class="alert alert-warning">
                    <strong>Issue:</strong> Headers/footers not rendering<br>
                    <strong>Solution:</strong> Check database connection and run setup script
                </div>
                
                <div class="alert alert-warning">
                    <strong>Issue:</strong> CSS styles not loading<br>
                    <strong>Solution:</strong> Ensure addDynamicTemplateCSS() is called in head
                </div>
                
                <div class="alert alert-warning">
                    <strong>Issue:</strong> JavaScript not working<br>
                    <strong>Solution:</strong> Ensure addDynamicTemplateJS() is called before </body>
                </div>
            </div>
            
            <div class="col-md-6">
                <h4>Debug Tools</h4>
                
                <h5>Test Suite:</h5>
                <div class="code-block">
                    php test_dynamic_templates.php
                </div>
                
                <h5>Check Availability:</h5>
                <div class="code-block">
&lt;?php
if (isDynamicTemplatesAvailable()) {
    echo "Dynamic templates available";
} else {
    echo "Using fallback templates";
}
?&gt;
                </div>
                
                <h5>Database Check:</h5>
                <div class="code-block">
                    SHOW TABLES LIKE 'dynamic_%';
                </div>
            </div>
        </div>
    </section>

    <!-- Best Practices -->
    <section class="section-card">
        <h2><i class="fas fa-star me-2"></i>Best Practices</h2>
        
        <div class="row">
            <div class="col-md-4">
                <h4><i class="fas fa-check-circle text-success feature-icon"></i>Performance</h4>
                <ul>
                    <li>Use caching for frequently accessed content</li>
                    <li>Minimize custom CSS/JS injection</li>
                    <li>Optimize images in media library</li>
                    <li>Enable gzip compression</li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h4><i class="fas fa-shield-alt text-primary feature-icon"></i>Security</h4>
                <ul>
                    <li>Always sanitize user input</li>
                    <li>Use prepared statements</li>
                    <li>Validate file uploads</li>
                    <li>Implement CSRF protection</li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h4><i class="fas fa-mobile-alt text-info feature-icon"></i>Responsive</h4>
                <ul>
                    <li>Test on all device sizes</li>
                    <li>Use mobile-first approach</li>
                    <li>Optimize touch targets</li>
                    <li>Minimize external dependencies</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Resources -->
    <section class="section-card">
        <h2><i class="fas fa-link me-2"></i>Quick Links & Resources</h2>
        
        <div class="row">
            <div class="col-md-6">
                <h4>System Files</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>admin/dynamic_content_manager.php">Admin Panel</a></li>
                    <li><a href="<?= BASE_URL ?>dynamic_demo.php">Live Demo</a></li>
                    <li><a href="<?= BASE_URL ?>test_dynamic_templates.php">Test Suite</a></li>
                    <li><a href="<?= BASE_URL ?>tools/setup_dynamic_database.php">Database Setup</a></li>
                    <li><a href="<?= BASE_URL ?>tools/migrate_to_dynamic.php">Migration Tool</a></li>
                </ul>
            </div>
            
            <div class="col-md-6">
                <h4>Documentation</h4>
                <ul>
                    <li><strong>Database Schema:</strong> 5 dynamic tables</li>
                    <li><strong>Template Classes:</strong> OOP PHP architecture</li>
                    <li><strong>Helper Functions:</strong> Easy integration utilities</li>
                    <li><strong>Admin Interface:</strong> Visual content management</li>
                    <li><strong>Migration Tools:</strong> Automated page conversion</li>
                </ul>
            </div>
        </div>
    </section>

</main>

<?php
// Render dynamic footer for demonstration
renderDynamicFooter('main');
?>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
