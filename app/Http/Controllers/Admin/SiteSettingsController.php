<?php
namespace App\Http\Controllers\Admin;

use App\Services\SiteContentService;

class SiteSettingsController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = SiteContentService::getInstance();
    }

    public function index()
    {
        $this->requireAdmin();
        $tab = $_GET['tab'] ?? 'general';

        $settings = $this->service->getSection('settings');

        $this->render('admin/site-settings/settings', [
            'page_title' => 'Site Settings',
            'active_tab' => $tab,
            'settings' => $settings,
            'success' => $_SESSION['site_settings_success'] ?? null,
            'error' => $_SESSION['site_settings_error'] ?? null,
        ]);
        unset($_SESSION['site_settings_success'], $_SESSION['site_settings_error']);
    }

    public function update()
    {
        $this->requireAdmin();

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['site_settings_error'] = 'Invalid CSRF token';
            $this->redirect('/admin/site-settings');
            return;
        }

        $data = $_POST['settings'] ?? [];
        $tab = $_POST['active_tab'] ?? 'general';

        // Handle image uploads for settings
        if (!empty($_FILES['settings_image'])) {
            $uploadDir = dirname(dirname(dirname(__DIR__))) . '/assets/images/site-settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['settings_image']['name'] as $key => $name) {
                if ($_FILES['settings_image']['error'][$key] === UPLOAD_ERR_OK && !empty($name)) {
                    $tmpPath = $_FILES['settings_image']['tmp_name'][$key];
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
                    if (in_array($ext, $allowed)) {
                        $filename = $key . '_' . time() . '.' . $ext;
                        $dest = $uploadDir . $filename;
                        if (move_uploaded_file($tmpPath, $dest)) {
                            $data[$key] = 'assets/images/site-settings/' . $filename;
                        }
                    }
                }
            }
        }

        if (!empty($data)) {
            $success = $this->service->bulkUpdate('settings', $data);
            if ($success) {
                $_SESSION['site_settings_success'] = ucfirst($tab) . ' settings saved successfully!';
            } else {
                $_SESSION['site_settings_error'] = 'Failed to save settings. Please try again.';
            }
        } else {
            $_SESSION['site_settings_error'] = 'No data received.';
        }

        $this->redirect('/admin/site-settings?tab=' . urlencode($tab));
    }
}
