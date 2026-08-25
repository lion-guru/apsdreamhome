<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateDocumentController
 * Handles document management
 */
class DocumentController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Document list
     */
    public function documents()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $documents = $db->fetchAll("
                SELECT * FROM user_documents
                WHERE user_id = ?{$tidSql}
                ORDER BY created_at DESC
            ", $params) ?: [];

            $this->render('associate/documents', [
                'page_title' => 'My Documents - Associate Portal',
                'page_description' => 'Manage your documents',
                'documents' => $documents,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateDocumentController error: ' . $e->getMessage());
        }
    }

    /**
     * Upload document
     */
    public function uploadDocument()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/documents');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Handle file upload
            if (empty($_FILES['document']['tmp_name']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded');
            }

            $file = $_FILES['document'];
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedTypes));
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('File size exceeds 10MB limit');
            }

            $uploadDir = __DIR__ . '/../../../public/uploads/associate_documents/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileName = 'doc_' . uniqid() . '.' . $ext;
            $dest = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                throw new Exception('Failed to upload file');
            }

            $data = [
                'user_id' => $_SESSION['user_id'],
                'document_type' => $_POST['document_type'] ?? 'other',
                'file_name' => $file['name'],
                'file_path' => '/uploads/associate_documents/' . $fileName,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'description' => trim($_POST['description'] ?? ''),
                'status' => 'pending',
                'tenant_id' => TenantContext::getId(),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->db->insert('user_documents', $data);
            $docId = (int)$this->db->lastInsertId();

            $this->logActivity($userId, 'document_uploaded', ['document_id' => $docId]);

            $_SESSION['success'] = 'Document uploaded successfully!';
            $this->redirect('/associate/documents');
        } catch (\Throwable $e) {
            error_log('AssociateDocumentController::uploadDocument error: ' . $e->getMessage());
            $_SESSION['error'] = 'Upload failed: ' . $e->getMessage();
            $this->redirect('/associate/documents');
        }
    }
}

