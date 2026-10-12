<?php

namespace App\Http\Controllers\Admin;

use App\Services\SolarSubsidyService;
use App\Core\Database\Database;

/**
 * Admin PM Surya Ghar Green Solar Township Desk Controller
 */
class SolarDeskController extends AdminController
{
    private SolarSubsidyService $solarService;

    public function __construct()
    {
        parent::__construct();
        $this->solarService = new SolarSubsidyService();
    }

    public function index()
    {
        $this->requireAdmin();

        $status = !empty($_GET['status']) ? trim((string)$_GET['status']) : null;
        $requests = $this->solarService->getAllRequests($status);

        $pdo = Database::getInstance()->getConnection();
        $stats = $pdo->query(
            "SELECT COUNT(*) AS total_requests,
                    SUM(CASE WHEN status IN ('inquiry', 'site_feasibility') THEN 1 ELSE 0 END) AS pending_feasibility,
                    SUM(CASE WHEN status = 'discom_sanctioned' THEN 1 ELSE 0 END) AS discom_sanctioned,
                    SUM(CASE WHEN status = 'installed' THEN 1 ELSE 0 END) AS installed_count,
                    COALESCE(SUM(central_subsidy_amount + state_subsidy_amount), 0) AS total_subsidy_claimed,
                    COALESCE(SUM(system_capacity_kw), 0) AS total_kw_capacity
             FROM customer_solar_requests"
        )->fetch(\PDO::FETCH_ASSOC);

        $this->render('admin/green_energy/solar_desk', [
            'page_title' => 'PM Surya Ghar Solar Township Desk',
            'requests' => $requests,
            'stats' => $stats,
            'current_status' => $status
        ]);
    }

    public function update(int $id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $status = trim((string)($_POST['status'] ?? 'inquiry'));
        $extra = [
            'installer_partner' => trim((string)($_POST['installer_partner'] ?? '')),
            'installer_contact' => trim((string)($_POST['installer_contact'] ?? '')),
            'sanction_ref' => trim((string)($_POST['sanction_ref'] ?? '')),
            'notes' => trim((string)($_POST['notes'] ?? ''))
        ];

        $res = $this->solarService->updateRequestStatus($id, $status, $extra);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }

        $this->redirect('/admin/green-energy/solar');
    }
}
