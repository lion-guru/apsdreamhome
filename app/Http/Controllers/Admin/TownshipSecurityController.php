<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipSecurityService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Security Guard Shift Roster & Night Patrol Incident Desk
 */
class TownshipSecurityController extends AdminController
{
    private TownshipSecurityService $securityService;

    public function __construct()
    {
        parent::__construct();
        $this->securityService = new TownshipSecurityService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $rosters = $this->securityService->getRosters($colonyId, 30);
        $incidents = $this->securityService->getIncidents($colonyId, 30);
        $metrics = $this->securityService->getTodaySecurityMetrics();

        $this->render('admin/township_security/index', [
            'page_title' => 'Township Security Guard Shift & Patrol Desk',
            'colonies' => $colonies,
            'rosters' => $rosters,
            'incidents' => $incidents,
            'metrics' => $metrics,
            'current_colony' => $colonyId
        ]);
    }

    public function storeRoster()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->securityService->assignRoster($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to record security roster';
        } else {
            $_SESSION['success'] = 'Security guard shift recorded successfully!';
        }

        header('Location: /admin/township-security');
        exit;
    }

    public function storeIncident()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->securityService->logIncident($_POST);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'] ?? 'Failed to log incident';
        } else {
            $_SESSION['success'] = "Incident logged successfully! Token: {$result['incident_token']}";
        }

        header('Location: /admin/township-security');
        exit;
    }

    public function resolveIncident()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $incidentId = (int)($_POST['incident_id'] ?? 0);
        $notes = trim($_POST['resolution_notes'] ?? '');
        $adminName = $_SESSION['user_name'] ?? 'Admin Executive';

        if ($incidentId > 0 && $notes !== '') {
            $this->securityService->resolveIncident($incidentId, $notes, $adminName);
            $_SESSION['success'] = 'Incident resolved and archived successfully!';
        } else {
            $_SESSION['error'] = 'Resolution notes are required';
        }

        header('Location: /admin/township-security');
        exit;
    }
}
