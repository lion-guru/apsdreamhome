<?php

namespace App\Http\Controllers\Admin;

use App\Services\OcrService;

class DocumentOCRController extends AdminController
{
    private $ocrService;

    public function __construct()
    {
        parent::__construct();
        $this->ocrService = new OcrService($this->db);
        $this->ocrService->initSchema();
    }

    private function getUserId(): int
    {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->ocrService->getStats();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $doctype = $_GET['doctype'] ?? '';
        $search = $_GET['q'] ?? '';
        $result = $this->ocrService->listDocuments($page, 15, $status, $doctype, $search);

        return $this->render('admin/ocr/index', [
            'page_title' => 'OCR Document Pipeline',
            'stats' => $stats,
            'documents' => $result['documents'],
            'total' => $result['total'],
            'page' => $result['page'],
            'total_pages' => $result['total_pages'],
            'status' => $status,
            'doctype' => $doctype,
            'search' => $search,
            'doc_types' => $this->ocrService->getDocTypes(),
            'doc_type_labels' => [
                'aadhaar' => 'Aadhaar Card',
                'pan' => 'PAN Card',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
                'cheque' => 'Cheque',
                'invoice' => 'Invoice',
                'contract' => 'Contract',
            ],
        ]);
    }

    public function upload()
    {
        $this->requireAdmin();
        return $this->render('admin/ocr/upload', [
            'page_title' => 'Upload Document for OCR',
            'doc_types' => $this->ocrService->getDocTypes(),
            'doc_type_labels' => [
                'aadhaar' => 'Aadhaar Card',
                'pan' => 'PAN Card',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
                'cheque' => 'Cheque',
                'invoice' => 'Invoice',
                'contract' => 'Contract',
            ],
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (empty($_FILES['document_file'])) {
            $this->setFlash('error', 'No file was uploaded.');
            $this->redirect('/admin/ocr/upload');
            return;
        }

        $documentType = $_POST['document_type'] ?? 'aadhaar';
        $userId = $this->getUserId();

        $result = $this->ocrService->uploadDocument($_FILES['document_file'], $userId, $documentType);

        if (!$result['ok']) {
            $this->setFlash('error', $result['error']);
            $this->redirect('/admin/ocr/upload');
            return;
        }

        $this->setFlash('success', 'Document uploaded successfully. ID: ' . $result['id']);
        $this->redirect('/admin/ocr/detail/' . $result['id']);
    }

    public function process($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $result = $this->ocrService->processDocument($id);

        if (!$result['ok']) {
            $this->setFlash('error', 'OCR processing failed: ' . $result['error']);
        } else {
            $fieldCount = count($result['fields'] ?? []);
            $conf = round(($result['confidence'] ?? 0) * 100);
            $this->setFlash('success', "OCR completed. Extracted {$fieldCount} fields with {$conf}% confidence.");
        }

        $this->redirect('/admin/ocr/detail/' . $id);
    }

    public function detail($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $doc = $this->ocrService->getDocument($id);

        if (!$doc) {
            $this->setFlash('error', 'Document not found.');
            $this->redirect('/admin/ocr');
            return;
        }

        $structuredData = $doc['structured_data'] ?? [];
        if (is_string($structuredData)) {
            $structuredData = json_decode($structuredData, true) ?? [];
        }

        return $this->render('admin/ocr/detail', [
            'page_title' => 'Document Detail — OCR',
            'doc' => $doc,
            'fields' => $doc['fields'] ?? [],
            'structured_data' => $structuredData,
            'doc_type_label' => $this->ocrService->getDocTypeLabel($doc['document_type'] ?? 'unknown'),
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->ocrService->approveDocument((int)$id, $this->getUserId());

        if ($result['ok']) {
            $this->setFlash('success', 'Document verified and approved.');
        } else {
            $this->setFlash('error', 'Approval failed: ' . $result['error']);
        }

        $this->redirect('/admin/ocr/detail/' . (int)$id);
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $reason = trim($_POST['rejection_reason'] ?? '');
        if ($reason === '') {
            $this->setFlash('error', 'Rejection reason is required.');
            $this->redirect('/admin/ocr/detail/' . (int)$id);
            return;
        }

        $result = $this->ocrService->rejectDocument((int)$id, $reason);

        if ($result['ok']) {
            $this->setFlash('success', 'Document rejected.');
        } else {
            $this->setFlash('error', 'Rejection failed: ' . $result['error']);
        }

        $this->redirect('/admin/ocr/detail/' . (int)$id);
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->ocrService->deleteDocument((int)$id);

        if ($result['ok']) {
            $this->setFlash('success', 'Document deleted.');
        } else {
            $this->setFlash('error', 'Delete failed: ' . $result['error']);
        }

        $this->redirect('/admin/ocr');
    }

    public function templates()
    {
        $this->requireAdmin();
        $templates = $this->ocrService->listTemplates();

        return $this->render('admin/ocr/templates', [
            'page_title' => 'OCR Templates',
            'templates' => $templates,
            'doc_types' => $this->ocrService->getDocTypes(),
            'doc_type_labels' => [
                'aadhaar' => 'Aadhaar Card',
                'pan' => 'PAN Card',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
                'cheque' => 'Cheque',
                'invoice' => 'Invoice',
                'contract' => 'Contract',
            ],
        ]);
    }

    public function templateForm($id = null)
    {
        $this->requireAdmin();
        $template = null;
        if ($id !== null) {
            $template = $this->ocrService->getTemplate((int)$id);
            if (!$template) {
                $this->setFlash('error', 'Template not found.');
                $this->redirect('/admin/ocr/templates');
                return;
            }
        }

        $fieldsJson = json_encode($template['field_definitions'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->render('admin/ocr/template_form', [
            'page_title' => $template ? 'Edit OCR Template' : 'Create OCR Template',
            'template' => $template,
            'fields_json' => $fieldsJson,
            'doc_types' => $this->ocrService->getDocTypes(),
            'doc_type_labels' => [
                'aadhaar' => 'Aadhaar Card',
                'pan' => 'PAN Card',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
                'cheque' => 'Cheque',
                'invoice' => 'Invoice',
                'contract' => 'Contract',
            ],
        ]);
    }

    public function templateStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'template_name' => trim($_POST['template_name'] ?? ''),
            'document_type' => $_POST['document_type'] ?? 'aadhaar',
            'is_active' => (int)($_POST['is_active'] ?? 1),
            'field_definitions' => [],
        ];

        if ($data['template_name'] === '') {
            $this->setFlash('error', 'Template name is required.');
            $this->redirect('/admin/ocr/templates/create');
            return;
        }

        $fieldsRaw = trim($_POST['field_definitions_json'] ?? '[]');
        $decoded = json_decode($fieldsRaw, true);
        if (!is_array($decoded)) {
            $this->setFlash('error', 'Field definitions must be valid JSON.');
            $this->redirect('/admin/ocr/templates/create');
            return;
        }
        $data['field_definitions'] = $decoded;

        $editId = (int)($_POST['template_id'] ?? 0);
        $result = $this->ocrService->saveTemplate($data, $editId);

        if ($result['ok']) {
            $this->setFlash('success', 'Template saved successfully.');
        } else {
            $this->setFlash('error', 'Save failed: ' . $result['error']);
        }

        $this->redirect('/admin/ocr/templates');
    }

    public function templateDelete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->ocrService->deleteTemplate((int)$id);

        if ($result['ok']) {
            $this->setFlash('success', 'Template deleted.');
        } else {
            $this->setFlash('error', 'Delete failed: ' . $result['error']);
        }

        $this->redirect('/admin/ocr/templates');
    }
}
