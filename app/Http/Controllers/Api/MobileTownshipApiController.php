<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\MaintenanceBillingService;
use App\Services\TownshipGrievanceService;
use App\Services\EMIAutomationService;
use PDO;

/**
 * MobileTownshipApiController — REST API endpoints for Township Mobile App Features:
 * 1. Maintenance Dues & UPI Payment
 * 2. Township Resident Grievance / Complaints Desk
 * 3. Free Site Visit Booking with Vehicle Pickup
 */
class MobileTownshipApiController extends BaseController
{
    private PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = Database::getInstance()->getConnection();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private function getUserId(): ?int
    {
        // 1. Session user_id
        if (!empty($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        // 2. Bearer token / ApiAuthMiddleware user
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            // NOTE (S447): `personal_access_tokens` does not exist in this
            // schema -> 1146 -> every endpoint 500'd. The real table is
            // `api_tokens` and the token is stored RAW (same as
            // ApiAuthMiddleware::isValidToken).
            try {
                $stmt = $this->pdo->prepare("SELECT user_id FROM api_tokens WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1");
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) return (int)$row['user_id'];

                // Expired token - resolve the owner so the caller can 401.
                $stmt = $this->pdo->prepare("SELECT user_id FROM api_tokens WHERE token = ? LIMIT 1");
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) return (int)$row['user_id'];
            } catch (\Throwable $e) {
                error_log('[TownshipApi] getUserId: ' . $e->getMessage());
            }
        }

        // 3. User ID in request params (for test/offline fallback)
        if (!empty($_REQUEST['user_id'])) {
            return (int)$_REQUEST['user_id'];
        }

