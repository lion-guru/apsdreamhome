<?php

namespace App\Controllers;

/**
 * APS Dream Home - AI Controller
 * MVC Integration for AI Chat System
 */

class AIController extends BaseController
{
    private $config;
    
    public function __construct()
    {
        parent::__construct();
        $this->config = require_once __DIR__ . '/../../config/gemini_config.php';
    }
    
    /**
     * AI Chat Page - Main Interface
     */
    public function chat()
    {
        $data = [
            'page_title' => 'AI Assistant - APS Dream Home',
            'page_description' => 'Professional AI Chat Assistant for Real Estate & Development',
            'current_user_role' => $this->getUserRole(),
            'user_name' => $this->getUserName(),
            'api_configured' => !empty($this->config['api_key'])
        ];
        
        $this->render('pages/ai_chat', $data);
    }
    
    /**
     * Enhanced AI Chat Page
     */
    public function chatEnhanced()
    {
        $data = [
            'page_title' => 'Enhanced AI Assistant - APS Dream Home',
            'page_description' => 'Role-based AI Assistant with Lead Management',
            'current_user_role' => $this->getUserRole(),
            'user_name' => $this->getUserName(),
            'available_roles' => $this->getAvailableRoles(),
            'api_configured' => !empty($this->config['api_key'])
        ];
        
        $this->render('pages/ai_chat_enhanced', $data);
    }
    
    /**
     * API Endpoint for AI Chat
     */
    public function apiChat()
    {
        // Set headers
        header('Content-Type: application/json');
        
        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['error' => 'Invalid request']);
            return;
        }
        
        // Forward to fixed backend
        $backend_url = __DIR__ . '/../../ai_backend_fixed.php';
        
        if (file_exists($backend_url)) {
            // Include the backend file
            $_POST = $input;
            include $backend_url;
        } else {
            echo json_encode(['error' => 'AI backend not found']);
        }
    }
    
    /**
     * AI Chat Popup (AJAX endpoint)
     */
    public function chatPopup()
    {
        $data = [
            'page_title' => 'AI Assistant',
            'popup_mode' => true,
            'user_role' => $this->getUserRole()
        ];
        
        // Render popup view
        $this->render('partials/ai_chat_popup', $data);
    }
    
    /**
     * Property-specific AI Chat
     */
    public function propertyChat($property_id = null)
    {
        $property_data = null;
        
        if ($property_id) {
            // Load property data from model
            $property_model = new \App\Models\Property();
            $property_data = $property_model->getPropertyById($property_id);
        }
        
        $data = [
            'page_title' => 'Property AI Assistant - APS Dream Home',
            'page_description' => 'AI Assistant for Property Information',
            'property' => $property_data,
            'user_role' => $this->getUserRole(),
            'context' => $property_data ? "Property ID: {$property_id}, Type: {$property_data['type']}, Location: {$property_data['location']}" : ''
        ];
        
        $this->render('pages/property_ai_chat', $data);
    }
    
    /**
     * Lead Management Integration
     */
    public function saveLead()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        // Add user context
        $input['created_by'] = $this->getUserId();
        $input['user_role'] = $this->getUserRole();
        
        // Forward to lead management
        $lead_file = __DIR__ . '/../../save_lead.php';
        
        if (file_exists($lead_file)) {
            $_POST = $input;
            include $lead_file;
        } else {
            echo json_encode(['success' => false, 'error' => 'Lead management not found']);
        }
    }
    
    /**
     * Get Lead Statistics
     */
    public function leadStats()
    {
        header('Content-Type: application/json');
        
        $lead_file = __DIR__ . '/../../get_lead_count.php';
        
        if (file_exists($lead_file)) {
            include $lead_file;
        } else {
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }
    
    /**
     * AI Configuration Page (Admin only)
     */
    public function configuration()
    {
        // Check if user has admin rights
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            return;
        }
        
        $data = [
            'page_title' => 'AI Configuration - APS Dream Home',
            'page_description' => 'Configure AI Assistant Settings',
            'api_configured' => !empty($this->config['api_key']),
            'api_key_status' => $this->checkAPIKeyStatus(),
            'usage_stats' => $this->getUsageStats()
        ];
        
        $this->render('admin/ai_configuration', $data);
    }
    
    /**
     * Test AI API Connection
     */
    public function testAPI()
    {
        header('Content-Type: application/json');
        
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        
        $test_message = "Hello! This is a test message. Please respond with 'API Test Successful!'";
        
        // Create test request
        $test_data = [
            'message' => $test_message,
            'role' => 'superadmin',
            'context' => 'API Connection Test'
        ];
        
        // Call backend
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/apsdreamhome/ai_backend_fixed.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            echo json_encode([
                'success' => true,
                'response' => $result,
                'message' => 'API connection successful!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'http_code' => $http_code,
                'response' => $response,
                'message' => 'API connection failed!'
            ]);
        }
    }
    
    /**
     * Get available roles for current user
     */
    private function getAvailableRoles()
    {
        $all_roles = [
            'director' => '👨‍💼 Director',
            'sales' => '💼 Sales Executive',
            'developer' => '👨‍💻 Developer',
            'bugfixer' => '🐛 Bug Fixer',
            'ithead' => '🖥️ IT Head',
            'superadmin' => '🔐 Super Admin',
            'customer' => '👤 Customer'
        ];
        
        // Filter roles based on user permissions
        $user_role = $this->getUserRole();
        
        if ($user_role === 'superadmin') {
            return $all_roles;
        } elseif ($user_role === 'director') {
            return array_intersect_key($all_roles, [
                'director' => true,
                'sales' => true,
                'customer' => true
            ]);
        } elseif ($user_role === 'developer') {
            return array_intersect_key($all_roles, [
                'developer' => true,
                'bugfixer' => true,
                'customer' => true
            ]);
        }
        
        // Default to customer role
        return ['customer' => $all_roles['customer']];
    }
    
    /**
     * Get current user role from session
     */
    private function getUserRole()
    {
        // This should be implemented based on your auth system
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'customer';
    }
    
    /**
     * Get current user ID
     */
    private function getUserId()
    {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    }
    
    /**
     * Get current user name
     */
    private function getUserName()
    {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
    }
    
    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        $admin_roles = ['superadmin', 'director', 'ithead'];
        return in_array($this->getUserRole(), $admin_roles);
    }
    
    /**
     * Check API key status
     */
    private function checkAPIKeyStatus()
    {
        if (empty($this->config['api_key'])) {
            return 'not_configured';
        }
        
        if ($this->config['api_key'] === 'YOUR_REAL_GEMINI_API_KEY_HERE') {
            return 'placeholder';
        }
        
        return 'configured';
    }
    
    /**
     * Get usage statistics
     */
    private function getUsageStats()
    {
        // This would typically come from a database or analytics service
        return [
            'total_requests' => 0,
            'today_requests' => 0,
            'rate_limit_hits' => 0,
            'cache_hits' => 0,
            'average_response_time' => 0
        ];
    }
}
?>
