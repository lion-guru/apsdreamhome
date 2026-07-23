<?php

namespace App\Http\Controllers\Api;

use App\Services\Legal\ESignService;

class ESignController extends BaseApiController
{
    protected ESignService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ESignService();
    }

    /**
     * Initiate eSign for a document
     * POST /api/esign/initiate
     * Body: {
     *   booking_id, document_id, document_type,
     *   signer_name, signer_aadhaar, signer_phone, signer_email,
     *   document_content, template_id
     * }
     */
    public function initiate()
    {
        $input = $this->getJsonInput();

        $required = ['signer_name', 'signer_aadhaar', 'signer_phone', 'document_content'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->jsonError("Missing required field: $field");
            }
        }

        try {
            $result = $this->service->initiateESign([
                'booking_id' => (int)($input['booking_id'] ?? 0),
                'document_id' => (int)($input['document_id'] ?? 0),
                'document_type' => $input['document_type'] ?? 'booking_agreement',
                'signer_name' => $input['signer_name'],
                'signer_aadhaar' => $input['signer_aadhaar'],
                'signer_phone' => $input['signer_phone'],
                'signer_email' => $input['signer_email'] ?? '',
                'document_content' => $input['document_content'],
                'template_id' => $input['template_id'] ?? '',
            ]);

            if ($result['success']) {
                return $this->jsonSuccess($result, 'eSign initiated successfully');
            }

            return $this->jsonError($result['error']);
        } catch (\Exception $e) {
            error_log('[ESignController::initiate] ' . $e->getMessage());
            return $this->jsonError('Failed to initiate eSign');
        }
    }

    /**
     * Verify OTP for eSign
     * POST /api/esign/verify-otp
     * Body: {transaction_id, otp}
     */
    public function verifyOtp()
    {
        $input = $this->getJsonInput();

        $transactionId = $input['transaction_id'] ?? '';
        $otp = $input['otp'] ?? '';

        if (!$transactionId || !$otp) {
            return $this->jsonError('Transaction ID and OTP are required');
        }

        try {
            $result = $this->service->verifyOtp($transactionId, $otp);

            if ($result['success']) {
                return $this->jsonSuccess($result, 'OTP verified successfully');
            }

            return $this->jsonError($result['error']);
        } catch (\Exception $e) {
            error_log('[ESignController::verifyOtp] ' . $e->getMessage());
            return $this->jsonError('OTP verification failed');
        }
    }

    /**
     * Get eSign transaction status
     * GET /api/esign/status/{transactionId}
     */
    public function getStatus($transactionId)
    {
        try {
            $result = $this->service->getTransactionStatus($transactionId);

            if ($result['success']) {
                return $this->jsonSuccess($result);
            }

            return $this->jsonError($result['error'], 404);
        } catch (\Exception $e) {
            error_log('[ESignController::getStatus] ' . $e->getMessage());
            return $this->jsonError('Failed to get status');
        }
    }

    /**
     * Get signed document
     * GET /api/esign/document/{transactionId}
     */
    public function getDocument($transactionId)
    {
        try {
            $result = $this->service->getSignedDocument($transactionId);

            if ($result['success']) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="signed_document_' . $transactionId . '.pdf"');
                echo $result['document'];
                exit;
            }

            return $this->jsonError($result['error'], 404);
        } catch (\Exception $e) {
            error_log('[ESignController::getDocument] ' . $e->getMessage());
            return $this->jsonError('Failed to get document');
        }
    }

    /**
     * List eSign transactions for a booking
     * GET /api/esign/booking/{bookingId}
     */
    public function getByBooking($bookingId)
    {
        $bookingId = (int)$bookingId;

        if ($bookingId <= 0) {
            return $this->jsonError('Invalid booking ID');
        }

        try {
            $result = $this->service->getByBooking($bookingId);
            return $this->jsonSuccess($result);
        } catch (\Exception $e) {
            error_log('[ESignController::getByBooking] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch transactions');
        }
    }

    /**
     * Callback endpoint for eSign provider
     * POST /api/esign/callback
     */
    public function callback()
    {
        $input = $this->getJsonInput();

        // Log callback for debugging
        error_log('[ESignController::callback] Received: ' . json_encode($input));

        // This would be called by the eSign provider (NSDL/CDSL) after signing
        // Implementation depends on provider's callback format
        
        return $this->jsonSuccess(['received' => true]);
    }
}