<?php
/**
 * APS Dream Home - Browser Based Real Testing
 * Using Puppeteer to actually browse and click like a human
 */

require_once __DIR__ . '/vendor/autoload.php';

use Nesk\Puphpeteer\Puppeteer;

class RealBrowserTester {
    private $puppeteer;
    private $browser;
    private $page;
    private $baseUrl = 'http://localhost/apsdreamhome';
    private $results = [];
    private $screenshots = [];
    
    public function __construct() {
        echo "🌐 REAL BROWSER TESTING - Like a Human\n";
        echo "=====================================\n\n";
    }
    
    public function start() {
        try {
            echo "1️⃣ Starting Puppeteer Browser...\n";
            $this->puppeteer = new Puppeteer([
                'headless' => false, // Show browser so we can see
                'args' => ['--no-sandbox', '--disable-setuid-sandbox']
            ]);
            $this->browser = $this->puppeteer->launch();
            $this->page = $this->browser->newPage();
            $this->page->setViewport(['width' => 1400, 'height' => 900]);
            echo "   ✅ Browser started\n\n";
            return true;
        } catch (Exception $e) {
            echo "   ❌ Failed: " . $e->getMessage() . "\n\n";
            return false;
        }
    }
    
    /**
     * Test Admin Login Page
     */
    public function testAdminLogin() {
        echo "2️⃣ Testing Admin Login Page...\n";
        try {
            $this->page->goto($this->baseUrl . '/admin/login');
            $this->page->waitForTimeout(2000);
            
            // Take screenshot
            $screenshot = __DIR__ . '/testing/screenshots/01_admin_login.png';
            $this->page->screenshot(['path' => $screenshot, 'fullPage' => true]);
            echo "   📸 Screenshot: 01_admin_login.png\n";
            
            // Check if login form exists
            $formExists = $this->page->evaluate("() => document.querySelector('form') !== null");
            $captchaExists = $this->page->evaluate("() => document.querySelector('[name*=captcha]') !== null || document.querySelector('[name*=math]') !== null");
            $emailField = $this->page->evaluate("() => document.querySelector('input[type=email]') !== null || document.querySelector('input[name=email]') !== null");
            
            echo "   Form present: " . ($formExists ? '✅' : '❌') . "\n";
            echo "   CAPTCHA present: " . ($captchaExists ? '✅' : '⚠️') . "\n";
            echo "   Email field: " . ($emailField ? '✅' : '❌') . "\n";
            
            $this->results['admin_login'] = [
                'status' => $formExists ? 'working' : 'broken',
                'form' => $formExists,
                'captcha' => $captchaExists,
                'email_field' => $emailField,
                'screenshot' => '01_admin_login.png'
            ];
            
            return $formExists;
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
            $this->results['admin_login'] = ['status' => 'error', 'error' => $e->getMessage()];
            return false;
        }
    }
    
    /**
     * Try to login as admin
     */
    public function loginAsAdmin() {
        echo "\n3️⃣ Attempting Admin Login...\n";
        try {
            // Fill email
            $this->page->type('input[type=email], input[name=email], input[name=username]', 'admin@apsdreamhome.com');
            echo "   ✅ Email filled\n";
            
            // Fill password
            $this->page->type('input[type=password], input[name=password]', 'admin123');
            echo "   ✅ Password filled\n";
            
            // Handle CAPTCHA if present
            $captchaText = $this->page->evaluate("() => {
                const captchaLabel = document.querySelector('label[for=captcha], .captcha-question, [name=math_captcha]');
                return captchaLabel ? captchaLabel.textContent : null;
            }");
            
            if ($captchaText) {
                echo "   📝 CAPTCHA found: $captchaText\n";
                // Extract math problem and solve
                if (preg_match('/(\d+)\s*\+\s*(\d+)/', $captchaText, $matches)) {
                    $answer = $matches[1] + $matches[2];
                    $this->page->type('input[name=captcha], input[name=math_captcha]', (string)$answer);
                    echo "   ✅ CAPTCHA answered: $answer\n";
                }
            }
            
            // Click login button
            $this->page->click('button[type=submit], input[type=submit], .btn-login');
            echo "   ⏳ Submitting...\n";
            
            // Wait for navigation
            $this->page->waitForNavigation(['timeout' => 5000]);
            $this->page->waitForTimeout(2000);
            
            // Take screenshot after login
            $screenshot = __DIR__ . '/testing/screenshots/02_after_login.png';
            $this->page->screenshot(['path' => $screenshot, 'fullPage' => true]);
            echo "   📸 Screenshot: 02_after_login.png\n";
            
            // Check if we're on dashboard
            $url = $this->page->url();
            $onDashboard = strpos($url, 'dashboard') !== false;
            
            // Check for sidebar
            $sidebarExists = $this->page->evaluate("() => document.querySelector('.sidebar') !== null || document.querySelector('[class*=sidebar]') !== null");
            
            // Check for header profile
            $profileExists = $this->page->evaluate("() => {
                return document.querySelector('.profile') !== null || 
                       document.querySelector('[class*=profile]') !== null ||
                       document.querySelector('.user-menu') !== null ||
                       document.querySelector('.dropdown-toggle') !== null;
            }");
            
            echo "   URL: $url\n";
            echo "   On Dashboard: " . ($onDashboard ? '✅' : '❌') . "\n";
            echo "   Sidebar present: " . ($sidebarExists ? '✅' : '❌') . "\n";
            echo "   Profile menu: " . ($profileExists ? '✅' : '❌') . "\n";
            
            $this->results['after_login'] = [
                'url' => $url,
                'on_dashboard' => $onDashboard,
                'sidebar' => $sidebarExists,
                'profile_menu' => $profileExists,
                'screenshot' => '02_after_login.png'
            ];
            
            return $onDashboard;
        } catch (Exception $e) {
            echo "   ❌ Login error: " . $e->getMessage() . "\n";
            
            // Still take screenshot
            try {
                $screenshot = __DIR__ . '/testing/screenshots/02_login_error.png';
                $this->page->screenshot(['path' => $screenshot, 'fullPage' => true]);
                echo "   📸 Error screenshot: 02_login_error.png\n";
            } catch (Exception $e2) {}
            
            $this->results['after_login'] = ['status' => 'error', 'error' => $e->getMessage()];
            return false;
        }
    }
    
