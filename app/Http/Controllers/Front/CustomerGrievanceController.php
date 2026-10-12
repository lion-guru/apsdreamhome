<?php

namespace App\Http\Controllers\Front;

use App\Services\TownshipGrievanceService;
use App\Core\Database\Database;
use PDO;

/**
 * Customer Self-Service Township Infrastructure Grievance Helpdesk
 */
class CustomerGrievanceController extends PageController
{
    private TownshipGrievanceService $grievanceService;

    public function __construct()
    {
        parent::__construct();
        $this->grievanceService = new TownshipGrievanceService();
    }

    private function requireCustomer(): int
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        $allowedRoles = ['', 'customer', 'user'];
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        return (int)$_SESSION['user_id'];
    }

    public function index()
    {
        $cid = $this->requireCustomer();
        $grievances = $this->grievanceService->getCustomerGrievances($cid);

        $this->view('customer/grievances/index', [
            'page_title' => 'My Township Society Maintenance Requests',
            'grievances' => $grievances
        ]);
    }

    public function create()
    {
        $cid = $this->requireCustomer();
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT pb.id AS booking_id, pb.plot_id, p.plot_number, p.colony_id, c.name AS colony_name
             FROM plot_bookings pb
             JOIN plots p ON pb.plot_id = p.id
             JOIN colonies c ON p.colony_id = c.id
             WHERE pb.customer_id = ?
             ORDER BY pb.id DESC"
        );
        $stmt->execute([$cid]);
        $plots = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('customer/grievances/create', [
            'page_title' => 'Raise Township Infrastructure Grievance',
            'plots' => $plots
        ]);
    }

    public function store()
    {
        $cid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $colonyId = (int)($_POST['colony_id'] ?? 0);
        $plotId = !empty($_POST['plot_id']) ? (int)$_POST['plot_id'] : null;

        $data = [
            'tenant_id' => 1,
            'customer_id' => $cid,
            'colony_id' => $colonyId,
            'plot_id' => $plotId,
            'category' => trim((string)($_POST['category'] ?? 'water_supply')),
            'priority' => trim((string)($_POST['priority'] ?? 'medium')),
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? ''))
        ];

        $res = $this->grievanceService->submitGrievance($data);

        if ($res['success']) {
            $_SESSION['flash_success'] = "Maintenance request submitted. Ticket Token: {$res['ticket_token']}";
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/customer/township-grievances');
            exit;
        }

        $_SESSION['flash_error'] = $res['message'];
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/customer/township-grievances/create');
        exit;
    }

    public function close($id)
    {
        $cid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $rating = (int)($_POST['resident_rating'] ?? 5);
        $feedback = trim((string)($_POST['resident_feedback'] ?? ''));

        $details = $this->grievanceService->getGrievanceDetails($id);
        if (!$details || (int)$details['customer_id'] !== $cid) {
            http_response_code(403);
            echo "Access Denied.";
            exit;
        }

        $this->grievanceService->closeWithRating($id, $rating, $feedback);
        $_SESSION['flash_success'] = 'Thank you for your rating! Ticket has been closed.';
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/customer/township-grievances');
        exit;
    }
}
