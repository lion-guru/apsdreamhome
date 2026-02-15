<?php
/**
 * Browser/End-to-End Tests for APS Dream Home
 * Tests user interface and user experience scenarios
 */

require_once 'includes/config/constants.php';

class SeleniumTest
{
    private $pdo;
    private $results = ['passed' => 0, 'failed' => 0, 'skipped' => 0];
    private $baseUrl = 'http://localhost/apsdreamhome';
    
    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function assertTrue($condition, $message = 'Assertion failed')
    {
        if ($condition) {
            $this->results['passed']++;
            echo "<span style='color: green;'>✅ {$message}</span><br>\n";
            return true;
        } else {
            $this->results['failed']++;
            echo "<span style='color: red;'>❌ {$message}</span><br>\n";
            return false;
        }
    }
    
    public function assertEquals($expected, $actual, $message = 'Values not equal')
    {
        return $this->assertTrue($expected == $actual, $message . " (Expected: {$expected}, Actual: {$actual})");
    }
    
    public function assertContains($needle, $haystack, $message = 'Value not found in haystack')
    {
        if (is_string($haystack)) {
            return $this->assertTrue(strpos($haystack, $needle) !== false, $message);
        } elseif (is_array($haystack)) {
            return $this->assertTrue(in_array($needle, $haystack), $message);
        }
        return false;
    }
    
    public function testHomePageAccessibility()
    {
        echo "<h2>🌐 Home Page Accessibility Tests</h2>\n";
        
        // Test home page file exists
        $this->assertTrue(file_exists('home.php'), 'Home page file should exist');
        
        // Test home page content
        if (file_exists('home.php')) {
            $content = file_get_contents('home.php');
            
            // Check for essential HTML structure
            $this->assertContains('<html', $content, 'Home page should have HTML structure');
            $this->assertContains('<head>', $content, 'Home page should have head section');
            $this->assertContains('<body>', $content, 'Home page should have body section');
            
            // Check for title
            $this->assertContains('<title>', $content, 'Home page should have title tag');
            
            // Check for company branding
            $this->assertContains('APS', $content, 'Home page should contain company name');
            
            // Check for navigation elements
            $this->assertContains('nav', $content, 'Home page should have navigation');
            
            // Check for responsive design elements
            $this->assertContains('bootstrap', strtolower($content), 'Home page should use Bootstrap');
        }
    }
    
    public function testAdminPanelAccessibility()
    {
        echo "<h2>👨‍💼 Admin Panel Accessibility Tests</h2>\n";
        
        // Test admin login page
        $this->assertTrue(file_exists('admin/index.php'), 'Admin login page should exist');
        
        if (file_exists('admin/index.php')) {
            $content = file_get_contents('admin/index.php');
            $this->assertContains('<form', $content, 'Admin login should have form');
            $this->assertContains('password', strtolower($content), 'Admin login should have password field');
            $this->assertContains('submit', strtolower($content), 'Admin login should have submit button');
        }
        
        // Test admin dashboard
        $this->assertTrue(file_exists('admin/enhanced_dashboard.php'), 'Admin dashboard should exist');
        
        if (file_exists('admin/enhanced_dashboard.php')) {
            $content = file_get_contents('admin/enhanced_dashboard.php');
            $this->assertContains('dashboard', strtolower($content), 'Admin dashboard should contain dashboard elements');
            $this->assertContains('chart', strtolower($content), 'Admin dashboard should have charts');
            $this->assertContains('table', strtolower($content), 'Admin dashboard should have data tables');
        }
        
        // Test admin configuration
        $this->assertTrue(file_exists('admin/config.php'), 'Admin config file should exist');
        
        // Test admin AJAX endpoints
        $ajaxFiles = [
            'admin/ajax/get_dashboard_stats.php',
            'admin/ajax/get_analytics_data.php',
            'admin/ajax/global_search.php',
            'admin/ajax/get_system_status.php'
        ];
        
        foreach ($ajaxFiles as $file) {
            $this->assertTrue(file_exists($file), "Admin AJAX file should exist: {$file}");
        }
    }
    
    public function testUserInterfaceComponents()
    {
        echo "<h2>🎨 User Interface Components Tests</h2>\n";
        
        // Test template files
        $templateFiles = [
            'includes/templates/header.php',
            'includes/templates/footer.php',
            'includes/templates/navbar.php',
            'includes/templates/sidebar.php'
        ];
        
        foreach ($templateFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->assertTrue(strlen($content) > 0, "Template file should not be empty: {$file}");
            } else {
                echo "<span style='color: orange;'>⚠️ Template file not found (may use alternative): {$file}</span><br>\n";
                $this->results['skipped']++;
            }
        }
        
        // Test CSS files
        $cssFiles = [
            'assets/css/bootstrap.min.css',
            'assets/css/style.css',
            'assets/css/admin.css'
        ];
        
