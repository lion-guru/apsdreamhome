<?php

namespace App\Services\AI;

use PDO;
use Exception;
use \App\Traits\ServiceTenantTrait;

class DocumentAIService
{
    use \App\Traits\ServiceTenantTrait;

    /** @var PDO */
    protected $db;

    /** @var array */
    protected $extractionEngines = [
        'google_document_ai' => 'Google Document AI',
        'azure_form_recognizer' => 'Azure Form Recognizer',
        'aws_textract' => 'AWS Textract',
        'tesseract_ocr' => 'Tesseract OCR',
        'custom_ml' => 'Custom ML Model',
        'mock' => 'Mock Engine (Testing)',
    ];

    /** @var array */
    protected $documentTypes = [
        'sale_deed',
        'agreement_to_sell',
        'gift_deed',
        'lease_deed',
        'mortgage_deed',
        'release_deed',
        'partition_deed',
        'power_of_attorney',
        'will',
        'court_order',
        'property_tax_receipt',
        'mutation_certificate',
        'encumbrance_certificate',
        'khata_certificate',
        'other',
    ];

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
     * Create a document extraction job
     * 
     * @param array $data {
     *     document_type: string,
     *     source_type: string,
     *     original_filename: string,
     *     file_path: string,
     *     file_url: string,
     *     mime_type: string,
     *     file_size: int,
     *     extraction_engine: string,
     *     created_by: int,
     * }
     * @return array
     */
    public function createExtractionJob(array $data): array
    {
        $required = ['document_type', 'original_filename'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Missing required field: $field"];
            }
        }

        if (!in_array($data['document_type'], $this->documentTypes)) {
            return ['success' => false, 'error' => 'Invalid document type'];
        }

        $engine = $data['extraction_engine'] ?? 'mock';
        if (!isset($this->extractionEngines[$engine])) {
            return ['success' => false, 'error' => 'Invalid extraction engine'];
        }

        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO document_extraction_jobs 
                (document_type, source_type, original_filename, file_path, file_url, mime_type, file_size,
                 extraction_engine, status, created_by, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'queued', ?, ?)
            ");
            $stmt->execute([
                $data['document_type'],
                $data['source_type'] ?? 'upload',
                $data['original_filename'],
                $data['file_path'] ?? null,
                $data['file_url'] ?? null,
                $data['mime_type'] ?? null,
                $data['file_size'] ?? 0,
                $engine,
                $data['created_by'] ?? null,
                json_encode($data['metadata'] ?? []),
            ]);

            $jobId = (int)$this->db->lastInsertId();

            // Queue for processing (in production, this would go to a queue worker)
            // For now, process immediately if mock engine
            if ($engine === 'mock') {
                $this->processJob($jobId);
            }

