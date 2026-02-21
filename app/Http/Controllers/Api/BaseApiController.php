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

        if ($this->request()->getMethod() === 'OPTIONS') {
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
}
