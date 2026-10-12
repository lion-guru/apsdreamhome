<?php

namespace App\Http\Controllers\Admin;

use App\Services\TownshipGrievanceService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Resident Grievance & Society AMC Civil Maintenance Helpdesk
 */
class TownshipGrievanceController extends AdminController
{
    private TownshipGrievanceService $grievanceService;

    public function __construct()
    {
        parent::__construct();
        $this->grievanceService = new TownshipGrievanceService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;
        $category = trim((string)($_GET['category'] ?? 'all'));
        $status = trim((string)($_GET['status'] ?? 'all'));

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $sql = "SELECT trg.*, c.name AS colony_name, p.plot_number, u.name AS resident_name, u.phone AS resident_phone
                FROM township_resident_grievances trg
                JOIN colonies c ON trg.colony_id = c.id
                LEFT JOIN plots p ON trg.plot_id = p.id
                JOIN users u ON trg.customer_id = u.id
                WHERE 1=1";
        $params = [];

        if ($colonyId) {
            $sql .= " AND trg.colony_id = ?";
            $params[] = $colonyId;
        }
        if ($category !== 'all') {
            $sql .= " AND trg.category = ?";
            $params[] = $category;
        }
        if ($status !== 'all') {
            $sql .= " AND trg.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY trg.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $grievances = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stats = $this->grievanceService->getSummaryStats();
        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/township_grievance/index', [
            'page_title' => 'Township Infrastructure Grievance & Society AMC Helpdesk',
            'grievances' => $grievances,
            'stats' => $stats,
            'colonies' => $colonies,
            'current_colony' => $colonyId,
            'current_category' => $category,
            'current_status' => $status
        ]);
    }

    public function assign($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $data = [
            'assigned_contractor_name' => trim((string)($_POST['assigned_contractor_name'] ?? '')),
            'assigned_contact' => trim((string)($_POST['assigned_contact'] ?? '')),
            'estimated_cost' => (float)($_POST['estimated_cost'] ?? 0.0)
        ];

        $this->grievanceService->assignContractor($id, $data);
        $_SESSION['flash_success'] = 'Contractor assigned and ticket moved to In Progress.';
        $this->redirect('/admin/township-grievances');
    }

    public function resolve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $notes = trim((string)($_POST['resolution_notes'] ?? 'Issue resolved on site.'));

        $this->grievanceService->resolveGrievance($id, $notes);
        $_SESSION['flash_success'] = 'Ticket marked as Resolved. Resident notified for closure rating.';
        $this->redirect('/admin/township-grievances');
    }
}
