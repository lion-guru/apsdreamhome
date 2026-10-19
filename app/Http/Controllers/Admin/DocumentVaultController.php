<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\DocumentVaultService;
use Exception;

class DocumentVaultController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new DocumentVaultService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'employee_id' => $_GET['employee_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'verification_status' => $_GET['verification_status'] ?? null,
            'expiring_within' => $_GET['expiring_within'] ?? null,
        ];
        $documents = $this->service->getDocuments($filters);
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        $stats = $this->service->getStats();

        return $this->render('admin/document_vault/index', [
            'page_title' => 'Document Vault',
            'documents' => $documents,
            'categories' => $categories,
            'employees' => $employees,
            'filters' => $_GET,
            'stats' => $stats,
        ]);
    }

    public function categories()
    {
        $this->requireAdmin();
        $categories = $this->service->getCategories(['is_active' => 1]);
        return $this->render('admin/document_vault/categories', [
            'page_title' => 'Document Categories',
            'categories' => $categories,
        ]);
    }

    public function storeCategory()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->createCategory($_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/document-vault/categories');
    }

    public function documents()
    {
        $this->requireAdmin();
        $filters = [
            'employee_id' => $_GET['employee_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'verification_status' => $_GET['verification_status'] ?? null,
        ];
        $documents = $this->service->getDocuments($filters);
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");

        return $this->render('admin/document_vault/documents', [
            'page_title' => 'Documents',
            'documents' => $documents,
            'categories' => $categories,
            'employees' => $employees,
            'filters' => $_GET,
        ]);
    }

    public function upload()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'No file uploaded');
            $this->redirect(BASE_URL . '/admin/document-vault/documents');
        }

        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'document_name' => $_POST['document_name'] ?? '',
            'issue_date' => $_POST['issue_date'] ?? null,
            'expiry_date' => $_POST['expiry_date'] ?? null,
            'issuing_authority' => $_POST['issuing_authority'] ?? null,
            'document_number_ref' => $_POST['document_number_ref'] ?? null,
            'is_confidential' => !empty($_POST['is_confidential']),
            'access_level' => $_POST['access_level'] ?? 'self',
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'uploaded_by' => $_SESSION['admin_id'] ?? 1,
        ];

        try {
            $result = $this->service->uploadDocument($data['employee_id'], $data, $_FILES['document_file']);
            $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        } catch (Exception $e) {
            $this->setFlash('error', 'Upload failed: ' . $e->getMessage());
        }
        $this->redirect(BASE_URL . '/admin/document-vault/documents');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $doc = $this->service->getDocument($id);
        if (!$doc) {
            $this->setFlash('error', 'Document not found');
            $this->redirect(BASE_URL . '/admin/document-vault/documents');
        }
        return $this->render('admin/document_vault/show', [
            'page_title' => 'Document: ' . $doc['document_name'],
            'document' => $doc,
        ]);
    }

    public function verify($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->verifyDocument($id, $_SESSION['admin_id'] ?? 1, $_POST['status'] ?? 'verified', $_POST['rejection_reason'] ?? '');
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/document-vault/' . $id);
    }

    public function requests()
    {
        $this->requireAdmin();
        $filters = [
            'employee_id' => $_GET['employee_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];
        $requests = $this->service->getRequests($filters);
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");

        return $this->render('admin/document_vault/requests', [
            'page_title' => 'Document Requests',
            'requests' => $requests,
            'categories' => $categories,
            'employees' => $employees,
            'filters' => $_GET,
        ]);
    }

    public function createRequest()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = [
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'requested_by' => $_SESSION['admin_id'] ?? 1,
            'due_date' => $_POST['due_date'],
            'priority' => $_POST['priority'] ?? 'normal',
            'message' => $_POST['message'] ?? null,
        ];
        $result = $this->service->createRequest($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/document-vault/requests');
    }

    public function verifyRequest($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->verifyRequest($id, $_SESSION['admin_id'] ?? 1, $_POST['status'] ?? 'verified', $_POST['rejection_reason'] ?? '');
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/document-vault/requests');
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }

    public function sendReminders()
    {
        $this->requireAdmin();
        $count = $this->service->sendReminders();
        $this->setFlash('success', "Sent $count reminder(s)");
        $this->redirect(BASE_URL . '/admin/document-vault/requests');
    }
}