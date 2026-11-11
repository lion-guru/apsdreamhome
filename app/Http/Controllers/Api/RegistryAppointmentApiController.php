<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\RegistryAppointmentService;
use PDO;

/**
 * RegistryAppointmentApiController — Mobile REST API for booking
 * Sub-Registrar Office (SRO) Registry Appointments.
 *
 * Endpoints:
 *   GET  /api/v2/mobile/registry-appointments          — list customer appointments
 *   GET  /api/v2/mobile/registry-appointments/{id}/slip — appointment slip details
 *   POST /api/v2/mobile/registry-appointments/book      — create appointment request
 *
 * Uses the same RegistryAppointmentService as the web customer portal so
 * stamp-duty calc, NOC clearance, and duplicate-appointment guards behave
 * identically on mobile and web.
 */
class RegistryAppointmentApiController extends BaseController
{
    private RegistryAppointmentService $regService;
    private PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->regService = new RegistryAppointmentService();
        $this->pdo = Database::getInstance()->getConnection();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getUserId(): ?int
    {
        // Middleware sets $GLOBALS['api_user_id'] after validating a Bearer token
        // against the api_tokens table. Prefer that; fall back to session.
        if (!empty($GLOBALS['api_user_id'])) {
            return (int)$GLOBALS['api_user_id'];
        }

        if (!empty($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            $stmt = $this->pdo->prepare(
                "SELECT user_id FROM api_tokens WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1"
            );
            $stmt->execute([$token]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return (int)$row['user_id'];
        }

        return null;
    }

    /**
     * GET /api/v2/mobile/registry-appointments
     * List the logged-in customer's registry appointments with booking details.
     */
    public function list(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $appointments = $this->regService->getCustomerAppointments($userId);

        $this->jsonResponse([
            'success' => true,
            'count' => count($appointments),
            'appointments' => $appointments,
        ]);
    }

    /**
     * GET /api/v2/mobile/registry-appointments/{id}/slip
     * Slip/gatepass details for one appointment (ownership-checked).
     */
    public function slip(int $id): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $appointment = $this->regService->getAppointmentDetail($id);
        if (!$appointment) {
            $this->jsonResponse(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        // Ownership check: appointment must belong to the logged-in customer's booking.
        $stmt = $this->pdo->prepare("SELECT customer_id FROM plot_bookings WHERE id = ?");
        $stmt->execute([(int)$appointment['booking_id']]);
        $ownerId = (int)$stmt->fetchColumn();

        if ($ownerId !== $userId && ($_SESSION['role'] ?? '') !== 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $this->jsonResponse([
            'success' => true,
            'appointment' => $appointment,
        ]);
    }

    /**
     * POST /api/v2/mobile/registry-appointments/book
     * Create a new registry appointment request.
     */
    public function book(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $bookingId = (int)($body['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid booking selection.'], 422);
        }

        // Verify booking belongs to this customer before delegating to the service.
        $stmt = $this->pdo->prepare("SELECT id FROM plot_bookings WHERE id = ? AND customer_id = ?");
        $stmt->execute([$bookingId, $userId]);
        if (!$stmt->fetch()) {
            $this->jsonResponse(['success' => false, 'message' => 'Booking record not found or access denied.'], 404);
        }

        $res = $this->regService->requestAppointment($bookingId, $body);
        $status = $res['success'] ? 201 : 422;
        $this->jsonResponse($res, $status);
    }
}
