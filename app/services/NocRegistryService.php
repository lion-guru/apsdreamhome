<?php

namespace App\Services;

use PDO;

class NocRegistryService
{
    private $db;

    public function __construct($pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $configPath = 'C:/xampp/htdocs/apsdreamhome/config/database.php';
            $config = require $configPath;
            $this->db = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'], $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
    }

    /**
     * Full eligibility check for a booking.
     * Returns ['eligible' => bool, 'checks' => [...], 'blockers' => [...]]
     */
    public function checkEligibility(int $bookingId): array
    {
        $checks = [];
        $blockers = [];

        $booking = $this->getBooking($bookingId);
        if (!$booking) {
            return ['eligible' => false, 'checks' => [], 'blockers' => ['Booking not found']];
        }

        // 1. EMI check — all installments must be paid
        $emi = $this->checkEmiStatus($bookingId);
        $checks[] = $emi;
        if (!$emi['passed']) $blockers[] = $emi['message'];

        // 2. Penalty check — no accrued penalties
        $penalty = $this->checkPenalties($bookingId);
        $checks[] = $penalty;
        if (!$penalty['passed']) $blockers[] = $penalty['message'];

        // 3. RERA compliance — colony must have filing for current quarter
        $rera = $this->checkReraCompliance($booking);
        $checks[] = $rera;
        if (!$rera['passed']) $blockers[] = $rera['message'];

        // 4. Documents — required docs uploaded
        $docs = $this->checkDocuments($bookingId);
        $checks[] = $docs;
        if (!$docs['passed']) $blockers[] = $docs['message'];

        // 5. Commissions — all settled
        $comm = $this->checkCommissions($bookingId);
        $checks[] = $comm;
        if (!$comm['passed']) $blockers[] = $comm['message'];

        // 6. Existing NOC must be approved (if requested)
        $noc = $this->checkNocStatus($bookingId);
        $checks[] = $noc;
        if (!$noc['passed']) $blockers[] = $noc['message'];

        return [
            'eligible' => empty($blockers),
            'checks' => $checks,
            'blockers' => $blockers,
            'booking' => $booking
        ];
    }

