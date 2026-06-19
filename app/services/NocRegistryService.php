<?php
namespace App\Services;

use PDO;

/**
 * NocRegistryService — NOC (No Objection Certificate) & Registry pipeline
 * Full lifecycle: eligibility check → NOC request → approval → registry appointment → completion
 * Blocking: outstanding EMI / penalties / incomplete docs prevent progression
 */
class NocRegistryService
{
    private $db;

    public function __construct($pdo = null)
    {
        $this->db = $pdo;
    }

    private function getPdo(): PDO
    {
        if ($this->db instanceof PDO) return $this->db;
        $config = require 'C:/xampp/htdocs/apsdreamhome/config/database.php';
        $this->db = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
            $config['username'], $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $this->db;
    }

    // ========== ELIGIBILITY CHECKS ==========

    /**
     * Check if a booking is eligible for NOC
     * Returns array of checks, each with pass/fail + reason
     */
    public function checkNocEligibility(int $bookingId): array
    {
        $pdo = $this->getPdo();
        $checks = [];

        $booking = $this->getBooking($bookingId);
        if (!$booking) {
            return ['eligible' => false, 'checks' => [], 'error' => 'Booking not found'];
        }

        // 1. Booking must be fully_paid or registration_done
        $statusOk = in_array($booking['status'], ['fully_paid', 'registration_done']);
        $checks[] = [
            'name' => 'Booking Fully Paid',
            'passed' => $statusOk,
            'detail' => $statusOk
                ? "Status: {$booking['status']}"
                : "Booking status is '{$booking['status']}' — must be 'fully_paid' or 'registration_done'",
            'blocker' => !$statusOk,
        ];

        // 2. No overdue installments
        $overdue = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(amount - paid_amount), 0) as balance
            FROM booking_payment_schedules WHERE booking_id = ? AND status IN ('pending','overdue') AND due_date < CURDATE()");
        $overdue->execute([$bookingId]);
        $overdueData = $overdue->fetch(PDO::FETCH_ASSOC);
        $overdueCount = (int)$overdueData['cnt'];
        $overdueBalance = (float)$overdueData['balance'];
        $checks[] = [
            'name' => 'No Overdue Installments',
            'passed' => $overdueCount === 0,
            'detail' => $overdueCount === 0
                ? 'All installments current'
                : "{$overdueCount} overdue installment(s), balance: ₹" . number_format($overdueBalance, 2),
            'blocker' => $overdueCount > 0,
        ];

        // 3. No unpaid penalties
        $penalty = $pdo->prepare("SELECT COALESCE(SUM(accrued_penalty), 0) as total
            FROM booking_payment_schedules WHERE booking_id = ? AND accrued_penalty > 0 AND status != 'paid'");
        $penalty->execute([$bookingId]);
        $penaltyData = $penalty->fetch(PDO::FETCH_ASSOC);
        $penaltyBalance = (float)$penaltyData['total'];
        $checks[] = [
            'name' => 'No Outstanding Penalties',
            'passed' => $penaltyBalance == 0,
            'detail' => $penaltyBalance == 0
                ? 'No penalties outstanding'
                : "Outstanding penalties: ₹" . number_format($penaltyBalance, 2),
            'blocker' => $penaltyBalance > 0,
        ];

        // 4. No existing active NOC
        $nocCheck = $pdo->prepare("SELECT id, status FROM noc_requests WHERE booking_id = ? AND status IN ('pending','processing','approved')");
        $nocCheck->execute([$bookingId]);
        $activeNoc = $nocCheck->fetch(PDO::FETCH_ASSOC);
        $checks[] = [
            'name' => 'No Active NOC Request',
            'passed' => !$activeNoc,
            'detail' => !$activeNoc
                ? 'No active NOC request'
                : "Active NOC #{$activeNoc['id']} (status: {$activeNoc['status']})",
            'blocker' => (bool)$activeNoc,
        ];

        // 5. All commissions settled
        $commCheck = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid
            FROM mlm_commission_ledger WHERE property_id = ?");
        $commCheck->execute([$booking['plot_id']]);
        $commData = $commCheck->fetch(PDO::FETCH_ASSOC);
        $commBalance = (float)($commData['total'] ?? 0) - (float)($commData['paid'] ?? 0);
        $checks[] = [
            'name' => 'Commissions Settled',
            'passed' => $commBalance <= 0,
            'detail' => $commBalance <= 0
                ? 'All commissions paid'
                : "Commission balance: ₹" . number_format($commBalance, 2),
            'blocker' => $commBalance > 0,
        ];

        $blockers = array_filter($checks, fn($c) => $c['blocker']);
        return [
            'eligible' => empty($blockers),
            'checks' => $checks,
            'blocker_count' => count($blockers),
            'booking' => $booking,
        ];
    }

