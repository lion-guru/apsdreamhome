<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipLightingService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central Street Lighting & Feeder Pillar Uptime Desk Controller
 */
class TownshipLightingController extends AdminController
{
    private TownshipLightingService $lightingService;

    public function __construct()
    {
        parent::__construct();
        $this->lightingService = new TownshipLightingService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->lightingService->getLogs($colonyId, 50);
        $summary = $this->lightingService->getTodaySummary();

        $this->render('admin/lighting/index', [
            'page_title' => 'Township Central Street Lighting & Feeder Pillar Uptime Desk',
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

        $result = $this->lightingService->logLightingAudit($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save street lighting audit';
        } else {
            $_SESSION['success'] = "Street lighting audit logged successfully! (Uptime: {$result['uptime_percent']}%, {$result['poles_functional']} functional poles)";
        }

        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;
        $redirectUrl = '/admin/township-lighting' . ($colonyId ? '?colony_id=' . $colonyId : '');
        header('Location: ' . $redirectUrl);
        exit;
    }

    public function print($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $log = $this->lightingService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'Lighting log not found';
            header('Location: /admin/township-lighting');
            exit;
        }

        $this->render('admin/lighting/print', [
            'page_title' => 'Township Street Lighting Uptime Certificate',
            'log' => $log
        ], 'layouts/empty');
    }
}
