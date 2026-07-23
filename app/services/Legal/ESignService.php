<?php

namespace App\Services\Legal;

use PDO;
use Exception;

class ESignService
{
    /** @var PDO */
    protected $db;

    /** @var array */
    protected $config = [];

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
        $this->loadConfig();
    }

    protected function loadConfig(): void
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->query("SELECT config_key, config_value FROM esign_config WHERE is_active = 1");
            $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $this->config = $configs;
        } catch (Exception $e) {
            error_log('[ESignService::loadConfig] ' . $e->getMessage());
        }
    }

    /**
     * Initiate eSign for a document
     * 
     * @param array $data {
     *     booking_id: int,
     *     document_id: int,
     *     document_type: string,
     *     signer_name: string,
     *     signer_aadhaar: string (12 digits),
     *     signer_phone: string,
     *     signer_email: string,
     *     document_content: string,
     *     template_id: string (optional)
     * }
     * @return array
     */
    public function initiateESign(array $data): array
    {
        $bookingId = (int)($data['booking_id'] ?? 0);
        $documentId = (int)($data['document_id'] ?? 0);
        $documentType = $data['document_type'] ?? 'booking_agreement';
        $signerName = $data['signer_name'] ?? '';
        $signerAadhaar = $data['signer_aadhaar'] ?? '';
        $signerPhone = $data['signer_phone'] ?? '';
        $signerEmail = $data['signer_email'] ?? '';
        $documentContent = $data['document_content'] ?? '';
        $templateId = $data['template_id'] ?? '';

        // Validate required fields
        if (!$signerName || !$signerAadhaar || !$signerPhone || !$documentContent) {
            return ['success' => false, 'error' => 'Missing required fields: signer_name, signer_aadhaar, signer_phone, document_content'];
        }

        // Validate Aadhaar format (12 digits)
        $aadhaarClean = preg_replace('/\D/', '', $signerAadhaar);
        if (strlen($aadhaarClean) !== 12) {
            return ['success' => false, 'error' => 'Invalid Aadhaar number. Must be 12 digits.'];
        }

        // Validate phone
        $phoneClean = preg_replace('/\D/', '', $signerPhone);
        if (strlen($phoneClean) < 10) {
            return ['success' => false, 'error' => 'Invalid phone number.'];
        }

        // Generate document hash
        $documentHash = hash('sha256', $documentContent);

        // Generate transaction reference
        $transactionId = $this->generateTransactionId($documentType);

        // Save transaction to database
        $transactionIdDb = $this->saveTransaction([
            'booking_id' => $bookingId,
            'document_id' => $documentId,
            'document_type' => $documentType,
            'transaction_id' => $this->generateTransactionId($documentType),
            'signer_name' => $signerName,
            'signer_aadhaar' => $this->maskAadhaar($aadhaarClean),
            'signer_phone' => $phoneClean,
            'signer_email' => $signerEmail,
            'esign_provider' => 'mock',
            'document_hash' => $documentHash,
            'document_content' => base64_encode($documentContent),
            'template_id' => $templateId,
            'status' => 'initiated',
            'expires_at' => date('Y-m-d H:i:s', time() + 1800), // 30 minutes
        ]);

        if (!$transactionIdDb) {
            return ['success' => false, 'error' => 'Failed to save transaction'];
        }

        // For mock provider, simulate the eSign flow
        // In production, this would call the actual eSign provider API (NSDL/CDSL/eMudhra)
        $result = $this->mockInitiateESign($transactionId, $signerPhone, $aadhaarClean);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'transaction_db_id' => $transactionIdDb,
            'status' => 'initiated',
            'otp_sent' => true,
            'otp_sent_to' => $this->maskPhone($phoneClean),
            'expires_at' => date('Y-m-d H:i:s', time() + 1800),
            'message' => 'eSign initiated. OTP sent to registered mobile number.',
        ];
    }

    /**
     * Verify OTP for eSign
     */
    public function verifyOtp(string $transactionId, string $otp): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM esign_transactions WHERE transaction_id = ? AND status IN ('initiated', 'pending_otp')
            ");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                return ['success' => false, 'error' => 'Transaction not found or expired'];
            }

            // Check expiry
            if (strtotime($transaction['expires_at']) < time()) {
                $this->updateStatus($transaction['id'], 'expired');
                return ['success' => false, 'error' => 'OTP expired. Please initiate eSign again.'];
            }

            // For mock, any 6-digit OTP works
            if (strlen($otp) !== 6 || !ctype_digit($otp)) {
                return ['success' => false, 'error' => 'Invalid OTP format. Enter 6-digit OTP.'];
            }

            // Update status to signed
            $this->updateStatus($transaction['id'], 'signed', date('Y-m-d H:i:s'), json_encode([
                'otp_verified_at' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]));

            // Generate signed document (mock)
            $signedDocument = $this->generateSignedDocument($transaction);

            // Update with signed document
            $this->updateSignedDocument($transaction['id'], $signedDocument);

            return [
                'success' => true,
                'message' => 'Document signed successfully',
                'transaction_id' => $transactionId,
                'signed_at' => date('Y-m-d H:i:s'),
                'document_url' => "/api/esign/document/{$transactionId}",
            ];
        } catch (Exception $e) {
            error_log('[ESignService::verifyOtp] ' . $e->getMessage());
            return ['success' => false, 'error' => 'OTP verification failed'];
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $transactionId): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM esign_transactions WHERE transaction_id = ?");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            return [
                'success' => true,
                'transaction' => [
                    'transaction_id' => $transaction['transaction_id'],
                    'booking_id' => $transaction['booking_id'],
                    'document_type' => $transaction['document_type'],
                    'signer_name' => $transaction['signer_name'],
                    'status' => $transaction['status'],
                    'created_at' => $transaction['created_at'],
                    'signed_at' => $transaction['signed_at'],
                    'expires_at' => $transaction['expires_at'],
                    'document_hash' => $transaction['document_hash'],
                ],
            ];
        } catch (Exception $e) {
            error_log('[ESignService::getTransactionStatus] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get status'];
        }
    }

    /**
     * Get signed document
     */
    public function getSignedDocument(string $transactionId): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM esign_transactions WHERE transaction_id = ? AND status = 'signed'
            ");
            $stmt->execute([$transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                return ['success' => false, 'error' => 'Signed document not found'];
            }

            // Generate PDF with signature
            $pdf = $this->generateSignedPdf($transaction);

            return [
                'success' => true,
                'document' => $pdf,
                'filename' => "signed_{$transaction['document_type']}_{$transactionId}.pdf",
            ];
        } catch (Exception $e) {
            error_log('[ESignService::getSignedDocument] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to get document'];
        }
    }

    /**
     * Get transactions by booking
     */
    public function getByBooking(int $bookingId): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM esign_transactions WHERE booking_id = ? ORDER BY created_at DESC
            ");
            $stmt->execute([$bookingId]);
            return ['success' => true, 'transactions' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) {
            error_log('[ESignService::getByBooking] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to fetch transactions'];
        }
    }

    // ========== Helper Methods ==========

    protected function generateTransactionId(string $documentType): string
    {
        $prefix = match ($documentType) {
            'booking_agreement' => 'BA',
            'sale_deed' => 'SD',
            'emi_agreement' => 'EA',
            'cancellation_agreement' => 'CA',
            'power_of_attorney' => 'PA',
            default => 'ES',
        };
        return $prefix . '-' . date('YmdHis') . '-' . random_int(1000, 9999);
    }

    protected function maskAadhaar(string $aadhaar): string
    {
        return 'XXXX-XXXX-' . substr($aadhaar, -4);
    }

    protected function maskPhone(string $phone): string
    {
        return substr($phone, 0, 2) . '*****' . substr($phone, -2);
    }

    protected function saveTransaction(array $data): int
    {
        if (!$this->db) return 0;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO esign_transactions 
                (booking_id, document_id, document_type, transaction_id, signer_name, signer_aadhaar, 
                 signer_phone, signer_email, esign_provider, document_hash, document_content, 
                 template_id, status, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['booking_id'] ?? null,
                $data['document_id'] ?? null,
                $data['document_type'],
                $data['transaction_id'],
                $data['signer_name'],
                $data['signer_aadhaar'],
                $data['signer_phone'],
                $data['signer_email'] ?? '',
                $data['esign_provider'] ?? 'mock',
                $data['document_hash'],
                $data['document_content'],
                $data['template_id'] ?? null,
                $data['status'],
                $data['expires_at'],
            ]);
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('[ESignService::saveTransaction] ' . $e->getMessage());
            return 0;
        }
    }

    protected function updateStatus(int $id, string $status, ?string $signedAt = null, ?string $responseData = null): bool
    {
        if (!$this->db) return false;

        try {
            $sql = "UPDATE esign_transactions SET status = ?";
            $params = [$status];

            if ($signedAt) {
                $sql .= ", signed_at = ?";
                $params[] = $signedAt;
            }
            if ($responseData) {
                $sql .= ", response_data = ?";
                $params[] = $responseData;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log('[ESignService::updateStatus] ' . $e->getMessage());
            return false;
        }
    }

    protected function updateSignedDocument(int $id, string $documentUrl): bool
    {
        if (!$this->db) return false;

        try {
            $stmt = $this->db->prepare("
                UPDATE esign_transactions SET signed_document_url = ? WHERE id = ?
            ");
            return $stmt->execute([$documentUrl, $id]);
        } catch (Exception $e) {
            error_log('[ESignService::updateSignedDocument] ' . $e->getMessage());
            return false;
        }
    }

    // ========== Mock Methods for Testing ==========

    protected function mockInitiateESign(string $transactionId, string $phone, string $aadhaar): array
    {
        // In production, this would call the actual eSign provider API
        // For now, we simulate the flow
        
        if (!$this->db) return ['success' => true];

        try {
            // Update status to pending_otp
            $stmt = $this->db->prepare("
                UPDATE esign_transactions SET status = 'pending_otp', otp_sent_at = NOW() 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$transactionId]);
        } catch (Exception $e) {
            error_log('[ESignService::mockInitiateESign] ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'OTP sent to registered mobile'];
    }

    protected function generateSignedDocument(array $transaction): string
    {
        $content = base64_decode($transaction['document_content'] ?? '');
        
        return $content . "\n\n--- DIGITALLY SIGNED ---\n"
            . "Signed by: {$transaction['signer_name']}\n"
            . "Aadhaar: {$transaction['signer_aadhaar']}\n"
            . "Transaction ID: {$transaction['transaction_id']}\n"
            . "Signed at: " . date('Y-m-d H:i:s') . "\n"
            . "Document Hash: {$transaction['document_hash']}\n";
    }

    protected function generateSignedPdf(array $transaction): string
    {
        $content = $this->generateSignedDocument($transaction);

        // Try to use Dompdf if available
        if (class_exists('Dompdf\Dompdf')) {
            $html = nl2br(htmlspecialchars($content));
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml("<pre>{$html}</pre>");
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        // Fallback: return as text with PDF header
        return "%PDF-1.4\n" . $content;
    }
}