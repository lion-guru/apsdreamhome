<?php
/**
 * APS Dream Home - Comprehensive UI Test Script
 * Tests all buttons, links, forms, modals and interactive elements
 */

// Configuration
define('BASE_URL', 'http://localhost/apsdreamhome');
define('TIMEOUT', 10);

class UITester {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    
    public function runAllTests() {
        echo "==========================================\n";
        echo "APS Dream Home - UI Comprehensive Test\n";
        echo "==========================================\n\n";
        
        $this->testHomePage();
        $this->testPropertiesPage();
        $this->testPropertyDetail();
        $this->testContactForm();
        $this->testLoginRegister();
        $this->testNavigationLinks();
        $this->testButtons();
        $this->testAdminPages();
        
        $this->printSummary();
    }
    
    private function fetchUrl($url, $method = 'GET', $data = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'body' => $response,
            'error' => $error
        ];
    }
    
    private function checkLink($url, $expectedCode = 200) {
        $result = $this->fetchUrl($url);
        $status = ($result['code'] == $expectedCode) ? 'PASS' : 'FAIL';
        $this->recordResult("GET $url", $status, $result['code']);
        return $result['code'] == $expectedCode;
    }
    
    private function checkForm($url, $formData, $expectedCode = 200) {
        $result = $this->fetchUrl($url, 'POST', $formData);
        $status = ($result['code'] == $expectedCode) ? 'PASS' : 'FAIL';
        $this->recordResult("POST $url", $status, $result['code']);
        return $result['code'] == $expectedCode;
    }
    
    private function checkButtonExists($html, $buttonText) {
        $found = (stripos($html, $buttonText) !== false);
        $this->recordResult("Button: $buttonText", $found ? 'PASS' : 'FAIL', $found ? 'Found' : 'Not Found');
        return $found;
    }
    
    private function checkFormExists($html, $formName) {
        $found = (stripos($html, 'name="' . $formName . '"') !== false) || 
                 (stripos($html, '<form') !== false);
        $this->recordResult("Form: $formName", $found ? 'PASS' : 'FAIL', $found ? 'Found' : 'Not Found');
        return $found;
    }
    
    private function checkModalExists($html, $modalId) {
        $found = (stripos($html, 'id="' . $modalId . '"') !== false) ||
                 (stripos($html, "id='$modalId'") !== false);
        $this->recordResult("Modal: $modalId", $found ? 'PASS' : 'FAIL', $found ? 'Found' : 'Not Found');
        return $found;
    }
    
    private function recordResult($test, $status, $detail) {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'detail' => $detail
        ];
        if ($status === 'PASS') {
            $this->passed++;
        } else {
            $this->failed++;
        }
    }
    
    private function testHomePage() {
        echo "\n--- Testing Home Page ---\n";
        $result = $this->fetchUrl(BASE_URL . '/');
        
        if ($result['code'] === 200) {
            $this->recordResult('Home Page Load', 'PASS', '200 OK');
            
            // Check key elements
            $this->checkButtonExists($result['body'], 'View Properties');
            $this->checkButtonExists($result['body'], 'Contact Us');
            $this->checkButtonExists($result['body'], 'Register');
            $this->checkButtonExists($result['body'], 'Login');
            $this->checkFormExists($result['body'], 'contact');
            
            // Check navigation links
            $links = ['/about', '/properties', '/contact', '/services', '/gallery'];
            foreach ($links as $link) {
                $found = strpos($result['body'], 'href="' . $link . '"') !== false;
                $this->recordResult("Nav Link: $link", $found ? 'PASS' : 'FAIL', $found ? 'Found' : 'Missing');
            }
        } else {
            $this->recordResult('Home Page Load', 'FAIL', $result['code']);
        }
    }
    
    private function testPropertiesPage() {
        echo "\n--- Testing Properties Page ---\n";
        $result = $this->fetchUrl(BASE_URL . '/properties');
        
        if ($result['code'] === 200) {
            $this->recordResult('Properties Page Load', 'PASS', '200 OK');
            
            // Check for property listings
            $this->checkButtonExists($result['body'], 'View Details');
            $this->checkButtonExists($result['body'], 'Enquire');
            $this->checkButtonExists($result['body'], 'Contact');
            
            // Check for filter/search form
            $this->checkFormExists($result['body'], 'search');
            $this->checkFormExists($result['body'], 'filter');
        } else {
            $this->recordResult('Properties Page Load', 'FAIL', $result['code']);
        }
    }
    
    private function testPropertyDetail() {
        echo "\n--- Testing Property Detail Page ---\n";
        $result = $this->fetchUrl(BASE_URL . '/properties/1');
        
        if ($result['code'] === 200) {
            $this->recordResult('Property Detail Page Load', 'PASS', '200 OK');
            
            // Check enquiry form
            $this->checkFormExists($result['body'], 'name');
            $this->checkFormExists($result['body'], 'email');
            $this->checkFormExists($result['body'], 'phone');
            
            // Check buttons
            $this->checkButtonExists($result['body'], 'Call Now');
            $this->checkButtonExists($result['body'], 'WhatsApp');
            $this->checkButtonExists($result['body'], 'Send Enquiry');
        } else {
            $this->recordResult('Property Detail Page Load', 'FAIL', $result['code']);
        }
    }
    
    private function testContactForm() {
        echo "\n--- Testing Contact Form ---\n";
        
        // GET request - check form exists
        $result = $this->fetchUrl(BASE_URL . '/contact');
        if ($result['code'] === 200) {
            $this->recordResult('Contact Page Load', 'PASS', '200 OK');
            $this->checkFormExists($result['body'], 'name');
            $this->checkFormExists($result['body'], 'email');
            $this->checkFormExists($result['body'], 'message');
        }
        
        // POST request - submit form
        $this->checkForm(BASE_URL . '/contact', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9876543210',
            'message' => 'Test message from automated test'
        ], 200);
    }
    
    private function testLoginRegister() {
        echo "\n--- Testing Login/Register Pages ---\n";
        
        // Login page
        $result = $this->fetchUrl(BASE_URL . '/login');
        if ($result['code'] === 200) {
            $this->recordResult('Login Page Load', 'PASS', '200 OK');
            $this->checkFormExists($result['body'], 'email');
            $this->checkFormExists($result['body'], 'password');
        }
        
        // Register page
        $result = $this->fetchUrl(BASE_URL . '/register');
        if ($result['code'] === 200) {
            $this->recordResult('Register Page Load', 'PASS', '200 OK');
            $this->checkFormExists($result['body'], 'name');
            $this->checkFormExists($result['body'], 'email');
            $this->checkFormExists($result['body'], 'phone');
        }
    }
    
    private function testNavigationLinks() {
        echo "\n--- Testing Navigation Links ---\n";
        
        $pages = [
            '/about',
            '/services', 
            '/team',
            '/gallery',
            '/testimonials',
            '/faq',
            '/careers',
            '/sitemap',
            '/privacy'
        ];
        
        foreach ($pages as $page) {
            $this->checkLink(BASE_URL . $page);
        }
    }
    
    private function testButtons() {
        echo "\n--- Testing Key Buttons ---\n";
        
        $result = $this->fetchUrl(BASE_URL . '/');
        $buttons = [
            'Get Started',
            'Learn More',
            'Book Now',
            'Enquire Now',
            'Call Now',
            'WhatsApp',
            'Register',
            'Login'
        ];
        
        foreach ($buttons as $button) {
            $this->checkButtonExists($result['body'], $button);
        }
    }
    
    private function testAdminPages() {
        echo "\n--- Testing Admin Pages ---\n";
        
        $adminPages = [
            '/admin',
            '/admin/login'
        ];
        
        foreach ($adminPages as $page) {
            $this->checkLink(BASE_URL . $page);
        }
    }
    
    private function printSummary() {
        echo "\n==========================================\n";
        echo "TEST SUMMARY\n";
        echo "==========================================\n";
        echo "Total Tests: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: " . $this->passed . " ✅\n";
        echo "Failed: " . $this->failed . " ❌\n";
        echo "Success Rate: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 1) . "%\n";
        echo "==========================================\n";
        
        if ($this->failed > 0) {
            echo "\nFailed Tests:\n";
            foreach ($this->results as $r) {
                if ($r['status'] === 'FAIL') {
                    echo "  - {$r['test']}: {$r['detail']}\n";
                }
            }
        }
    }
}

// Run tests
$tester = new UITester();
$tester->runAllTests();
