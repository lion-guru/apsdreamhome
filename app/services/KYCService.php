<?php

namespace App\Services;

use App\Services\KYC\NSDLVerificationService;
use App\Services\KYC\UIDAIVerificationService;
use App\Traits\ServiceTenantTrait;

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
    use ServiceTenantTrait;

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
                SELECT * FROM kyc_requests WHERE user_id = ? {$this->tenantSql()} ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
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
                INSERT INTO kyc_verification_logs (type, identifier, success, message, ip_address, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ")->execute([
                $type,
                $this->maskIdentifier($type, $identifier),
                $success ? 1 : 0,
                $message,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $this->getTenantId(),
            ]);
        } catch (\Throwable $e) {
            // Log failure shouldn't break verification flow
            error_log("[KYCService] log failed: " . $e->getMessage());
        }
    }

    private function getTenantId(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }

    private function maskIdentifier(string $type, string $identifier): string
    {
        if ($type === 'pan') return substr($identifier, 0, 5) . '****' . substr($identifier, -1);
        return 'XXXXXXXX' . substr($identifier, -4);
    }

    /**
     * Cross-check OCR-extracted document fields against user-submitted KYC data.
     * Flags mismatches for manual review.
     *
     * @param int $kycRequestId
     * @return array {match: bool, mismatches: array, matches: array, ocr_docs: array, has_ocr: bool}
     */
    public function crossCheckOcr(int $kycRequestId): array
    {
        $kyc = $this->db->fetchOne(
            "SELECT * FROM kyc_requests WHERE id = ? {$this->tenantSql()}",
            array_merge([$kycRequestId], $this->tVal())
        );

        if (!$kyc) {
            return ['match' => false, 'error' => 'KYC request not found', 'mismatches' => [], 'matches' => [], 'ocr_docs' => []];
        }

        $userId = (int)$kyc['user_id'];

        $ocrDocs = $this->db->fetchAll(
            "SELECT * FROM ocr_documents WHERE uploaded_by = ? {$this->tenantSql()} ORDER BY created_at DESC",
            array_merge([$userId], $this->tVal())
        );

        if (empty($ocrDocs)) {
            return [
                'match' => true,
                'mismatches' => [],
                'matches' => [],
                'ocr_docs' => [],
                'has_ocr' => false,
                'message' => 'No OCR documents found for this user - skipping OCR cross-check'
            ];
        }

        $mismatches = [];
        $matches = [];
        $ocrDetails = [];

        foreach ($ocrDocs as $doc) {
            $docType = $doc['document_type'] ?? 'unknown';
            $extractedFields = $this->db->fetchAll(
                "SELECT * FROM ocr_extracted_fields WHERE ocr_document_id = :id",
                [':id' => $doc['id']]
            );

            $fields = [];
            foreach ($extractedFields as $f) {
                $fields[$f['field_name']] = $f['field_value'];
            }

            $ocrDetails[] = [
                'id' => $doc['id'],
                'document_type' => $docType,
                'file_name' => $doc['file_name'],
                'extracted_fields' => $fields,
                'confidence_score' => $doc['confidence_score'],
            ];

            if ($docType === 'pan' && !empty($fields['pan_number'])) {
                $ocrPan = strtoupper(preg_replace('/\s/', '', $fields['pan_number']));
                $submittedPan = strtoupper(trim($kyc['pan_number'] ?? ''));
                if ($ocrPan !== $submittedPan) {
                    $mismatches[] = [
                        'field' => 'pan_number',
                        'document' => $doc['file_name'],
                        'document_type' => $docType,
                        'submitted' => $submittedPan,
                        'ocr_extracted' => $ocrPan,
                    ];
                } else {
                    $matches[] = 'pan_number';
                }
            }

            if ($docType === 'aadhaar' && !empty($fields['aadhaar_number'])) {
                $ocrAadhaar = preg_replace('/\D/', '', $fields['aadhaar_number']);
                $submittedAadhaar = preg_replace('/\D/', '', $kyc['aadhaar_number'] ?? '');
                if ($ocrAadhaar !== $submittedAadhaar) {
                    $mismatches[] = [
                        'field' => 'aadhaar_number',
                        'document' => $doc['file_name'],
                        'document_type' => $docType,
                        'submitted' => $submittedAadhaar,
                        'ocr_extracted' => $ocrAadhaar,
                    ];
                } else {
                    $matches[] = 'aadhaar_number';
                }
            }

            if (!empty($fields['name']) && !empty($kyc['legal_name'])) {
                $ocrName = strtoupper(trim($fields['name']));
                $submittedName = strtoupper(trim($kyc['legal_name'] ?? ''));
                if ($ocrName !== $submittedName) {
                    $mismatches[] = [
                        'field' => 'legal_name',
                        'document' => $doc['file_name'],
                        'document_type' => $docType,
                        'submitted' => $submittedName,
                        'ocr_extracted' => $ocrName,
                    ];
                } else {
                    $matches[] = 'legal_name';
                }
            }

            if (!empty($fields['dob']) && !empty($kyc['dob'])) {
                $ocrDob = $fields['dob'];
                $submittedDob = $kyc['dob'];
                if ($ocrDob !== $submittedDob) {
                    $mismatches[] = [
                        'field' => 'dob',
                        'document' => $doc['file_name'],
                        'document_type' => $docType,
                        'submitted' => $submittedDob,
                        'ocr_extracted' => $ocrDob,
                    ];
                } else {
                    $matches[] = 'dob';
                }
            }
        }

        $hasMismatches = count($mismatches) > 0;

        return [
            'match' => !$hasMismatches && count($ocrDocs) > 0,
            'mismatches' => $mismatches,
            'matches' => $matches,
            'ocr_docs' => $ocrDetails,
            'has_ocr' => count($ocrDocs) > 0,
        ];
    }

    /**
     * Get PDO connection (lazy-loads from config)
     */
    private function getPdo()
    {
        try {
            return \App\Core\Database\Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            error_log("[KYCService] DB connection failed: " . $e->getMessage());
            return null;
        }
    }
}
