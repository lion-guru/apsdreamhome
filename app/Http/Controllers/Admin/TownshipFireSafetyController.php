<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipFireSafetyService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central Fire Hydrant Network & UP Fire Safety NOC Inspection Desk Controller
 */
class TownshipFireSafetyController extends AdminController
{
    private TownshipFireSafetyService $fireService;

    public function __construct()
    {
        parent::__construct();
        $this->fireService = new TownshipFireSafetyService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->fireService->getLogs($colonyId, 50);
        $summary = $this->fireService->getTodaySummary($colonyId);

        $this->render('admin/fire_safety/index', [
            'page_title' => 'Township Central Fire Hydrant Network & UP Fire NOC Desk',
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

        $result = $this->fireService->logFireInspection($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save Fire Safety inspection log';
        } else {
            $_SESSION['success'] = "Fire hydrant network inspection logged! (Line Pressure: {$result['pressure_bar']} Bar, Valve Health: {$result['valve_health_pct']}%, NOC Status: {$result['noc_status']})";
        }

        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $redirectUrl = '/admin/township-fire-safety' . ($colonyId ? '?colony_id=' . $colonyId : '');
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function print($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $log = $this->fireService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'Fire safety inspection record not found';
            header('Location: /admin/township-fire-safety');
            exit;
        }

        $this->render('admin/fire_safety/print', [
            'page_title' => 'Township Fire Hydrant & NOC Inspection Certificate',
            'log' => $log
        ], 'layouts/empty');
    }
}
