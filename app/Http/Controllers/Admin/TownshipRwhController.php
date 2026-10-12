<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipRwhService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Rainwater Harvesting & Groundwater Recharge Desk Controller
 */
class TownshipRwhController extends AdminController
{
    private TownshipRwhService $rwhService;

    public function __construct()
    {
        parent::__construct();
        $this->rwhService = new TownshipRwhService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->rwhService->getLogs($colonyId, 50);
        $summary = $this->rwhService->getTodaySummary();

        $this->render('admin/rwh/index', [
            'page_title' => 'Township Rainwater Harvesting & Groundwater Recharge Desk',
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

        $result = $this->rwhService->logRwhAudit($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save rainwater harvesting audit';
        } else {
            $_SESSION['success'] = "Rainwater recharge pit audit logged successfully! ({$result['recharge_capacity_kl']} kL capacity, Status: {$result['readiness']})";
        }

        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $redirectUrl = '/admin/township-rwh' . ($colonyId ? '?colony_id=' . $colonyId : '');
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function print($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $log = $this->rwhService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'RWH log not found';
            header('Location: /admin/township-rwh');
            exit;
        }

        $this->render('admin/rwh/print', [
            'page_title' => 'Rainwater Harvesting & Ground Water Compliance Certificate',
            'log' => $log
        ], 'layouts/empty');
    }
}
