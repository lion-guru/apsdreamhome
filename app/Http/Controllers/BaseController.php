<?php

namespace App\Http\Controllers;

use App\Core\Controller as CoreController;
use Exception;

class BaseController extends CoreController
{
    protected $data = [];
    protected $models = [];
    protected $layout = 'layouts/base';
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance();
        $this->loadModels();
        // Ensure CSRF token is initialized
        $this->getCsrfToken();
    }

    /**
     * Validate CSRF token from request
     */
    public function validateCsrfToken($token = null): bool
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        return $this->verifyCsrfToken($token);
    }

    /**
     * CSRF token utilities
     */
    protected function getCsrfToken(): string
    {
        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->session->get('csrf_token');
    }

    protected function verifyCsrfToken(?string $token): bool
    {
        $storedToken = $this->session->get('csrf_token');
        return !empty($storedToken) && is_string($token) && hash_equals($storedToken, $token);
    }

    /**
     * Set flash message
     */
    protected function setFlash(string $type, string $message): void
    {
        $this->session->set($type, $message);
    }

    /**
     * Get flash message
     */
    protected function getFlash(string $type): ?string
    {
        if ($this->session->has($type)) {
            $message = $this->session->get($type);
            $this->session->remove($type);
            return $message;
        }
        return null;
    }

    /**
     * Require user to be logged in
     */
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Authentication required'], 401);
            } else {
                $this->session->set('redirect_url', $_SERVER['REQUEST_URI']);
                $this->redirect('login');
                return;
            }
        }
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        if ($this->session->has('user_id') || $this->session->has('auser') || $this->session->has('associate_id') || $this->session->has('admin_logged_in')) {
            return true;
        }

        if (isset($this->auth)) {
            if (method_exists($this->auth, 'isLoggedIn')) {
                return $this->auth->isLoggedIn();
            }
            if (method_exists($this->auth, 'check')) {
                return $this->auth->check();
            }
        }

        return false;
    }

    /**
     * Check if current user is an associate
     */
    public function isAssociateLoggedIn(): bool
    {
        return isset($_SESSION['associate_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'associate');
    }

    /**
     * Require user to be an admin
     */
    public function requireAdmin()
    {
        if (!$this->isAdmin()) {
            if ($this->isAjaxRequest()) {
                $this->json(['error' => 'Admin access required'], 403);
            } else {
                $this->forbidden();
            }
        }
    }

    /**
     * Load all available models for easy access
     */
    protected function loadModels()
    {
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

    protected function getViewsBasePath(): string
    {
        if (defined('APP_ROOT')) {
            return rtrim(APP_ROOT, '/\\') . '/app/views/';
        }

        return dirname(__DIR__, 2) . '/views/';
    }

    /**
     * Get model instance
     */
    public function model($modelName)
    {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Check if current user is an admin
     */
    public function isAdmin(): bool
    {
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }

        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return true;
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }

        // Fallback to auth instance if session role is not set
        if (isset($this->auth)) {
            if (method_exists($this->auth, 'isAdmin') && $this->auth->isAdmin()) {
                return true;
            }
            if (method_exists($this->auth, 'user')) {
                $user = $this->auth->user();
                return $user && isset($user->role) && $user->role === 'admin';
            }
        }

        return false;
    }

    /**
     * Redirect to a specific path
     */
    public function redirect($path, $statusCode = 302)
    {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    /**
     * Render view with data, falling back to legacy renderer when core session utilities are unavailable
     */
    public function render($view, $data = [], $layout = null)
    {
        $data = array_merge($this->data, $data);
        $this->data = $data;
        $layout = $layout ?? $this->layout;

        if ($this->session && method_exists($this->session, 'getFlashBag')) {
            // Add flash messages to all views
            $data['flash'] = $this->session->getFlashBag()->all();
            $this->session->getFlashBag()->clear(); // Clear flash messages after retrieving

            // Add auth and user to all views
            $output = parent::view($view, $data, $layout);
            echo $output;
            return $output;
        }

        extract($data, EXTR_SKIP);

        $basePath = rtrim($this->getViewsBasePath(), '/\\') . '/';
        $viewPath = $basePath . ltrim(str_replace('\\', '/', $view), '/') . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$viewPath}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layout) {
            $layoutPath = $basePath . ltrim(str_replace('\\', '/', $layout), '/') . '.php';
            if (file_exists($layoutPath)) {
                $content = $content ?? '';
                include $layoutPath;
                return;
            }
        }

        echo $content;
        return $content;
    }
    /**
     * Render error page
     */
    public function renderError($message)
    {
        return $this->notFound($message);
    }

    /**
     * Get base URL
     */
    public function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . $host . $scriptDir . '/';
    }

    /**
     * Check if current user is associate
     */
    public function isAssociate()
    {
        return isset($_SESSION['associate_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'associate');
    }

    /**
     * Get current user ID
     */
    public function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Send JSON response (wrapper for json())
     * 
     * @param mixed $data
     * @param int $statusCode
     */
    protected function jsonResponse($data, int $statusCode = 200)
    {
        return $this->json($data, $statusCode);
    }

    /**
     * Send JSON error response
     * 
     * @param string $message
     * @param int $statusCode
     */
    protected function jsonError(string $message, int $statusCode = 400)
    {
        return $this->json(['success' => false, 'error' => $message], $statusCode);
    }

    /**
     * Get current associate ID
     */
    public function getCurrentAssociateId()
    {
        return $_SESSION['associate_id'] ?? null;
    }

    /**
     * Send a 403 Forbidden response
     */
    public function forbidden($message = 'Forbidden')
    {
        return parent::forbidden($message);
    }

    /**
     * Check if request is AJAX
     */
    public function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Set flash message for next request
     */
    protected function setFlashMessage($type, $message)
    {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get flash message if exists
     */
    protected function getFlashMessage()
    {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload($file, $allowedTypes = [], $maxSize = 5242880)
    {
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
    protected function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email address
     */
    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    protected function validatePhone($phone)
    {
        return preg_match('/^[0-9]{10,15}$/', $phone);
    }

    /**
     * Format currency
     */
    protected function formatCurrency($amount, $currency = '₹')
    {
        return $currency . number_format($amount, 0, '.', ',');
    }

    /**
     * Format date
     */
    protected function formatDate($date, $format = 'd M Y')
    {
        return date($format, strtotime($date));
    }

    /**
     * Get user IP address
     */
    protected function getUserIP()
    {
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
    protected function logActivity($action, $details = '')
    {
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
            if ($this->db) {
                $stmt = $this->db->prepare("
                    INSERT INTO user_activity_logs 
                    (user_id, associate_id, action, details, ip_address, user_agent, created_at)
                    VALUES (:userId, :associateId, :action, :details, :ipAddress, :userAgent, :createdAt)
                ");
                $stmt->execute([
                    'userId' => $logData['user_id'],
                    'associateId' => $logData['associate_id'],
                    'action' => $logData['action'],
                    'details' => $logData['details'],
                    'ipAddress' => $logData['ip_address'],
                    'userAgent' => $logData['user_agent'],
                    'createdAt' => $logData['created_at']
                ]);
            }
        } catch (Exception $e) {
            error_log('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if feature is enabled
     */
    protected function isFeatureEnabled($feature)
    {
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
    protected function getConfig($key, $default = null)
    {
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
