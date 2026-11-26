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
        $smtpHealth = \App\Services\SmtpHealthService::report();

        $maint = new \App\Services\MaintenanceService();
        $maintenance = [
            'enabled' => $maint->isEnabled(),
            'message' => $maint->getMessage(),
            'eta' => $maint->getEta(),
            'ips' => implode(', ', $maint->getAllowedIps()),
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ];

        $this->render('admin/site-settings/settings', [
            'page_title' => 'Site Settings',
            'active_tab' => $tab,
            'settings' => $settings,
            'maintenance' => $maintenance,
            'smtpHealth' => $smtpHealth,
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
            $uploadDir = (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 4)) . '/assets/images/site-settings/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
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
            // Handle maintenance mode persistence
            if ($tab === 'general' && isset($data['maintenance_mode'])) {
                try {
                    $maint = new \App\Services\MaintenanceService();
                    if ($data['maintenance_mode'] === '1') {
                        $maint->enable($data['maintenance_message'] ?? '', $data['maintenance_eta'] ?? '');
                    } else {
                        $maint->disable();
                    }
                    if (isset($data['maintenance_ips'])) {
                        $rawIps = array_values(array_filter(array_map('trim', explode(',', (string)($data['maintenance_ips'] ?? ''))), fn($ip) => $ip !== ''));
                        foreach ($rawIps as $ip) {
                            $maint->addAllowedIp($ip);
                        }
                    }
                } catch (\Throwable $t) {
                    error_log("SiteSettingsController: Maintenance mode update failed: " . $t->getMessage());
                }
            }

            $success = $this->service->bulkUpdate('settings', $data);
            if ($success) {
                if ($tab === 'email') {
                    $this->syncSmtpToEnv($data);
                }
                $_SESSION['site_settings_success'] = ucfirst($tab) . ' settings saved successfully!';
            } else {
                $_SESSION['site_settings_error'] = 'Failed to save settings. Please try again.';
            }
        } else {
            $_SESSION['site_settings_error'] = 'No data received.';
        }

        $this->redirect('/admin/site-settings?tab=' . urlencode($tab));
    }

    public function clearCache()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $this->service->clearAllCache();
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            echo json_encode(['success' => true, 'message' => 'Site settings and template caches cleared successfully!']);
        } catch (\Throwable $t) {
            echo json_encode(['success' => false, 'message' => 'Cache clear error: ' . $t->getMessage()]);
        }
        exit;
    }

    /**
     * Synchronize SMTP settings saved from the UI into .env and app_settings
     */
    private function syncSmtpToEnv(array $data): void
    {
        try {
            $envFile = dirname(dirname(dirname(__DIR__))) . '/.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);

                $map = [
                    'smtp_host' => ['SMTP_HOST', 'MAIL_HOST'],
                    'smtp_port' => ['SMTP_PORT', 'MAIL_PORT'],
                    'smtp_username' => ['SMTP_USER', 'MAIL_USERNAME'],
                    'smtp_password' => ['SMTP_PASS', 'MAIL_PASSWORD'],
                    'smtp_encryption' => ['SMTP_ENCRYPTION', 'MAIL_ENCRYPTION'],
                    'smtp_from_name' => ['SMTP_FROM_NAME', 'MAIL_FROM_NAME'],
                    'smtp_from_email' => ['SMTP_FROM_EMAIL', 'MAIL_FROM_ADDRESS'],
                ];

                foreach ($map as $formKey => $envKeys) {
                    if (isset($data[$formKey]) && $data[$formKey] !== '') {
                        $val = trim($data[$formKey]);
                        if ($formKey === 'smtp_password') {
                            $val = str_replace([' ', '-'], '', $val);
                        }
                        foreach ($envKeys as $envKey) {
                            if (preg_match("/^{$envKey}=.*/m", $content)) {
                                $content = preg_replace("/^{$envKey}=.*/m", "{$envKey}={$val}", $content);
                            } else {
                                $content .= "\n{$envKey}={$val}";
                            }
                        }
                    }
                }
                file_put_contents($envFile, $content);
            }

            // Also keep app_settings table in sync
            $db = \App\Core\Database\Database::getInstance();
            foreach ($data as $k => $v) {
                if (str_starts_with($k, 'smtp_')) {
                    $val = trim($v);
                    if ($k === 'smtp_password') {
                        $val = str_replace([' ', '-'], '', $val);
                    }
                    $db->query(
                        "INSERT INTO app_settings (setting_key, setting_value, updated_at) 
                         VALUES (?, ?, NOW()) 
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                        [$k, $val]
                    );
                }
            }
        } catch (\Throwable $t) {
            error_log("SiteSettingsController: Failed to sync SMTP to .env: " . $t->getMessage());
        }
    }

    public function edit()
    {
        $this->requireAdmin();
        return $this->jsonResponse(['success' => true]);
    }

    public function reset()
    {
        $this->requireAdmin();
        return $this->jsonResponse(['success' => true]);
    }

    public function getStats()
    {
        $this->requireAdmin();
        return $this->jsonResponse(['success' => true]);
    }

    public function export()
    {
        $this->requireAdmin();
        return $this->jsonResponse(['success' => true]);
    }

    public function getCategory()
    {
        $this->requireAdmin();
        return $this->jsonResponse(['success' => true]);
    }
}
