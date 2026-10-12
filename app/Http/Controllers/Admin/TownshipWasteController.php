<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipWasteService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Solid Waste Management & Sanitation Collection Desk
 */
class TownshipWasteController extends AdminController
{
    private TownshipWasteService $wasteService;

    public function __construct()
    {
        parent::__construct();
        $this->wasteService = new TownshipWasteService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $logs = $this->wasteService->getLogs($colonyId, 50);
        $summary = $this->wasteService->getWasteSummary();

        $this->render('admin/waste/index', [
            'page_title' => 'Township Solid Waste & Sanitation Desk',
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

        $result = $this->wasteService->recordCollection($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to record waste collection log';
        } else {
            $_SESSION['success'] = "Sanitation & waste log recorded! ({$result['total_waste_kg']} kg collected)";
        }

        header('Location: /admin/township-waste');
        exit;
    }
}
