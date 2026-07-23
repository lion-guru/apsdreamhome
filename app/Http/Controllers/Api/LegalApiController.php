<?php

namespace App\Http\Controllers\Api;

use App\Core\Database;
use App\Services\Legal\LegalDocumentService;
use PDO;

class LegalApiController extends BaseApiController
{
    protected $docService;

    public function __construct()
    {
        parent::__construct();
        try {
            $db = Database::getInstance();
            $pdo = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
            $this->docService = new LegalDocumentService($pdo);
        } catch (\Exception $e) {
            $this->docService = null;
        }
    }

    public function getDocuments()
    {
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['customer_id'])) $filters['customer_id'] = (int)$_GET['customer_id'];
        $docs = $this->docService ? $this->docService->getDocuments($filters) : [];
        $this->jsonResponse(['success' => true, 'data' => $docs]);
    }

    public function getDocumentDetail($id)
    {
        $doc = $this->docService ? $this->docService->getDocumentById((int)$id) : null;
        if (!$doc) {
            $this->jsonResponse(['success' => false, 'error' => 'Document not found'], 404);
            return;
        }
        unset($doc['template_content'], $doc['merge_fields']);
        $uploads = $this->docService ? $this->docService->getUploads((int)$id) : [];
        $this->jsonResponse(['success' => true, 'data' => $doc, 'uploads' => $uploads]);
    }

    public function uploadDocument($id)
    {
        if (empty($_FILES['file'])) {
            $this->jsonResponse(['success' => false, 'error' => 'No file uploaded'], 400);
            return;
        }
        $id = (int)$id;
        if ($id <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid document ID'], 400);
            return;
        }
        $file = $_FILES['file'];
        $allowedExtensions = ['pdf','jpg','jpeg','png','gif','doc','docx','xls','xlsx','txt','csv'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            $this->jsonResponse(['success' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)], 400);
            return;
        }
        $uploadDir = 'uploads/legal/' . $id . '/';
        $realUploadDir = realpath($uploadDir) ?: $uploadDir;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        $dest = $uploadDir . $fileName;
        $realDest = realpath(dirname($dest)) . '/' . $fileName;
        if (strpos($realDest, realpath('uploads/legal') ?: 'uploads/legal') !== 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid upload path'], 400);
            return;
        }
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->jsonResponse(['success' => false, 'error' => 'Upload failed'], 500);
            return;
        }
        $result = $this->docService ? $this->docService->createUpload([
            'document_id' => (int)$id,
            'customer_id' => $_GET['customer_id'] ?? null,
            'file_path' => $dest,
            'file_name' => $file['name'],
            'file_type' => $file['type'],
            'file_size' => $file['size'],
            'upload_type' => $_POST['upload_type'] ?? 'signed_copy',
        ]) : ['success' => false, 'error' => 'Service unavailable'];
        $this->jsonResponse($result['success'] ? ['success' => true, 'data' => $result] : ['success' => false, 'error' => $result['error'] ?? 'Failed'], $result['success'] ? 200 : 500);
    }

    public function getCategories()
    {
        $cats = $this->docService ? $this->docService->getCategories() : [];
        $this->jsonResponse(['success' => true, 'data' => $cats]);
    }

    public function getTemplates()
    {
        $filters = [];
        if (!empty($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
        $tpls = $this->docService ? $this->docService->getTemplates($filters) : [];
        $this->jsonResponse(['success' => true, 'data' => $tpls]);
    }

    public function previewDocument($id)
    {
        $doc = $this->docService ? $this->docService->getDocumentById((int)$id) : null;
        if (!$doc || empty($doc['content'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Document not found or empty'], 404);
            return;
        }
        $this->jsonResponse(['success' => true, 'content' => $doc['content'], 'title' => $doc['title'], 'document_number' => $doc['document_number']]);
    }
}