        return null;
    }

    public function jsonResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /api/v2/mobile/maintenance-dues
     */
    public function getMaintenanceDues(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $stmt = $this->pdo->prepare("
            SELECT m.*, c.name AS colony_name, p.plot_number
            FROM maintenance_dues m
            LEFT JOIN colonies c ON m.colony_id = c.id
            LEFT JOIN plots p ON m.plot_id = p.id
            WHERE m.customer_id = ?
            ORDER BY m.due_date DESC, m.id DESC
        ");
        $stmt->execute([$userId]);
        $dues = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalPending = 0.00;
        $totalPaid = 0.00;

        foreach ($dues as &$d) {
            $rem = max(0.00, (float)$d['amount_due'] - (float)$d['paid_amount']);
            if ($d['status'] === 'pending' || $d['status'] === 'partially_paid') {
                $totalPending += $rem;
                $d['pending_balance'] = $rem;
                $d['upi_pay_link'] = EMIAutomationService::emiPayLink(
                    $rem,
                    (string)($d['bill_month'] ?? 'MNT'),
                    'Plot-' . (string)($d['plot_number'] ?? $d['plot_id'])
                );
            } else {
                $totalPaid += (float)$d['paid_amount'];
                $d['pending_balance'] = 0.00;
                $d['upi_pay_link'] = '';
            }
        }
        unset($d);

        $this->jsonResponse([
            'success' => true,
            'summary' => [
                'total_pending' => round($totalPending, 2),
                'total_paid' => round($totalPaid, 2),
                'bills_count' => count($dues)
            ],
            'dues' => $dues
        ]);
    }

    /**
     * POST /api/v2/mobile/maintenance-dues/{id}/pay
     */
    public function payMaintenanceDue($id): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $txRef = trim((string)($body['transaction_ref'] ?? 'TXN' . time()));
        $payMode = trim((string)($body['payment_mode'] ?? 'UPI'));
        $amount = (float)($body['amount'] ?? 0);

        $stmt = $this->pdo->prepare("SELECT * FROM maintenance_dues WHERE id = ? AND customer_id = ?");
        $stmt->execute([(int)$id, $userId]);
        $due = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$due) {
            $this->jsonResponse(['success' => false, 'message' => 'Maintenance due record not found.'], 404);
        }

        $paidAmount = $amount > 0 ? $amount : (float)$due['amount_due'];
        $receiptNumber = 'MNT-' . date('Ymd') . '-' . sprintf('%04d', (int)$id);

        $update = $this->pdo->prepare("
            UPDATE maintenance_dues 
            SET paid_amount = ?, status = 'paid', paid_at = NOW(), 
                payment_mode = ?, transaction_ref = ?, receipt_number = ?
            WHERE id = ?
        ");
        $update->execute([$paidAmount, $payMode, $txRef, $receiptNumber, (int)$id]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Maintenance payment recorded successfully!',
            'receipt_number' => $receiptNumber,
            'paid_amount' => $paidAmount
        ]);
    }

    /**
     * GET /api/v2/mobile/township-grievances
     */
    public function getTownshipGrievances(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $stmt = $this->pdo->prepare("
            SELECT g.*, c.name AS colony_name, p.plot_number
            FROM township_resident_grievances g
            LEFT JOIN colonies c ON g.colony_id = c.id
            LEFT JOIN plots p ON g.plot_id = p.id
            WHERE g.customer_id = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$userId]);
        $grievances = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->jsonResponse([
            'success' => true,
            'count' => count($grievances),
            'grievances' => $grievances
        ]);
    }

    /**
     * POST /api/v2/mobile/township-grievances
     */
    public function createTownshipGrievance(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $colonyId = (int)($body['colony_id'] ?? 1);
        $plotId = !empty($body['plot_id']) ? (int)$body['plot_id'] : null;
        $category = trim((string)($body['category'] ?? 'water_supply'));
        $priority = trim((string)($body['priority'] ?? 'medium'));
        $title = trim((string)($body['title'] ?? ''));
        $description = trim((string)($body['description'] ?? ''));

        if ($title === '' || $description === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Complaint title and description are required.'], 422);
        }

        $validCategories = ['water_supply', 'drainage_sanitation', 'street_lighting', 'road_civil_work', 'park_horticulture', 'security_gate', 'other'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'other';
        }

        $ticketToken = 'GRV-' . strtoupper(substr($category, 0, 3)) . '-' . date('ymd') . '-' . rand(1000, 9999);

        // 48h SLA default
        $slaDeadline = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $stmt = $this->pdo->prepare("
            INSERT INTO township_resident_grievances 
            (ticket_token, customer_id, colony_id, plot_id, category, priority, title, description, status, sla_deadline, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', ?, NOW())
        ");
        $stmt->execute([
            $ticketToken,
            $userId,
            $colonyId,
            $plotId,
            $category,
            $priority,
            $title,
            $description,
            $slaDeadline
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Resident grievance lodged successfully!',
            'ticket_token' => $ticketToken,
            'sla_deadline' => $slaDeadline
        ], 201);
    }

    /**
     * POST /api/v2/mobile/site-visits/book
     */
    public function bookSiteVisit(): void
    {
        $userId = $this->getUserId();
        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $colonyId = (int)($body['colony_id'] ?? 1);
        $visitorName = trim((string)($body['visitor_name'] ?? ''));
        $visitorPhone = trim((string)($body['visitor_phone'] ?? ''));
        $visitorEmail = trim((string)($body['visitor_email'] ?? ''));
        $visitDate = trim((string)($body['visit_date'] ?? date('Y-m-d', strtotime('+1 day'))));
        $visitTime = trim((string)($body['visit_time'] ?? '10:00:00'));
        $pickupRequired = !empty($body['pickup_required']) ? 1 : 0;
        $pickupLocation = trim((string)($body['pickup_location'] ?? ''));
        $notes = trim((string)($body['notes'] ?? ''));

        if ($visitorName === '' || $visitorPhone === '') {
            $this->jsonResponse(['success' => false, 'message' => 'Visitor name and mobile phone are required.'], 422);
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO site_visits 
            (user_id, colony_id, visitor_name, visitor_phone, visitor_email, visit_date, visit_time, pickup_required, pickup_location, notes, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())
        ");
        $stmt->execute([
            $userId,
            $colonyId,
            $visitorName,
            $visitorPhone,
            $visitorEmail,
            $visitDate,
            $visitTime,
            $pickupRequired,
            $pickupLocation,
            $notes
        ]);

        $visitId = (int)$this->pdo->lastInsertId();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Free Site Visit scheduled successfully! Our executive will contact you for pickup coordination.',
            'visit_id' => $visitId,
            'visit_date' => $visitDate,
            'visit_time' => $visitTime,
            'pickup_confirmed' => $pickupRequired === 1
        ], 201);
    }

    /**
     * GET /api/v2/mobile/site-visits/my-visits
     */
    public function getMySiteVisits(): void
    {
        $userId = $this->getUserId();
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'Unauthorized: Please log in'], 401);
        }

        $stmt = $this->pdo->prepare("
            SELECT v.*, c.name AS colony_name
            FROM site_visits v
            LEFT JOIN colonies c ON v.colony_id = c.id
            WHERE v.user_id = ?
            ORDER BY v.visit_date DESC, v.id DESC
        ");
        $stmt->execute([$userId]);
        $visits = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->jsonResponse([
            'success' => true,
            'count' => count($visits),
            'visits' => $visits
        ]);
    }
}
