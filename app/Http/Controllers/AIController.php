<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminController;

/**
 * APS Dream Home - AI Controller
 * MVC Integration for AI Chat System
 */

class AIController extends AdminController
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

        // Use AIGateway for chat processing
        try {
            if (class_exists('App\Services\AI\AIGateway')) {
                $gateway = \App\Services\AI\AIGateway::getInstance();
                $message = $input['message'] ?? $input['query'] ?? '';
                $result = $gateway->process('chat', ['message' => $message], [
                    'user_id' => $this->getUserId(),
                    'user_role' => $input['context'] ?? 'general',
                ]);
                $response = $result['result']['text'] ?? $result['result']['parsed']['text'] ?? $result['result'] ?? 'No response';
                echo json_encode(['success' => true, 'response' => $response, 'engine' => $result['engine'] ?? 'unknown']);
            } else {
                echo json_encode(['error' => 'AI service not configured']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['error' => 'AI processing failed']);
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

        // Save lead to database — tenant-scoped (leads has tenant_id)
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = (int)$this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $tenantInsert = $tid > 1 ? [$tid] : [];
            $stmt = $db->prepare("INSERT INTO leads (name, email, phone, message, source, created_by, created_at{$tenantCol}) VALUES (?, ?, ?, ?, 'ai_chat', ?, NOW(){$tenantVal})");
            $stmt->execute(array_merge([
                $input['name'] ?? '',
                $input['email'] ?? '',
                $input['phone'] ?? '',
                $input['message'] ?? '',
                $input['created_by']
            ], $tenantInsert));
            echo json_encode(['success' => true, 'message' => 'Lead saved successfully']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to save lead']);
        }
    }

    /**
     * Get Lead Statistics
     */
    public function leadStats()
    {
        header('Content-Type: application/json');

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $count = $db->query("SELECT COUNT(*) FROM leads WHERE deleted_at IS NULL")->fetchColumn();
            echo json_encode(['success' => true, 'count' => (int)$count]);
        } catch (\Throwable $e) {
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
        $baseUrl = rtrim(BASE_URL, '/');
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/ai_backend_fixed.php');
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
     * Get user role from session
     */
    protected function getUserRole(): string
    {
        @session_start();
        if (isset($_SESSION['role'])) {
            return (string)$_SESSION['role'];
        }
        if (isset($_SESSION['admin_user_id']) || isset($_SESSION['admin_id'])) {
            return 'admin';
        }
        if (isset($_SESSION['user_id'])) {
            return 'customer';
        }
        return 'guest';
    }
    
    // senior-developer methods removed — dead stubs depending on missing SENIOR_DEVELOPER_WORKING.php

    /**
     * Get current user ID
     */
    protected function getUserId(): int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
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
    protected function isAdmin()
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

        if (strpos($this->config['api_key'], 'YOUR_') === 0) {
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

    /**
     * AI Property Valuation Page
     */
    public function propertyValuation()
    {
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        // Get existing properties for selection
        $sql = "SELECT p.id, p.title, p.location, p.price, p.area, 
                       p.type as property_type, p.bedrooms, p.bathrooms,
                       pi.image_path as primary_image
                FROM properties p
                LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
                WHERE p.status = 'available'
                ORDER BY p.created_at DESC
                LIMIT 50";
        $stmt = $pdo->query($sql);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        try {
            // Get recent valuations
            $sql = "SELECT pv.*, p.title as property_title
                    FROM property_valuations pv
                    LEFT JOIN properties p ON pv.property_id = p.id
                    ORDER BY pv.created_at DESC
                    LIMIT 10";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $pdo->query($sql);
        $recentValuations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get popular locations
        $sql = "SELECT DISTINCT location FROM properties GROUP BY location ORDER BY COUNT(*) DESC LIMIT 10";
        $stmt = $pdo->query($sql);
        $locations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $data = [
            'page_title' => 'AI Property Valuation - APS Dream Home',
            'properties' => $properties,
            'recent_valuations' => $recentValuations,
            'locations' => $locations,
            'property_types' => ['apartment', 'villa', 'plot', 'commercial', 'shop', 'office']
        ];

        $this->render('pages/ai-valuation', $data);
    }

    /**
     * Generate property valuation via API
     */
    public function apiValuation()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['location']) || empty($input['area_sqft']) || empty($input['property_type'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Missing required fields']);
            }

            $valuation = $this->calculateValuation($input);

            // Save to database
            $db = \App\Core\Database\Database::getInstance();
            $pdo = $db->getConnection();

            // property_valuations table does not exist in current schema — guarded; if present, would be tenant-scoped
            $tid = (int)$this->tenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $tenantInsert = $tid > 1 ? [$tid] : [];
            // Check table exists before insert to avoid 1146
            $tableExists = false;
            try { $pdo->query("SELECT 1 FROM property_valuations LIMIT 0"); $tableExists = true; } catch (\Throwable $e) { $tableExists = false; }
            if ($tableExists) {
                $sql = "INSERT INTO property_valuations 
                        (property_id, location, area_sqft, property_type, bedrooms, bathrooms,
                         estimated_price, price_per_sqft, confidence_score, similar_properties_count, created_at{$tenantCol}) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(){$tenantVal})";
                $stmt = $pdo->prepare($sql);
                $params = [
                    $input['property_id'] ?? null,
                    $input['location'],
                    $input['area_sqft'],
                    $input['property_type'],
                    $input['bedrooms'] ?? 0,
                    $input['bathrooms'] ?? 0,
                    $valuation['estimated_price'],
                    $valuation['price_per_sqft'],
                    $valuation['confidence_score'],
                    $valuation['similar_properties_count']
                ];
                if ($tid > 1) $params[] = $tid;
                $stmt->execute($params);
            }

            $valuationId = $pdo->lastInsertId();

            $this->jsonResponse([
                'success' => true,
                'valuation' => $valuation,
                'valuation_id' => $valuationId
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Calculate property valuation
     */
    private function calculateValuation($input)
    {
        // Base rates by location
        $locationRates = [
            'Gorakhpur' => 3500,
            'Lucknow' => 4500,
            'Noida' => 6000,
            'Delhi' => 8000,
            'Mumbai' => 15000,
            'Bangalore' => 7000,
            'Hyderabad' => 5500,
            'Chennai' => 5000,
            'Pune' => 6000,
            'Kolkata' => 4000,
            'default' => 3500
        ];

        $baseRate = $locationRates[$input['location']] ?? $locationRates['default'];

        // Type multipliers
        $typeMultipliers = [
            'apartment' => 1.0,
            'villa' => 1.3,
            'plot' => 0.8,
            'commercial' => 1.5,
            'shop' => 1.4,
            'office' => 1.2
        ];
        $typeMultiplier = $typeMultipliers[$input['property_type']] ?? 1.0;

        // Calculate price
        $area = floatval($input['area_sqft']);
        $bedroomPremium = ($input['bedrooms'] ?? 0) * 50000;
        $bathroomPremium = ($input['bathrooms'] ?? 0) * 30000;

        $basePrice = $area * $baseRate * $typeMultiplier;
        $estimatedPrice = $basePrice + $bedroomPremium + $bathroomPremium;

        // Find similar properties
        $db = \App\Core\Database\Database::getInstance();
        $pdo = $db->getConnection();

        $areaMin = $area * 0.8;
        $areaMax = $area * 1.2;

        $sql = "SELECT price FROM properties 
                WHERE location LIKE ? AND property_type = ?
                AND area_sqft BETWEEN ? AND ? AND status = 'available'
                LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['%' . $input['location'] . '%', $input['property_type'], $areaMin, $areaMax]);
        $similar = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $similarCount = count($similar);
        $similarAvg = $similarCount > 0 ? array_sum($similar) / $similarCount : 0;

        // Blend AI and market prices
        if ($similarAvg > 0) {
            $estimatedPrice = ($estimatedPrice * 0.6) + ($similarAvg * 0.4);
        }

        $pricePerSqft = $area > 0 ? round($estimatedPrice / $area, 2) : 0;
        $confidenceScore = min(95, 50 + ($similarCount * 5));

        return [
            'estimated_price' => round($estimatedPrice),
            'price_per_sqft' => $pricePerSqft,
            'price_range' => ['min' => round($estimatedPrice * 0.9), 'max' => round($estimatedPrice * 1.1)],
            'confidence_score' => $confidenceScore,
            'similar_properties_count' => $similarCount,
            'similar_properties_avg' => round($similarAvg)
        ];
    }
}