    /**
     * Check if a booking is eligible for Registry
     * Requires: NOC approved + all checks from eligibility
     */
    public function checkRegistryEligibility(int $bookingId): array
    {
        $pdo = $this->getPdo();

        // First run NOC eligibility
        $nocResult = $this->checkNocEligibility($bookingId);
        $checks = $nocResult['checks'];

        // 6. NOC must be approved
        $approvedNoc = $pdo->prepare("SELECT id FROM noc_requests WHERE booking_id = ? AND status = 'approved' ORDER BY id DESC LIMIT 1");
        $approvedNoc->execute([$bookingId]);
        $nocRow = $approvedNoc->fetch(PDO::FETCH_ASSOC);
        $checks[] = [
            'name' => 'NOC Approved',
            'passed' => (bool)$nocRow,
            'detail' => $nocRow
                ? "NOC #{$nocRow['id']} approved"
                : 'No approved NOC — request and approve NOC first',
            'blocker' => !$nocRow,
        ];

        // 7. No existing registry in progress
        $regCheck = $pdo->prepare("SELECT id, status FROM registries WHERE booking_id = ? AND status NOT IN ('rejected','cancelled')");
        $regCheck->execute([$bookingId]);
        $activeReg = $regCheck->fetch(PDO::FETCH_ASSOC);
        $checks[] = [
            'name' => 'No Active Registry',
            'passed' => !$activeReg,
            'detail' => !$activeReg
                ? 'No active registry'
                : "Active registry #{$activeReg['id']} (status: {$activeReg['status']})",
            'blocker' => (bool)$activeReg,
        ];

        $blockers = array_filter($checks, fn($c) => $c['blocker']);
        return [
            'eligible' => empty($blockers),
            'checks' => $checks,
            'blocker_count' => count($blockers),
            'booking' => $nocResult['booking'],
        ];
    }

    // ========== NOC OPERATIONS ==========

    public function createNocRequest(array $data): array
    {
        $pdo = $this->getPdo();

        $bookingId = (int)($data['booking_id'] ?? 0);
        if ($bookingId <= 0) return ['success' => false, 'error' => 'Invalid booking ID'];

        // Verify eligibility
        $eligibility = $this->checkNocEligibility($bookingId);
        if (!$eligibility['eligible']) {
            $failed = array_filter($eligibility['checks'], fn($c) => $c['blocker']);
            $reasons = array_map(fn($c) => $c['name'], $failed);
            return ['success' => false, 'error' => 'Eligibility check failed: ' . implode(', ', $reasons)];
        }

        $booking = $eligibility['booking'];

        $stmt = $pdo->prepare("INSERT INTO noc_requests (booking_id, plot_id, user_id, requested_by, purpose, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $bookingId,
            $booking['plot_id'],
            $booking['customer_id'],
            $data['requested_by'] ?? null,
            $data['purpose'] ?? 'Property transfer / Registry',
            $data['notes'] ?? null,
        ]);

