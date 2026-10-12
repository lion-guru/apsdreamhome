<?php

namespace App\Http\Controllers\Front;

use App\Services\RegistryAppointmentService;
use App\Core\Database\Database;

/**
 * Customer Self-Service Sub-Registrar Office (SRO) Registry Appointment Portal
 */
class CustomerRegistryController extends PageController
{
    private RegistryAppointmentService $regService;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new RegistryAppointmentService();
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
        $uid = $this->requireCustomer();
        $appointments = $this->regService->getCustomerAppointments($uid);

        // Fetch user's plot bookings that could request registry
        $pdo = Database::getInstance()->getConnection();
        $bSt = $pdo->prepare(
            "SELECT pb.id, pb.booking_number, pb.total_plot_value, pb.status, p.plot_number, c.name AS colony_name
             FROM plot_bookings pb
             JOIN plots p ON p.id = pb.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pb.customer_id = ? AND pb.status IN ('fully_paid', 'emi_active', 'partially_paid', 'agreement_signed')
             ORDER BY pb.created_at DESC"
        );
        $bSt->execute([$uid]);
        $bookings = $bSt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->render('customer/registry/index', [
            'page_title' => 'Registry & Deed Execution Desk',
            'appointments' => $appointments,
            'bookings' => $bookings,
        ]);
    }

    public function applyForm($booking_id)
    {
        $uid = $this->requireCustomer();
        $pdo = Database::getInstance()->getConnection();

        $bSt = $pdo->prepare(
            "SELECT pb.*, p.plot_number, p.area_sqft, c.name AS colony_name, c.location AS colony_location
             FROM plot_bookings pb
             JOIN plots p ON p.id = pb.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE pb.id = ? AND pb.customer_id = ?"
        );
        $bSt->execute([(int)$booking_id, $uid]);
        $booking = $bSt->fetch(\PDO::FETCH_ASSOC);

        if (!$booking) {
            $this->setFlash('error', 'Booking record not found or access denied.');
            return $this->redirect('/customer/registry-appointments');
        }

        $this->render('customer/registry/apply', [
            'page_title' => 'Book SRO Registry Date Slot',
            'booking' => $booking,
        ]);
    }

    public function applyStore()
    {
        $uid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $bookingId = (int)($_POST['booking_id'] ?? 0);

        // Verify booking belongs to logged-in user
        $pdo = Database::getInstance()->getConnection();
        $chk = $pdo->prepare("SELECT id FROM plot_bookings WHERE id = ? AND customer_id = ?");
        $chk->execute([$bookingId, $uid]);
        if (!$chk->fetch()) {
            $this->setFlash('error', 'Invalid booking selection.');
            return $this->redirect('/customer/registry-appointments');
        }

        $res = $this->regService->requestAppointment($bookingId, $_POST);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/customer/registry-appointments');
    }

    public function slip($id)
    {
        $uid = $this->requireCustomer();
        $appointment = $this->regService->getAppointmentDetail((int)$id);

        if (!$appointment) {
            $this->setFlash('error', 'Appointment record not found.');
            return $this->redirect('/customer/registry-appointments');
        }

        // Verify ownership
        $pdo = Database::getInstance()->getConnection();
        $chk = $pdo->prepare("SELECT customer_id FROM plot_bookings WHERE id = ?");
        $chk->execute([(int)$appointment['booking_id']]);
        $ownerId = (int)$chk->fetchColumn();

        if ($ownerId !== $uid && ($_SESSION['role'] ?? '') !== 'admin') {
            $this->setFlash('error', 'Access denied.');
            return $this->redirect('/customer/registry-appointments');
        }

        require dirname(__DIR__, 2) . '/views/admin/legal/registry_appointment_slip.php';
        exit;
    }
}
