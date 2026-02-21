<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * AI Document OCR Model
 * Handles document processing, text extraction, and data validation
 */
class OCR extends Model
{
    protected $table = 'ocr_documents';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const DOC_TYPE_AADHAR = 'aadhar';
    const DOC_TYPE_PAN = 'pan';
    const DOC_TYPE_PASSPORT = 'passport';
    const DOC_TYPE_DRIVING_LICENSE = 'driving_license';
    const DOC_TYPE_BANK_STATEMENT = 'bank_statement';
    const DOC_TYPE_SALARY_SLIP = 'salary_slip';
    const DOC_TYPE_INVOICE = 'invoice';
    const DOC_TYPE_RECEIPT = 'receipt';
    const DOC_TYPE_CONTRACT = 'contract';
    const DOC_TYPE_OTHER = 'other';

    /**
     * Process document for OCR
     */
    public function processDocument(int $documentId, string $filePath, string $documentType = null): array
    {
        // Create OCR document record
        $ocrId = $this->createOCRDocument($documentId, $filePath, $documentType);

        // Auto-classify document if type not provided
        if (!$documentType) {
            $documentType = $this->classifyDocument($filePath);
        }

        // Process document based on type
        $result = $this->performOCR($ocrId, $filePath, $documentType);

        return $result;
    }

