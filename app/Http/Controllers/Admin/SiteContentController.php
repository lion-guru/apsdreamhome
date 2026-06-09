<?php
namespace App\Http\Controllers\Admin;

use App\Services\SiteContentService;

class SiteContentController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = SiteContentService::getInstance();
    }

    /**
     * GET /admin/site-content — Section list
     */
    public function index()
    {
        $this->requireAdmin();
        $sections = $this->service->getSections();

        $this->render('admin/site-content/index', [
            'page_title' => 'Site Content Manager',
            'sections' => $sections,
        ]);
    }

    /**
     * GET /admin/site-content/edit/{section} — Edit a section
     */
    public function edit($section = 'about')
    {
        $this->requireAdmin();
        $items = $this->service->getFullSection($section);
        $grouped = [];
        foreach ($items as $item) {
            $group = $item['content_group'] ?? 'general';
            $grouped[$group][] = $item;
        }

        $this->render('admin/site-content/edit', [
            'page_title' => 'Edit: ' . ucfirst($section) . ' Content',
            'section' => $section,
            'items' => $items,
            'grouped' => $grouped,
        ]);
    }

    /**
     * POST /admin/site-content/update/{section} — Save section
     */
    public function update($section = 'about')
    {
        $this->requireAdmin();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $data = $_POST['content'] ?? [];
        if (empty($data)) {
            $this->setFlash('error', 'No data received');
            $this->redirect('/admin/site-content/edit/' . $section);
            return;
        }

        // Handle image uploads
        if (!empty($_FILES['content_image'])) {
            foreach ($_FILES['content_image']['name'] as $key => $name) {
                if ($_FILES['content_image']['error'][$key] === UPLOAD_ERR_OK && !empty($name)) {
                    $tmpPath = $_FILES['content_image']['tmp_name'][$key];
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (in_array($ext, $allowed)) {
                        $filename = 'content_' . $section . '_' . $key . '_' . time() . '.' . $ext;
                        $dest = ROOT_PATH . '/assets/images/content/' . $filename;
                        if (!is_dir(dirname($dest))) {
                            mkdir(dirname($dest), 0755, true);
                        }
                        if (move_uploaded_file($tmpPath, $dest)) {
                            $data[$key] = 'assets/images/content/' . $filename;
                        }
                    }
                }
            }
        }

        $success = $this->service->bulkUpdate($section, $data);
        if ($success) {
            $this->setFlash('success', ucfirst($section) . ' content updated successfully');
        } else {
            $this->setFlash('error', 'Failed to update content');
        }

        $this->redirect('/admin/site-content/edit/' . $section);
    }

    /**
     * POST /admin/site-content/create — Add new content entry
     */
    public function create()
    {
        $this->requireAdmin();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $success = $this->service->create([
            'section'       => $_POST['section'] ?? '',
            'content_key'   => $_POST['content_key'] ?? '',
            'content_value' => $_POST['content_value'] ?? '',
            'content_type'  => $_POST['content_type'] ?? 'text',
            'content_group' => $_POST['content_group'] ?? null,
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_active'     => 1,
        ]);

        if ($success) {
            $this->setFlash('success', 'Content entry created');
        } else {
            $this->setFlash('error', 'Failed to create entry');
        }

        $this->redirect('/admin/site-content/edit/' . ($_POST['section'] ?? 'about'));
    }

    /**
     * POST /admin/site-content/delete — Delete content entry
     */
    public function delete()
    {
        $this->requireAdmin();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $section = $_POST['section'] ?? '';
        $key = $_POST['content_key'] ?? '';

        $success = $this->service->delete($section, $key);
        if ($success) {
            $this->setFlash('success', 'Content entry deleted');
        } else {
            $this->setFlash('error', 'Failed to delete entry');
        }

        $this->redirect('/admin/site-content/edit/' . $section);
    }
}
