<?php

namespace App\Http\Controllers\Admin;

use App\Services\DocumentEsignService;
use App\Services\DocumentService;

class DocumentEsignController extends AdminController
{
    protected DocumentEsignService $service;
    protected DocumentService $docService;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DocumentEsignService();
        $this->docService = new DocumentService();
    }

    public function index()
    {
        $this->requireAdmin();
        $tid = $this->tenantId();
        $documents = $this->service->getDocumentsByTenant($tid);
        $this->render('admin/document_esign/index', [
            'page_title' => 'Document E-Sign',
            'documents' => $documents
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $tid = $this->tenantId();
        $documents = $this->docService->getDocumentsByTenant($tid);
        $this->render('admin/document_esign/create', [
            'page_title' => 'Create Document E-Sign',
            'documents' => $documents
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $input = $this->getPostInput();
        $this->validateCsrfOrFail();

        $data = [
            'document_type' => $input['document_type'] ?? 'transaction',
            'title' => $input['title'] ?? '',
            'content' => $input['content'] ?? '',
            'status' => 'pending',
            'created_by' => $this->getAdminId(),
        ];

        $result = $this->service->createDocument($data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Document created successfully');
            return $this->redirect("/admin/document-esign/{$result['id']}");
        }

        $this->setFlash('error', $result['error'] ?? 'Failed to create document');
        return $this->redirect('/admin/document-esign');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $document = $this->service->getDocumentById((int)$id);
        
        if (!$document) {
            $this->setFlash('error', 'Document not found');
            return $this->redirect('/admin/document-esign');
        }

        $this->render('admin/document_esign/show', [
            'page_title' => 'Document Details',
            'document' => $document
        ]);
    }

    public function sign($id)
    {
        $this->requireAdmin();
        $input = $this->getPostInput();
        $this->validateCsrfOrFail();

        $signatureData = $input['signature_data'] ?? null;
        $verificationCode = $input['verification_code'] ?? '';

        if (!$signatureData) {
            $this->setFlash('error', 'Signature data is required');
            return $this->redirect("/admin/document-esign/{$id}");
        }

        $data = [
            'signature_data' => $signatureData,
            'signed_by' => $this->getAdminId(),
            'signed_at' => date('Y-m-d H:i:s'),
            'verification_code' => $verificationCode,
        ];

        $result = $this->service->signDocument((int)$id, $data);
        
        if ($result['success']) {
            $this->setFlash('success', 'Document signed successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to sign document');
        }

        return $this->redirect("/admin/document-esign/{$id}");
    }

    public function verify($id)
    {
        $this->requireAdmin();
        $verificationCode = $_GET['code'] ?? '';
        
        $result = $this->service->verifyDocument((int)$id, $verificationCode);

        if ($result['success']) {
            $document = $result['document'];
            $this->render('admin/document_esign/verify', [
                'page_title' => 'Verify Document',
                'document' => $document
            ]);
        } else {
            $this->setFlash('error', $result['error'] ?? 'Invalid verification code');
            return $this->redirect('/admin/document-esign');
        }
    }

    public function cancel($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->service->cancelDocument((int)$id, $this->getAdminId());

        if ($result['success']) {
            $this->setFlash('success', 'Document cancelled');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to cancel document');
        }

        return $this->redirect('/admin/document-esign');
    }
}
