<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\KYCService;

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
        try {
            $this->kycService = new KYCService();
        } catch (\Throwable $e) {
            error_log('KYCService init failed: ' . $e->getMessage());
            $this->kycService = null;
        }
    }

    /**
     * Verify PAN Number
     * POST /api/kyc/verify-pan
     */
    public function verifyPAN()
    {
        header('Content-Type: application/json');

        if (!$this->kycService) {
            return $this->jsonError('KYC service is not available', 503);
        }

        try {
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $pan = $this->request()->input('pan');
            $name = $this->request()->input('name', '');

            if (empty($pan)) {
                return $this->jsonError('PAN number is required', 400);
            }

            $pan = strtoupper(trim($pan));
            $result = $this->kycService->verifyPAN($pan, $name);
            $this->logKYCAttempt('pan', $pan, $result['success'] ?? false);

            if ($result['success']) {
                $this->saveVerificationLog('pan', $pan, true, $result['message'] ?? 'Verified');
                return $this->jsonSuccess(
                    $result['data'] ?? [],
                    $result['message'] ?? 'PAN verified successfully'
                );
            } else {
                $this->saveVerificationLog('pan', $pan, false, $result['message'] ?? 'Failed');
                return $this->jsonError(
                    $result['message'] ?? 'PAN verification failed',
                    400,
                    $result['data'] ?? []
                );
            }
        } catch (\Throwable $e) {
            error_log("KYC PAN Verification Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error during PAN verification', 'data' => []]);
            exit;
        }
    }

    /**
     * Verify Aadhaar Number
     * POST /api/kyc/verify-aadhaar
     */
    public function verifyAadhaar()
    {
        header('Content-Type: application/json');

        if (!$this->kycService) {
            return $this->jsonError('KYC service is not available', 503);
        }

        try {
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $aadhaar = $this->request()->input('aadhaar');

            if (empty($aadhaar)) {
                return $this->jsonError('Aadhaar number is required', 400);
            }

            $result = $this->kycService->verifyAadhaar($aadhaar);
            $this->logKYCAttempt('aadhaar', $aadhaar, $result['success'] ?? false);

            if ($result['success']) {
                $this->saveVerificationLog('aadhaar', $aadhaar, true, $result['message'] ?? 'Verified');
                return $this->jsonSuccess(
                    $result['data'] ?? [],
                    $result['message'] ?? 'Aadhaar verified successfully'
                );
            } else {
                $this->saveVerificationLog('aadhaar', $aadhaar, false, $result['message'] ?? 'Failed');
                return $this->jsonError(
                    $result['message'] ?? 'Aadhaar verification failed',
                    400,
                    $result['data'] ?? []
                );
            }
        } catch (\Throwable $e) {
            error_log("KYC Aadhaar Verification Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error during Aadhaar verification', 'data' => []]);
            exit;
        }
    }

    /**
     * Get KYC verification status for current user
     * GET /api/kyc/status
     */
    public function getStatus()
    {
        header('Content-Type: application/json');

        try {
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $userId = $this->getCurrentUserId();

            $db = \App\Core\Database\Database::getInstance();

            // Get latest KYC request from kyc_requests table
            $kyc = $db->fetchOne(
                "SELECT pan_number, aadhaar_number, legal_name, status, rejection_reason, verified_at, created_at
                 FROM kyc_requests WHERE user_id = ? ORDER BY created_at DESC",
                [$userId]
            );

            $panStatus = null;
            $aadhaarStatus = null;

            if ($kyc) {
                if (!empty($kyc['pan_number'])) {
                    $panStatus = [
                        'verified' => $kyc['status'] === 'approved',
                        'pan_number' => substr($kyc['pan_number'], 0, 4) . 'XXXX' . substr($kyc['pan_number'], -1),
                        'status' => $kyc['status'],
                        'verified_at' => $kyc['verified_at'] ?? null,
                    ];
                }
                if (!empty($kyc['aadhaar_number'])) {
                    $aadhaarStatus = [
                        'verified' => $kyc['status'] === 'approved',
                        'aadhaar_number' => 'XXXX XXXX ' . substr($kyc['aadhaar_number'], -4),
                        'status' => $kyc['status'],
                        'verified_at' => $kyc['verified_at'] ?? null,
                    ];
                }
            }

            return $this->jsonSuccess([
                'kyc_status' => $kyc['status'] ?? null,
                'pan' => $panStatus,
                'aadhaar' => $aadhaarStatus,
                'is_fully_verified' => ($kyc['status'] ?? '') === 'approved',
                'rejection_reason' => $kyc['rejection_reason'] ?? null,
                'submitted_at' => $kyc['created_at'] ?? null,
            ], 'KYC status retrieved successfully');
        } catch (\Throwable $e) {
            error_log("KYC Status Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error', 'data' => []]);
            exit;
        }
    }

    /**
     * Log KYC verification attempt
     */
    private function logKYCAttempt($type, $identifier, $success)
    {
        try {
            if ($this->kycService) {
                $this->kycService->logVerification($type, $identifier, $success, $success ? 'Passed' : 'Failed');
            }
        } catch (\Throwable $e) {
            error_log("KYC Log Attempt Error: " . $e->getMessage());
        }
    }

    /**
     * Save verification log to kyc_verification_logs table
     */
    private function saveVerificationLog($type, $identifier, $success, $message)
    {
        try {
            if ($this->kycService) {
                $this->kycService->logVerification($type, $identifier, $success, $message);
            }
        } catch (\Throwable $e) {
            error_log("KYC Save Log Error: " . $e->getMessage());
        }
    }

    /**
     * Send OTP to Aadhaar-linked mobile
     * POST /api/kyc/aadhaar/send-otp
     */
    public function sendAadhaarOtp()
    {
        header('Content-Type: application/json');

        try {
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $aadhaar = $this->request()->input('aadhaar');
            if (empty($aadhaar)) {
                return $this->jsonError('Aadhaar number is required', 400);
            }

            $uidai = new \App\Services\KYC\UIDAIVerificationService();
            $result = $uidai->sendOtp($aadhaar);

            if ($result['success']) {
                return $this->jsonSuccess($result['data'] ?? [], $result['message'] ?? 'OTP sent');
            }
            return $this->jsonError($result['message'] ?? 'Failed to send OTP', 400, $result['data'] ?? []);
        } catch (\Throwable $e) {
            error_log("KYC Send OTP Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error', 'data' => []]);
            exit;
        }
    }

    /**
     * Verify Aadhaar OTP
     * POST /api/kyc/aadhaar/verify-otp
     */
    public function verifyAadhaarOtp()
    {
        header('Content-Type: application/json');

        try {
            $authError = $this->requireLogin();
            if ($authError) return $authError;

            $aadhaar = $this->request()->input('aadhaar');
            $otp = $this->request()->input('otp');
            $txnId = $this->request()->input('transaction_id');

            if (empty($aadhaar) || empty($otp) || empty($txnId)) {
                return $this->jsonError('Aadhaar, OTP, and transaction_id are required', 400);
            }

            $uidai = new \App\Services\KYC\UIDAIVerificationService();
            $result = $uidai->verifyOtp($aadhaar, $otp, $txnId);
            $this->logKYCAttempt('aadhaar_otp', $aadhaar, $result['success'] ?? false);

            if ($result['success']) {
                $this->saveVerificationLog('aadhaar_otp', $aadhaar, true, $result['message'] ?? 'OTP verified');
                return $this->jsonSuccess($result['data'] ?? [], $result['message'] ?? 'OTP verified');
            }
            $this->saveVerificationLog('aadhaar_otp', $aadhaar, false, $result['message'] ?? 'OTP failed');
            return $this->jsonError($result['message'] ?? 'OTP verification failed', 400, $result['data'] ?? []);
        } catch (\Throwable $e) {
            error_log("KYC Verify OTP Error: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error', 'data' => []]);
            exit;
        }
    }
}