    /**
     * Test Sidebar Menus
     */
    public function testSidebarMenus() {
        echo "\n4️⃣ Testing Sidebar Menu Items...\n";
        
        $menuItems = [
            ['name' => 'Dashboard', 'selector' => "a[href*='dashboard'], .sidebar a:first-child"],
            ['name' => 'Properties', 'selector' => "a[href*='properties'], a:has-text('Properties')"],
            ['name' => 'Customers', 'selector' => "a[href*='customers']"],
            ['name' => 'Leads', 'selector' => "a[href*='leads']"],
            ['name' => 'Users', 'selector' => "a[href*='users']"],
            ['name' => 'Locations', 'selector' => "a[href*='locations']"],
            ['name' => 'MLM', 'selector' => "a[href*='mlm']"],
            ['name' => 'Commissions', 'selector' => "a[href*='commission']"],
            ['name' => 'Settings', 'selector' => "a[href*='settings']"],
        ];
        
        foreach ($menuItems as $index => $item) {
            try {
                echo "\n   Testing {$item['name']}...\n";
                
                // Find and click menu
                $link = $this->page->querySelector($item['selector']);
                if (!$link) {
                    // Try broader selector
                    $link = $this->page->evaluate("() => {
                        const links = document.querySelectorAll('a');
                        for (let a of links) {
                            if (a.textContent.toLowerCase().includes('" . strtolower($item['name']) . "')) return a;
                        }
                        return null;
                    }");
                }
                
                if (!$link) {
                    echo "      ⚠️ Menu '{$item['name']}' not found\n";
                    $this->results['sidebar_' . $item['name']] = ['status' => 'not_found'];
                    continue;
                }
                
                // Click the link
                $link->click();
                $this->page->waitForNavigation(['timeout' => 5000])->catch(function(){});
                $this->page->waitForTimeout(2000);
                
                $url = $this->page->url();
                $loaded = strpos($url, 'error') === false && strpos($url, 'login') === false;
                
                // Screenshot
                $num = $index + 3;
                $screenshot = __DIR__ . "/testing/screenshots/{$num}_{$item['name']}.png";
                $this->page->screenshot(['path' => $screenshot, 'fullPage' => true]);
                
                echo "      URL: $url\n";
                echo "      Status: " . ($loaded ? '✅ Loaded' : '❌ Error/Redirect') . "\n";
                echo "      📸 {$num}_{$item['name']}.png\n";
                
                $this->results['sidebar_' . $item['name']] = [
                    'status' => $loaded ? 'working' : 'broken',
                    'url' => $url,
                    'screenshot' => "{$num}_{$item['name']}.png"
                ];
                
            } catch (Exception $e) {
                echo "      ❌ Error: " . $e->getMessage() . "\n";
                $this->results['sidebar_' . $item['name']] = ['status' => 'error', 'error' => $e->getMessage()];
            }
        }
    }
    
