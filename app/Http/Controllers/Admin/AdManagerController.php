<?php
// NOTE: Direct writes here target app_settings (global config table — does NOT have tenant_id, cross-tenant).
// app_settings table does not exist in current schema; AdManager business writes (ad_placements — tenant_id YES) are delegated to AdManagerService which is tenant-scoped. This controller intentionally NOT tenant-scoped for direct reads/writes.

namespace App\Http\Controllers\Admin;

use App\Services\AdManagerService;

class AdManagerController extends AdminController
{
    private $adService;

    public function __construct()
    {
        parent::__construct();
        $this->adService = new AdManagerService();
    }

    public function index()
    {
        $this->requireAdmin();
        $slots = $this->adService->getAllSlots();

        $summary = ['total' => count($slots), 'active' => 0, 'total_views' => 0, 'total_clicks' => 0];
        foreach ($slots as $s) {
            if ($s['status'] === 'active') $summary['active']++;
            $summary['total_views'] += (int)($s['views'] ?? 0);
            $summary['total_clicks'] += (int)($s['clicks'] ?? 0);
        }

        $this->render('admin/ads/index', [
            'page_title' => 'Ad Manager',
            'slots' => $slots,
            'summary' => $summary,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $this->adService->upsertSlot($_POST);
            $this->flashMessage('Ad slot saved', 'success');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        $this->render('admin/ads/form', [
            'page_title' => 'New Ad Slot',
            'ad' => null,
        ]);
    }

    public function edit(int $id)
    {
        $this->requireAdmin();
        $slots = $this->adService->getAllSlots();
        $ad = null;
        foreach ($slots as $s) {
            if ((int)$s['id'] === $id) { $ad = $s; break; }
        }

        if (!$ad) {
            $this->flashMessage('Ad not found', 'error');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $_POST['id'] = $id;
            $this->adService->upsertSlot($_POST);
            $this->flashMessage('Ad slot updated', 'success');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        $this->render('admin/ads/form', [
            'page_title' => 'Edit Ad: ' . $ad['title'],
            'ad' => $ad,
        ]);
    }

    public function delete(int $id)
    {
        $this->requireAdmin();
        $this->adService->deleteSlot($id);
        $this->flashMessage('Ad slot deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/ads');
        exit;
    }

    public function settings()
    {
        $this->requireAdmin();

        $adsensePublisherId = '';
        $autoAdCode = '';

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
            $stmt->execute(['adsense_publisher_id']);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $adsensePublisherId = $row['setting_value'];
            }
            $stmt2 = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
            $stmt2->execute(['adsense_auto_ad_code']);
            $row2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
            if ($row2) {
                $autoAdCode = $row2['setting_value'];
            }
        } catch (\Exception $e) { error_log('AdManagerController index: ' . $e->getMessage()); }

        $this->render('admin/ads/settings', [
            'page_title' => 'AdSense Settings',
            'adsense_publisher_id' => $adsensePublisherId,
            'auto_ad_code' => $autoAdCode,
        ]);
    }

    public function trackClick(int $id)
    {
        try {
            $svc = new \App\Services\AdManagerService();
            $svc->incrementClicks($id);
        } catch (\Exception $e) {
                    error_log("AdManagerController.php: " . $e->getMessage());
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $ref);
        exit;
    }

    public function saveSettings()
    {
        $this->requireAdmin();

        $publisherId = $_POST['adsense_publisher_id'] ?? '';
        $autoAdCode = $_POST['auto_ad_code'] ?? '';

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $stmt = $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute(['adsense_publisher_id', $publisherId, $publisherId]);

            $stmt2 = $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt2->execute(['adsense_auto_ad_code', $autoAdCode, $autoAdCode]);

            $this->flashMessage('AdSense settings saved', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Error saving settings: ' . $e->getMessage(), 'error');
        }

        header('Location: ' . BASE_URL . '/admin/ads/settings');
        exit;
    }
}
