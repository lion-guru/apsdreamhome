<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class LegalDocumentController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private function getDb()
    {
        return $this->db;
    }

    // ── DASHBOARD ──────────────────────────────────────────

    public function index()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];
        $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';

        $stats = [
            'total'       => (int)$db->query("SELECT COUNT(*) FROM legal_documents WHERE 1=1" . $tidSql, $tidParams)->fetchColumn(),
            'active'      => (int)$db->query("SELECT COUNT(*) FROM legal_documents WHERE status = 'active'" . $tidSql, $tidParams)->fetchColumn(),
            'templates'   => (int)$db->query("SELECT COUNT(*) FROM legal_document_templates WHERE 1=1" . $tidSql, $tidParams)->fetchColumn(),
            'categories'  => (int)$db->query("SELECT COUNT(*) FROM legal_document_categories WHERE 1=1" . $tidSql, $tidParams)->fetchColumn(),
            'clauses'     => (int)$db->query("SELECT COUNT(*) FROM legal_clause_library")->fetchColumn(),
            'prompts'     => (int)$db->query("SELECT COUNT(*) FROM legal_ai_prompts")->fetchColumn(),
        ];

        try {
            $docs = $db->query("SELECT d.*, u.name as creator_name FROM legal_documents d LEFT JOIN users u ON d.created_by = u.id WHERE d.tenant_id = ? ORDER BY d.created_at DESC LIMIT 20", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $docs = [];
        }

        return $this->render('admin/legal/index', [
            'page_title' => 'Legal Documentation',
            'stats'      => $stats,
            'documents'  => $docs,
        ]);
    }

    // ── CATEGORIES ─────────────────────────────────────────

    public function categories()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];

        try {
            $cats = $db->query("SELECT c.*, (SELECT COUNT(*) FROM legal_document_templates WHERE category_id = c.id) as template_count FROM legal_document_categories c WHERE c.tenant_id = ? ORDER BY c.sort_order ASC, c.name ASC", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
        }

        return $this->render('admin/legal/categories', [
            'page_title' => 'Document Categories',
            'categories' => $cats,
        ]);
    }

    public function categoryCreate()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-folder');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            $this->setFlash('error', 'Category name is required');
            $this->redirect('/admin/legal/categories');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO legal_document_categories (name, slug, description, icon, sort_order, is_active, tenant_id, created_by, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?, NOW())");
            $stmt->execute([$name, $slug, $description, $icon, $sort_order, $tid, $_SESSION['admin_id'] ?? null]);
            $this->setFlash('success', 'Category created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/categories');
    }

    public function categoryUpdate($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-folder');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $db->prepare("UPDATE legal_document_categories SET name=?, description=?, icon=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=? AND tenant_id=?");
            $stmt->execute([$name, $description, $icon, $sort_order, $is_active, $id, $tid]);
            $this->setFlash('success', 'Category updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/categories');
    }

    public function categoryDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_document_categories WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Category deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/categories');
    }

    // ── TEMPLATES ──────────────────────────────────────────

    public function templates()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];
        $tidSql = $tid > 1 ? 't.tenant_id = ?' : '1=1';

        $catFilter = $_GET['category_id'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = "WHERE $tidSql";
        if ($catFilter !== '') $where .= " AND t.category_id = " . (int)$catFilter;
        if ($statusFilter !== '') $where .= " AND t.status = " . $db->quote($statusFilter);
        if ($search !== '') $where .= " AND (t.name LIKE " . $db->quote("%$search%") . " OR t.description LIKE " . $db->quote("%$search%") . ")";

        try {
            $tpls = $db->query("SELECT t.*, c.name as category_name FROM legal_document_templates t LEFT JOIN legal_document_categories c ON t.category_id = c.id $where ORDER BY t.created_at DESC", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $tpls = [];
        }

        try {
            $cats = $db->query("SELECT id, name FROM legal_document_categories WHERE tenant_id = ? ORDER BY name", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
        }

        $merge_fields = ['{{name}}', '{{email}}', '{{phone}}', '{{address}}', '{{date}}', '{{company}}', '{{document_number}}'];

        return $this->render('admin/legal/templates', [
            'page_title'   => 'Document Templates',
            'templates'    => $tpls,
            'categories'   => $cats,
            'merge_fields' => $merge_fields,
        ]);
    }

    public function templateCreate()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();

        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $content = $_POST['content'] ?? '';
        $merge_fields = trim($_POST['merge_fields'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $is_customer_facing = isset($_POST['is_customer_facing']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Template name is required');
            $this->redirect('/admin/legal/templates');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO legal_document_templates (tenant_id, category_id, name, description, content, merge_fields, status, is_customer_facing, version, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())");
            $stmt->execute([$tid, $category_id ?: null, $name, $description, $content, $merge_fields, $status, $is_customer_facing, $_SESSION['admin_id'] ?? null]);
            $this->setFlash('success', 'Template created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/templates');
    }

    public function templateEdit($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];
        $id = (int)$id;

        try {
            $tpl = $db->prepare("SELECT * FROM legal_document_templates WHERE id = ? AND tenant_id = ?");
            $tpl->execute([$id, $tid]);
            $tpl = $tpl->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $tpl = null;
        }

        try {
            $cats = $db->query("SELECT id, name FROM legal_document_categories WHERE tenant_id = ? ORDER BY name", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
        }

        if (!$tpl) {
            $this->setFlash('error', 'Template not found');
            $this->redirect('/admin/legal/templates');
            return;
        }

        return $this->render('admin/legal/template_edit', [
            'page_title' => 'Edit Template',
            'template'   => $tpl,
            'categories' => $cats,
        ]);
    }

    public function templateUpdate($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $content = $_POST['content'] ?? '';
        $merge_fields = trim($_POST['merge_fields'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $is_customer_facing = isset($_POST['is_customer_facing']) ? 1 : 0;

        try {
            $stmt = $db->prepare("UPDATE legal_document_templates SET category_id=?, name=?, description=?, content=?, merge_fields=?, status=?, is_customer_facing=?, updated_at=NOW() WHERE id=? AND tenant_id=?");
            $stmt->execute([$category_id ?: null, $name, $description, $content, $merge_fields, $status, $is_customer_facing, $id, $tid]);
            $this->setFlash('success', 'Template updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/templates');
    }

    public function templateDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_document_templates WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Template deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/templates');
    }

    public function templateRestore($id, $version = null)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        $this->setFlash('info', 'Template version restore not yet implemented');
        $this->redirect('/admin/legal/templates');
    }

    // ── CLAUSE LIBRARY ─────────────────────────────────────

    public function clauses()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();

        $catFilter = $_GET['category_id'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = "WHERE 1=1";
        if ($catFilter !== '') $where .= " AND cl.category_id = " . (int)$catFilter;
        if ($search !== '') $where .= " AND (cl.title LIKE " . $db->quote("%$search%") . " OR cl.content LIKE " . $db->quote("%$search%") . " OR cl.tags LIKE " . $db->quote("%$search%") . ")";

        try {
            $clauses = $db->query("SELECT cl.*, c.name as category_name FROM legal_clause_library cl LEFT JOIN legal_document_categories c ON cl.category_id = c.id $where ORDER BY cl.sort_order ASC, cl.title ASC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $clauses = [];
        }

        try {
            $cats = $db->query("SELECT id, name FROM legal_document_categories ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
        }

        return $this->render('admin/legal/clauses', [
            'page_title' => 'Clause Library',
            'clauses'    => $clauses,
            'categories' => $cats,
        ]);
    }

    public function clauseCreate()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $tags = trim($_POST['tags'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($title)) {
            $this->setFlash('error', 'Clause title is required');
            $this->redirect('/admin/legal/clauses');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO legal_clause_library (category_id, title, content, tags, sort_order, is_active, created_by, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, NOW())");
            $stmt->execute([$category_id ?: null, $title, $content, $tags, $sort_order, $_SESSION['admin_id'] ?? null]);
            $this->setFlash('success', 'Clause created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/clauses');
    }

    public function clauseUpdate($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $tags = trim($_POST['tags'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $db->prepare("UPDATE legal_clause_library SET category_id=?, title=?, content=?, tags=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$category_id ?: null, $title, $content, $tags, $sort_order, $is_active, $id]);
            $this->setFlash('success', 'Clause updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/clauses');
    }

    public function clauseDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_clause_library WHERE id=?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Clause deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/clauses');
    }

    // ── DOCUMENTS ──────────────────────────────────────────

    public function documents()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];

        $catFilter = $_GET['category'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = $tid > 1 ? "WHERE d.tenant_id = ?" : "WHERE 1=1";
        if ($catFilter !== '') $where .= " AND d.category = " . $db->quote($catFilter);
        if ($statusFilter !== '') $where .= " AND d.status = " . $db->quote($statusFilter);
        if ($search !== '') $where .= " AND (d.title LIKE " . $db->quote("%$search%") . " OR d.description LIKE " . $db->quote("%$search%") . ")";

        try {
            $docs = $db->query("SELECT d.*, u.name as creator_name FROM legal_documents d LEFT JOIN users u ON d.created_by = u.id $where ORDER BY d.created_at DESC", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $docs = [];
        }

        try {
            $cats = $db->query("SELECT DISTINCT category FROM legal_documents WHERE tenant_id = ? AND category IS NOT NULL ORDER BY category", $tidParams)->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            $cats = [];
        }

        return $this->render('admin/legal/documents', [
            'page_title' => 'Legal Documents',
            'documents'  => $docs,
            'categories' => $cats,
        ]);
    }

    public function documentCreate()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];

        try {
            $templates = $db->query("SELECT id, name FROM legal_document_templates WHERE tenant_id = ? AND status = 'active' ORDER BY name", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $templates = [];
        }
        try {
            $customers = $db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
        }
        try {
            $bookings = $db->query("SELECT id, customer_name FROM plot_bookings ORDER BY id DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bookings = [];
        }
        try {
            $plots = $db->query("SELECT id, plot_number FROM plots ORDER BY plot_number LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $plots = [];
        }
        try {
            $associates = $db->query("SELECT a.id, u.name FROM associates a JOIN users u ON a.user_id = u.id ORDER BY u.name LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $associates = [];
        }
        try {
            $colonies = $db->query("SELECT id, name FROM colonies ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $colonies = [];
        }

        $merge_fields = ['{{name}}', '{{email}}', '{{phone}}', '{{address}}', '{{date}}', '{{company}}', '{{document_number}}'];

        return $this->render('admin/legal/document_create', [
            'page_title'   => 'Create Document',
            'templates'    => $templates,
            'customers'    => $customers,
            'bookings'     => $bookings,
            'plots'        => $plots,
            'associates'   => $associates,
            'colonies'     => $colonies,
            'merge_fields' => $merge_fields,
        ]);
    }

    public function documentDetail($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $doc = $db->prepare("SELECT d.*, u.name as creator_name FROM legal_documents d LEFT JOIN users u ON d.created_by = u.id WHERE d.id = ? AND d.tenant_id = ?");
            $doc->execute([$id, $tid]);
            $doc = $doc->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $doc = null;
        }

        try {
            $uploads = $db->prepare("SELECT * FROM legal_document_uploads WHERE document_id = ? ORDER BY created_at DESC");
            $uploads->execute([$id]);
            $uploads = $uploads->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $uploads = [];
        }

        if (!$doc) {
            $this->setFlash('error', 'Document not found');
            $this->redirect('/admin/legal/documents');
            return;
        }

        return $this->render('admin/legal/document_detail', [
            'page_title' => $doc['title'] ?? 'Document Detail',
            'doc'        => $doc,
            'uploads'    => $uploads,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();

        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = trim($_POST['category'] ?? '');
        $document_type = trim($_POST['document_type'] ?? 'terms');
        $summary = trim($_POST['summary'] ?? '');
        $template_id = (int)($_POST['template_id'] ?? 0) ?: null;
        $entity_type = trim($_POST['entity_type'] ?? 'general');
        $is_mandatory = isset($_POST['is_mandatory']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';

        if (empty($title)) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/admin/legal/documents/create');
            return;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));

        try {
            $stmt = $db->prepare("INSERT INTO legal_documents (tenant_id, title, slug, template_id, category, document_type, content, summary, status, is_mandatory, entity_type, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$tid, $title, $slug, $template_id, $category, $document_type, $content, $summary, $status, $is_mandatory, $entity_type, $_SESSION['admin_id'] ?? null]);
            $docId = $db->lastInsertId();
            $this->setFlash('success', 'Document created');
            $this->redirect("/admin/legal/documents/$docId");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            $this->redirect('/admin/legal/documents/create');
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $doc = $db->prepare("SELECT d.*, u.name as creator_name FROM legal_documents d LEFT JOIN users u ON d.created_by = u.id WHERE d.id = ? AND d.tenant_id = ?");
            $doc->execute([$id, $tid]);
            $doc = $doc->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $doc = null;
        }

        try {
            $uploads = $db->prepare("SELECT * FROM legal_document_uploads WHERE document_id = ? ORDER BY created_at DESC");
            $uploads->execute([$id]);
            $uploads = $uploads->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $uploads = [];
        }

        if (!$doc) {
            $this->setFlash('error', 'Document not found');
            $this->redirect('/admin/legal/documents');
            return;
        }

        return $this->render('admin/legal/document_detail', [
            'page_title' => $doc['title'] ?? 'Document Detail',
            'doc'        => $doc,
            'uploads'    => $uploads,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        $fields = [];
        $params = [];
        $cols = ['title', 'content', 'category', 'document_type', 'summary', 'status', 'is_mandatory', 'entity_type'];
        foreach ($cols as $col) {
            if (isset($_POST[$col])) {
                $fields[] = "$col = ?";
                $params[] = $_POST[$col];
            }
        }
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        $params[] = $tid;

        if (empty($fields) || count($fields) <= 1) {
            $this->setFlash('info', 'No changes to save');
            $this->redirect("/admin/legal/documents/$id");
            return;
        }

        try {
            $sql = "UPDATE legal_documents SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $this->setFlash('success', 'Document updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect("/admin/legal/documents/$id");
    }

    public function updateStatus($id, $status)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;
        $allowed = ['active', 'inactive', 'draft', 'archived'];
        $status = in_array($status, $allowed) ? $status : 'active';

        try {
            $stmt = $db->prepare("UPDATE legal_documents SET status=? WHERE id=? AND tenant_id=?");
            $stmt->execute([$status, $id, $tid]);
            $this->setFlash('success', "Document status set to $status");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect("/admin/legal/documents/$id");
    }

    public function kycVerify($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("UPDATE legal_documents SET kyc_verified = 1, kyc_verified_at = NOW(), kyc_verified_by = ? WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$_SESSION['admin_id'] ?? null, $id, $tid]);
            $this->setFlash('success', 'Document KYC verified');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect("/admin/legal/documents/$id");
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $this->documentDetail($id);
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_documents WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Document deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/documents');
    }

    public function restore($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("UPDATE legal_documents SET deleted_at = NULL WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Document restored');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/documents');
    }

    public function forceDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_documents WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Document permanently deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/documents');
    }

    public function publish($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("UPDATE legal_documents SET status='active', published_at=NOW() WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Document published');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect("/admin/legal/documents/$id");
    }

    public function archive($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("UPDATE legal_documents SET status='inactive' WHERE id=? AND tenant_id=?");
            $stmt->execute([$id, $tid]);
            $this->setFlash('success', 'Document archived');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect("/admin/legal/documents/$id");
    }

    public function bulkAction()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = $this->tenantId();

        $action = $_POST['action'] ?? '';
        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            $this->setFlash('error', 'No documents selected');
            $this->redirect('/admin/legal/documents');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $intIds = array_map('intval', $ids);

        try {
            switch ($action) {
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM legal_documents WHERE id IN ($placeholders) AND tenant_id = ?");
                    $params = array_merge($intIds, [$tid]);
                    $stmt->execute($params);
                    break;
                case 'publish':
                    $stmt = $db->prepare("UPDATE legal_documents SET status='active', published_at=NOW() WHERE id IN ($placeholders) AND tenant_id = ?");
                    $params = array_merge($intIds, [$tid]);
                    $stmt->execute($params);
                    break;
                case 'archive':
                    $stmt = $db->prepare("UPDATE legal_documents SET status='inactive' WHERE id IN ($placeholders) AND tenant_id = ?");
                    $params = array_merge($intIds, [$tid]);
                    $stmt->execute($params);
                    break;
            }
            $this->setFlash('success', 'Bulk action completed');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/documents');
    }

    public function versions($id)
    {
        $this->requireAdmin();
        $this->setFlash('info', 'Version history not yet implemented');
        $this->redirect("/admin/legal/documents/$id");
    }

    public function showVersion($id, $versionId = null)
    {
        $this->requireAdmin();
        $this->redirect("/admin/legal/documents/$id");
    }

    public function createVersion($id)
    {
        $this->requireAdmin();
        $this->setFlash('info', 'Version creation not yet implemented');
        $this->redirect("/admin/legal/documents/$id");
    }

    public function restoreVersion($id, $versionId = null)
    {
        $this->requireAdmin();
        $this->setFlash('info', 'Version restore not yet implemented');
        $this->redirect("/admin/legal/documents/$id");
    }

    public function acceptanceStats($id)
    {
        $this->requireAdmin();
        $this->setFlash('info', 'Acceptance stats not yet implemented');
        $this->redirect("/admin/legal/documents/$id");
    }

    public function exportPdf($id)
    {
        $this->requireAdmin();
        $this->setFlash('info', 'PDF export not yet implemented');
        $this->redirect("/admin/legal/documents/$id");
    }

    // ── AI COMPOSER ────────────────────────────────────────

    public function aiComposer()
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $tid = (int)$this->tenantId();
        $tidParams = $tid > 1 ? [$tid] : [];

        try {
            $prompts = $db->query("SELECT * FROM legal_ai_prompts WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $prompts = [];
        }
        try {
            $cats = $db->query("SELECT id, name FROM legal_document_categories WHERE tenant_id = ? ORDER BY name", $tidParams)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $cats = [];
        }
        try {
            $customers = $db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $customers = [];
        }

        $merge_fields = ['{{name}}', '{{email}}', '{{phone}}', '{{address}}', '{{date}}', '{{company}}', '{{document_number}}'];

        return $this->render('admin/legal/ai_composer', [
            'page_title'   => 'AI Document Composer',
            'prompts'      => $prompts,
            'categories'   => $cats,
            'customers'    => $customers,
            'merge_fields' => $merge_fields,
        ]);
    }

    public function aiGenerate()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $prompt_id = (int)($_POST['prompt_id'] ?? 0);
        $custom_prompt = trim($_POST['custom_prompt'] ?? '');

        // Placeholder — actual AI generation requires AIGateway integration
        $this->setFlash('info', 'AI generation requires AIGateway configuration. Coming soon.');
        $this->redirect('/admin/legal/ai-composer');
    }

    // ── AI PROMPTS ─────────────────────────────────────────

    public function aiPrompts()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        try {
            $prompts = $db->query("SELECT * FROM legal_ai_prompts ORDER BY document_category, name")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $prompts = [];
        }

        return $this->render('admin/legal/ai_prompts', [
            'page_title' => 'AI Prompt Templates',
            'prompts'    => $prompts,
        ]);
    }

    public function aiPromptCreate()
    {
        $this->requireAdmin();
        $db = $this->getDb();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prompt_template = $_POST['prompt_template'] ?? '';
        $document_category = trim($_POST['document_category'] ?? '');
        $model = trim($_POST['model'] ?? 'gemini');
        $temperature = (float)($_POST['temperature'] ?? 0.3);
        $max_tokens = (int)($_POST['max_tokens'] ?? 2048);

        if (empty($name) || empty($prompt_template)) {
            $this->setFlash('error', 'Name and prompt template are required');
            $this->redirect('/admin/legal/ai-prompts');
            return;
        }

        try {
            $stmt = $db->prepare("INSERT INTO legal_ai_prompts (name, description, prompt_template, document_category, model, temperature, max_tokens, is_active, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())");
            $stmt->execute([$name, $description, $prompt_template, $document_category, $model, $temperature, $max_tokens, $_SESSION['admin_id'] ?? null]);
            $this->setFlash('success', 'Prompt created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/ai-prompts');
    }

    public function aiPromptUpdate($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prompt_template = $_POST['prompt_template'] ?? '';
        $document_category = trim($_POST['document_category'] ?? '');
        $model = trim($_POST['model'] ?? 'gemini');
        $temperature = (float)($_POST['temperature'] ?? 0.3);
        $max_tokens = (int)($_POST['max_tokens'] ?? 2048);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        try {
            $stmt = $db->prepare("UPDATE legal_ai_prompts SET name=?, description=?, prompt_template=?, document_category=?, model=?, temperature=?, max_tokens=?, is_active=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$name, $description, $prompt_template, $document_category, $model, $temperature, $max_tokens, $is_active, $id]);
            $this->setFlash('success', 'Prompt updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/ai-prompts');
    }

    public function aiPromptDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_ai_prompts WHERE id=?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Prompt deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/legal/ai-prompts');
    }

    // ── UPLOADS ────────────────────────────────────────────

    public function uploadVerify($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("UPDATE legal_document_uploads SET status='verified', verified_at=NOW(), verified_by=? WHERE id=?");
            $stmt->execute([$_SESSION['admin_id'] ?? null, $id]);
            $this->setFlash('success', 'Upload verified');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/legal/documents');
    }

    public function uploadDelete($id)
    {
        $this->requireAdmin();
        $db = $this->getDb();
        $id = (int)$id;

        try {
            $stmt = $db->prepare("DELETE FROM legal_document_uploads WHERE id=?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Upload deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/legal/documents');
    }
}
