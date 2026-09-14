<?php
namespace App\Http\Controllers\Admin;

class PagesController extends AdminController
{
    /**
     * List all CMS pages
     */
    public function index()
    {
        $this->requireAdmin();
        $pages = [];
        try {
            $stmt = $this->db->query("SELECT id, title, slug, status, updated_at FROM pages ORDER BY id");
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { error_log('PagesController::index error: ' . $e->getMessage()); }
        $this->render('admin/pages/index', ['pages' => $pages, 'page_title' => 'CMS Pages']);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/pages/create', ['page_title' => 'Create New Page']);
    }

    /**
     * Store new page + initial version
     */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if (empty($title) || empty($slug)) {
            $this->setFlash('error', 'Title and Slug are required.');
            return $this->redirect('/admin/pages/create');
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, tenant_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $slug, $content, $meta_description, $meta_keywords, $status, $this->tenantId()]);
            $pageId = $this->db->lastInsertId();

            // Save initial version
            $this->saveVersion($pageId, 1, $title, $slug, $content, $meta_description, $meta_keywords, $status, 'Initial version');

            $this->setFlash('success', "Page \"{$title}\" created successfully.");
        } catch (\Exception $e) {
            error_log('PagesController::store error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create page.');
        }
        return $this->redirect('/admin/pages');
    }

    /**
     * Show edit form + version history
     */
    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $page = null;
        $versions = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($page) {
                $vstmt = $this->db->prepare("SELECT * FROM page_versions WHERE page_id = ? ORDER BY version_number DESC LIMIT 20");
                $vstmt->execute([$id]);
                $versions = $vstmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) { error_log('PagesController::edit error: ' . $e->getMessage()); }

        if (!$page) {
            $this->setFlash('error', 'Page not found.');
            return $this->redirect('/admin/pages');
        }

        $this->render('admin/pages/edit', [
            'page' => $page,
            'versions' => $versions,
            'page_title' => 'Edit: ' . $page['title'],
        ]);
    }

    /**
     * Update page + auto-save version
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $change_summary = trim($_POST['change_summary'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Title is required.');
            return $this->redirect("/admin/pages/edit/{$id}");
        }

        try {
            // Get current version number
            $vstmt = $this->db->prepare("SELECT COALESCE(MAX(version_number), 0) + 1 AS next_version FROM page_versions WHERE page_id = ?");
            $vstmt->execute([$id]);
            $nextVersion = (int)$vstmt->fetchColumn();

            // Get current slug
            $slugStmt = $this->db->prepare("SELECT slug FROM pages WHERE id = ?");
            $slugStmt->execute([$id]);
            $slug = $slugStmt->fetchColumn() ?: '';

            // Update page
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE pages SET title = ?, content = ?, meta_description = ?, meta_keywords = ?, status = ?, updated_at = NOW() WHERE id = ? $tenantSql");
            $stmt->execute(array_merge([$title, $content, $meta_description, $meta_keywords, $status, $id], $tenantParams));

            // Auto-save version snapshot
            $this->saveVersion($id, $nextVersion, $title, $slug, $content, $meta_description, $meta_keywords, $status, $change_summary ?: "Updated via admin panel");

            $this->setFlash('success', "Page \"{$title}\" updated. Version #{$nextVersion} saved.");
        } catch (\Exception $e) {
            error_log('PagesController::update error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update page.');
        }
        return $this->redirect("/admin/pages/edit/{$id}");
    }

    /**
     * Restore a page to a previous version
     * POST /admin/pages/{id}/restore/{versionId}
     */
    public function restore($id, $versionId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $versionId = (int)$versionId;

        try {
            // Fetch the version snapshot
            $stmt = $this->db->prepare("SELECT * FROM page_versions WHERE id = ? AND page_id = ?");
            $stmt->execute([$versionId, $id]);
            $version = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$version) {
                $this->setFlash('error', 'Version not found.');
                return $this->redirect("/admin/pages/edit/{$id}");
            }

            // Get next version number
            $vstmt = $this->db->prepare("SELECT COALESCE(MAX(version_number), 0) + 1 AS next_version FROM page_versions WHERE page_id = ?");
            $vstmt->execute([$id]);
            $nextVersion = (int)$vstmt->fetchColumn();

            // Restore page content from version
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $ustmt = $this->db->prepare("UPDATE pages SET title = ?, content = ?, meta_description = ?, meta_keywords = ?, status = ?, updated_at = NOW() WHERE id = ? $tenantSql");
            $ustmt->execute(array_merge([
                $version['title'], $version['content'], $version['meta_description'],
                $version['meta_keywords'], $version['status'], $id
            ], $tenantParams));

            // Save as new version with restore note
            $this->saveVersion($id, $nextVersion, $version['title'], $version['slug'], $version['content'],
                $version['meta_description'], $version['meta_keywords'], $version['status'],
                "Restored from version #{$version['version_number']}"
            );

            $this->setFlash('success', "Page restored to version #{$version['version_number']}. New version #{$nextVersion} created.");
        } catch (\Exception $e) {
            error_log('PagesController::restore error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to restore version.');
        }
        return $this->redirect("/admin/pages/edit/{$id}");
    }

    /**
     * AJAX: Preview page content as it would appear on the public site
     * GET /admin/pages/{id}/preview
     */
    public function preview($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $stmt = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) { $page = null; }

        if (!$page) {
            http_response_code(404);
            echo 'Page not found.';
            return;
        }

        // Render preview with the same view the public would see
        $slug = $page['slug'] ?? '';
        $viewMap = [
            'terms-conditions'      => 'pages/terms',
            'privacy-policy'        => 'pages/privacy',
            'refund-policy'         => 'pages/refund_policy',
            'disclaimer'            => 'pages/disclaimer',
            'cancellation-policy'   => 'pages/cancellation_policy',
            'associate-rules'       => 'pages/associate_rules',
        ];

        $view = $viewMap[$slug] ?? null;
        if ($view && file_exists(APP_PATH . "/views/{$view}.php")) {
            $this->render($view, [
                'page_title' => ($page['title'] ?? '') . ' [PREVIEW]',
                'pageContent' => $page['content'] ?? '',
                'preview_mode' => true,
            ]);
        } else {
            // Generic preview: render content directly
            echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($page['title'] ?? '') . ' [PREVIEW]</title>';
            echo '<link href="' . BASE_URL . '/assets/css/bootstrap.min.css" rel="stylesheet">';
            echo '<style>body{max-width:900px;margin:40px auto;padding:0 20px;font-family:Georgia,serif;line-height:1.8;color:#1a1a2e;}';
            echo '.preview-banner{background:#ffc107;color:#000;padding:10px 20px;border-radius:8px;margin-bottom:20px;text-align:center;font-family:sans-serif;font-size:14px;}';
            echo 'h2,h3{color:#0a192f;}</style></head><body>';
            echo '<div class="preview-banner"><i class="fas fa-eye"></i> PREVIEW MODE — This is how the page will appear to visitors. <a href="' . BASE_URL . '/admin/pages/edit/' . $id . '">Back to Editor</a></div>';
            echo '<h1>' . htmlspecialchars($page['title'] ?? '') . '</h1>';
            echo $page['content'] ?? '';
            echo '</body></html>';
        }
    }

    /**
     * Save a version snapshot
     */
    private function saveVersion(int $pageId, int $version, string $title, string $slug, string $content, string $metaDesc, string $metaKw, string $status, string $summary): void
    {
        try {
            $adminName = $_SESSION['admin_name'] ?? $_SESSION['admin_email'] ?? 'Admin';
            $stmt = $this->db->prepare("
                INSERT INTO page_versions (page_id, version_number, title, slug, content, meta_description, meta_keywords, status, change_summary, changed_by, changed_by_name, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $pageId, $version, $title, $slug, $content, $metaDesc, $metaKw, $status,
                $summary, $_SESSION['admin_id'] ?? null, $adminName, $this->tenantId()
            ]);
        } catch (\Exception $e) {
            error_log('PagesController::saveVersion error: ' . $e->getMessage());
        }
    }
}
