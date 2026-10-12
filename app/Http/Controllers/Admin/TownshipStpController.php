<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipStpService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central Sewage Treatment Plant (STP) & UPPCB Effluent Recycling Desk Controller
 */
class TownshipStpController extends AdminController
{
    private TownshipStpService $stpService;

    public function __construct()
    {
        parent::__construct();
        $this->stpService = new TownshipStpService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->stpService->getLogs($colonyId, 50);
        $summary = $this->stpService->getTodaySummary($colonyId);

        $this->render('admin/stp/index', [
            'page_title' => 'Township Central STP Effluent & UPPCB Compliance Desk',
            'colonies' => $colonies,
            'logs' => $logs,
            'summary' => $summary,
            'current_colony' => $colonyId
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->stpService->logStpRun($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save STP effluent run log';
        } else {
            $_SESSION['success'] = "STP effluent log recorded successfully! (Treated: {$result['treated_water_output_kl']} kL, BOD Removal: {$result['bod_efficiency_pct']}%, Status: {$result['compliance_status']})";
        }

        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $redirectUrl = '/admin/township-stp' . ($colonyId ? '?colony_id=' . $colonyId : '');
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function print($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $log = $this->stpService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'STP effluent log record not found';
            header('Location: /admin/township-stp');
            exit;
        }

        $this->render('admin/stp/print', [
            'page_title' => 'Township STP Effluent Analysis Certificate',
            'log' => $log
        ], 'layouts/empty');
    }
}