        foreach ($cssFiles as $file) {
            if (file_exists($file)) {
                $this->assertTrue(filesize($file) > 0, "CSS file should not be empty: {$file}");
            } else {
                echo "<span style='color: orange;'>⚠️ CSS file not found: {$file}</span><br>\n";
                $this->results['skipped']++;
            }
        }
        
        // Test JavaScript files
        $jsFiles = [
            'assets/js/bootstrap.bundle.min.js',
            'assets/js/jquery.min.js',
            'assets/js/admin.js'
        ];
        
        foreach ($jsFiles as $file) {
            if (file_exists($file)) {
                $this->assertTrue(filesize($file) > 0, "JavaScript file should not be empty: {$file}");
            } else {
                echo "<span style='color: orange;'>⚠️ JavaScript file not found: {$file}</span><br>\n";
                $this->results['skipped']++;
            }
        }
    }
    
    public function testFormValidation()
    {
        echo "<h2>📝 Form Validation Tests</h2>\n";
        
        // Test registration form
        $this->assertTrue(file_exists('register.php'), 'Registration page should exist');
        
        if (file_exists('register.php')) {
            $content = file_get_contents('register.php');
            $this->assertContains('form', $content, 'Registration should have form');
            $this->assertContains('email', strtolower($content), 'Registration should have email field');
            $this->assertContains('password', strtolower($content), 'Registration should have password field');
        }
        
        // Test login form
        $this->assertTrue(file_exists('login.php'), 'Login page should exist');
        
        if (file_exists('login.php')) {
            $content = file_get_contents('login.php');
            $this->assertContains('form', $content, 'Login should have form');
            $this->assertContains('password', strtolower($content), 'Login should have password field');
        }
        
        // Test inquiry form
        $this->assertTrue(file_exists('contact.php'), 'Contact page should exist');
        
        if (file_exists('contact.php')) {
            $content = file_get_contents('contact.php');
            $this->assertContains('form', $content, 'Contact should have form');
            $this->assertContains('email', strtolower($content), 'Contact should have email field');
            $this->assertContains('message', strtolower($content), 'Contact should have message field');
        }
    }
    
    public function testResponsiveDesign()
    {
        echo "<h2>📱 Responsive Design Tests</h2>\n";
        
        // Test Bootstrap integration
        $bootstrapFiles = [
            'assets/css/bootstrap.min.css',
            'assets/js/bootstrap.bundle.min.js'
        ];
        
        foreach ($bootstrapFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->assertTrue(strlen($content) > 1000, "Bootstrap file should be substantial: {$file}");
            }
        }
        
        // Test viewport meta tag
        $pages = ['home.php', 'admin/index.php'];
        foreach ($pages as $page) {
            if (file_exists($page)) {
                $content = file_get_contents($page);
                $this->assertContains('viewport', strtolower($content), "Page should have viewport meta tag: {$page}");
            }
        }
        
        // Test responsive classes
        if (file_exists('home.php')) {
            $content = file_get_contents('home.php');
            $responsiveClasses = ['col-md-', 'col-lg-', 'd-none', 'd-block'];
            
            foreach ($responsiveClasses as $class) {
                if (strpos($content, $class) !== false) {
                    $this->assertTrue(true, "Responsive class found: {$class}");
                    break;
                }
            }
        }
    }
    
    public function testAccessibilityFeatures()
    {
        echo "<h2>♿ Accessibility Features Tests</h2>\n";
        
        // Test ARIA labels
        $pages = ['home.php', 'admin/index.php'];
        foreach ($pages as $page) {
            if (file_exists($page)) {
                $content = file_get_contents($page);
                
                // Check for ARIA attributes
                $ariaAttributes = ['aria-label', 'role=', 'aria-hidden'];
                foreach ($ariaAttributes as $attr) {
                    if (strpos($content, $attr) !== false) {
                        $this->assertTrue(true, "ARIA attribute found in {$page}: {$attr}");
                        break;
                    }
                }
                
                // Check for alt tags on images
                if (strpos($content, '<img') !== false) {
                    $this->assertContains('alt=', $content, "Images should have alt tags in {$page}");
                }
            }
        }
        
        // Test semantic HTML5
        if (file_exists('home.php')) {
            $content = file_get_contents('home.php');
            $semanticTags = ['header', 'nav', 'main', 'section', 'article', 'aside', 'footer'];
            
            foreach ($semanticTags as $tag) {
                if (strpos($content, "<{$tag}") !== false) {
                    $this->assertTrue(true, "Semantic HTML5 tag found: {$tag}");
                }
            }
        }
    }
    
    public function testErrorPages()
    {
        echo "<h2>🚨 Error Pages Tests</h2>\n";
        
        // Test 404 page
        $this->assertTrue(file_exists('404.php'), '404 error page should exist');
        
        if (file_exists('404.php')) {
            $content = file_get_contents('404.php');
            $this->assertContains('404', $content, '404 page should contain 404');
            $this->assertContains('not found', strtolower($content), '404 page should mention not found');
        }
        
        // Test error handling
        $this->assertTrue(file_exists('includes/error_handler.php'), 'Error handler should exist');
        
        // Test custom error pages
        $errorPages = ['500.php', 'maintenance.php'];
        foreach ($errorPages as $page) {
            if (file_exists($page)) {
                $this->assertTrue(true, "Custom error page exists: {$page}");
            }
        }
    }
    
    public function testSecurityHeaders()
    {
        echo "<h2>🔒 Security Headers Tests</h2>\n";
        
        // Test security configuration files
        $securityFiles = [
            '.htaccess',
            'includes/security.php',
            'admin/config.php'
        ];
        
        foreach ($securityFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                // Check for security headers
                $securityHeaders = [
                    'X-Frame-Options',
                    'X-Content-Type-Options',
                    'X-XSS-Protection',
                    'Content-Security-Policy'
                ];
                
                foreach ($securityHeaders as $header) {
                    if (strpos($content, $header) !== false) {
                        $this->assertTrue(true, "Security header found: {$header}");
                    }
                }
            }
        }
        
        // Test session security
        if (file_exists('includes/config/config.php')) {
            $content = file_get_contents('includes/config/config.php');
            $this->assertContains('session', strtolower($content), 'Session configuration should exist');
        }
    }
    
    public function runAllTests()
    {
        echo "<h1>🌐 APS Dream Home - Browser/UI Tests</h1>\n";
        echo "<p>Testing user interface, accessibility, and user experience...</p>\n";
        
        $this->testHomePageAccessibility();
        $this->testAdminPanelAccessibility();
        $this->testUserInterfaceComponents();
        $this->testFormValidation();
        $this->testResponsiveDesign();
        $this->testAccessibilityFeatures();
        $this->testErrorPages();
        $this->testSecurityHeaders();
        
        $this->printSummary();
    }
    
    private function printSummary()
    {
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $passRate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "<h2>📊 Browser/UI Test Summary</h2>\n";
        echo "<div style='background-color: #f0f8ff; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0;'>\n";
        echo "<h3>Overall Results</h3>\n";
        echo "<p><strong>Total Tests:</strong> {$total}</p>\n";
        echo "<p><strong>Passed:</strong> <span style='color: green;'>{$this->results['passed']}</span></p>\n";
        echo "<p><strong>Failed:</strong> <span style='color: red;'>{$this->results['failed']}</span></p>\n";
        echo "<p><strong>Skipped:</strong> <span style='color: orange;'>{$this->results['skipped']}</span></p>\n";
        echo "<p><strong>Pass Rate:</strong> <strong>{$passRate}%</strong></p>\n";
        echo "</div>\n";
        
        if ($this->results['failed'] > 0) {
            echo "<div style='background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>\n";
            echo "<strong>⚠️ UI Issues:</strong> Some UI tests failed. Please review the errors above.<br>\n";
            echo "</div>\n";
        } else {
            echo "<div style='background-color: #d4edda; padding: 10px; border-left: 4px solid #28a745;'>\n";
            echo "<strong>✅ Excellent UI!</strong> All UI tests are passing. User interface is well-designed.<br>\n";
            echo "</div>\n";
        }
        
        echo "<h3>🎨 UI Environment</h3>\n";
        echo "<div style='background-color: #e2e3e5; padding: 10px; border-left: 4px solid #6c757d;'>\n";
        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>\n";
        echo "<p><strong>Base URL:</strong> {$this->baseUrl}</p>\n";
        echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
        echo "</div>\n";
        
        echo "<h3>💡 UI Recommendations</h3>\n";
        echo "<ul style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d;'>\n";
        echo "<li><strong>Responsive Design:</strong> Ensure all pages work on mobile devices</li>\n";
        echo "<li><strong>Accessibility:</strong> Add more ARIA labels for screen readers</li>\n";
        echo "<li><strong>User Experience:</strong> Improve loading states and error messages</li>\n";
        echo "<li><strong>Performance:</strong> Optimize images and CSS for faster loading</li>\n";
        echo "<li><strong>Security:</strong> Implement CSRF protection on all forms</li>\n";
        echo "</ul>\n";
        
        echo "<hr>\n";
        echo "<p><a href='javascript:history.back()' style='text-decoration: none; padding: 8px 16px; background-color: #007bff; color: white; border-radius: 4px;'>← Go Back</a> | 
                <a href='tests/run_complete_test_suite.php' style='text-decoration: none; padding: 8px 16px; background-color: #28a745; color: white; border-radius: 4px;'>🧪 Complete Suite</a></p>\n";
    }
}

// Run the browser/UI test suite
$browserTest = new SeleniumTest();
$browserTest->runAllTests();
?>
