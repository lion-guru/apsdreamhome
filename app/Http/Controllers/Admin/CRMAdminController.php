<?php
/**
 * CRM Admin — Role-based dashboards + Lead deduplication/merge
 */
namespace App\Http\Controllers\Admin;

use App\Core\Database;
use App\Services\CRMService;

class CRMAdminController extends AdminController
{
    /**
     * Role-based CRM dashboard — auto-detects user role and renders appropriate view
     */
    public function roleDashboard()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $service = new CRMService();

        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = strtolower($_SESSION['role'] ?? 'admin');

        $data = $service->getRoleDashboardData($userId, $role);

        // Override role via GET for testing
        if (!empty($_GET['role']) && in_array($_GET['role'], ['admin','manager','employee','associate','agent','telecaller'])) {
            $role = $_GET['role'];
            $data = $service->getRoleDashboardData($userId, $role);
        }

        $data['page_title'] = ucfirst($role) . ' CRM Dashboard';
        $data['current_role'] = $role;

        return $this->render('admin/crm/role_dashboard', $data);
    }

    /**
     * Lead deduplication — find and merge duplicate leads
     */
    public function dedup()
    {
        $this->requireAdmin();
        $service = new CRMService();

        $duplicates = $service->findDuplicates();

        return $this->render('admin/crm/dedup', [
            'duplicates' => $duplicates,
            'total_pairs' => count($duplicates),
            'page_title' => 'Lead Deduplication',
        ]);
    }

    /**
     * Merge two duplicate leads
     */
    public function merge()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/crm/dedup');
        }

        $keepId = (int)($_POST['keep_id'] ?? 0);
        $removeId = (int)($_POST['remove_id'] ?? 0);

        if ($keepId <= 0 || $removeId <= 0 || $keepId === $removeId) {
            $this->setFlash('error', 'Invalid lead IDs for merge');
            return $this->redirect('/admin/crm/dedup');
        }

        $service = new CRMService();
        $result = $service->mergeLeads($keepId, $removeId);

        if ($result['success']) {
            $merged = count($result['fields_merged'] ?? []);
            $this->setFlash('success', "Merged '{$result['removed']}' into '{$result['kept']}' — {$merged} fields updated");
        } else {
            $this->setFlash('error', 'Merge failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        return $this->redirect('/admin/crm/dedup');
    }

    /**
     * Bulk auto-merge — merge all duplicates automatically (keep newest)
     */
    public function bulkMerge()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/crm/dedup');
        }

        $service = new CRMService();
        $duplicates = $service->findDuplicates();

        $merged = 0;
        $errors = 0;

        foreach ($duplicates as $d) {
            // Keep the newer lead (higher ID = newer)
            $keepId = max($d['id1'], $d['id2']);
            $removeId = min($d['id1'], $d['id2']);

            $result = $service->mergeLeads($keepId, $removeId);
            if ($result['success']) {
                $merged++;
            } else {
                $errors++;
            }
        }

        $this->setFlash('success', "Bulk merge complete: {$merged} merged, {$errors} errors");
        return $this->redirect('/admin/crm/dedup');
    }
}