    /**
     * Test Header Profile Menu
     */
    public function testHeaderProfile() {
        echo "\n5️⃣ Testing Header Profile Menu...\n";
        try {
            // Look for profile dropdown or menu
            $profileSelectors = [
                '.profile',
                '.user-menu',
                '.dropdown-toggle',
                '[class*=profile]',
                '[class*=user]',
                'a:has(img)',
                '.nav-right a:last-child'
            ];
            
            $profileFound = false;
            foreach ($profileSelectors as $selector) {
                try {
                    $el = $this->page->querySelector($selector);
                    if ($el) {
                        echo "   Found profile element: $selector\n";
                        $el->click();
                        $this->page->waitForTimeout(1000);
                        
                        $screenshot = __DIR__ . '/testing/screenshots/12_profile_menu.png';
                        $this->page->screenshot(['path' => $screenshot, 'fullPage' => false]);
                        echo "   📸 Screenshot: 12_profile_menu.png\n";
                        
                        $profileFound = true;
                        break;
                    }
                } catch (Exception $e) {}
            }
            
            if (!$profileFound) {
                echo "   ❌ Profile menu not found\n";
            }
            
            $this->results['header_profile'] = ['found' => $profileFound];
            
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Generate Test Report
     */
    public function generateReport() {
        echo "\n\n📊 TEST REPORT\n";
        echo "=============\n\n";
        
        // Count results
        $working = 0;
        $broken = 0;
        $errors = 0;
        
        foreach ($this->results as $test => $result) {
            if (isset($result['status'])) {
                if ($result['status'] === 'working') $working++;
                elseif ($result['status'] === 'broken' || $result['status'] === 'not_found') $broken++;
                elseif ($result['status'] === 'error') $errors++;
            }
        }
        
        echo "Working: $working\n";
        echo "Broken/Not Found: $broken\n";
        echo "Errors: $errors\n\n";
        
        // Save JSON report
        $reportFile = __DIR__ . '/testing/reports/browser_test_results.json';
        file_put_contents($reportFile, json_encode($this->results, JSON_PRETTY_PRINT));
        echo "📄 JSON Report: testing/reports/browser_test_results.json\n";
        
        // HTML Report
        $html = $this->generateHtmlReport();
        $htmlFile = __DIR__ . '/testing/reports/browser_test_report.html';
        file_put_contents($htmlFile, $html);
        echo "🌐 HTML Report: testing/reports/browser_test_report.html\n";
        
        return [$working, $broken, $errors];
    }
    
    private function generateHtmlReport() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Browser Test Report - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">🌐 Real Browser Test Report</h1>
    <p class="text-muted">Generated: ' . date('Y-m-d H:i:s') . '</p>
    
    <div class="row mb-4">';
        
        foreach ($this->results as $test => $result) {
            $status = $result['status'] ?? 'unknown';
            $badgeClass = $status === 'working' ? 'success' : ($status === 'error' ? 'danger' : 'warning');
            
            $html .= '
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>' . htmlspecialchars($test) . '</strong>
                    <span class="badge bg-' . $badgeClass . '">' . htmlspecialchars($status) . '</span>
                </div>
                <div class="card-body">';
            
            if (isset($result['url'])) {
                $html .= '<p class="small text-muted">URL: ' . htmlspecialchars($result['url']) . '</p>';
            }
            if (isset($result['error'])) {
                $html .= '<p class="text-danger">Error: ' . htmlspecialchars($result['error']) . '</p>';
            }
            if (isset($result['screenshot'])) {
                $html .= '<img src="../screenshots/' . $result['screenshot'] . '" class="img-fluid mt-2" alt="Screenshot">';
            }
            
            $html .= '</div></div></div>';
        }
        
        $html .= '
    </div>
</div>
</body>
</html>';
        return $html;
    }
    
    public function close() {
        if ($this->browser) {
            $this->browser->close();
            echo "\n✅ Browser closed\n";
        }
    }
}

// Ensure directories exist
if (!is_dir(__DIR__ . '/testing/screenshots')) {
    mkdir(__DIR__ . '/testing/screenshots', 0755, true);
}
if (!is_dir(__DIR__ . '/testing/reports')) {
    mkdir(__DIR__ . '/testing/reports', 0755, true);
}

// Run tests
$tester = new RealBrowserTester();
if ($tester->start()) {
    $tester->testAdminLogin();
    $tester->loginAsAdmin();
    $tester->testSidebarMenus();
    $tester->testHeaderProfile();
    $tester->generateReport();
    $tester->close();
}

echo "\n\n🎉 TESTING COMPLETE!\n";
echo "Check screenshots in: testing/screenshots/\n";
echo "Check reports in: testing/reports/\n";
?>
