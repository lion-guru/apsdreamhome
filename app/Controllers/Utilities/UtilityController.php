<?php

namespace App\Controllers\Utilities;

use App\Services\Utilities\UtilityService;
use App\Services\Monitoring\AuthenticationService;

/**
 * Utility Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class UtilityController
{
    private $utilityService;
    private $authService;
    private $viewRenderer;
    
    public function __construct()
    {
        $this->utilityService = new UtilityService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new \App\Core\ViewRenderer();
    }
    
    /**
     * Generate slug (AJAX)
     */
    public function generateSlug($request)
    {
        $text = $request['post']['text'] ?? '';
        
        if (empty($text)) {
            return $this->utilityService->createResponse(false, 'Text is required');
        }
        
        $slug = $this->utilityService->generateSlug($text);
        
        return $this->utilityService->createResponse(true, 'Slug generated successfully', [
            'slug' => $slug
        ]);
    }
    
    /**
     * Format currency (AJAX)
     */
    public function formatCurrency($request)
    {
        $amount = $request['post']['amount'] ?? 0;
        $currency = $request['post']['currency'] ?? 'INR';
        
        $formatted = $this->utilityService->formatCurrency($amount, $currency);
        
        return $this->utilityService->createResponse(true, 'Currency formatted successfully', [
            'formatted' => $formatted
        ]);
    }
    
    /**
     * Format date (AJAX)
     */
    public function formatDate($request)
    {
        $date = $request['post']['date'] ?? '';
        $format = $request['post']['format'] ?? 'Y-m-d H:i:s';
        
        $formatted = $this->utilityService->formatDate($date, $format);
        
        return $this->utilityService->createResponse(true, 'Date formatted successfully', [
            'formatted' => $formatted
        ]);
    }
    
    /**
     * Calculate time ago (AJAX)
     */
    public function timeAgo($request)
    {
        $datetime = $request['post']['datetime'] ?? '';
        
        if (empty($datetime)) {
            return $this->utilityService->createResponse(false, 'Datetime is required');
        }
        
        $timeAgo = $this->utilityService->timeAgo($datetime);
        
        return $this->utilityService->createResponse(true, 'Time ago calculated successfully', [
            'time_ago' => $timeAgo
        ]);
    }
    
    /**
     * Truncate text (AJAX)
     */
    public function truncateText($request)
    {
        $text = $request['post']['text'] ?? '';
        $length = intval($request['post']['length'] ?? 100);
        $suffix = $request['post']['suffix'] ?? '...';
        
        if (empty($text)) {
            return $this->utilityService->createResponse(false, 'Text is required');
        }
        
        $truncated = $this->utilityService->truncateText($text, $length, $suffix);
        
        return $this->utilityService->createResponse(true, 'Text truncated successfully', [
            'truncated' => $truncated
        ]);
    }
    
    /**
     * Generate random string (AJAX)
     */
    public function generateRandomString($request)
    {
        $length = intval($request['post']['length'] ?? 32);
        $type = $request['post']['type'] ?? 'alnum';
        
        $randomString = $this->utilityService->generateRandomString($length, $type);
        
        return $this->utilityService->createResponse(true, 'Random string generated successfully', [
            'random_string' => $randomString
        ]);
    }
    
    /**
     * Validate email (AJAX)
     */
    public function validateEmail($request)
    {
        $email = $request['post']['email'] ?? '';
        
        if (empty($email)) {
            return $this->utilityService->createResponse(false, 'Email is required');
        }
        
        $isValid = $this->utilityService->validateEmail($email);
        
        return $this->utilityService->createResponse(true, 'Email validation completed', [
            'is_valid' => $isValid
        ]);
    }
    
    /**
     * Validate phone (AJAX)
     */
    public function validatePhone($request)
    {
        $phone = $request['post']['phone'] ?? '';
        
        if (empty($phone)) {
            return $this->utilityService->createResponse(false, 'Phone number is required');
        }
        
        $isValid = $this->utilityService->validatePhone($phone);
        
        return $this->utilityService->createResponse(true, 'Phone validation completed', [
            'is_valid' => $isValid
        ]);
    }
    
    /**
     * Sanitize input (AJAX)
     */
    public function sanitizeInput($request)
    {
        $input = $request['post']['input'] ?? '';
        $type = $request['post']['type'] ?? 'string';
        
        if (empty($input)) {
            return $this->utilityService->createResponse(false, 'Input is required');
        }
        
        $sanitized = $this->utilityService->sanitizeInput($input, $type);
        
        return $this->utilityService->createResponse(true, 'Input sanitized successfully', [
            'sanitized' => $sanitized
        ]);
    }
    
    /**
     * Get client IP (AJAX)
     */
    public function getClientIp($request)
    {
        // Check authentication for admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return $this->utilityService->createResponse(false, 'Access denied');
        }
        
        $ip = $this->utilityService->getClientIp();
        
        return $this->utilityService->createResponse(true, 'Client IP retrieved successfully', [
            'ip' => $ip
        ]);
    }
    
    /**
     * Get user agent (AJAX)
     */
    public function getUserAgent($request)
    {
        // Check authentication for admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return $this->utilityService->createResponse(false, 'Access denied');
        }
        
        $userAgent = $this->utilityService->getUserAgent();
        
        return $this->utilityService->createResponse(true, 'User agent retrieved successfully', [
            'user_agent' => $userAgent
        ]);
    }
    
    /**
     * Check if AJAX request (AJAX)
     */
    public function isAjaxRequest($request)
    {
        $isAjax = $this->utilityService->isAjaxRequest();
        
        return $this->utilityService->createResponse(true, 'AJAX check completed', [
            'is_ajax' => $isAjax
        ]);
    }
    
    /**
     * Convert bytes to human readable (AJAX)
     */
    public function bytesToHuman($request)
    {
        $bytes = intval($request['post']['bytes'] ?? 0);
        $precision = intval($request['post']['precision'] ?? 2);
        
        $humanReadable = $this->utilityService->bytesToHuman($bytes, $precision);
        
        return $this->utilityService->createResponse(true, 'Bytes converted successfully', [
            'human_readable' => $humanReadable
        ]);
    }
    
    /**
     * Create pagination (AJAX)
     */
    public function createPagination($request)
    {
        $totalItems = intval($request['post']['total_items'] ?? 0);
        $itemsPerPage = intval($request['post']['items_per_page'] ?? 20);
        $currentPage = intval($request['post']['current_page'] ?? 1);
        
        if ($totalItems <= 0 || $itemsPerPage <= 0) {
            return $this->utilityService->createResponse(false, 'Invalid pagination parameters');
        }
        
        $pagination = $this->utilityService->createPagination($totalItems, $itemsPerPage, $currentPage);
        
        return $this->utilityService->createResponse(true, 'Pagination created successfully', [
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Generate CSRF token (AJAX)
     */
    public function generateCsrfToken($request)
    {
        $token = $this->utilityService->generateCsrfToken();
        
        return $this->utilityService->createResponse(true, 'CSRF token generated successfully', [
            'token' => $token
        ]);
    }
    
    /**
     * Validate CSRF token (AJAX)
     */
    public function validateCsrfToken($request)
    {
        $token = $request['post']['token'] ?? '';
        
        if (empty($token)) {
            return $this->utilityService->createResponse(false, 'Token is required');
        }
        
        $isValid = $this->utilityService->validateCsrfToken($token);
        
        return $this->utilityService->createResponse(true, 'CSRF token validation completed', [
            'is_valid' => $isValid
        ]);
    }
    
    /**
     * Get base URL (AJAX)
     */
    public function getBaseUrl($request)
    {
        $baseUrl = $this->utilityService->getBaseUrl();
        
        return $this->utilityService->createResponse(true, 'Base URL retrieved successfully', [
            'base_url' => $baseUrl
        ]);
    }
    
    /**
     * Show utilities dashboard
     */
    public function dashboard($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->utilityService->redirect('/login');
            return;
        }
        
        $data = [
            'title' => 'Utilities Dashboard - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];
        
        unset($_SESSION['success'], $_SESSION['errors']);
        
        return $this->viewRenderer->render('utilities/dashboard', $data);
    }
    
    /**
     * System information (AJAX)
     */
    public function getSystemInfo($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return $this->utilityService->createResponse(false, 'Access denied');
        }
        
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'current_timestamp' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'client_ip' => $this->utilityService->getClientIp(),
            'user_agent' => $this->utilityService->getUserAgent(),
            'is_ajax' => $this->utilityService->isAjaxRequest(),
            'base_url' => $this->utilityService->getBaseUrl()
        ];
        
        return $this->utilityService->createResponse(true, 'System information retrieved successfully', [
            'system_info' => $systemInfo
        ]);
    }
    
    /**
     * Test multiple utilities (AJAX)
     */
    public function testUtilities($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return $this->utilityService->createResponse(false, 'Access denied');
        }
        
        $tests = [];
        
        // Test slug generation
        $tests['slug'] = $this->utilityService->generateSlug('Test String for Slug Generation');
        
        // Test currency formatting
        $tests['currency'] = [
            'INR' => $this->utilityService->formatCurrency(1234567.89, 'INR'),
            'USD' => $this->utilityService->formatCurrency(1234567.89, 'USD'),
            'EUR' => $this->utilityService->formatCurrency(1234567.89, 'EUR')
        ];
        
        // Test date formatting
        $tests['date'] = $this->utilityService->formatDate(date('Y-m-d H:i:s'), 'd M Y h:i A');
        
        // Test time ago
        $tests['time_ago'] = $this->utilityService->timeAgo(date('Y-m-d H:i:s', strtotime('-2 hours')));
        
        // Test text truncation
        $tests['truncate'] = $this->utilityService->truncateText('This is a long text that should be truncated to demonstrate the functionality of the truncateText method in the UtilityService class.', 50);
        
        // Test random string generation
        $tests['random_string'] = [
            'alnum' => $this->utilityService->generateRandomString(16, 'alnum'),
            'alpha' => $this->utilityService->generateRandomString(16, 'alpha'),
            'numeric' => $this->utilityService->generateRandomString(16, 'numeric')
        ];
        
        // Test validation
        $tests['validation'] = [
            'email_valid' => $this->utilityService->validateEmail('test@example.com'),
            'email_invalid' => $this->utilityService->validateEmail('invalid-email'),
            'phone_valid' => $this->utilityService->validatePhone('+1234567890'),
            'phone_invalid' => $this->utilityService->validatePhone('123')
        ];
        
        // Test sanitization
        $tests['sanitization'] = [
            'string' => $this->utilityService->sanitizeInput('<script>alert("xss")</script>Test', 'string'),
            'email' => $this->utilityService->sanitizeInput('test@example.com', 'email'),
            'int' => $this->utilityService->sanitizeInput('123', 'int')
        ];
        
        // Test bytes conversion
        $tests['bytes'] = $this->utilityService->bytesToHuman(1234567890);
        
        // Test pagination
        $tests['pagination'] = $this->utilityService->createPagination(100, 10, 3);
        
        // Test CSRF
        $csrfToken = $this->utilityService->generateCsrfToken();
        $tests['csrf'] = [
            'token' => $csrfToken,
            'validated' => $this->utilityService->validateCsrfToken($csrfToken)
        ];
        
        return $this->utilityService->createResponse(true, 'Utilities test completed successfully', [
            'tests' => $tests
        ]);
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }
}