    /**
     * Create OCR document record
     */
    private function createOCRDocument(int $documentId, string $filePath, string $documentType = null): int
    {
        $fileInfo = pathinfo($filePath);

        $ocrData = [
            'original_document_id' => $documentId,
            'file_path' => $filePath,
            'file_name' => $fileInfo['basename'],
            'file_size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'document_type' => $documentType ?? self::DOC_TYPE_OTHER,
            'ocr_status' => self::STATUS_PROCESSING,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($ocrData);
    }

    /**
     * Classify document type using AI
     */
    private function classifyDocument(string $filePath): string
    {
        // Simulate document classification based on file content patterns
        $content = $this->extractFileContent($filePath);
        $contentHash = hash('sha256', $content);

        // Check if we have classification history
        $existing = $this->query(
            "SELECT predicted_type FROM document_classification WHERE document_content_hash = ?",
            [$contentHash]
        )->fetch();

        if ($existing) {
            return $existing['predicted_type'];
        }

        // Perform classification based on content patterns
        $classification = $this->performDocumentClassification($content);

        // Store classification result
        $this->query(
            "INSERT INTO document_classification (document_content_hash, predicted_type, confidence_score)
             VALUES (?, ?, ?)",
            [$contentHash, $classification['type'], $classification['confidence']]
        );

        return $classification['type'];
    }

    /**
     * Perform document classification
     */
    private function performDocumentClassification(string $content): array
    {
        $content = strtolower($content);

        // Aadhaar Card patterns
        if (preg_match('/aadhaar|आधार|unique identification|uid/i', $content) &&
            preg_match('/\d{4}\s*\d{4}\s*\d{4}/', $content)) {
            return ['type' => self::DOC_TYPE_AADHAR, 'confidence' => 0.95];
        }

        // PAN Card patterns
        if (preg_match('/permanent account number|pan|income tax/i', $content) &&
            preg_match('/[A-Z]{5}[0-9]{4}[A-Z]/', $content)) {
            return ['type' => self::DOC_TYPE_PAN, 'confidence' => 0.95];
        }

        // Passport patterns
        if (preg_match('/passport|republic of india|type.*p/i', $content) &&
            preg_match('/[A-Z][0-9]{7}/', $content)) {
            return ['type' => self::DOC_TYPE_PASSPORT, 'confidence' => 0.90];
        }

        // Bank statement patterns
        if (preg_match('/bank statement|account statement|transaction history/i', $content) &&
            preg_match('/account.*number|balance|deposit|withdrawal/i', $content)) {
            return ['type' => self::DOC_TYPE_BANK_STATEMENT, 'confidence' => 0.85];
        }

        // Salary slip patterns
        if (preg_match('/salary slip|payslip|basic salary|hra|conveyance/i', $content)) {
            return ['type' => self::DOC_TYPE_SALARY_SLIP, 'confidence' => 0.85];
        }

        // Invoice patterns
        if (preg_match('/invoice.*number|bill.*to|total.*amount|tax.*invoice/i', $content)) {
            return ['type' => self::DOC_TYPE_INVOICE, 'confidence' => 0.80];
        }

        return ['type' => self::DOC_TYPE_OTHER, 'confidence' => 0.50];
    }

    /**
     * Perform OCR processing
     */
    private function performOCR(int $ocrId, string $filePath, string $documentType): array
    {
        $startTime = microtime(true);

        try {
            // Extract text content
            $extractedText = $this->extractFileContent($filePath);

            // Get OCR template for document type
            $template = $this->getOCRTemplate($documentType);

            // Extract structured data
            $structuredData = [];
            $confidenceScore = 0;
            $extractedFields = [];

            if ($template) {
                $extractionResult = $this->extractStructuredData($extractedText, $template);
                $structuredData = $extractionResult['data'];
                $confidenceScore = $extractionResult['confidence'];
                $extractedFields = $extractionResult['fields'];
            } else {
                // Basic text extraction for unknown document types
                $structuredData = ['raw_text' => $extractedText];
                $confidenceScore = 0.5;
            }

            $processingTime = microtime(true) - $startTime;

            // Update OCR document record
            $this->update($ocrId, [
                'ocr_status' => self::STATUS_COMPLETED,
                'processing_time' => round($processingTime, 2),
                'confidence_score' => $confidenceScore,
                'extracted_text' => $extractedText,
                'structured_data' => json_encode($structuredData),
                'processed_by' => 'APS_OCR_v1.0',
                'processed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Save extracted fields
            $this->saveExtractedFields($ocrId, $extractedFields);

            // Validate extracted data
            $validation = $this->validateExtractedData($ocrId, $structuredData, $documentType);
            $this->update($ocrId, [
                'validation_status' => $validation['status'],
                'validation_errors' => json_encode($validation['errors']),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'ocr_id' => $ocrId,
                'document_type' => $documentType,
                'confidence_score' => $confidenceScore,
                'structured_data' => $structuredData,
                'validation_status' => $validation['status']
            ];

        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;

            $this->update($ocrId, [
                'ocr_status' => self::STATUS_FAILED,
                'processing_time' => round($processingTime, 2),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => false,
                'ocr_id' => $ocrId,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract content from file
     */
    private function extractFileContent(string $filePath): string
    {
        $content = '';

        if (!file_exists($filePath)) {
            return '';
        }

        $mimeType = mime_content_type($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Handle different file types
        if ($mimeType === 'text/plain' || in_array($extension, ['txt', 'csv'])) {
            $content = file_get_contents($filePath);
        } elseif (in_array($extension, ['pdf'])) {
            // Simulate PDF text extraction
            $content = $this->extractPDFFallback($filePath);
        } elseif (in_array($extension, ['doc', 'docx'])) {
            // Simulate Word document text extraction
            $content = $this->extractWordFallback($filePath);
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // For images, we'd normally use OCR library
            // For now, return placeholder
            $content = "Image content - OCR would extract text from image here";
        } else {
            $content = "Unsupported file type: " . $mimeType;
        }

        return $content;
    }

    /**
     * Get OCR template for document type
     */
    private function getOCRTemplate(string $documentType): ?array
    {
        return $this->query(
            "SELECT * FROM ocr_templates WHERE document_type = ? AND is_active = 1 LIMIT 1",
            [$documentType]
        )->fetch();
    }

    /**
     * Extract structured data using template
     */
    private function extractStructuredData(string $text, array $template): array
    {
        $fieldMappings = json_decode($template['field_mappings'], true);
        $validationRules = json_decode($template['validation_rules'] ?? '{}', true);

        $extractedData = [];
        $extractedFields = [];
        $totalConfidence = 0;
        $fieldCount = 0;

        foreach ($fieldMappings as $fieldName => $rules) {
            $extractedValue = $this->extractFieldValue($text, $fieldName, $rules);
            $confidence = $this->calculateFieldConfidence($extractedValue, $rules);

            $extractedData[$fieldName] = $extractedValue;
            $extractedFields[] = [
                'field_name' => $fieldName,
                'field_value' => $extractedValue,
                'confidence_score' => $confidence,
                'validation_status' => 'valid'
            ];

            $totalConfidence += $confidence;
            $fieldCount++;
        }

        return [
            'data' => $extractedData,
            'fields' => $extractedFields,
            'confidence' => $fieldCount > 0 ? $totalConfidence / $fieldCount : 0
        ];
    }

    /**
     * Extract field value from text
     */
    private function extractFieldValue(string $text, string $fieldName, array $rules): string
    {
        $pattern = $rules['pattern'] ?? '';

        if (empty($pattern)) {
            // Fallback extraction based on field name
            return $this->extractFieldByName($text, $fieldName);
        }

        // Use regex pattern to extract value
        if (preg_match($pattern, $text, $matches)) {
            return $matches[1] ?? $matches[0];
        }

        return '';
    }

    /**
     * Extract field by name (fallback method)
     */
    private function extractFieldByName(string $text, string $fieldName): string
    {
        $text = strtolower($text);

        switch ($fieldName) {
            case 'name':
                // Look for name patterns
                if (preg_match('/name[:\s]+([A-Za-z\s]+)/i', $text, $matches)) {
                    return trim($matches[1]);
                }
                break;

            case 'aadhaar_number':
            case 'pan_number':
                // Look for ID numbers
                if (preg_match('/(\d{4}\s*\d{4}\s*\d{4})/', $text, $matches)) {
                    return $matches[1];
                }
                if (preg_match('/([A-Z]{5}[0-9]{4}[A-Z])/', $text, $matches)) {
                    return $matches[1];
                }
                break;

            case 'invoice_number':
                // Look for invoice numbers
                if (preg_match('/invoice.*?(?:number|#)?[:\s]+([A-Z0-9-]+)/i', $text, $matches)) {
                    return $matches[1];
                }
                break;

            case 'total_amount':
                // Look for total amounts
                if (preg_match('/total.*?(?:amount)?[:\s]+(?:rs\.?|₹|inr)?\s*([0-9,]+\.?\d*)/i', $text, $matches)) {
                    return str_replace(',', '', $matches[1]);
                }
                break;

            case 'date':
                // Look for dates
                if (preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{4})/', $text, $matches)) {
                    return $matches[1];
                }
                break;
        }

        return '';
    }

    /**
     * Calculate field extraction confidence
     */
    private function calculateFieldConfidence(string $value, array $rules): float
    {
        if (empty($value)) {
            return 0.0;
        }

        $confidence = 0.5; // Base confidence

        // Check pattern match
        if (!empty($rules['pattern']) && preg_match($rules['pattern'], $value)) {
            $confidence += 0.3;
        }

        // Check length constraints
        if (!empty($rules['exact_length']) && strlen($value) === $rules['exact_length']) {
            $confidence += 0.2;
        }

        return min(1.0, $confidence);
    }

    /**
     * Save extracted fields
     */
    private function saveExtractedFields(int $ocrId, array $fields): void
    {
        $db = Database::getInstance();

        foreach ($fields as $field) {
            $db->query(
                "INSERT INTO ocr_extracted_fields (ocr_document_id, field_name, field_value, confidence_score, validation_status)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $ocrId,
                    $field['field_name'],
                    $field['field_value'],
                    $field['confidence_score'],
                    $field['validation_status']
                ]
            );
        }
    }

    /**
     * Validate extracted data
     */
    private function validateExtractedData(int $ocrId, array $data, string $documentType): array
    {
        $errors = [];
        $isValid = true;

        // Document-specific validation
        switch ($documentType) {
            case self::DOC_TYPE_AADHAR:
                if (empty($data['aadhaar_number']) || !preg_match('/^\d{4}\s\d{4}\s\d{4}$/', $data['aadhaar_number'])) {
                    $errors[] = 'Invalid Aadhaar number format';
                    $isValid = false;
                }
                if (empty($data['name'])) {
                    $errors[] = 'Name is required';
                    $isValid = false;
                }
                break;

            case self::DOC_TYPE_PAN:
                if (empty($data['pan_number']) || !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $data['pan_number'])) {
                    $errors[] = 'Invalid PAN number format';
                    $isValid = false;
                }
                break;

            case self::DOC_TYPE_INVOICE:
                if (empty($data['invoice_number'])) {
                    $errors[] = 'Invoice number is required';
                    $isValid = false;
                }
                if (empty($data['total_amount']) || !is_numeric($data['total_amount'])) {
                    $errors[] = 'Valid total amount is required';
                    $isValid = false;
                }
                break;
        }

        return [
            'status' => $isValid ? 'valid' : (empty($errors) ? 'pending' : 'invalid'),
            'errors' => $errors
        ];
    }

    /**
     * Extract PDF content (fallback method)
     */
    private function extractPDFFallback(string $filePath): string
    {
        // In a real implementation, this would use a PDF parsing library
        // For now, return simulated content based on file name patterns
        $fileName = basename($filePath);

        if (stripos($fileName, 'aadhar') !== false) {
            return "Government of India\nUnique Identification Authority of India\nName: John Doe\nAadhaar Number: 1234 5678 9012\nAddress: 123 Main Street, New Delhi - 110001";
        }

        if (stripos($fileName, 'pan') !== false) {
            return "Income Tax Department\nPermanent Account Number\nName: John Doe\nPAN: ABCDE1234F\nDate of Birth: 01/01/1990";
        }

        if (stripos($fileName, 'invoice') !== false) {
            return "INVOICE\nInvoice Number: INV-2024-001\nDate: 15/01/2024\nTotal Amount: ₹50,000\nTax: ₹9,000";
        }

        return "PDF Document Content - " . $fileName;
    }

    /**
     * Extract Word document content (fallback method)
     */
    private function extractWordFallback(string $filePath): string
    {
        // Similar to PDF extraction, this would use a Word document parsing library
        return "Word Document Content - " . basename($filePath);
    }

    /**
     * Get OCR results for a document
     */
    public function getOCRResults(int $documentId): ?array
    {
        $ocrDoc = $this->query(
            "SELECT * FROM ocr_documents WHERE original_document_id = ? ORDER BY created_at DESC LIMIT 1",
            [$documentId]
        )->fetch();

        if (!$ocrDoc) {
            return null;
        }

        // Get extracted fields
        $fields = $this->query(
            "SELECT * FROM ocr_extracted_fields WHERE ocr_document_id = ? ORDER BY field_name",
            [$ocrDoc['id']]
        )->fetchAll();

        $ocrDoc['extracted_fields'] = $fields;
        $ocrDoc['structured_data'] = json_decode($ocrDoc['structured_data'], true);
        $ocrDoc['validation_errors'] = json_decode($ocrDoc['validation_errors'], true);

        return $ocrDoc;
    }

    /**
     * Update extracted field value
     */
    public function updateExtractedField(int $fieldId, string $correctedValue, int $userId): array
    {
        $field = $this->query("SELECT * FROM ocr_extracted_fields WHERE id = ?", [$fieldId])->fetch();

        if (!$field) {
            return ['success' => false, 'message' => 'Field not found'];
        }

        $this->query(
            "UPDATE ocr_extracted_fields SET
             corrected_value = ?,
             corrected_by = ?,
             corrected_at = ?
             WHERE id = ?",
            [$correctedValue, $userId, date('Y-m-d H:i:s'), $fieldId]
        );

        // Update OCR document structured data
        $ocrDoc = $this->find($field['ocr_document_id']);
        if ($ocrDoc) {
            $structuredData = json_decode($ocrDoc['structured_data'], true);
            $structuredData[$field['field_name']] = $correctedValue;

            $this->update($ocrDoc['id'], [
                'structured_data' => json_encode($structuredData),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return ['success' => true, 'message' => 'Field updated successfully'];
    }

    /**
     * Get OCR statistics
     */
    public function getOCRStatistics(): array
    {
        $stats = $this->query(
            "SELECT
                COUNT(*) as total_documents,
                SUM(CASE WHEN ocr_status = 'completed' THEN 1 ELSE 0 END) as processed_documents,
                SUM(CASE WHEN validation_status = 'valid' THEN 1 ELSE 0 END) as valid_documents,
                AVG(confidence_score) as avg_confidence,
                AVG(processing_time) as avg_processing_time
             FROM ocr_documents"
        )->fetch();

        // Get document type breakdown
        $typeStats = $this->query(
            "SELECT document_type, COUNT(*) as count
             FROM ocr_documents
             WHERE ocr_status = 'completed'
             GROUP BY document_type
             ORDER BY count DESC"
        )->fetchAll();

        return [
            'total_documents' => (int)($stats['total_documents'] ?? 0),
            'processed_documents' => (int)($stats['processed_documents'] ?? 0),
            'valid_documents' => (int)($stats['valid_documents'] ?? 0),
            'avg_confidence' => round((float)($stats['avg_confidence'] ?? 0), 4),
            'avg_processing_time' => round((float)($stats['avg_processing_time'] ?? 0), 2),
            'document_types' => $typeStats
        ];
    }

    /**
     * Reprocess failed OCR documents
     */
    public function reprocessFailedDocuments(): array
    {
        $failedDocs = $this->query(
            "SELECT * FROM ocr_documents WHERE ocr_status = 'failed' LIMIT 10"
        )->fetchAll();

        $reprocessed = 0;
        foreach ($failedDocs as $doc) {
            if (file_exists($doc['file_path'])) {
                $result = $this->performOCR($doc['id'], $doc['file_path'], $doc['document_type']);
                if ($result['success']) {
                    $reprocessed++;
                }
            }
        }

        return [
            'success' => true,
            'reprocessed_count' => $reprocessed,
            'message' => "Reprocessed {$reprocessed} documents"
        ];
    }
}
