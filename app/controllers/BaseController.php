<?php
namespace App\Controllers;

use Exception;

class BaseController {
    protected $data = [];
    protected $models = [];
    protected $layout = 'layouts/base';

    public function __construct() {
        // Initialize model integration
        $this->loadModels();
    }

    /**
     * Load all available models for easy access
     */
    protected function loadModels() {
        $modelFiles = [
            'User' => '../models/User.php',
            'Property' => '../models/Property.php',
            'Associate' => '../models/Associate.php',
            'Customer' => '../models/Customer.php',
            'Payment' => '../models/Payment.php',
            'Project' => '../models/Project.php',
            'Farmer' => '../models/Farmer.php',
            'CRMLead' => '../models/CRMLead.php',
            'AssociateMLM' => '../models/AssociateMLM.php',
            'PropertyFavorite' => '../models/PropertyFavorite.php',
            'PropertyInquiry' => '../models/PropertyInquiry.php',
            'Admin' => '../models/Admin.php',
            'Employee' => '../models/Employee.php',
            'AIChatbot' => '../models/AIChatbot.php'
        ];

        foreach ($modelFiles as $modelName => $filePath) {
            $fullPath = __DIR__ . '/' . $filePath;
            if (file_exists($fullPath)) {
                $className = "App\\Models\\" . $modelName;
                if (class_exists($className)) {
                    $this->models[$modelName] = new $className();
                }
            }
        }
    }

    /**
     * Get model instance
     */
    protected function model($modelName) {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Render view with data
     */
    protected function render($view, $data = [], $layout = null) {
        $this->data = array_merge($this->data, $data);

        extract($this->data);

        if (!defined('BASE_URL')) {
            define('BASE_URL', $this->getBaseUrl());
        }
        if (!defined('ASSET_URL')) {
            define('ASSET_URL', BASE_URL . 'assets/');
        }
        if (!defined('APP_NAME')) {
            define('APP_NAME', 'APS Dream Home');
        }

        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            error_log("View not found: $viewPath");
            $this->renderError('View not found: ' . $view);
            return;
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        $layoutFile = $layout ?? $this->layout;
        if ($layoutFile) {
            $layoutPath = __DIR__ . '/../views/' . $layoutFile . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }

        echo $content;
    }

    /**
     * Render error page
     */
    protected function renderError($message) {
        http_response_code(500);
        include __DIR__ . '/../views/errors/500.php';
        exit;
    }

    /**
     * Get base URL
     */
    protected function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

        return $protocol . $host . $scriptDir . '/';
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        if (strpos($url, 'http') === 0) {
            header('Location: ' . $url);
        } else {
            header('Location: ' . $this->getBaseUrl() . ltrim($url, '/'));
        }
        exit;
    }

    /**
     * Send a 404 Not Found response
     */
    protected function notFound() {
        http_response_code(404);
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found']);
        } else {
            include __DIR__ . '/../views/errors/404.php';
        }
        exit;
    }

    /**
     * Require user to be logged in
     */
    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            } else {
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
                header('Location: ' . $this->getBaseUrl() . 'login');
                exit;
            }
        }
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Check if current user is associate
     */
    protected function isAssociate() {
        return isset($_SESSION['associate_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'associate');
    }

    /**
     * Get current user ID
     */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current associate ID
     */
    protected function getCurrentAssociateId() {
        return $_SESSION['associate_id'] ?? null;
    }

    /**
     * Send a 403 Forbidden response
     */
    protected function forbidden() {
        http_response_code(403);
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
        } else {
            include __DIR__ . '/../views/errors/403.php';
        }
        exit;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Set flash message for next request
     */
    protected function setFlashMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get flash message if exists
     */
    protected function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }

    /**
     * Generate CSRF token
     */
    protected function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File size too large'];
        }

        // Check file type
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }

        // Generate unique filename
        $newName = uniqid() . '.' . $fileType;
        $uploadPath = __DIR__ . '/../../uploads/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $newName,
                'path' => $uploadPath,
                'url' => BASE_URL . 'uploads/' . $newName
            ];
        }

        return ['success' => false, 'error' => 'Upload failed'];
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email address
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    protected function validatePhone($phone) {
        return preg_match('/^[0-9]{10,15}$/', $phone);
    }

    /**
     * Format currency
     */
    protected function formatCurrency($amount, $currency = '₹') {
        return $currency . number_format($amount, 0, '.', ',');
    }

    /**
     * Format date
     */
    protected function formatDate($date, $format = 'd M Y') {
        return date($format, strtotime($date));
    }

    /**
     * Get user IP address
     */
    protected function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Log user activity
     */
    protected function logActivity($action, $details = '') {
        $logData = [
            'user_id' => $this->getCurrentUserId(),
            'associate_id' => $this->getCurrentAssociateId(),
            'action' => $action,
            'details' => $details,
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Log to database if activity logging table exists
        try {
            global $pdo;
            if ($pdo) {
                $stmt = $pdo->prepare("
                    INSERT INTO user_activity_logs
                    (user_id, associate_id, action, details, ip_address, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $logData['user_id'],
                    $logData['associate_id'],
                    $logData['action'],
                    $logData['details'],
                    $logData['ip_address'],
                    $logData['user_agent'],
                    $logData['created_at']
                ]);
            }
        } catch (\Exception $e) {
            error_log('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if feature is enabled
     */
    protected function isFeatureEnabled($feature) {
        $enabledFeatures = [
            'mlm' => true,
            'ai_chatbot' => true,
            'payment_gateway' => true,
            'analytics' => true,
            'metaverse' => true,
            'quantum_computing' => true,
            'sustainability' => true,
            'blockchain' => true,
            'edge_computing' => true
        ];

        return isset($enabledFeatures[$feature]) && $enabledFeatures[$feature];
    }

    /**
     * Get system configuration
     */
    protected function getConfig($key, $default = null) {
        $config = [
            'app_name' => 'APS Dream Home',
            'app_version' => '2.0.0',
            'company_name' => 'APS Dream Home Pvt Ltd',
            'support_email' => 'support@apsdreamhome.com',
            'pagination_limit' => 12,
            'file_upload_max_size' => 5242880, // 5MB
            'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'session_timeout' => 3600 // 1 hour
        ];

        return $config[$key] ?? $default;
    }
}
