<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipWaterService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central Borewell Pumping & Potable Water Supply Daily Utility Log Desk
 */
class TownshipWaterController extends AdminController
{
    private TownshipWaterService $waterService;

    public function __construct()
    {
        parent::__construct();
        $this->waterService = new TownshipWaterService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->waterService->getLogs($colonyId, 50);
        $summary = $this->waterService->getTodaySummary();

        $this->render('admin/water_supply/index', [
            'page_title' => 'Township Borewell & Potable Water Supply Log Desk',
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

        $result = $this->waterService->logPumpingRun($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save water log';
        } else {
            $_SESSION['success'] = "Pumping run logged successfully! ({$result['water_pumped_kl']} kL pumped)";
        }

        header('Location: /admin/township-water-supply');
        exit;
    }

    public function printLog()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $log = $this->waterService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'Water log record not found';
            header('Location: /admin/township-water-supply');
            exit;
        }

        $quality = $this->waterService->evaluatePotableQuality((int)$log['tds_ppm'], (float)$log['ph_level']);

        $this->render('admin/water_supply/print', [
            'page_title' => "Water Pumping Certificate #{$log['id']}",
            'log' => $log,
            'quality' => $quality
        ]);
    }
}
