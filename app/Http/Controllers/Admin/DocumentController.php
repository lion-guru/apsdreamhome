<?php

namespace App\Http\Controllers\Admin;

class DocumentController extends AdminController
{
    private function getDb()
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // EXISTING METHODS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function index()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
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
        try {
            $db = $this->getDb();
            $cats = $db->query("SELECT id, name FROM document_categories WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
            $types = $db->query("SELECT id, name FROM document_types WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
            $types = [];
        }
        $this->render('admin/documents/upload', ['page_title' => 'Upload Document', 'categories' => $cats, 'doc_types' => $types]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $file = $_FILES['document_file'];
                $title = $_POST['title'] ?? 'Untitled';
                $type = $_POST['type'] ?? 'other';
                $description = $_POST['description'] ?? '';
                $relatedType = $_POST['related_type'] ?? '';
                $relatedId = (int)($_POST['related_id'] ?? 0);
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $docTypeId = !empty($_POST['doc_type_id']) ? (int)$_POST['doc_type_id'] : null;

                $uploadDir = STORAGE_PATH . '/uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Validate upload before processing
                $validation = UploadValidator::validate($file, ['types' => 'documents', 'max_size' => 25]);
                if (!$validation['valid']) {
                    $_SESSION['flash_message'] = 'Upload rejected: ' . $validation['error'];
                    $_SESSION['flash_type'] = 'danger';
                    header('Location: ' . BASE_URL . '/admin/documents');
                    exit;
                }

                $safeName = $validation['sanitized_name'];
                $filePath = $uploadDir . $safeName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $stmt = $db->prepare("INSERT INTO documents (title, type, description, file_path, file_size, related_type, related_id, category_id, doc_type_id, uploaded_by, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
                    $stmt->execute([$title, $type, $description, $filePath, $file['size'], $relatedType, $relatedId, $categoryId, $docTypeId, $_SESSION['admin_id'] ?? 0]);

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
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("SELECT file_path FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($doc && !empty($doc['file_path']) && file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $stmt = $db->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = 'Document deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Delete failed: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/documents');
        exit;
    }

    public function download($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("SELECT file_path, title FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($doc && !empty($doc['file_path']) && file_exists($doc['file_path'])) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($doc['file_path']) . '"');
                header('Content-Length: ' . filesize($doc['file_path']));
                readfile($doc['file_path']);
                exit;
            }
        } catch (\Exception $e) {
            // fall through
                    error_log("DocumentController.php: " . $e->getMessage());
        }
        $_SESSION['flash_message'] = 'File not found';
        $_SESSION['flash_type'] = 'danger';
        header('Location: ' . BASE_URL . '/admin/documents');
        exit;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // CATEGORIES
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function categories()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT c.*, p.name as parent_name FROM document_categories c LEFT JOIN document_categories p ON c.parent_id = p.id ORDER BY c.name");
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $parents = $db->query("SELECT id, name FROM document_categories WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $categories = [];
            $parents = [];
        }
        $this->render('admin/documents/categories', ['page_title' => 'Document Categories', 'categories' => $categories, 'parents' => $parents]);
    }

    public function storeCategory()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $slug = $_POST['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $name));
                $description = $_POST['description'] ?? '';
                $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $isActive = isset($_POST['is_active']) ? 1 : 1;

                $stmt = $db->prepare("INSERT INTO document_categories (name, slug, description, parent_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description, $parentId, $isActive]);
                $_SESSION['flash_message'] = 'Category created successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/categories');
        exit;
    }

    public function updateCategory($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $slug = $_POST['slug'] ?? '';
                $description = $_POST['description'] ?? '';
                $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $stmt = $db->prepare("UPDATE document_categories SET name = ?, slug = ?, description = ?, parent_id = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $parentId, $isActive, $id]);
                $_SESSION['flash_message'] = 'Category updated successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/categories');
        exit;
    }

    public function deleteCategory($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("DELETE FROM document_categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = 'Category deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/documents/categories');
        exit;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // TYPES
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function types()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT t.*, c.name as category_name FROM document_types t LEFT JOIN document_categories c ON t.category_id = c.id ORDER BY t.name");
            $types = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $categories = $db->query("SELECT id, name FROM document_categories WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $types = [];
            $categories = [];
        }
        $this->render('admin/documents/types', ['page_title' => 'Document Types', 'types' => $types, 'categories' => $categories]);
    }

    public function storeType()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $slug = $_POST['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $name));
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $description = $_POST['description'] ?? '';

                $stmt = $db->prepare("INSERT INTO document_types (name, slug, category_id, description, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$name, $slug, $categoryId, $description]);
                $_SESSION['flash_message'] = 'Document type created successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/types');
        exit;
    }

    public function updateType($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $slug = $_POST['slug'] ?? '';
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $description = $_POST['description'] ?? '';
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $stmt = $db->prepare("UPDATE document_types SET name = ?, slug = ?, category_id = ?, description = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $categoryId, $description, $isActive, $id]);
                $_SESSION['flash_message'] = 'Document type updated successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/types');
        exit;
    }

    public function deleteType($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("DELETE FROM document_types WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = 'Document type deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/documents/types');
        exit;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // TEMPLATES
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function templates()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT t.*, c.name as category_name FROM document_templates t LEFT JOIN document_categories c ON t.category_id = c.id ORDER BY t.name");
            $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $categories = $db->query("SELECT id, name FROM document_categories WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $templates = [];
            $categories = [];
        }
        $this->render('admin/documents/templates', ['page_title' => 'Document Templates', 'templates' => $templates, 'categories' => $categories]);
    }

    public function storeTemplate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $type = $_POST['type'] ?? 'document';
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $content = $_POST['content'] ?? '';
                $description = $_POST['description'] ?? '';

                $stmt = $db->prepare("INSERT INTO document_templates (name, type, category_id, content, description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute([$name, $type, $categoryId, $content, $description]);
                $_SESSION['flash_message'] = 'Template created successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/templates');
        exit;
    }

    public function editTemplate($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("SELECT * FROM document_templates WHERE id = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(\PDO::FETCH_ASSOC);
            $categories = $db->query("SELECT id, name FROM document_categories WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $template = null;
            $categories = [];
        }
        $this->render('admin/documents/template_edit', ['page_title' => 'Edit Template', 'template' => $template, 'categories' => $categories]);
    }

    public function updateTemplate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                $name = $_POST['name'] ?? '';
                $type = $_POST['type'] ?? 'document';
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $content = $_POST['content'] ?? '';
                $description = $_POST['description'] ?? '';
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $stmt = $db->prepare("UPDATE document_templates SET name = ?, type = ?, category_id = ?, content = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $type, $categoryId, $content, $description, $isActive, $id]);
                $_SESSION['flash_message'] = 'Template updated successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/templates');
        exit;
    }

    public function deleteTemplate($id)
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->prepare("DELETE FROM document_templates WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = 'Template deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ' . BASE_URL . '/admin/documents/templates');
        exit;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // REVIEWS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function reviews()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
            try {
                $sql = "SELECT r.*, d.title as document_title, u.name as reviewer_name FROM document_reviews r LEFT JOIN documents d ON r.document_id = d.id LEFT JOIN users u ON r.reviewer_id = u.id";
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $params = [];
            if (!empty($statusFilter)) {
                $sql .= " WHERE r.review_status = ?";
                $params[] = $statusFilter;
            }
            $sql .= " ORDER BY r.created_at DESC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $docs = $db->query("SELECT id, title FROM documents ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $reviews = [];
            $docs = [];
        }
        $this->render('admin/documents/reviews', ['page_title' => 'Document Reviews', 'reviews' => $reviews, 'documents' => $docs, 'status_filter' => $_GET['status'] ?? '']);
    }

    public function storeReview()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            try {
                $db = $this->getDb();
                try {
                    $stmt = $db->prepare("INSERT INTO document_reviews (document_id, reviewer_id, review_status, comments, reviewed_at, created_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                }
                $stmt->execute([
                    (int)$_POST['document_id'],
                    (int)($_SESSION['admin_id'] ?? 0),
                    $_POST['review_status'] ?? 'pending',
                    $_POST['comments'] ?? ''
                ]);
                $_SESSION['flash_message'] = 'Review submitted successfully';
                $_SESSION['flash_type'] = 'success';
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/reviews');
        exit;
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // CLASSIFICATION
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function classification()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT cl.*, d.title as document_title, u.name as classified_by_name FROM document_classification cl LEFT JOIN documents d ON cl.document_id = d.id LEFT JOIN users u ON cl.classified_by = u.id ORDER BY cl.created_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/classification', ['page_title' => 'Document Classification', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // BUSINESS DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function businessDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.user_id = u.id WHERE d.entity_type = 'business' ORDER BY d.uploaded_on DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/business_documents', ['page_title' => 'Business Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // CUSTOMER DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function customerDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name, c.name as customer_name FROM documents d LEFT JOIN users u ON d.user_id = u.id LEFT JOIN users c ON d.entity_id = c.id WHERE d.entity_type = 'customer' ORDER BY d.uploaded_on DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/customer_documents', ['page_title' => 'Customer Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // USER DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function userDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name, u2.name as user_name FROM documents d LEFT JOIN users u ON d.user_id = u.id LEFT JOIN users u2 ON d.entity_id = u2.id WHERE d.entity_type = 'user' ORDER BY d.uploaded_on DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/user_documents', ['page_title' => 'User Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // PROPERTY DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function propertyDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.user_id = u.id WHERE d.entity_type = 'property' ORDER BY d.uploaded_on DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/property_documents', ['page_title' => 'Property Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // GENERATED DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function generatedDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT gd.*, t.name as template_name, u.name as generated_by_name FROM generated_documents gd LEFT JOIN document_templates t ON gd.template_id = t.id LEFT JOIN users u ON gd.generated_by = u.id ORDER BY gd.created_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/generated_documents', ['page_title' => 'Generated Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // OCR DOCUMENTS
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function ocrDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT o.*, d.title as original_document_title FROM ocr_documents o LEFT JOIN documents d ON o.original_document_id = d.id ORDER BY o.created_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/ocr_documents', ['page_title' => 'OCR Documents', 'records' => $records]);
    }

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // SEARCH
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function search()
    {
        $this->requireAdmin();
        $q = trim($_GET['q'] ?? '');
        $results = [];

        if (!empty($q)) {
            try {
                $db = $this->getDb();
                $likeQ = '%' . $q . '%';

                $tables = [
                    'documents' => ['document_type', 'url'],
                ];

                foreach ($tables as $table => $columns) {
                    if (empty($columns)) {
                        continue;
                    }
                    $where = [];
                    $params = [];
                    foreach ($columns as $col) {
                        $where[] = "$col LIKE ?";
                        $params[] = $likeQ;
                    }
                    $sql = "SELECT * FROM $table WHERE " . implode(' OR ', $where) . " LIMIT 20";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    foreach ($rows as &$row) {
                        $row['_source_table'] = $table;
                    }
                    $results = array_merge($results, $rows);
                }
            } catch (\Exception $e) {
                $results = [];
            }
        }

        $this->render('admin/documents/search', ['page_title' => 'Search Documents', 'results' => $results, 'query' => $q]);
    }
}
