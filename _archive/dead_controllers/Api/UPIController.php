<?php

namespace App\Http\Controllers\Api;

use App\Services\Finance\UPIQRService;

class UPIController extends BaseApiController
{
    protected UPIQRService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UPIQRService();
    }

    /**
     * Generate UPI QR code for payment
     * POST /api/upi/generate
     * Body: {amount, booking_id, payment_type, note, payer_name, expires_in_hours}
     */
    public function generate()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        $amount = (float)($input['amount'] ?? 0);
        $bookingId = (int)($input['booking_id'] ?? 0);
        $paymentType = $input['payment_type'] ?? 'emi';
        $note = $input['note'] ?? 'Payment for APS Dream Home';
        $payerName = $input['payer_name'] ?? '';
        $expiresInHours = (int)($input['expires_in_hours'] ?? 24);

        if ($amount <= 0) {
            return $this->jsonError('Amount must be greater than 0');
        }

        if ($bookingId <= 0) {
            return $this->jsonError('Booking ID is required');
        }

        try {
            $result = $this->service->generate([
                'amount' => $amount,
                'booking_id' => $bookingId,
                'payment_type' => $paymentType,
                'note' => $note,
                'payer_name' => $payerName,
                'expires_in_hours' => $expiresInHours,
            ]);

            if (!empty($result['success'])) {
                return $this->jsonSuccess($result, 'UPI QR generated successfully');
            }

            return $this->jsonError($result['error'] ?? 'Failed to generate QR');
        } catch (\Exception $e) {
            error_log('[UPIController::generate] ' . $e->getMessage());
            return $this->jsonError('Generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get pending UPI payment for a booking
     * GET /api/upi/booking/{bookingId}?type=emi
     */
    public function getByBooking($bookingId)
    {
        $bookingId = (int)$bookingId;
        $paymentType = $_GET['type'] ?? 'emi';

        if ($bookingId <= 0) {
            return $this->jsonError('Invalid booking ID');
        }

        try {
            $payment = $this->service->getByBooking($bookingId, $paymentType);

            if ($payment) {
                return $this->jsonSuccess($payment, 'Payment link found');
            }

            return $this->jsonError('No pending payment found', 404);
        } catch (\Exception $e) {
            error_log('[UPIController::getByBooking] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch payment');
        }
    }

    /**
     * Verify UPI payment (webhook/callback)
     * POST /api/upi/verify
     * Body: {transaction_ref, status, ...webhook_data}
     */
    public function verify()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        $transactionRef = $input['transaction_ref'] ?? '';
        $status = $input['status'] ?? 'paid';

        if (!$transactionRef) {
            return $this->jsonError('Transaction reference is required');
        }

        try {
            $result = $this->service->verifyPayment($transactionRef, $input);

            if (!empty($result['success'])) {
                return $this->jsonSuccess($result['payment'] ?? [], $result['message'] ?? 'Payment verified');
            }

            return $this->jsonError($result['error'] ?? 'Verification failed');
        } catch (\Exception $e) {
            error_log('[UPIController::verify] ' . $e->getMessage());
            return $this->jsonError('Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all pending UPI payments for a booking
     * GET /api/upi/pending/{bookingId}
     */
    public function getPending($bookingId)
    {
        $bookingId = (int)$bookingId;

        if ($bookingId <= 0) {
            return $this->jsonError('Invalid booking ID');
        }

        try {
            $payments = $this->service->getPendingForBooking($bookingId);
            return $this->jsonSuccess($payments);
        } catch (\Exception $e) {
            error_log('[UPIController::getPending] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch pending payments');
        }
    }

    /**
     * Get UPI merchant info
     * GET /api/upi/merchant-info
     */
    public function getMerchantInfo()
    {
        try {
            // This would need a public method in service, for now return default
            return $this->jsonSuccess([
                'merchant_name' => 'APS Dream Home',
                'upi_id' => 'apsdreamhome@upi',
                'merchant_code' => 'APS001',
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to get config');
        }
    }
}