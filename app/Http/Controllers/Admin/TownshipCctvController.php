<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipCctvService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central CCTV Surveillance & Camera Uptime Audit Desk
 */
class TownshipCctvController extends AdminController
{
    private TownshipCctvService $cctvService;

    public function __construct()
    {
        parent::__construct();
        $this->cctvService = new TownshipCctvService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->cctvService->getLogs($colonyId, 50);
        $summary = $this->cctvService->getTodaySummary();

        $this->render('admin/cctv/index', [
            'page_title' => 'Township CCTV Surveillance & Uptime Audit Desk',
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

        $result = $this->cctvService->logAudit($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save CCTV audit';
        } else {
            $_SESSION['success'] = "CCTV audit saved! (Uptime: {$result['uptime_percentage']}%, Offline: {$result['offline_count']} cameras)";
        }

        header('Location: /admin/township-cctv');
        exit;
    }

    public function printAudit()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $log = $this->cctvService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'CCTV log record not found';
            header('Location: /admin/township-cctv');
            exit;
        }

        $this->render('admin/cctv/print', [
            'page_title' => "CCTV Audit Certificate #{$log['id']}",
            'log' => $log
        ]);
    }
}