        $nocId = (int)$pdo->lastInsertId();
        return ['success' => true, 'noc_id' => $nocId, 'message' => "NOC request #{$nocId} created"];
    }

    public function approveNoc(int $nocId, int $approvedBy, ?string $remarks = null): array
    {
        $pdo = $this->getPdo();

        $noc = $this->getNoc($nocId);
        if (!$noc) return ['success' => false, 'error' => 'NOC not found'];
        if (!in_array($noc['status'], ['pending', 'processing'])) {
            return ['success' => false, 'error' => "Cannot approve NOC in '{$noc['status']}' status"];
        }

        $stmt = $pdo->prepare("UPDATE noc_requests SET status = 'approved', approved_by = ?, processed_at = NOW(), notes = IFNULL(CONCAT(notes, '\n'), '') WHERE id = ?");
        $stmt->execute([$approvedBy, $nocId]);

        return ['success' => true, 'message' => "NOC #{$nocId} approved"];
    }

    public function rejectNoc(int $nocId, int $rejectedBy, string $reason): array
    {
        $pdo = $this->getPdo();

        $noc = $this->getNoc($nocId);
        if (!$noc) return ['success' => false, 'error' => 'NOC not found'];

        $stmt = $pdo->prepare("UPDATE noc_requests SET status = 'rejected', rejection_reason = ?, processed_at = NOW(), approved_by = ? WHERE id = ?");
        $stmt->execute([$reason, $rejectedBy, $nocId]);

        return ['success' => true, 'message' => "NOC #{$nocId} rejected"];
    }

    public function blockNoc(int $nocId, string $reason): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("UPDATE noc_requests SET status = 'blocked', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $nocId]);
        return ['success' => true, 'message' => "NOC #{$nocId} blocked"];
    }

    // ========== REGISTRY OPERATIONS ==========

    public function createRegistry(array $data): array
    {
        $pdo = $this->getPdo();
        $bookingId = (int)($data['booking_id'] ?? 0);

        // Verify eligibility
        $eligibility = $this->checkRegistryEligibility($bookingId);
        if (!$eligibility['eligible']) {
            $failed = array_filter($eligibility['checks'], fn($c) => $c['blocker']);
            $reasons = array_map(fn($c) => $c['name'], $failed);
            return ['success' => false, 'error' => 'Registry eligibility failed: ' . implode(', ', $reasons)];
        }

        $booking = $eligibility['booking'];

        // Calculate stamp duty (4% of property value for UP)
        $plotValue = (float)$booking['total_plot_value'];
        $stampDuty = round($plotValue * 0.04, 2);
        $registrationFee = min(round($plotValue * 0.01, 2), 30000); // max ₹30K
        $otherCharges = 1000; // nominal typing/nomination

        $stmt = $pdo->prepare("INSERT INTO registries (booking_id, plot_id, user_id, associate_id, sub_registrar_office, stamp_duty_amount, registration_fee, other_charges, total_registry_cost, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([
            $bookingId,
            $booking['plot_id'],
            $booking['customer_id'],
            $booking['associate_id'] ?? null,
            $data['sub_registrar_office'] ?? 'SRO Gorakhpur',
            $stampDuty,
            $registrationFee,
            $otherCharges,
            $stampDuty + $registrationFee + $otherCharges,
            $data['notes'] ?? null,
        ]);

        $regId = (int)$pdo->lastInsertId();
        return [
            'success' => true,
            'registry_id' => $regId,
            'stamp_duty' => $stampDuty,
            'registration_fee' => $registrationFee,
            'total_cost' => $stampDuty + $registrationFee + $otherCharges,
            'message' => "Registry #{$regId} created. Total cost: ₹" . number_format($stampDuty + $registrationFee + $otherCharges, 2),
        ];
    }

    public function updateRegistryStatus(int $registryId, string $newStatus, ?string $notes = null, ?string $regNo = null): array
    {
        $pdo = $this->getPdo();
        $validTransitions = [
            'pending' => ['appointment_scheduled', 'cancelled'],
            'appointment_scheduled' => ['documents_submitted', 'cancelled'],
            'documents_submitted' => ['in_progress', 'rejected'],
            'in_progress' => ['completed', 'rejected'],
            'rejected' => ['pending'],
            'cancelled' => ['pending'],
        ];

        $reg = $this->getRegistry($registryId);
        if (!$reg) return ['success' => false, 'error' => 'Registry not found'];

        if (!in_array($newStatus, $validTransitions[$reg['status']] ?? [])) {
            return ['success' => false, 'error' => "Cannot transition from '{$reg['status']}' to '{$newStatus}'"];
        }

        $updates = ['status' => $newStatus];
        if ($notes) $updates['notes'] = $notes;
        if ($regNo) $updates['registration_no'] = $regNo;
        if ($newStatus === 'completed') {
            $updates['registration_date'] = date('Y-m-d');
        }

        $setClauses = [];
        $params = [];
        foreach ($updates as $k => $v) {
            $setClauses[] = "{$k} = ?";
            $params[] = $v;
        }
        $params[] = $registryId;

        $stmt = $pdo->prepare("UPDATE registries SET " . implode(', ', $setClauses) . " WHERE id = ?");
        $stmt->execute($params);

        // If completed, update booking status
        if ($newStatus === 'completed') {
            $pdo->prepare("UPDATE plot_bookings SET status = 'registration_done' WHERE id = ?")->execute([$reg['booking_id']]);
        }

        return ['success' => true, 'message' => "Registry #{$registryId} updated to '{$newStatus}'"];
    }

    // ========== STAMP DUTY CALCULATOR ==========

    public function calculateStampDuty(float $plotValue, string $state = 'Uttar Pradesh'): array
    {
        $rates = [
            'Uttar Pradesh' => ['stamp_duty' => 0.04, 'registration_cap' => 30000],
            'Bihar' => ['stamp_duty' => 0.06, 'registration_cap' => 50000],
            'Rajasthan' => ['stamp_duty' => 0.05, 'registration_cap' => 50000],
            'Maharashtra' => ['stamp_duty' => 0.06, 'registration_cap' => 30000],
        ];

        $rate = $rates[$state] ?? $rates['Uttar Pradesh'];
        $stampDuty = round($plotValue * $rate['stamp_duty'], 2);
        $registrationFee = min(round($plotValue * 0.01, 2), $rate['registration_cap']);
        $otherCharges = 1000;

        return [
            'plot_value' => $plotValue,
            'state' => $state,
            'stamp_duty_rate' => $rate['stamp_duty'] * 100 . '%',
            'stamp_duty' => $stampDuty,
            'registration_fee' => $registrationFee,
            'other_charges' => $otherCharges,
            'total_cost' => $stampDuty + $registrationFee + $otherCharges,
        ];
    }

    // ========== DASHBOARD ==========

    public function getDashboardStats(): array
    {
        $pdo = $this->getPdo();

        $nocPending = (int)$pdo->query("SELECT COUNT(*) FROM noc_requests WHERE status IN ('pending','processing')")->fetchColumn();
        $nocApproved = (int)$pdo->query("SELECT COUNT(*) FROM noc_requests WHERE status = 'approved'")->fetchColumn();
        $nocRejected = (int)$pdo->query("SELECT COUNT(*) FROM noc_requests WHERE status IN ('rejected','blocked')")->fetchColumn();
        $nocTotal = (int)$pdo->query("SELECT COUNT(*) FROM noc_requests")->fetchColumn();

        $regPending = (int)$pdo->query("SELECT COUNT(*) FROM registries WHERE status IN ('pending','appointment_scheduled','documents_submitted','in_progress')")->fetchColumn();
        $regCompleted = (int)$pdo->query("SELECT COUNT(*) FROM registries WHERE status = 'completed'")->fetchColumn();
        $regTotal = (int)$pdo->query("SELECT COUNT(*) FROM registries")->fetchColumn();
        $regTotalCost = (float)$pdo->query("SELECT COALESCE(SUM(total_registry_cost), 0) FROM registries WHERE status = 'completed'")->fetchColumn();

        return compact('nocPending', 'nocApproved', 'nocRejected', 'nocTotal', 'regPending', 'regCompleted', 'regTotal', 'regTotalCost');
    }

    // ========== HELPERS ==========

    public function getBooking(int $id): ?array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT pb.*, p.plot_number as plot_no, p.colony_id, c.name as colony_name, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM plot_bookings pb
            JOIN plots p ON pb.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON pb.customer_id = u.id
            WHERE pb.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getNoc(int $id): ?array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT n.*, pb.booking_number, p.plot_number as plot_no, c.name as colony_name, u.name as customer_name
            FROM noc_requests n
            JOIN plot_bookings pb ON n.booking_id = pb.id
            JOIN plots p ON n.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON n.user_id = u.id
            WHERE n.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getRegistry(int $id): ?array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT r.*, pb.booking_number, p.plot_number as plot_no, c.name as colony_name, u.name as customer_name
            FROM registries r
            JOIN plot_bookings pb ON r.booking_id = pb.id
            JOIN plots p ON r.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listNocs(array $filters = []): array
    {
        $pdo = $this->getPdo();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "n.status = ?";
            $params[] = $filters['status'];
        }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $pdo->prepare("SELECT n.*, pb.booking_number, p.plot_number, c.name as colony_name, u.name as customer_name
            FROM noc_requests n
            JOIN plot_bookings pb ON n.booking_id = pb.id
            JOIN plots p ON n.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON n.user_id = u.id
            {$whereClause}
            ORDER BY n.created_at DESC
            LIMIT 100");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listRegistries(array $filters = []): array
    {
        $pdo = $this->getPdo();
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $pdo->prepare("SELECT r.*, pb.booking_number, p.plot_number, c.name as colony_name, u.name as customer_name
            FROM registries r
            JOIN plot_bookings pb ON r.booking_id = pb.id
            JOIN plots p ON r.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON r.user_id = u.id
            {$whereClause}
            ORDER BY r.created_at DESC
            LIMIT 100");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listEligibleBookings(): array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->query("SELECT pb.id, pb.booking_number, pb.status, pb.total_plot_value, p.plot_number as plot_no, c.name as colony_name, u.name as customer_name
            FROM plot_bookings pb
            JOIN plots p ON pb.plot_id = p.id
            JOIN colonies c ON p.colony_id = c.id
            JOIN users u ON pb.customer_id = u.id
            WHERE pb.status IN ('fully_paid', 'registration_done')
            ORDER BY pb.booking_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
