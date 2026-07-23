<?php

namespace App\Http\Controllers\Api;

use App\Services\AI\DocumentAIService;

class DocumentAIController extends BaseApiController
{
    protected DocumentAIService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DocumentAIService();
    }

    /**
     * Create document extraction job
     * POST /api/document-ai/extract
     * Body: {document_type, source_type, original_filename, file_path, file_url, mime_type, file_size, extraction_engine, created_by}
     */
    public function createJob()
    {
        $input = $this->getJsonInput();

        $result = $this->service->createExtractionJob($input);
        
        if ($result['success']) {
            return $this->jsonSuccess($result, 'Extraction job created');
        }
        
        return $this->jsonError($result['error']);
    }

    /**
     * Process extraction job
     * POST /api/document-ai/process/{jobId}
     */
    public function processJob($jobId)
    {
        $jobId = (int)$jobId;
        
        if ($jobId <= 0) {
            return $this->jsonError('Invalid job ID');
        }

        try {
            $result = $this->service->processJob($jobId);
            
            if ($result['success']) {
                return $this->jsonSuccess($result, 'Job processed successfully');
            }
            
            return $this->jsonError($result['error']);
        } catch (\Exception $e) {
            error_log('[DocumentAIController::processJob] ' . $e->getMessage());
            return $this->jsonError('Processing failed');
        }
    }

    /**
     * Get job status and results
     * GET /api/document-ai/job/{jobId}
     */
    public function getJob($jobId)
    {
        $jobId = (int)$jobId;
        
        if ($jobId <= 0) {
            return $this->jsonError('Invalid job ID');
        }

        try {
            $job = $this->service->getJob($jobId);
            
            if (!$job) {
                return $this->jsonError('Job not found', 404);
            }
            
            return $this->jsonSuccess($job);
        } catch (\Exception $e) {
            error_log('[DocumentAIController::getJob] ' . $e->getMessage());
            return $this->jsonError('Failed to get job');
        }
    }

    /**
     * List extraction jobs
     * GET /api/document-ai/jobs?status=&document_type=&engine=&limit=50&offset=0
     */
    public function listJobs()
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'document_type' => $_GET['document_type'] ?? '',
            'engine' => $_GET['engine'] ?? '',
            'created_by' => $_GET['created_by'] ?? '',
        ];
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        try {
            $jobs = $this->service->listJobs($filters, $limit, $offset);
            return $this->jsonSuccess(['jobs' => $jobs, 'limit' => $limit, 'offset' => $offset]);
        } catch (\Exception $e) {
            error_log('[DocumentAIController::listJobs] ' . $e->getMessage());
            return $this->jsonError('Failed to list jobs');
        }
    }

    /**
     * Review and approve/correct extracted data
     * POST /api/document-ai/review/{jobId}
     * Body: {action: "approve|correct", corrections: {"field": "value"}, reviewer_id}
     */
    public function reviewJob($jobId)
    {
        $jobId = (int)$jobId;
        
        if ($jobId <= 0) {
            return $this->jsonError('Invalid job ID');
        }

        $input = $this->getJsonInput();

        $action = $input['action'] ?? '';
        $corrections = $input['corrections'] ?? [];
        $reviewerId = (int)($input['reviewer_id'] ?? 0);

        if (!in_array($action, ['approve', 'correct'])) {
            return $this->jsonError('Action must be "approve" or "correct"');
        }

        if ($reviewerId <= 0) {
            return $this->jsonError('Reviewer ID is required');
        }

        try {
            $result = $this->service->reviewJob($jobId, $reviewerId, $corrections, $action);
            
            if ($result['success']) {
                return $this->jsonSuccess($result);
            }
            
            return $this->jsonError($result['error']);
        } catch (\Exception $e) {
            error_log('[DocumentAIController::reviewJob] ' . $e->getMessage());
            return $this->jsonError('Review failed');
        }
    }

    /**
     * Get field template for document type
     * GET /api/document-ai/template/{documentType}
     */
    public function getTemplate($documentType)
    {
        try {
            $template = $this->service->getTemplate($documentType);
            return $this->jsonSuccess(['template' => $template, 'document_type' => $documentType]);
        } catch (\Exception $e) {
            error_log('[DocumentAIController::getTemplate] ' . $e->getMessage());
            return $this->jsonError('Failed to get template');
        }
    }

    /**
     * Get available extraction engines
     * GET /api/document-ai/engines
     */
    public function getEngines()
    {
        try {
            $engines = $this->service->getEngines();
            return $this->jsonSuccess(['engines' => $engines]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to get engines');
        }
    }

    /**
     * Get supported document types
     * GET /api/document-ai/document-types
     */
    public function getDocumentTypes()
    {
        try {
            $types = $this->service->getDocumentTypes();
            return $this->jsonSuccess(['document_types' => $types]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to get document types');
        }
    }
}