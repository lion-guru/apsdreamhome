<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipHorticultureService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Central Park Horticulture & Green Canopy Tree Census Desk
 */
class TownshipHorticultureController extends AdminController
{
    private TownshipHorticultureService $horticultureService;

    public function __construct()
    {
        parent::__construct();
        $this->horticultureService = new TownshipHorticultureService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->horticultureService->getLogs($colonyId, 50);
        $summary = $this->horticultureService->getGreenSummary();

        $this->render('admin/horticulture/index', [
            'page_title' => 'Township Green Canopy & Park Horticulture Desk',
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

        $result = $this->horticultureService->recordLog($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to record horticulture log';
        } else {
            $_SESSION['success'] = "Park horticulture census logged! ({$result['trees_count']} trees audited)";
        }

        header('Location: /admin/township-horticulture');
        exit;
    }
}
