<?php

namespace App\Http\Controllers\Api;

use App\Services\DocumentEsignService;
use App\Core\Middleware\TenantContext;

class DocumentEsignApiController extends BaseApiController
{
    protected DocumentEsignService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DocumentEsignService();
    }

    /**
     * Create document e-sign request
     * POST /api/v2/mobile/document-esign/store
     * Body: {document_type, title, content}
     */
    public function store()
    {
        $input = $this->getJsonInput();
        
        $data = [
            'document_type' => $input['document_type'] ?? 'transaction',
            'title' => $input['title'] ?? '',
            'content' => $input['content'] ?? '',
            'created_by' => $this->getUserId(),
            'tenant_id' => TenantContext::getId(),
        ];

        $result = $this->service->createDocument($data);
        
        if ($result['success']) {
            return $this->jsonSuccess(['id' => $result['id']], 'Document created successfully');
        }

        return $this->jsonError($result['error'] ?? 'Failed to create document');
    }

    /**
     * Sign document with signature data
     * POST /api/v2/mobile/document-esign/sign/{id}
     * Body: {signature_data, verification_code}
     */
    public function sign($id)
    {
        $input = $this->getJsonInput();
        $data = [
            'signature_data' => $input['signature_data'] ?? null,
            'signed_by' => $this->getUserId(),
            'signed_at' => date('Y-m-d H:i:s'),
            'verification_code' => $input['verification_code'] ?? bin2hex(random_bytes(16)),
        ];

        $result = $this->service->signDocument((int)$id, $data);
        
        if ($result['success']) {
            return $this->jsonSuccess(null, 'Document signed successfully');
        }

        return $this->jsonError($result['error'] ?? 'Failed to sign document');
    }

    /**
     * Get document details
     * GET /api/v2/mobile/document-esign/{id}
     */
    public function getDocument($id)
    {
        $document = $this->service->getDocumentById((int)$id);
        
        if (!$document) {
            return $this->jsonError('Document not found', 404);
        }

        return $this->jsonSuccess($document);
    }

    /**
     * Get documents by tenant
     * GET /api/v2/mobile/document-esign
     */
    public function getDocuments()
    {
        $tenantId = TenantContext::getId();
        $documents = $this->service->getDocumentsByTenant($tenantId);
        
        return $this->jsonSuccess(['documents' => $documents]);
    }
}