            return [
                'success' => true,
                'job_id' => $jobId,
                'status' => 'queued',
                'message' => 'Document extraction job created successfully',
            ];
        } catch (Exception $e) {
            error_log('[DocumentAIService::createExtractionJob] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create extraction job'];
        }
    }

    /**
     * Process an extraction job
     */
    public function processJob(int $jobId): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        $startTime = microtime(true);

        try {
            // Get job details
            $stmt = $this->db->prepare("SELECT * FROM document_extraction_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$job) {
                return ['success' => false, 'error' => 'Job not found'];
            }

            if ($job['status'] !== 'queued') {
                return ['success' => false, 'error' => 'Job already processed or processing'];
            }

            // Update status to processing
            $this->updateJobStatus($jobId, 'processing');

            // Extract data based on engine
            $engine = $job['extraction_engine'];
            $extractedData = $this->extractWithEngine($engine, $job);

            if (!empty($extractedData['success'])) {
                $processingTime = (int)((microtime(true) - $startTime) * 1000);
                
                // Update with extracted data
                $stmt = $this->db->prepare("
                    UPDATE document_extraction_jobs 
                    SET status = 'completed', 
                        extracted_data = ?, 
                        confidence_score = ?,
                        review_required = ?,
                        processing_time_ms = ?,
                        completed_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    json_encode($extractedData['data']),
                    $extractedData['confidence'] ?? 0.85,
                    $extractedData['review_required'] ?? 1,
                    $processingTime,
                    $jobId,
                ]);

                return [
                    'success' => true,
                    'job_id' => $jobId,
                    'status' => 'completed',
                    'extracted_data' => $extractedData['data'],
                    'confidence' => $extractedData['confidence'] ?? 0.85,
                ];
            } else {
                $this->updateJobStatus($jobId, 'failed', $extractedData['error'] ?? 'Extraction failed');
                return ['success' => false, 'error' => $extractedData['error'] ?? 'Extraction failed'];
            }
        } catch (Exception $e) {
            $this->updateJobStatus($jobId, 'failed', $e->getMessage());
            error_log('[DocumentAIService::processJob] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Extract data using specified engine
     */
    protected function extractWithEngine(string $engine, array $job): array
    {
        switch ($engine) {
            case 'mock':
                return $this->mockExtract($job);
            case 'tesseract_ocr':
                return $this->tesseractExtract($job);
            case 'google_document_ai':
                return $this->googleDocumentAIExtract($job);
            case 'azure_form_recognizer':
                return $this->azureFormRecognizerExtract($job);
            case 'aws_textract':
                return $this->awsTextractExtract($job);
            case 'custom_ml':
                return $this->customMLExtract($job);
            default:
                return ['success' => false, 'error' => 'Unknown engine: ' . $engine];
        }
    }

    /**
     * Mock extraction for testing - returns template-based data
     */
    protected function mockExtract(array $job): array
    {
        $docType = $job['document_type'];
        $template = $this->getFieldTemplate($docType);
        
        // Generate mock extracted data based on template
        $extractedData = [];
        if ($template) {
            foreach ($template as $field) {
                $extractedData[$field['field_name']] = $this->generateMockValue($field);
            }
        }

        return [
            'success' => true,
            'data' => $extractedData,
            'confidence' => 0.92,
            'review_required' => 1,
        ];
    }

    /**
     * Tesseract OCR extraction (placeholder)
     */
    protected function tesseractExtract(array $job): array
    {
        // In production: exec('tesseract ' . $job['file_path'] . ' stdout -l eng')
        return [
            'success' => false,
            'error' => 'Tesseract OCR not configured. Install tesseract-ocr and configure path.',
        ];
    }

    /**
     * Google Document AI extraction (placeholder)
     */
    protected function googleDocumentAIExtract(array $job): array
    {
        // In production: Use Google Cloud Document AI client library
        return [
            'success' => false,
            'error' => 'Google Document AI not configured. Set GOOGLE_APPLICATION_CREDENTIALS and processor ID.',
        ];
    }

    /**
     * Azure Form Recognizer extraction (placeholder)
     */
    protected function azureFormRecognizerExtract(array $job): array
    {
        // In production: Use Azure Form Recognizer SDK
        return [
            'success' => false,
            'error' => 'Azure Form Recognizer not configured. Set AZURE_FORM_RECOGNIZER_ENDPOINT and KEY.',
        ];
    }

    /**
     * AWS Textract extraction (placeholder)
     */
    protected function awsTextractExtract(array $job): array
    {
        // In production: Use AWS SDK for PHP Textract
        return [
            'success' => false,
            'error' => 'AWS Textract not configured. Set AWS credentials and region.',
        ];
    }

    /**
     * Custom ML model extraction (placeholder)
     */
    protected function customMLExtract(array $job): array
    {
        // In production: Call Python script or model API
        return [
            'success' => false,
            'error' => 'Custom ML model not configured.',
        ];
    }

    /**
     * Get field template for document type
     */
    protected function getFieldTemplate(string $docType): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM document_field_templates 
                WHERE document_type = ? AND is_active = 1
                ORDER BY field_order
            ");
            $stmt->execute([$docType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Generate mock value based on field type
     */
    protected function generateMockValue(array $field): mixed
    {
        $type = $field['field_type'] ?? 'text';
        
        return match ($type) {
            'name' => 'John ' . ['Smith', 'Doe', 'Johnson', 'Williams', 'Brown'][random_int(0, 4)],
            'aadhaar' => 'XXXX-XXXX-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'pan' => 'ABCDE' . random_int(1000, 9999) . 'F',
            'phone' => '+91 ' . random_int(7000000000, 9999999999),
            'email' => 'user' . random_int(100, 999) . '@example.com',
            'address' => random_int(1, 999) . ' ' . ['Main St', 'Park Ave', 'Oak Rd', 'Elm St', 'Gandhi Marg'][random_int(0, 4)] . ', ' . ['Gorakhpur', 'Lucknow', 'Delhi', 'Mumbai'][random_int(0, 3)],
            'pincode' => str_pad(random_int(110001, 999999), 6, '0', STR_PAD_LEFT),
            'date' => date('d/m/Y', strtotime('-' . random_int(0, 3650) . ' days')),
            'amount' => '₹' . number_format(random_int(100000, 50000000), 2),
            'number' => random_int(1, 1000),
            'area' => random_int(500, 5000) . ' sq ft',
            'rate' => '₹' . number_format(random_int(2000, 15000), 2) . '/sq ft',
            'text' => 'Sample extracted text for ' . $field['field_label'] ?? 'field',
            default => 'Mock value for ' . ($field['field_label'] ?? $field['field_name']),
        };
    }

    /**
     * Update job status
     */
    protected function updateJobStatus(int $jobId, string $status, string $error = null): void
    {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE document_extraction_jobs 
                SET status = ?, error_message = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $error, $jobId]);
        } catch (Exception $e) {
            error_log('[DocumentAIService::updateJobStatus] ' . $e->getMessage());
        }
    }

    /**
     * Get job details
     */
    public function getJob(int $jobId): ?array
    {
        if (!$this->db) return null;
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_extraction_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * List jobs with filters
     */
    public function listJobs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        if (!$this->db) return [];
        
        try {
            $where = [];
            $params = [];
            
            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['document_type'])) {
                $where[] = 'document_type = ?';
                $params[] = $filters['document_type'];
            }
            if (!empty($filters['engine'])) {
                $where[] = 'extraction_engine = ?';
                $params[] = $filters['engine'];
            }
            if (!empty($filters['created_by'])) {
                $where[] = 'created_by = ?';
                $params[] = $filters['created_by'];
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare("
                SELECT * FROM document_extraction_jobs 
                $whereClause
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Review and approve/correct extracted data
     */
    public function reviewJob(int $jobId, int $reviewerId, array $corrections, string $action): array
    {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM document_extraction_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$job) {
                return ['success' => false, 'error' => 'Job not found'];
            }
            
            if ($job['status'] !== 'completed') {
                return ['success' => false, 'error' => 'Job not ready for review'];
            }
            
            $extractedData = json_decode($job['extracted_data'] ?? '[]', true);
            
            // Apply corrections
            foreach ($corrections as $field => $value) {
                $extractedData[$field] = $value;
            }
            
            $newStatus = $action === 'approve' ? 'approved' : 'corrected';
            
            $stmt = $this->db->prepare("
                UPDATE document_extraction_jobs 
                SET status = ?, 
                    extracted_data = ?, 
                    review_required = 0,
                    reviewed_by = ?,
                    reviewed_at = NOW(),
                    review_notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $newStatus,
                json_encode($extractedData),
                $reviewerId,
                $action === 'correct' ? 'Corrected fields: ' . implode(', ', array_keys($corrections)) : 'Approved as-is',
                $jobId,
            ]);
            
            return [
                'success' => true,
                'status' => $newStatus,
                'message' => $action === 'approve' ? 'Job approved' : 'Job corrected and approved',
            ];
        } catch (Exception $e) {
            error_log('[DocumentAIService::reviewJob] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Review failed'];
        }
    }

    /**
     * Get field template for document type
     */
    public function getTemplate(string $docType): array
    {
        return $this->getFieldTemplate($docType);
    }

    /**
     * Get all available engines
     */
    public function getEngines(): array
    {
        return $this->extractionEngines;
    }

    /**
     * Get all document types
     */
    public function getDocumentTypes(): array
    {
        return $this->documentTypes;
    }
}