<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class BaseApiController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
        // Rate limiting for API endpoints
        if (class_exists('\App\Middleware\TenantRateLimitMiddleware')) {
            \App\Middleware\TenantRateLimitMiddleware::check();
        }
        
        // Standard API headers
        \header('Content-Type: application/json');
        $this->handleCors();
    }

    /**
     * API controllers skip CSRF protection (use token/auth instead)
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Handle CORS for API requests
     */
    protected function handleCors()
    {
        \header("Access-Control-Allow-Origin: *");
        \header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        \header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-Key");

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            \header("HTTP/1.1 200 OK");
            exit();
        }
    }

    /**
     * Validate incoming JSON payload against a rules array
     */
    protected function validateApiPayload(array $rules): array
    {
        $input = \json_decode(\file_get_contents('php://input'), true) ?? $_POST;
        $validator = new \App\Services\ValidatorService();
        return $validator->validatePayloadSchema($input, $rules);
    }

    /**
     * Success JSON response
     */
    protected function jsonSuccess($data = [], string $message = 'Success', int $statusCode = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error JSON response
     */
    protected function jsonError(string $message = 'Error occurred', int $statusCode = 400, array $data = []): void
    {
        $this->jsonResponse([
            'success' => false,
            'error' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Require admin for API requests
     */
    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $this->jsonError('Unauthorized access. Admin privileges required.', 401);
            return;
        }
    }

    /**
     * Require login for API requests
     */
    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->jsonError('Authentication required.', 401);
            return;
        }
    }

    /**
     * Require associate for API requests
     */
    public function requireAssociate(): void
    {
        if (!$this->isAssociate()) {
            $this->jsonError('Unauthorized access. Associate privileges required.', 401);
            return;
        }
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
        } catch (\Throwable $e) {
            logger()->error("API Key Validation Error: " . $e->getMessage());
            return $this->jsonError('Internal server error during API key validation', 500);
        }
    }

    public function model($modelName)
    {
        if (!isset($this->models[$modelName])) {
            $class = "App\\Models\\{$modelName}";
            if (!class_exists($class)) {
                throw new \RuntimeException("Model not found: {$class}");
            }
            $this->models[$modelName] = new $class();
        }
        return $this->models[$modelName];
    }

    /**
     * Get merged request input (query + parsed body + raw JSON fallback).
     * The framework's createFromGlobals() may fail to parse the JSON body
     * when php://input is consumed upstream, so fall back to decoding it directly.
     */
    protected function getJsonInput(): array
    {
        $input = $this->request()->request->all();
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = array_merge($decoded, $input);
            }
        }
        return $input;
    }

    /**
     * Get a single request input value with JSON fallback.
     */
    protected function inputWithJson($key, $default = null)
    {
        $all = $this->getJsonInput();
        return $all[$key] ?? $default;
    }

    public function render(string $view, array $data = [], ?string $layout = null, bool $echo = true): void
    {
        // Define BASE_URL if not defined — auto-detect from request
        if (!defined('BASE_URL')) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            $scheme = $isHttps ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
            $basePath = preg_replace('#/public$#', '', str_replace('\\', '/', $scriptDir));
            define('BASE_URL', rtrim("$scheme://$host$basePath", '/'));
        }
        
        $data = array_merge($this->data, $data);
        $layout = $layout ?? $this->layout;

        // Use the fallback implementation since we don't have flash bag
        extract($data, EXTR_SKIP);

        $basePath = rtrim($this->getViewsBasePath(), '/\\') . '/';
        $viewPath = $basePath . ltrim(str_replace('\\', '/', $view), '/') . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$viewPath}");
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
            }
        }

        // End performance monitoring for non-layout renders
        $this->endPerformanceMonitoring();

        if ($echo) {
            echo $content;
        }
    }

    public function renderError(string $title, string $message, int $code = 500): void
    {
        $msg = $message ?? $title;
        $this->notFound($msg);
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

    protected function forbidden(string $message = 'Forbidden'): void
    {
        parent::forbidden($message);
    }

    public function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
