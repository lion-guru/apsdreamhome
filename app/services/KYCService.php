<?php

namespace App\Services;

use App\Services\KYC\NSDLVerificationService;
use App\Services\KYC\UIDAIVerificationService;

/**
 * KYC Service — orchestrator for PAN + Aadhaar verification
 * 
 * Flow:
 * 1. Format validation (regex + Verhoeff)
 * 2. Provider call (NSDL for PAN, UIDAI for Aadhaar)
 * 3. Log verification attempt to kyc_verification_logs table
 * 
 * Test modes:
 * - NSDL_TEST_MODE=true → mock PAN responses
 * - UIDAI_TEST_MODE=true → mock Aadhaar responses
 */
class KYCService
{
    private $nsdl;
    private $uidai;
    private $db;

    public function __construct($pdo = null)
    {
        $this->nsdl = new NSDLVerificationService();
        $this->uidai = new UIDAIVerificationService();
        $this->db = $pdo ?? $this->getPdo();
    }

    /**
     * Verify PAN Number
     * 
     * @param string $pan PAN number (10 chars)
     * @param string $name Full name as on PAN card
     * @return array {success, message, data}
     */
    public function verifyPAN(string $pan, string $name = ''): array
    {
        $pan = strtoupper(trim($pan));

        // Format validation
        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan)) {
            $this->logVerification('pan', $pan, false, 'Invalid PAN format');
            return [
                'success' => false,
                'message' => 'Invalid PAN format. Expected: AAAAA9999A',
                'data' => ['pan' => $pan, 'status' => 'INVALID_FORMAT'],
            ];
        }

        // 4th character must be a valid PAN type
        $validTypes = ['A', 'B', 'C', 'F', 'G', 'H', 'J', 'K', 'L', 'P', 'T'];
        if (!in_array($pan[3], $validTypes)) {
            $this->logVerification('pan', $pan, false, 'Invalid PAN type character');
            return [
                'success' => false,
                'message' => 'Invalid PAN type character at position 4',
                'data' => ['pan' => $pan, 'status' => 'INVALID_TYPE'],
            ];
        }

        // NSDL verification
        $result = $this->nsdl->verify($pan, $name);
        $this->logVerification('pan', $pan, $result['success'], $result['message'] ?? '');

        return $result;
    }

    /**
     * Verify Aadhaar Number
     * 
     * @param string $aadhaar 12-digit Aadhaar number
     * @param string $name Name as on Aadhaar
     * @param string|null $dob Date of birth (YYYY-MM-DD)
     * @return array {success, message, data}
     */
    public function verifyAadhaar(string $aadhaar, string $name = '', ?string $dob = null): array
    {
        $aadhaar = preg_replace('/\D/', '', $aadhaar);

        // Length check
        if (strlen($aadhaar) !== 12) {
            $this->logVerification('aadhaar', $aadhaar, false, 'Aadhaar must be 12 digits');
            return [
                'success' => false,
                'message' => 'Aadhaar must be exactly 12 digits',
                'data' => ['aadhaar' => $aadhaar, 'valid' => false],
            ];
        }

        // Verhoeff checksum validation
        if (!$this->uidai->verhoeffCheck($aadhaar)) {
            $this->logVerification('aadhaar', $aadhaar, false, 'Checksum failed');
            return [
                'success' => false,
                'message' => 'Invalid Aadhaar number (checksum mismatch)',
                'data' => ['aadhaar' => 'XXXXXXXX' . substr($aadhaar, -4), 'valid' => false],
            ];
        }

        // UIDAI verification
        $result = $this->uidai->verify($aadhaar, $name, $dob);
        $this->logVerification('aadhaar', $aadhaar, $result['success'], $result['message'] ?? '');

        return $result;
    }

    /**
     * Get verification status for a user
     */
    public function getVerificationStatus(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM kyc_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if user is fully KYC-verified (PAN + Aadhaar approved)
     */
    public function isFullyVerified(int $userId): bool
    {
        $status = $this->getVerificationStatus($userId);
        return !empty($status) && ($status['status'] ?? '') === 'approved';
    }

    /**
     * Log verification attempt
     */
    public function logVerification(string $type, string $identifier, bool $success, string $message): void
    {
        try {
            $this->db->prepare("
                INSERT INTO kyc_verification_logs (type, identifier, success, message, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ")->execute([
                $type,
                $this->maskIdentifier($type, $identifier),
                $success ? 1 : 0,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            // Log failure shouldn't break verification flow
            error_log("[KYCService] log failed: " . $e->getMessage());
        }
    }

    private function maskIdentifier(string $type, string $identifier): string
    {
        if ($type === 'pan') return substr($identifier, 0, 5) . '****' . substr($identifier, -1);
        return 'XXXXXXXX' . substr($identifier, -4);
    }

    /**
     * Get PDO connection (lazy-loads from config)
     */
    private function getPdo()
    {
        try {
            $config = require __DIR__ . '/../../config/database.php';
            return new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Exception $e) {
            error_log("[KYCService] DB connection failed: " . $e->getMessage());
            return null;
        }
    }
}
