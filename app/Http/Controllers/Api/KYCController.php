<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\KYCService;
use App\Core\Database;

/**
 * KYC API Controller
 * Handles PAN and Aadhaar verification endpoints for mobile app
 */
class KYCController extends BaseApiController
{
    private $kycService;

    public function __construct()
    {
        parent::__construct();
        $this->kycService = new KYCService();
    }

    /**
     * Verify PAN Number
     * POST /api/kyc/verify-pan
     */
    public function verifyPAN()
    {
        try {
            // Require authentication
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            // Get input data
            $pan = $this->request()->input('pan');
            $name = $this->request()->input('name', '');

            // Validate input
            if (empty($pan)) {
                return $this->jsonError('PAN number is required', 400);
            }

            // Sanitize PAN
            $pan = strtoupper(trim($pan));

            // Call KYC service
            $result = $this->kycService->verifyPAN($pan, $name);

            // Log verification attempt
            $this->logKYCAttempt('pan', $pan, $result['success'] ?? false);

            if ($result['success']) {
                // Save verification record if successful
                $this->saveVerificationRecord('pan', $pan, $result['data'] ?? []);
                return $this->jsonSuccess(
                    $result['data'] ?? [],
                    $result['message'] ?? 'PAN verified successfully'
                );
            } else {
                return $this->jsonError(
                    $result['message'] ?? 'PAN verification failed',
                    400,
                    $result['data'] ?? []
                );
            }

        } catch (\Exception $e) {
            error_log("KYC PAN Verification Error: " . $e->getMessage());
            return $this->jsonError('Internal server error during PAN verification', 500);
        }
    }

    /**
     * Verify Aadhaar Number
     * POST /api/kyc/verify-aadhaar
     */
    public function verifyAadhaar()
    {
        try {
            // Require authentication
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            // Get input data
            $aadhaar = $this->request()->input('aadhaar');

            // Validate input
            if (empty($aadhaar)) {
                return $this->jsonError('Aadhaar number is required', 400);
            }

            // Call KYC service
            $result = $this->kycService->verifyAadhaar($aadhaar);

            // Log verification attempt
            $this->logKYCAttempt('aadhaar', $aadhaar, $result['success'] ?? false);

            if ($result['success']) {
                // Save verification record if successful
                $this->saveVerificationRecord('aadhaar', $aadhaar, $result['data'] ?? []);
                return $this->jsonSuccess(
                    $result['data'] ?? [],
                    $result['message'] ?? 'Aadhaar verified successfully'
                );
            } else {
                return $this->jsonError(
                    $result['message'] ?? 'Aadhaar verification failed',
                    400,
                    $result['data'] ?? []
                );
            }

        } catch (\Exception $e) {
            error_log("KYC Aadhaar Verification Error: " . $e->getMessage());
            return $this->jsonError('Internal server error during Aadhaar verification', 500);
        }
    }

    /**
     * Get KYC verification status for current user
     * GET /api/kyc/status
     */
    public function getStatus()
    {
        try {
            // Require authentication
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $userId = $this->getCurrentUserId();

            // Get verification records from database
            $db = Database::getInstance();
            $sql = "SELECT * FROM kyc_verifications WHERE user_id = :user_id ORDER BY created_at DESC";
            $records = $db->fetchAll($sql, ['user_id' => $userId]);

            // Get latest status for each type
            $panStatus = null;
            $aadhaarStatus = null;

            foreach ($records as $record) {
                if ($record['type'] === 'pan' && !$panStatus) {
                    $panStatus = [
                        'verified' => true,
                        'verified_at' => $record['created_at'],
                        'details' => json_decode($record['response_data'], true)
                    ];
                }
                if ($record['type'] === 'aadhaar' && !$aadhaarStatus) {
                    $aadhaarStatus = [
                        'verified' => true,
                        'verified_at' => $record['created_at'],
                        'details' => json_decode($record['response_data'], true)
                    ];
                }
            }

            return $this->jsonSuccess([
                'pan' => $panStatus,
                'aadhaar' => $aadhaarStatus,
                'is_fully_verified' => ($panStatus !== null && $aadhaarStatus !== null)
            ], 'KYC status retrieved successfully');

        } catch (\Exception $e) {
            error_log("KYC Status Error: " . $e->getMessage());
            return $this->jsonError('Internal server error', 500);
        }
    }

    /**
     * Log KYC verification attempt
     */
    private function logKYCAttempt($type, $identifier, $success)
    {
        try {
            $userId = $this->getCurrentUserId();
            error_log("KYC Attempt: Type={$type}, User={$userId}, Success={$success}");
        } catch (\Exception $e) {
            // Silent fail - don't break the flow
                    error_log("KYCController.php: " . $e->getMessage());
        }
    }

    /**
     * Save verification record to database
     */
    private function saveVerificationRecord($type, $identifier, $data)
    {
        try {
            $userId = $this->getCurrentUserId();
            if (!$userId) return;

            $db = \App\Core\Database\Database::getInstance();
            
            // Check if kyc_verifications table exists, if not create it
            $this->ensureKYCTableExists($db);

            try {
                $sql = "INSERT INTO kyc_verifications 
                        (user_id, type, identifier, response_data, status, created_at) 
                        VALUES 
                        (:user_id, :type, :identifier, :response_data, 'verified', NOW())";
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            
            $db->execute($sql, [
                'user_id' => $userId,
                'type' => $type,
                'identifier' => $this->maskIdentifier($type, $identifier),
                'response_data' => json_encode($data)
            ]);

        } catch (\Exception $e) {
            error_log("KYC Save Record Error: " . $e->getMessage());
            // Silent fail - don't break the verification flow
        }
    }

    /**
     * Ensure KYC verifications table exists
     */
    private function ensureKYCTableExists($db)
    {
        try {
            $sql = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $db->execute($sql);
        } catch (\Exception $e) {
            error_log("KYC Table Creation Error: " . $e->getMessage());
        }
    }

    /**
     * Mask identifier for privacy
     */
    private function maskIdentifier($type, $identifier)
    {
        if ($type === 'pan') {
            // Show last 4 characters of PAN
            return 'XXXXX' . substr($identifier, -5);
        } elseif ($type === 'aadhaar') {
            // Show last 4 digits of Aadhaar
            return 'XXXXXXXX' . substr($identifier, -4);
        }
        return $identifier;
    }
}
