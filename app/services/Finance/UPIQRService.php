<?php

namespace App\Services\Finance;

use PDO;
use Exception;

class UPIQRService
{
    /** @var PDO */
    protected $db;

    /** @var string */
    protected $merchantName = 'APS Dream Home';
    
    /** @var string */
    protected $upiId = 'apsdreamhome@upi';
    
    /** @var string */
    protected $merchantCode = 'APS001';

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /**
     * Generate UPI payment link/QR
     * 
     * @param array $data {
     *     amount: float,
     *     booking_id: int,
     *     payment_type: string (emi, booking, down_payment, etc.),
     *     note: string,
     *     payer_name: string,
     *     expires_in_hours: int
     * }
     * @return array
     */
    public function generate(array $data): array
    {
        $amount = (float)($data['amount'] ?? 0);
        $bookingId = (int)($data['booking_id'] ?? 0);
        $paymentType = $data['payment_type'] ?? 'emi';
        $note = $data['note'] ?? 'Payment for APS Dream Home';
        $payerName = $data['payer_name'] ?? '';
        $expiresInHours = (int)($data['expires_in_hours'] ?? 24);

        if ($amount <= 0) {
            return ['success' => false, 'error' => 'Amount must be greater than 0'];
        }
        if ($bookingId <= 0) {
            return ['success' => false, 'error' => 'Booking ID is required'];
        }

        // Generate unique transaction reference
        $transactionRef = $this->generateTransactionRef($bookingId, $paymentType);

        // Build UPI intent URL
        $upiUrl = $this->buildUpiUrl($amount, $transactionRef, $note);

        // Generate QR code data (base64 PNG)
        $qrCodeBase64 = $this->generateQrCode($upiUrl);

        // Expiry time
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInHours} hours"));

        // Save to database if available
        $paymentId = $this->savePayment([
            'booking_id' => $bookingId,
            'transaction_ref' => $transactionRef,
            'amount' => $amount,
            'payment_type' => $paymentType,
            'upi_url' => $upiUrl,
            'qr_code' => $qrCodeBase64,
            'note' => $note,
            'payer_name' => $payerName,
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'transaction_ref' => $transactionRef,
            'amount' => $amount,
            'upi_url' => $upiUrl,
            'qr_code_base64' => $qrCodeBase64,
            'expires_at' => $expiresAt,
            'merchant_name' => $this->merchantName,
            'upi_id' => $this->upiId,
        ];
    }

    /**
     * Build UPI intent URL
     */
    protected function buildUpiUrl(float $amount, string $transactionRef, string $note): string
    {
        $params = [
            'pa' => $this->upiId,
            'pn' => $this->merchantName,
            'mc' => $this->merchantCode,
            'tid' => $transactionRef,
            'tr' => $transactionRef,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => $note,
        ];

        return 'upi://pay?' . http_build_query($params);
    }

    /**
     * Generate QR code as base64 PNG
     */
    protected function generateQrCode(string $upiUrl): string
    {
        // Try to use Endroid QrCode if available
        if (class_exists('Endroid\QrCode\QrCode')) {
            try {
                $qrCode = new \Endroid\QrCode\QrCode($upiUrl);
                $qrCode->setSize(300);
                $qrCode->setMargin(10);
                $qrCode->setEncoding('UTF-8');
                $qrCode->setErrorCorrectionLevel('M');
                
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                return 'data:image/png;base64,' . base64_encode($result->getString());
            } catch (Exception $e) {
                error_log('[UPIQRService] Endroid QrCode failed: ' . $e->getMessage());
            }
        }

        // Fallback: Generate QR via Google Charts API (for display purposes)
        $encodedUrl = urlencode($upiUrl);
        return 'data:image/png;base64,' . base64_encode(
            @file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$encodedUrl}&choe=UTF-8")
        ) ?: '';
    }

    /**
     * Generate unique transaction reference
     */
    protected function generateTransactionRef(int $bookingId, string $paymentType): string
    {
        $prefix = match ($paymentType) {
            'emi' => 'EMI',
            'booking' => 'BK',
            'down_payment' => 'DP',
            'registry' => 'RG',
            'penalty' => 'PN',
            default => 'PAY',
        };
        
        return $prefix . '-' . date('YmdHis') . '-' . $bookingId . '-' . random_int(1000, 9999);
    }

    /**
     * Save payment record
     */
    protected function savePayment(array $data): ?int
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO upi_payments 
                (booking_id, transaction_ref, amount, payment_type, upi_url, qr_code, note, payer_name, status, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ON DUPLICATE KEY UPDATE
                    upi_url = VALUES(upi_url),
                    qr_code = VALUES(qr_code),
                    status = 'pending',
                    expires_at = VALUES(expires_at)
            ");
            $stmt->execute([
                $data['booking_id'],
                $data['transaction_ref'],
                $data['amount'],
                $data['payment_type'],
                $data['upi_url'],
                $data['qr_code'],
                $data['note'] ?? '',
                $data['payer_name'] ?? '',
                $data['expires_at'],
            ]);

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('[UPIQRService::savePayment] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get pending payment for a booking
     */
    public function getByBooking(int $bookingId, string $paymentType = 'emi'): ?array
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM upi_payments 
                WHERE booking_id = ? AND payment_type = ? AND status = 'pending' 
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$bookingId, $paymentType]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[UPIQRService::getByBooking] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all pending payments for a booking
     */
    public function getPendingForBooking(int $bookingId): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM upi_payments 
                WHERE booking_id = ? AND status = 'pending' 
                AND (expires_at IS NULL OR expires_at > NOW())
                ORDER BY created_at DESC
            ");
            $stmt->execute([$bookingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[UPIQRService::getPendingForBooking] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verify payment via transaction reference
     */
    public function verifyPayment(string $transactionRef, array $webhookData = []): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            // Find payment by transaction reference
            $stmt = $this->db->prepare("
                SELECT * FROM upi_payments WHERE transaction_ref = ?
            ");
            $stmt->execute([$transactionRef]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            // Check if already paid
            if ($payment['status'] === 'paid') {
                return ['success' => true, 'message' => 'Payment already verified', 'payment' => $payment];
            }

            // Check expiry
            if ($payment['expires_at'] && strtotime($payment['expires_at']) < time()) {
                $this->updateStatus($payment['id'], 'expired');
                return ['success' => false, 'error' => 'Payment link expired'];
            }

            // Update status to paid
            $paidAt = date('Y-m-d H:i:s');
            $this->updateStatus($payment['id'], 'paid', $paidAt, json_encode($webhookData));

            return [
                'success' => true, 
                'message' => 'Payment verified successfully',
                'payment' => array_merge($payment, ['status' => 'paid', 'paid_at' => $paidAt]),
            ];
        } catch (Exception $e) {
            error_log('[UPIQRService::verifyPayment] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Verification failed'];
        }
    }

    /**
     * Update payment status
     */
    protected function updateStatus(int $paymentId, string $status, ?string $paidAt = null, ?string $webhookData = null): bool
    {
        if (!$this->db) return false;

        try {
            $sql = "UPDATE upi_payments SET status = ?";
            $params = [$status];
            
            if ($paidAt) {
                $sql .= ", paid_at = ?";
                $params[] = $paidAt;
            }
            if ($webhookData) {
                $sql .= ", webhook_data = ?";
                $params[] = $webhookData;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $paymentId;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log('[UPIQRService::updateStatus] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment by ID
     */
    public function getById(int $paymentId): ?array
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("SELECT * FROM upi_payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[UPIQRService::getById] ' . $e->getMessage());
            return null;
        }
    }
}