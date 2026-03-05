<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Standard API headers
        \header('Content-Type: application/json');
        $this->handleCors();
    }

    /**
     * Handle CORS for API requests
     */
    protected function handleCors()
    {
        \header("Access-Control-Allow-Origin: *");
        \header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        \header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-Key");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            \header("HTTP/1.1 200 OK");
            exit();
        }
    }

    /**
     * Success JSON response
     */
    protected function jsonSuccess($data = [], $message = 'Success', $statusCode = 200)
    {
        return $this->jsonResponse([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error JSON response
     */
    protected function jsonError($message = 'Error occurred', $statusCode = 400, $data = [])
    {
        return $this->jsonResponse([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Require admin for API requests
     */
    public function requireAdmin()
    {
        if (!$this->isAdmin()) {
            return $this->jsonError('Unauthorized access. Admin privileges required.', 401);
        }
        return null;
    }

    /**
     * Require login for API requests
     */
    public function requireLogin($redirectTo = '/auth/login')
    {
        if (!$this->isLoggedIn()) {
            return $this->jsonError('Authentication required.', 401);
        }
        return null;
    }

    /**
     * Require associate for API requests
     */
    public function requireAssociate()
    {
        if (!$this->isAssociate()) {
            return $this->jsonError('Unauthorized access. Associate privileges required.', 401);
        }
        return null;
    }

    /**
     * Validate API key
     */
    protected function validateApiKey($required = true)
    {
        $apiKey = $this->request()->header('X-API-Key') ?: $this->request()->input('api_key');

        if (!$apiKey) {
            if ($required) {
                return $this->jsonError('API Key is missing', 401);
            }
            return null;
        }

        try {
            $db = \App\Core\App::database();
            $sql = "SELECT * FROM api_keys WHERE api_key = :api_key AND status = 'active' LIMIT 1";
            $keyData = $db->fetch($sql, ['api_key' => $apiKey]);

            if (!$keyData) {
                return $this->jsonError('Invalid or revoked API Key', 401);
            }

            // Update last used timestamp
            $db->execute("UPDATE api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = :id", ['id' => $keyData['id']]);

            // Store key data for later use if needed
            $this->request()->set('api_key_data', $keyData);

            return null;
        } catch (\Exception $e) {
            logger()->error("API Key Validation Error: " . $e->getMessage());
            return $this->jsonError('Internal server error during API key validation', 500);
        }
    }

    public function model($modelName)
    {
        return $this->models[$modelName] ?? null;
    }

    public function render($view, $data = [], $layout = null, $echo = true)
    {
        // Define BASE_URL if not defined
        if (!defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost/apsdreamhome/public');
        }
        
        $data = array_merge($this->data, $data);
        $layout = $layout ?? $this->layout;

        // Use the fallback implementation since we don't have flash bag
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
                // Pass content to layout - layout expects $content variable
                $data['content'] = $content;
                extract($data, EXTR_SKIP);
                ob_start();
                include $layoutPath;
                $output = ob_get_clean();

                // End performance monitoring before output
                $this->endPerformanceMonitoring();

                if ($echo) {
                    echo $output;
                }
                return $output;
            }
        }

        // End performance monitoring for non-layout renders
        $this->endPerformanceMonitoring();

        if ($echo) {
            echo $content;
        }
        return $content;
    }

    public function renderError($message)
    {
        return $this->notFound($message);
    }

    public function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . $host . $scriptDir . '/';
    }

    public function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getCurrentAssociateId()
    {
        return $_SESSION['associate_id'] ?? null;
    }

    public function forbidden($message = 'Forbidden')
    {
        return parent::forbidden($message);
    }

    public function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
