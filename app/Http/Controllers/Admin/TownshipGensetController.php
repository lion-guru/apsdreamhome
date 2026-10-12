<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipGensetService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central DG Genset Power Backup & Diesel Consumption Audit Desk
 */
class TownshipGensetController extends AdminController
{
    private TownshipGensetService $gensetService;

    public function __construct()
    {
        parent::__construct();
        $this->gensetService = new TownshipGensetService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->gensetService->getLogs($colonyId, 50);
        $summary = $this->gensetService->getTodaySummary();

        $this->render('admin/genset/index', [
            'page_title' => 'Township Central DG Genset & Diesel Audit Desk',
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

        $result = $this->gensetService->logGensetRun($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to save DG Genset run';
        } else {
            $_SESSION['success'] = "DG Genset run logged! ({$result['run_hours']} hrs, {$result['diesel_consumed']} L diesel consumed, ₹" . number_format($result['diesel_expense_inr'], 2) . ")";
        }

        header('Location: /admin/township-genset');
        exit;
    }

    public function printSlip()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $log = $this->gensetService->getLogById($id);

        if (!$log) {
            $_SESSION['error'] = 'DG Genset log record not found';
            header('Location: /admin/township-genset');
            exit;
        }

        $this->render('admin/genset/print', [
            'page_title' => "DG Power Backup Run Certificate #{$log['id']}",
            'log' => $log
        ]);
    }
}
