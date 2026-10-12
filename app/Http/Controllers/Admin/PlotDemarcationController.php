<?php

namespace App\Http\Controllers\Admin;

use App\Services\PlotDemarcationService;
use App\Core\Database\Database;

/**
 * Township Plot Demarcation & Boundary Pillar Geotagging Survey Desk
 */
class PlotDemarcationController extends AdminController
{
    private PlotDemarcationService $demarcationService;

    public function __construct()
    {
        parent::__construct();
        $this->demarcationService = new PlotDemarcationService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;
        $status = trim((string)($_GET['status'] ?? 'all'));

        $demarcations = $this->demarcationService->getDemarcations($colonyId, $status);
        $stats = $this->demarcationService->getSummaryStats();

        $pdo = Database::getInstance()->getConnection();
        $colonies = $pdo->query(
            "SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $plots = $pdo->query(
            "SELECT p.id, p.plot_number, p.area_sqft, c.name AS colony_name
             FROM plots p
             JOIN colonies c ON p.colony_id = c.id
             ORDER BY c.name ASC, p.plot_number ASC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/plots/demarcation', [
            'page_title' => 'Plot Demarcation & Boundary Pillars Desk',
            'demarcations' => $demarcations,
            'stats' => $stats,
            'colonies' => $colonies,
            'plots' => $plots,
            'current_colony' => $colonyId,
            'current_status' => $status,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $plotId = (int)($_POST['plot_id'] ?? 0);
        $data = [
            'surveyor_name' => trim((string)($_POST['surveyor_name'] ?? '')),
            'surveyor_phone' => trim((string)($_POST['surveyor_phone'] ?? '')),
            'demarcation_date' => $_POST['demarcation_date'] ?? date('Y-m-d'),
            'pillar_status' => $_POST['pillar_status'] ?? 'pegs_driven',
            'corner_nw_lat' => $_POST['corner_nw_lat'] ?? null,
            'corner_nw_lng' => $_POST['corner_nw_lng'] ?? null,
            'corner_ne_lat' => $_POST['corner_ne_lat'] ?? null,
            'corner_ne_lng' => $_POST['corner_ne_lng'] ?? null,
            'corner_sw_lat' => $_POST['corner_sw_lat'] ?? null,
            'corner_sw_lng' => $_POST['corner_sw_lng'] ?? null,
            'corner_se_lat' => $_POST['corner_se_lat'] ?? null,
            'corner_se_lng' => $_POST['corner_se_lng'] ?? null,
            'possession_peg_handed_over' => !empty($_POST['possession_peg_handed_over']) ? 1 : 0,
            'boundary_notes' => trim((string)($_POST['boundary_notes'] ?? ''))
        ];

        $res = $this->demarcationService->recordDemarcation($plotId, $data);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }

        $this->redirect('/admin/plots/demarcation');
    }

    public function verify(int $id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $adminId = $_SESSION['user']['id'] ?? 1;
        $status = $_POST['pillar_status'] ?? 'verified';

        $ok = $this->demarcationService->updateStatus($id, $status, $adminId);

        if ($ok) {
            $this->setFlash('success', "Demarcation #{$id} status updated to '{$status}'.");
        } else {
            $this->setFlash('error', "Failed to update demarcation status.");
        }

        $this->redirect('/admin/plots/demarcation');
    }

    public function printCertificate(int $id)
    {
        $this->requireAdmin();

        $dem = $this->demarcationService->getDemarcationById($id);
        if (!$dem) {
            $this->setFlash('error', 'Demarcation record not found.');
            $this->redirect('/admin/plots/demarcation');
            return;
        }

        $this->render('admin/plots/demarcation_print', [
            'dem' => $dem
        ]);
    }
}
