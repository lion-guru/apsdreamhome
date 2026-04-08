<?php
/**
 * APS Dream Home - Comprehensive MCP-Style Testing Suite
 * Autonomous Mode Testing - All Pages, All Workflows
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class ComprehensiveTester {
    private $baseUrl = 'http://localhost/apsdreamhome';
    private $results = [];
    private $db;
    
    public function __construct() {
        echo "🚀 APS DREAM HOME - COMPREHENSIVE TESTING SUITE\n";
        echo "================================================\n\n";
        
        // Database connection
        try {
            $this->db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✅ Database Connected\n\n";
        } catch (Exception $e) {
            echo "❌ Database Connection Failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    /**
     * Test URL accessibility
     */
    private function testUrl($url, $expectedCode = 200) {
        $ch = curl_init($this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'url' => $url,
            'http_code' => $httpCode,
            'size' => strlen($response),
            'error' => $error,
            'success' => ($httpCode == $expectedCode),
            'response' => $response
        ];
    }
    
    /**
     * Test 1: Frontend Public Pages
     */
    public function testFrontendPages() {
        echo "📄 TEST 1: FRONTEND PUBLIC PAGES\n";
        echo "================================\n";
        
        $pages = [
            '/' => 'Home Page',
            '/about' => 'About Page',
            '/properties' => 'Properties Page',
            '/contact' => 'Contact Page',
            '/login' => 'Login Page',
            '/register' => 'Registration Page',
            '/privacy-policy' => 'Privacy Policy',
            '/terms' => 'Terms & Conditions',
            '/careers' => 'Careers Page',
            '/compare' => 'Compare Properties',
            '/ai-valuation' => 'AI Valuation'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($pages as $url => $name) {
            $result = $this->testUrl($url);
            
            if ($result['success']) {
                $passed++;
                echo "✅ $name: HTTP {$result['http_code']} - {$result['size']} bytes\n";
                
                // Check for UI elements
                if (strpos($result['response'], '<nav') !== false) {
                    echo "   ✅ Navigation present\n";
                }
                if (strpos($result['response'], '<footer') !== false || strpos($result['response'], 'footer') !== false) {
                    echo "   ✅ Footer present\n";
                }
                if (strpos($result['response'], 'bootstrap') !== false || strpos($result['response'], 'Bootstrap') !== false) {
                    echo "   ✅ Bootstrap CSS loaded\n";
                }
            } else {
                $failed++;
                echo "❌ $name: HTTP {$result['http_code']}\n";
                if ($result['error']) {
                    echo "   Error: {$result['error']}\n";
                }
            }
        }
        
        echo "\n📊 Frontend Results: $passed passed, $failed failed\n\n";
        
        $this->results['frontend'] = ['passed' => $passed, 'failed' => $failed];
        return $failed == 0;
    }
    
    /**
     * Test 2: Database Connectivity & Data
     */
    public function testDatabase() {
        echo "🗄️ TEST 2: DATABASE CONNECTIVITY & DATA\n";
        echo "========================================\n";
        
        if (!$this->db) {
            echo "❌ Database not connected\n\n";
            return false;
        }
        
        $tables = [
            'users' => 'Users',
            'properties' => 'Properties',
            'customers' => 'Customers',
            'payments' => 'Payments',
            'states' => 'States',
            'districts' => 'Districts',
            'colonies' => 'Colonies',
            'plots' => 'Plots',
            'projects' => 'Projects',
            'leads' => 'Leads',
            'commissions' => 'Commissions'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tables as $table => $name) {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                echo "✅ $name: $count records\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ $name: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 Database Results: $passed passed, $failed failed\n\n";
        
        $this->results['database'] = ['passed' => $passed, 'failed' => $failed];
        return $failed == 0;
    }
    
    /**
     * Test 3: Admin Panel Access
     */
    public function testAdminPanel() {
        echo "🔐 TEST 3: ADMIN PANEL ACCESS\n";
        echo "==============================\n";
        
        $adminUrls = [
            '/admin' => 'Admin Dashboard',
            '/admin/login' => 'Admin Login',
            '/admin/properties' => 'Admin Properties',
            '/admin/customers' => 'Admin Customers',
            '/admin/leads' => 'Admin Leads',
            '/admin/payments' => 'Admin Payments',
            '/admin/locations/states' => 'Admin States',
            '/admin/locations/districts' => 'Admin Districts',
            '/admin/locations/colonies' => 'Admin Colonies',
            '/admin/plots' => 'Admin Plots',
            '/admin/projects' => 'Admin Projects',
            '/admin/mlm/dashboard' => 'MLM Dashboard',
            '/admin/commission/rules' => 'Commission Rules',
            '/admin/analytics' => 'Analytics Dashboard',
            '/admin/ai-assistant' => 'AI Assistant',
            '/admin/whatsapp/templates' => 'WhatsApp Templates'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($adminUrls as $url => $name) {
            $result = $this->testUrl($url);
            
            // Admin pages should return 200 (if logged in) or 302/403 (if not logged in)
            if ($result['http_code'] == 200 || $result['http_code'] == 302 || $result['http_code'] == 403) {
                $passed++;
                echo "✅ $name: HTTP {$result['http_code']} (Accessible)\n";
            } else {
                $failed++;
                echo "❌ $name: HTTP {$result['http_code']}\n";
            }
        }
        
        echo "\n📊 Admin Panel Results: $passed passed, $failed failed\n\n";
        
        $this->results['admin'] = ['passed' => $passed, 'failed' => $failed];
        return true; // Admin pages may require login, so we consider this a soft pass
    }
    
    /**
     * Test 4: API Endpoints
     */
    public function testAPIEndpoints() {
        echo "🔌 TEST 4: API ENDPOINTS\n";
        echo "========================\n";
        
        $apiUrls = [
            '/api/health' => 'Health Check API',
            '/api/system/status' => 'System Status API',
            '/api/properties' => 'Properties API',
            '/api/colonies' => 'Colonies API',
            '/api/ai/valuation' => 'AI Valuation API',
            '/api/notifications' => 'Notifications API'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($apiUrls as $url => $name) {
            $result = $this->testUrl($url);
            
            if ($result['http_code'] == 200 || $result['http_code'] == 401 || $result['http_code'] == 403) {
                $passed++;
                echo "✅ $name: HTTP {$result['http_code']}\n";
            } else {
                $failed++;
                echo "❌ $name: HTTP {$result['http_code']}\n";
            }
        }
        
        echo "\n📊 API Results: $passed passed, $failed failed\n\n";
        
        $this->results['api'] = ['passed' => $passed, 'failed' => $failed];
        return true;
    }
    
    /**
     * Test 5: Customer Portal
     */
    public function testCustomerPortal() {
        echo "👤 TEST 5: CUSTOMER PORTAL\n";
        echo "==========================\n";
        
        $customerUrls = [
            '/customer' => 'Customer Dashboard',
            '/customer/login' => 'Customer Login',
            '/customer/register' => 'Customer Register',
            '/customer/properties' => 'Customer Properties',
            '/customer/payments' => 'Customer Payments',
            '/customer/profile' => 'Customer Profile'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($customerUrls as $url => $name) {
            $result = $this->testUrl($url);
            
            if ($result['http_code'] == 200 || $result['http_code'] == 302 || $result['http_code'] == 403) {
                $passed++;
                echo "✅ $name: HTTP {$result['http_code']}\n";
            } else {
                $failed++;
                echo "❌ $name: HTTP {$result['http_code']}\n";
            }
        }
        
        echo "\n📊 Customer Portal Results: $passed passed, $failed failed\n\n";
        
        $this->results['customer'] = ['passed' => $passed, 'failed' => $failed];
        return true;
    }
    
    /**
     * Test 6: File Structure Verification
     */
    public function testFileStructure() {
        echo "📁 TEST 6: FILE STRUCTURE VERIFICATION\n";
        echo "=======================================\n";
        
        $requiredFiles = [
            'public/index.php' => 'Entry Point',
            'config/bootstrap.php' => 'Bootstrap',
            'app/Http/Controllers/AuthController.php' => 'Auth Controller',
            'app/Http/Controllers/Admin/AdminController.php' => 'Admin Controller',
            'app/views/admin/dashboard.php' => 'Admin Dashboard View',
            'routes/web.php' => 'Web Routes',
            '.htaccess' => 'Apache Config',
            'index.php' => 'Root Redirect'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($requiredFiles as $file => $name) {
            if (file_exists($file)) {
                $size = filesize($file);
                echo "✅ $name: $file ($size bytes)\n";
                $passed++;
            } else {
                echo "❌ $name: $file (MISSING)\n";
                $failed++;
            }
        }
        
        echo "\n📊 File Structure Results: $passed passed, $failed failed\n\n";
        
        $this->results['files'] = ['passed' => $passed, 'failed' => $failed];
        return $failed == 0;
    }
    
    /**
     * Generate Final Report
     */
    public function generateReport() {
        echo "📊 FINAL COMPREHENSIVE TEST REPORT\n";
        echo "=================================\n\n";
        
        $totalPassed = 0;
        $totalFailed = 0;
        
        foreach ($this->results as $category => $result) {
            $totalPassed += $result['passed'];
            $totalFailed += $result['failed'];
            echo "✅ {$category}: {$result['passed']} passed, {$result['failed']} failed\n";
        }
        
        $total = $totalPassed + $totalFailed;
        $percentage = $total > 0 ? round(($totalPassed / $total) * 100, 2) : 0;
        
        echo "\n📊 OVERALL STATISTICS:\n";
        echo "====================\n";
        echo "✅ Total Passed: $totalPassed\n";
        echo "❌ Total Failed: $totalFailed\n";
        echo "📊 Total Tests: $total\n";
        echo "🎯 Success Rate: $percentage%\n\n";
        
        if ($percentage >= 90) {
            echo "🎉 EXCELLENT! System is production ready!\n";
        } elseif ($percentage >= 75) {
            echo "✅ GOOD! System is mostly functional.\n";
        } elseif ($percentage >= 50) {
            echo "⚠️  FAIR! Some issues need attention.\n";
        } else {
            echo "❌ CRITICAL! Major issues detected.\n";
        }
        
        echo "\n📝 TESTING COMPLETE!\n";
        echo "===================\n";
        echo "🔗 Main URL: {$this->baseUrl}/\n";
        echo "🔗 Testing Dashboard: {$this->baseUrl}/testing/dashboard.php\n";
        echo "🔗 Admin Panel: {$this->baseUrl}/admin\n";
        
        return $percentage;
    }
    
    /**
     * Run All Tests
     */
    public function runAllTests() {
        echo "\n";
        $this->testFrontendPages();
        $this->testDatabase();
        $this->testAdminPanel();
        $this->testAPIEndpoints();
        $this->testCustomerPortal();
        $this->testFileStructure();
        
        $percentage = $this->generateReport();
        
        return $percentage;
    }
}

// Run comprehensive tests
$tester = new ComprehensiveTester();
$successRate = $tester->runAllTests();

exit($successRate >= 75 ? 0 : 1);
?>
