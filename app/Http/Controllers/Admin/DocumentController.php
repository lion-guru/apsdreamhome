<?php

namespace App\Http\Controllers\Admin;

class DocumentController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id ORDER BY d.created_at DESC LIMIT 50");
            $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $documents = [];
        }
        $this->render('admin/documents/index', ['page_title' => 'Documents', 'documents' => $documents]);
    }

    public function upload()
    {
        $this->requireAdmin();
        $this->render('admin/documents/upload', ['page_title' => 'Upload Document']);
    }

    public function show(int $id)
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.uploaded_by = u.id WHERE d.id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $document = null;
        }
        $this->render('admin/documents/show', ['page_title' => 'Document Details', 'document' => $document]);
    }

    public function store()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
            try {
                $db = \App\Core\Database\Database::getInstance()->getConnection();
                $file = $_FILES['document_file'];
                $title = $_POST['title'] ?? 'Untitled';
                $type = $_POST['type'] ?? 'other';
                $description = $_POST['description'] ?? '';
                $relatedType = $_POST['related_type'] ?? '';
                $relatedId = (int)($_POST['related_id'] ?? 0);

                $uploadDir = STORAGE_PATH . '/uploads/documents/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $title) . '.' . $ext;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $stmt = $db->prepare("INSERT INTO documents (title, type, description, file_path, file_size, related_type, related_id, uploaded_by, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
                    $stmt->execute([$title, $type, $description, $filePath, $file['size'], $relatedType, $relatedId, $_SESSION['admin_id'] ?? 0]);

                    $_SESSION['flash_message'] = 'Document uploaded successfully';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    throw new \Exception('File upload failed');
                }
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Upload failed: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }

        header('Location: ' . BASE_URL . '/admin/documents');
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        header('Location: ' . BASE_URL . '/admin/documents');
        exit;
    }

    public function download($id)
    {
        $this->requireAdmin();
    }
}
