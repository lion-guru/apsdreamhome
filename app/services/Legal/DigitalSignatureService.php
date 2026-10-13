<?php

namespace App\Services\Legal;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Color\Color;
use App\Core\Database\Database;

class DigitalSignatureService
{
    private $db;
    private $baseUrl;
    private $uploadDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome';
        $this->uploadDir = STORAGE_PATH . '/uploads/qrcodes/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Generate QR code for a document/deed
     * @param string $verificationUrl The URL to encode in QR code
     * @param string $labelText Optional label text below QR code
     * @return array ['success' => bool, 'file_path' => string, 'file_url' => string, 'qr_data' => string]
     */
    public function generateQrCode(string $verificationUrl, string $labelText = ''): array
    {
        try {
            $qrCode = new QrCode($verificationUrl);
            $qrCode->setSize(300);
            $qrCode->setMargin(10);
            $qrCode->setEncoding('UTF-8');
            $qrCode->setErrorCorrectionLevel('H');

            $writer = new PngWriter();
            
            $label = null;
            if ($labelText !== '') {
                $font = new NotoSans(14);
                $label = \Endroid\QrCode\Label\Label::create($labelText, $font, LabelAlignment::CENTER);
            }

            $result = $writer->write($qrCode, label: $label);
            
            $fileName = 'qrcode_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
            $filePath = $this->uploadDir . $fileName;
            
            $result->saveToFile($filePath);
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'file_url' => BASE_URL . '/storage/uploads/qrcodes/' . $fileName,
                'qr_data' => $verificationUrl,
                'file_name' => $fileName
            ];
        } catch (\Exception $e) {
            error_log("QR Code Generation Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate verification QR code for a printed deed
     * @param int $documentId The document/deed ID
     * @param string $documentType Type of document (registry_deed, cancellation_deed, etc.)
     * @return array
     */
    public function generateDeedVerificationQr(int $documentId, string $documentType): array
    {
        $verificationUrl = $this->baseUrl . "/verify/document/{$documentType}/{$documentId}";
        $labelText = "APS Dream Home - {$documentType} Verification";
        
        $qrResult = $this->generateQrCode($verificationUrl, $labelText);
        
        if (!$qrResult['success']) {
            return $qrResult;
        }
        
        // Store QR code reference in database
        try {
            $this->db->execute("
                INSERT INTO document_qr_codes 
                (document_id, document_type, qr_code_path, qr_code_url, verification_url, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ", [$documentId, $documentType, $qrResult['file_path'], $qrResult['file_url'], $verificationUrl]);
        } catch (\Exception $e) {
            error_log("QR Code DB Save Error: " . $e->getMessage());
        }
        
        return $qrResult;
    }

    /**
     * Verify a document via QR code scan
     * @param string $documentType The document type
     * @param int $documentId The document ID
     * @return array
     */
    public function verifyDocument(string $documentType, int $documentId): array
    {
        try {
            // Map document type to table
            $tableMap = [
                'registry_deed' => 'registry_documents',
                'cancellation_deed' => 'cancellation_documents',
                'title_certificate' => 'title_certificates',
                'possession_certificate' => 'possession_certificates',
                'allotment_letter' => 'allotment_documents',
            ];
            
            $table = $tableMap[$documentType] ?? 'registry_documents';
            
            $query = "SELECT * FROM {$table} WHERE id = ?";
            $document = $this->db->fetchOne($query, [$documentId]);
            
            if (!$document) {
                return [
                    'success' => false,
                    'verified' => false,
                    'message' => 'Document not found in system'
                ];
            }
            
            // Check document status
            $status = $document['status'] ?? 'unknown';
            $isVerified = in_array($status, ['verified', 'approved', 'active', 'registered']);
            
            return [
                'success' => true,
                'verified' => $isVerified,
                'document' => $document,
                'status' => $status,
                'message' => $isVerified ? 'Document verified successfully' : 'Document status: ' . $status
            ];
        } catch (\Exception $e) {
            error_log("Document Verification Error: " . $e->getMessage());
            return [
                'success' => false,
                'verified' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate QR code for stamp paper verification
     */
    public function generateStampPaperQr(int $stampCalcId): array
    {
        $verificationUrl = $this->baseUrl . "/verify/stamp-paper/{$stampCalcId}";
        $labelText = "APS Dream Home - Stamp Paper Verification";
        
        return $this->generateQrCode($verificationUrl, $labelText);
    }

    /**
     * Verify stamp paper
     */
    public function verifyStampPaper(int $stampCalcId): array
    {
        try {
            $query = "SELECT * FROM stamp_duty_calculations WHERE id = ?";
            $calc = $this->db->fetchOne($query, [$stampCalcId]);
            
            if (!$calc) {
                return [
                    'success' => false,
                    'verified' => false,
                    'message' => 'Stamp paper calculation not found'
                ];
            }
            
            $status = $calc['status'] ?? 'unknown';
            $isVerified = in_array($status, ['verified', 'approved', 'paid']);
            
            return [
                'success' => true,
                'verified' => $isVerified,
                'calculation' => $calc,
                'status' => $status,
                'message' => $isVerified ? 'Stamp paper verified' : 'Stamp paper status: ' . $status
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'verified' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create the document_qr_codes table if not exists
     */
    public function ensureQrCodeTable(): void
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS document_qr_codes (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                    document_id BIGINT UNSIGNED NOT NULL,
                    document_type VARCHAR(100) NOT NULL,
                    qr_code_path VARCHAR(500) NOT NULL,
                    qr_code_url VARCHAR(500) NOT NULL,
                    verification_url VARCHAR(500) NOT NULL,
                    scan_count INT UNSIGNED DEFAULT 0,
                    last_scanned_at DATETIME NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_document (document_id, document_type),
                    KEY idx_tenant (tenant_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ";
            
            $this->db->execute($sql);
        } catch (\Exception $e) {
            error_log("QR Code Table Creation Error: " . $e->getMessage());
        }
    }
}