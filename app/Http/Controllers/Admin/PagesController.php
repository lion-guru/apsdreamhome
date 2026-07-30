<?php
namespace App\Http\Controllers\Admin;

class PagesController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $pages = [];
        try {
            $stmt = $this->db->query("SELECT id, title, slug, status, updated_at FROM pages ORDER BY title");
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log('PagesController exception: ' . $e->getMessage()); }
        $this->render('admin/pages/index', ['pages' => $pages, 'page_title' => 'CMS Pages']);
    }

    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/pages/create', ['page_title' => 'Create New Page']);
    }

    public function store()
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $content = $_POST['content'] ?? '';
        $meta_description = $_POST['meta_description'] ?? '';
        $meta_keywords = $_POST['meta_keywords'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        try {
            $stmt = $this->db->prepare("INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $slug, $content, $meta_description, $meta_keywords, $status, $this->tenantId()]);
        } catch (\Exception $e) { error_log('PagesController exception: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/pages');
        exit;
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $page = null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log('PagesController exception: ' . $e->getMessage()); }
        if (!$page) {
            header('Location: ' . BASE_URL . '/admin/pages');
            exit;
        }
        $this->render('admin/pages/edit', ['page' => $page, 'page_title' => 'Edit: ' . $page['title']]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $meta_description = $_POST['meta_description'] ?? '';
        $meta_keywords = $_POST['meta_keywords'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE pages SET title = ?, content = ?, meta_description = ?, meta_keywords = ?, status = ?, updated_at = NOW() WHERE id = ? $tenantSql");
            $stmt->execute(array_merge([$title, $content, $meta_description, $meta_keywords, $status, $id], $tenantParams));
        } catch (\Exception $e) { error_log('PagesController exception: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/pages');
        exit;
    }
}
