<?php

namespace App\Http\Controllers\Front;

use App\Services\SolarSubsidyService;
use App\Core\Database\Database;

/**
 * Customer Self-Service PM Surya Ghar Green Solar & Subsidy Portal
 */
class CustomerSolarController extends PageController
{
    private SolarSubsidyService $solarService;

    public function __construct()
    {
        parent::__construct();
        $this->solarService = new SolarSubsidyService();
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

    public function calculator()
    {
        $uid = $this->requireCustomer();
        $bookings = [];

        if ($uid > 0) {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT pb.id, pb.booking_number, p.plot_number, p.area_sqft, c.name AS colony_name
                 FROM plot_bookings pb
                 JOIN plots p ON pb.plot_id = p.id
                 JOIN colonies c ON p.colony_id = c.id
                 WHERE pb.customer_id = ?
                 ORDER BY pb.id DESC"
            );
            $stmt->execute([$uid]);
            $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        $defaultFeasibility = $this->solarService->calculateFeasibility(1000.0, 3500.0, 3.0);

        $this->render('customer/solar/calculator', [
            'page_title' => 'PM Surya Ghar Solar Calculator & Subsidy Desk',
            'bookings' => $bookings,
            'defaultFeasibility' => $defaultFeasibility,
            'is_logged_in' => ($uid > 0),
        ]);
    }

    public function applyStore()
    {
        $uid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
        $data = [
            'system_capacity_kw' => (float)($_POST['system_capacity_kw'] ?? 3.0),
            'monthly_bill' => (float)($_POST['monthly_bill'] ?? 3500.0),
            'plot_sqft' => (float)($_POST['plot_sqft'] ?? 1000.0),
            'consumer_number' => trim((string)($_POST['consumer_number'] ?? '')),
            'discom_provider' => trim((string)($_POST['discom_provider'] ?? 'Purvanchal Vidyut Vitaran Nigam Ltd (PVVNL)')),
            'notes' => trim((string)($_POST['notes'] ?? ''))
        ];

        $res = $this->solarService->submitSolarRequest($uid, $bookingId, $data);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
            $this->redirect('/customer/solar-desk');
        } else {
            $this->setFlash('error', $res['message']);
            $this->redirect('/customer/solar-calculator');
        }
    }

    public function track()
    {
        $uid = $this->requireCustomer();
        $requests = $this->solarService->getCustomerRequests($uid);

        $this->render('customer/solar/track', [
            'page_title' => 'My Green Solar Applications',
            'requests' => $requests
        ]);
    }
}
