<?php

namespace App\Http\Controllers\Admin;

class DocumentController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private function getDb()
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // EXISTING METHODS
    // ------------------------------------------------------------------------------------------------------------------------------

    public function index()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.uploaded_on DESC LIMIT 50");
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
            $stmt = $db->prepare("SELECT d.*, u.name as uploaded_by_name FROM documents d LEFT JOIN users u ON d.user_id = u.id WHERE d.id = ?");
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
                    $_SESSION['error'] = 'Upload rejected: ' . $validation['error'];
                    header('Location: ' . BASE_URL . '/admin/documents');
                    exit;
                }

                $safeName = $validation['sanitized_name'];
                $filePath = $uploadDir . $safeName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $tid = $this->tenantId();
                    $stmt = $db->prepare("INSERT INTO documents (type, url, entity_type, entity_id, user_id, uploaded_on, tenant_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                    $stmt->execute([$type, $filePath, $relatedType, $relatedId, $_SESSION['admin_id'] ?? 0, $tid]);

                    $_SESSION['success'] = 'Document uploaded successfully';
                } else {
                    throw new \Exception('File upload failed');
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Upload failed: ' . $e->getMessage();
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
            $stmt = $db->prepare("DELETE FROM documents WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Document deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Delete failed: ' . $e->getMessage();
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
        $_SESSION['error'] = 'File not found';
        header('Location: ' . BASE_URL . '/admin/documents');
        exit;
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // CATEGORIES
    // ------------------------------------------------------------------------------------------------------------------------------

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

                $stmt = $db->prepare("INSERT INTO document_categories (name, description, parent_id, is_active, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $parentId, $isActive]);
                $_SESSION['success'] = 'Category created successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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

                $stmt = $db->prepare("UPDATE document_categories SET name = ?, description = ?, parent_id = ?, is_active = ? WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$name, $description, $parentId, $isActive, $id, $this->tenantId()]);
                $_SESSION['success'] = 'Category updated successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
            $stmt = $db->prepare("DELETE FROM document_categories WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Category deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/documents/categories');
        exit;
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // TYPES
    // ------------------------------------------------------------------------------------------------------------------------------

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

                $stmt = $db->prepare("INSERT INTO document_types (name, category_id, description, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
                $stmt->execute([$name, $categoryId, $description]);
                $_SESSION['success'] = 'Document type created successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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

                $stmt = $db->prepare("UPDATE document_types SET name = ?, category_id = ?, description = ?, is_active = ? WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$name, $categoryId, $description, $isActive, $id, $this->tenantId()]);
                $_SESSION['success'] = 'Document type updated successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
            $stmt = $db->prepare("DELETE FROM document_types WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Document type deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/documents/types');
        exit;
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // TEMPLATES
    // ------------------------------------------------------------------------------------------------------------------------------

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

                $stmt = $db->prepare("INSERT INTO document_templates (template_name, category, content_html, description, is_active, created_at, updated_at, tenant_id) VALUES (?, ?, ?, ?, 1, NOW(), NOW(), ?)");
                $stmt->execute([$name, $type, $content, $description, $this->tenantId()]);
                $_SESSION['success'] = 'Template created successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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

                $stmt = $db->prepare("UPDATE document_templates SET template_name = ?, category = ?, content_html = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ? AND tenant_id = ?");
                $stmt->execute([$name, $type, $content, $description, $isActive, $id, $this->tenantId()]);
                $_SESSION['success'] = 'Template updated successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
            $stmt = $db->prepare("DELETE FROM document_templates WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Template deleted successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/documents/templates');
        exit;
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // REVIEWS
    // ------------------------------------------------------------------------------------------------------------------------------

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
            error_log($e->getMessage());
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
                    $tid = $this->tenantId();
                    $stmt = $db->prepare("INSERT INTO document_reviews (document_id, reviewer_id, review_status, comments, reviewed_at, created_at, tenant_id) VALUES (?, ?, ?, ?, NOW(), NOW(), ?)");
                } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                error_log($e->getMessage());
                }
                $stmt->execute([
                    (int)$_POST['document_id'],
                    (int)($_SESSION['admin_id'] ?? 0),
                    $_POST['review_status'] ?? 'pending',
                    $_POST['comments'] ?? '',
                    $tid
                ]);
                $_SESSION['success'] = 'Review submitted successfully';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
        header('Location: ' . BASE_URL . '/admin/documents/reviews');
        exit;
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // CLASSIFICATION
    // ------------------------------------------------------------------------------------------------------------------------------

    public function classification()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT cl.*, d.document_type as document_title, u.name as classified_by_name FROM document_classification cl LEFT JOIN documents d ON cl.document_id = d.id LEFT JOIN users u ON cl.classified_by = u.id ORDER BY cl.classified_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/classification', ['page_title' => 'Document Classification', 'records' => $records]);
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // BUSINESS DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

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

    // ------------------------------------------------------------------------------------------------------------------------------
    // CUSTOMER DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

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

    // ------------------------------------------------------------------------------------------------------------------------------
    // USER DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

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

    // ------------------------------------------------------------------------------------------------------------------------------
    // PROPERTY DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

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

    // ------------------------------------------------------------------------------------------------------------------------------
    // GENERATED DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

    public function generatedDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT gd.*, t.template_name as template_name, u.name as generated_by_name FROM generated_documents gd LEFT JOIN document_templates t ON gd.template_id = t.id LEFT JOIN users u ON gd.generated_by = u.id ORDER BY gd.created_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/generated_documents', ['page_title' => 'Generated Documents', 'records' => $records]);
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // OCR DOCUMENTS
    // ------------------------------------------------------------------------------------------------------------------------------

    public function ocrDocuments()
    {
        $this->requireAdmin();
        try {
            $db = $this->getDb();
            $stmt = $db->query("SELECT o.*, o.original_name as original_document_title FROM ocr_documents o LEFT JOIN documents d ON o.original_document_id = d.id ORDER BY o.created_at DESC LIMIT 50");
            $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $records = [];
        }
        $this->render('admin/documents/ocr_documents', ['page_title' => 'OCR Documents', 'records' => $records]);
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    // SEARCH
    // ------------------------------------------------------------------------------------------------------------------------------

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
