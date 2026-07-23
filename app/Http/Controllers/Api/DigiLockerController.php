<?php

namespace App\Http\Controllers\Api;

use App\Services\Communication\DigiLockerService;

class DigiLockerController extends BaseApiController
{
    protected DigiLockerService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DigiLockerService();
    }

    /**
     * Get authorization URL
     * GET /api/digilocker/auth-url
     */
    public function getAuthUrl()
    {
        $state = $_GET['state'] ?? '';
        
        $result = $this->service->getAuthUrl($state);
        
        if ($result['success']) {
            return $this->jsonSuccess($result);
        }
        
        return $this->jsonError($result['error']);
    }

    /**
     * Handle OAuth callback
     * GET /api/digilocker/callback?code=...&state=...
     */
    public function callback()
    {
        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';

        if (!$code || !$state) {
            return $this->jsonError('Missing code or state');
        }

        $result = $this->service->handleCallback($code, $state);
        
        if ($result['success']) {
            // Redirect to frontend with success
            $redirectUrl = '/user/profile?digilocker=success';
            header("Location: $redirectUrl");
            exit;
        }
        
        // Redirect to frontend with error
        $redirectUrl = '/user/profile?digilocker=error&msg=' . urlencode($result['error']);
        header("Location: $redirectUrl");
        exit;
    }

    /**
     * Get user's DigiLocker data
     * GET /api/digilocker/user-data
     */
    public function getUserData()
    {
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$userId) {
            return $this->jsonError('Unauthorized', 401);
        }

        $data = $this->service->getUserData($userId);
        
        if ($data) {
            return $this->jsonSuccess($data);
        }
        
        return $this->jsonError('No DigiLocker data found', 404);
    }

    /**
     * Initiate DigiLocker KYC for user
     * POST /api/digilocker/kyc/initiate
     */
    public function initiateKyc()
    {
        $userId = $_SESSION['user_id'] ?? 0;
        
        if (!$userId) {
            return $this->jsonError('Unauthorized', 401);
        }

        $result = $this->service->getAuthUrl('kyc_' . $userId);
        
        if ($result['success']) {
            return $this->jsonSuccess($result);
        }
        
        return $this->jsonError($result['error']);
    }
}