    /**
     * Create a NOC request.
     */
    public function requestNoc(array $data): array
    {
        try {
            $bookingId = (int)($data['booking_id'] ?? 0);
            if (!$bookingId) return ['success' => false, 'error' => 'Booking ID required'];

            // Check not already requested
            $existing = $this->db->prepare("SELECT id FROM noc_requests WHERE booking_id = ? AND status NOT IN ('rejected','cancelled')");
            $existing->execute([$bookingId]);
            if ($existing->fetch()) {
                return ['success' => false, 'error' => 'NOC already requested for this booking'];
            }

            $stmt = $this->db->prepare("INSERT INTO noc_requests (booking_id, plot_id, user_id, requested_by, purpose, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $bookingId,
                $data['plot_id'] ?? 0,
                $data['user_id'] ?? 0,
                $data['requested_by'] ?? 0,
                trim($data['purpose'] ?? ''),
                trim($data['notes'] ?? '')
            ]);
            $nocId = (int)$this->db->lastInsertId();

            return ['success' => true, 'noc_id' => $nocId];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Run eligibility and update NOC status accordingly.
     */
    public function processNoc(int $nocId): array
    {
        try {
            $noc = $this->getNocById($nocId);
            if (!$noc) return ['success' => false, 'error' => 'NOC not found'];

            $eligibility = $this->checkEligibility((int)$noc['booking_id']);
            if ($eligibility['eligible']) {
                $this->db->prepare("UPDATE noc_requests SET status = 'approved', processed_at = NOW() WHERE id = ?")->execute([$nocId]);
                return ['success' => true, 'status' => 'approved', 'checks' => $eligibility['checks']];
            } else {
                $this->db->prepare("UPDATE noc_requests SET status = 'blocked', rejection_reason = ?, processed_at = NOW() WHERE id = ?")->execute([implode('; ', $eligibility['blockers']), $nocId]);
                return ['success' => true, 'status' => 'blocked', 'blockers' => $eligibility['blockers'], 'checks' => $eligibility['checks']];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Approve a NOC manually.
     */
    public function approveNoc(int $nocId, int $adminId): bool
    {
        try {
            $this->db->prepare("UPDATE noc_requests SET status = 'approved', approved_by = ?, processed_at = NOW() WHERE id = ?")->execute([$adminId, $nocId]);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    /**
     * Reject a NOC.
     */
    public function rejectNoc(int $nocId, int $adminId, string $reason): bool
    {
        try {
            $this->db->prepare("UPDATE noc_requests SET status = 'rejected', rejection_reason = ?, approved_by = ?, processed_at = NOW() WHERE id = ?")->execute([$reason, $adminId, $nocId]);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    /**
     * Cancel a NOC request.
     */
    public function cancelNoc(int $nocId): bool
    {
        try {
            $this->db->prepare("UPDATE noc_requests SET status = 'cancelled', processed_at = NOW() WHERE id = ?")->execute([$nocId]);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    /**
     * List NOC requests with filters.
     */
    public function listNocs(string $status = '', int $limit = 50): array
    {
        try {
            $sql = "SELECT nr.*, pb.booking_number, pb.total_plot_value, p.plot_number, p.block, u.name as customer_name
                    FROM noc_requests nr
                    LEFT JOIN plot_bookings pb ON nr.booking_id = pb.id
                    LEFT JOIN plots p ON nr.plot_id = p.id
                    LEFT JOIN users u ON nr.user_id = u.id";
            $params = [];
            if ($status) {
                $sql .= " WHERE nr.status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY nr.created_at DESC LIMIT " . (int)$limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Get single NOC by ID.
     */
    public function getNocById(int $nocId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT nr.*, pb.booking_number, pb.total_plot_value, pb.status as booking_status, p.plot_number, p.block, u.name as customer_name, u.phone as customer_phone, u.email as customer_email FROM noc_requests nr LEFT JOIN plot_bookings pb ON nr.booking_id = pb.id LEFT JOIN plots p ON nr.plot_id = p.id LEFT JOIN users u ON nr.user_id = u.id WHERE nr.id = ?");
            $stmt->execute([$nocId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) { return null; }
    }

    /**
     * Create a registry request (requires approved NOC).
     */
    public function requestRegistry(array $data): array
    {
        try {
            $bookingId = (int)($data['booking_id'] ?? 0);
            if (!$bookingId) return ['success' => false, 'error' => 'Booking ID required'];

            // Must have approved NOC
            $noc = $this->db->prepare("SELECT id FROM noc_requests WHERE booking_id = ? AND status = 'approved'");
            $noc->execute([$bookingId]);
            if (!$noc->fetch()) {
                return ['success' => false, 'error' => 'Approved NOC required before registry. Please request and process NOC first.'];
            }

            // Check not already registered
            $existing = $this->db->prepare("SELECT id FROM registries WHERE booking_id = ? AND status NOT IN ('rejected','cancelled')");
            $existing->execute([$bookingId]);
            if ($existing->fetch()) {
                return ['success' => false, 'error' => 'Registry already exists for this booking'];
            }

            $stmt = $this->db->prepare("INSERT INTO registries (booking_id, plot_id, user_id, associate_id, sub_registrar_office, stamp_duty_amount, registration_fee, other_charges, total_registry_cost, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $bookingId,
                $data['plot_id'] ?? 0,
                $data['user_id'] ?? 0,
                $data['associate_id'] ?? null,
                trim($data['sub_registrar_office'] ?? ''),
                (float)($data['stamp_duty_amount'] ?? 0),
                (float)($data['registration_fee'] ?? 0),
                (float)($data['other_charges'] ?? 0),
                (float)($data['total_registry_cost'] ?? 0),
                trim($data['notes'] ?? '')
            ]);
            $regId = (int)$this->db->lastInsertId();

            return ['success' => true, 'registry_id' => $regId];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update registry status.
     */
    public function updateRegistryStatus(int $registryId, string $status, string $reason = ''): bool
    {
        try {
            $params = [$status];
            $sql = "UPDATE registries SET status = ?";
            if ($reason) {
                $sql .= ", rejection_reason = ?";
                $params[] = $reason;
            }
            if ($status === 'completed') {
                $sql .= ", registration_date = CURDATE()";
            }
            $sql .= ", updated_at = NOW() WHERE id = ?";
            $params[] = $registryId;
            $this->db->prepare($sql)->execute($params);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    /**
     * List all registry requests.
     */
    public function listRegistries(string $status = '', int $limit = 50): array
    {
        try {
            $sql = "SELECT r.*, pb.booking_number, pb.total_plot_value, p.plot_number, p.block, u.name as customer_name, nr.status as noc_status
                    FROM registries r
                    LEFT JOIN plot_bookings pb ON r.booking_id = pb.id
                    LEFT JOIN plots p ON r.plot_id = p.id
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN noc_requests nr ON nr.booking_id = r.booking_id AND nr.status = 'approved'";
            $params = [];
            if ($status) {
                $sql .= " WHERE r.status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY r.created_at DESC LIMIT " . (int)$limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    /**
     * Dashboard summary stats.
     */
    public function getDashboardSummary(): array
    {
        $stats = [
            'total_nocs' => 0, 'pending_nocs' => 0, 'approved_nocs' => 0, 'blocked_nocs' => 0,
            'total_registries' => 0, 'pending_registries' => 0, 'completed_registries' => 0,
            'eligible_bookings' => 0, 'blocked_bookings' => 0
        ];
        try {
            $row = $this->db->query("SELECT COUNT(*) as total, SUM(status='pending') as pending, SUM(status='approved') as approved, SUM(status='blocked') as blocked FROM noc_requests")->fetch(PDO::FETCH_ASSOC);
            if ($row) { $stats['total_nocs'] = $row['total']; $stats['pending_nocs'] = $row['pending'] ?? 0; $stats['approved_nocs'] = $row['approved'] ?? 0; $stats['blocked_nocs'] = $row['blocked'] ?? 0; }

            $row = $this->db->query("SELECT COUNT(*) as total, SUM(status='pending') as pending, SUM(status='completed') as completed FROM registries")->fetch(PDO::FETCH_ASSOC);
            if ($row) { $stats['total_registries'] = $row['total']; $stats['pending_registries'] = $row['pending'] ?? 0; $stats['completed_registries'] = $row['completed'] ?? 0; }

            // Count bookings eligible vs blocked
            $bookings = $this->db->query("SELECT id FROM plot_bookings WHERE status IN ('emi_active','partially_paid','fully_paid')")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($bookings as $bid) {
                $el = $this->checkEligibility((int)$bid);
                if ($el['eligible']) $stats['eligible_bookings']++;
                else $stats['blocked_bookings']++;
            }
        } catch (\Throwable $e) {}
        return $stats;
    }

    // ---- Private eligibility checks ----

    private function getBooking(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT pb.*, u.name as customer_name, u.phone as customer_phone, p.plot_number, p.block, p.colony_id, c.name as colony_name FROM plot_bookings pb LEFT JOIN users u ON pb.customer_id = u.id LEFT JOIN plots p ON pb.plot_id = p.id LEFT JOIN colonies c ON p.colony_id = c.id WHERE pb.id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) { return null; }
    }

    private function checkEmiStatus(int $bookingId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total, SUM(status IN ('overdue','pending')) as unpaid FROM booking_payment_schedules WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)($row['total'] ?? 0);
            $unpaid = (int)($row['unpaid'] ?? 0);
            if ($total === 0) return ['name' => 'EMI Status', 'passed' => true, 'message' => 'No installments (token-only booking)', 'detail' => '0 installments'];
            if ($unpaid === 0) return ['name' => 'EMI Status', 'passed' => true, 'message' => 'All installments paid', 'detail' => "$total/$total paid"];
            return ['name' => 'EMI Status', 'passed' => false, 'message' => "$unpaid of $total installments unpaid", 'detail' => "Unpaid: $unpaid, Total: $total"];
        } catch (\Throwable $e) {
            return ['name' => 'EMI Status', 'passed' => false, 'message' => 'Cannot check EMI status: ' . $e->getMessage(), 'detail' => 'Error'];
        }
    }

    private function checkPenalties(int $bookingId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(accrued_penalty), 0) as total_penalty FROM booking_payment_schedules WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            $penalty = (float)$stmt->fetchColumn();
            if ($penalty <= 0) return ['name' => 'Penalties', 'passed' => true, 'message' => 'No accrued penalties', 'detail' => '₹0'];
            return ['name' => 'Penalties', 'passed' => false, 'message' => "₹" . number_format($penalty, 2) . " accrued penalties must be cleared", 'detail' => "₹" . number_format($penalty, 2)];
        } catch (\Throwable $e) {
            return ['name' => 'Penalties', 'passed' => true, 'message' => 'Penalty check skipped', 'detail' => 'N/A'];
        }
    }

    private function checkReraCompliance(array $booking): array
    {
        try {
            $colonyId = $booking['colony_id'] ?? 0;
            if (!$colonyId) return ['name' => 'RERA Compliance', 'passed' => true, 'message' => 'No colony associated', 'detail' => 'N/A'];
            $year = (int)date('Y');
            $quarter = ceil((int)date('m') / 3);
            $stmt = $this->db->prepare("SELECT id FROM rera_compliance_log WHERE project_colony_id = ? AND year = ? AND quarter = ?");
            $stmt->execute([$colonyId, $year, $quarter]);
            if ($stmt->fetch()) return ['name' => 'RERA Compliance', 'passed' => true, 'message' => 'Current quarter RERA filed', 'detail' => "Q$quarter $year filed"];
            return ['name' => 'RERA Compliance', 'passed' => false, 'message' => "RERA compliance not filed for Q$quarter $year", 'detail' => "Q$quarter $year missing"];
        } catch (\Throwable $e) {
            return ['name' => 'RERA Compliance', 'passed' => true, 'message' => 'RERA check skipped', 'detail' => 'N/A'];
        }
    }

    private function checkDocuments(int $bookingId): array
    {
        $required = ['agreement', 'id_proof', 'noc'];
        try {
            $stmt = $this->db->prepare("SELECT document_type FROM booking_documents WHERE booking_id = ? AND status = 'verified'");
            $stmt->execute([$bookingId]);
            $uploaded = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $missing = array_diff($required, $uploaded);
            if (empty($missing)) return ['name' => 'Documents', 'passed' => true, 'message' => 'All required documents uploaded', 'detail' => count($uploaded) . ' verified'];
            return ['name' => 'Documents', 'passed' => false, 'message' => 'Missing: ' . implode(', ', $missing), 'detail' => 'Missing: ' . implode(', ', $missing)];
        } catch (\Throwable $e) {
            return ['name' => 'Documents', 'passed' => false, 'message' => 'Cannot check documents', 'detail' => 'Error'];
        }
    }

    private function checkCommissions(int $bookingId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as pending FROM booking_commissions WHERE booking_id = ? AND status = 'pending'");
            $stmt->execute([$bookingId]);
            $pending = (int)$stmt->fetchColumn();
            if ($pending === 0) return ['name' => 'Commissions', 'passed' => true, 'message' => 'All commissions settled', 'detail' => '0 pending'];
            return ['name' => 'Commissions', 'passed' => false, 'message' => "$pending commission(s) still pending", 'detail' => "$pending pending"];
        } catch (\Throwable $e) {
            return ['name' => 'Commissions', 'passed' => true, 'message' => 'Commission check skipped', 'detail' => 'N/A'];
        }
    }

    private function checkNocStatus(int $bookingId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT status FROM noc_requests WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['name' => 'NOC Request', 'passed' => false, 'message' => 'No NOC request found. Please raise one first.', 'detail' => 'Not requested'];
            if ($row['status'] === 'approved') return ['name' => 'NOC Request', 'passed' => true, 'message' => 'NOC approved', 'detail' => 'Approved'];
            if ($row['status'] === 'blocked') return ['name' => 'NOC Request', 'passed' => false, 'message' => 'NOC blocked — fix blockers and re-process', 'detail' => 'Blocked'];
            return ['name' => 'NOC Request', 'passed' => false, 'message' => "NOC status: {$row['status']}", 'detail' => ucfirst($row['status'])];
        } catch (\Throwable $e) {
            return ['name' => 'NOC Request', 'passed' => false, 'message' => 'Cannot check NOC status', 'detail' => 'Error'];
        }
    }
}
