<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class ShiftSwapService
{
    /**
     * Query runner: fetch a single row.
     *
     * NOTE (S451): this service calls $this->fetchOne($sql, $params) but the
     * helper never existed -> "Call to undefined method <Service>::fetchOne()".
     */
    private function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[' . __CLASS__ . '::fetchOne] ' . $e->getMessage());
            return null;
        }
    }

    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Request a shift swap
     */
    public function requestSwap(int $requesterId, array $data): array
    {
        // Validate requester (profile-first, legacy fallback)
        $requester = $this->fetchOne("
            SELECT ep.legacy_id AS id, u.name, ep.department
            FROM employee_profiles ep JOIN users u ON u.id = ep.user_id
            WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?) AND ep.status = 'active'
        ", [$requesterId, $requesterId, $requesterId]);
        if (!$requester) return ['success' => false, 'message' => 'Requester not found or inactive'];

        // Validate target (profile-first, legacy fallback)
        $targetId = (int)($data['target_id'] ?? 0);
        $target = $this->fetchOne("
            SELECT ep.legacy_id AS id, u.name
            FROM employee_profiles ep JOIN users u ON u.id = ep.user_id
            WHERE (ep.legacy_id = ? OR ep.user_id = ? OR ep.id = ?) AND ep.status = 'active'
        ", [$targetId, $targetId, $targetId]);
        if (!$target) return ['success' => false, 'message' => 'Target employee not found or inactive'];

        if ($targetId === $requesterId) {
            return ['success' => false, 'message' => 'Cannot swap with yourself'];
        }

        // Check for existing pending swap between these two
        $existing = $this->fetchOne("
            SELECT id FROM shift_swap_requests
            WHERE ((requester_id = ? AND target_id = ?) OR (requester_id = ? AND target_id = ?))
            AND status = 'pending'
        ", [$requesterId, $targetId, $targetId, $requesterId]);

        if ($existing) {
            return ['success' => false, 'message' => 'A pending swap request already exists between you'];
        }

        $swapDate = $data['swap_date'] ?? date('Y-m-d');
        if (!$swapDate || strtotime($swapDate) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'message' => 'Swap date must be today or in the future'];
        }

        // Validate shift times if provided
        $requesterShiftStart = $data['requester_shift_start'] ?? null;
        $requesterShiftEnd = $data['requester_shift_end'] ?? null;
        $targetShiftStart = $data['target_shift_start'] ?? null;
        $targetShiftEnd = $data['target_shift_end'] ?? null;

        if ($requesterShiftStart && $requesterShiftEnd) {
            if (strtotime($requesterShiftStart) >= strtotime($requesterShiftEnd)) {
                return ['success' => false, 'message' => 'Requester shift end must be after start'];
            }
        }

        if ($targetShiftStart && $targetShiftEnd) {
            if (strtotime($targetShiftStart) >= strtotime($targetShiftEnd)) {
                return ['success' => false, 'message' => 'Target shift end must be after start'];
            }
        }

        $this->execute("
            INSERT INTO shift_swap_requests
            (tenant_id, requester_id, target_id, swap_date, requester_shift_start, requester_shift_end,
             target_shift_start, target_shift_end, reason, status, requested_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ", [
            $this->tenantId(), $requesterId, $targetId, $swapDate,
            $requesterShiftStart, $requesterShiftEnd,
            $targetShiftStart, $targetShiftEnd,
            $data['reason'] ?? ''
        ]);

        return [
            'success' => true,
            'message' => 'Shift swap request sent',
            'swap_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Get employee's swaps (as requester or target)
     */
    public function getEmployeeSwaps(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT ssr.*,
                   u_r.name as requester_name, ep_r.employee_code as requester_code,
                   u_t.name as target_name, ep_t.employee_code as target_code,
                   CASE WHEN ssr.requester_id = ? THEN 'requester' ELSE 'target' END as role
            FROM shift_swap_requests ssr
            LEFT JOIN employee_profiles ep_r ON ep_r.legacy_id = ssr.requester_id OR ep_r.user_id = ssr.requester_id OR ep_r.id = ssr.requester_id
            LEFT JOIN users u_r ON u_r.id = ep_r.user_id
            LEFT JOIN employee_profiles ep_t ON ep_t.legacy_id = ssr.target_id OR ep_t.user_id = ssr.target_id OR ep_t.id = ssr.target_id
            LEFT JOIN users u_t ON u_t.id = ep_t.user_id
            WHERE (ssr.requester_id = ? OR ssr.target_id = ?)
              AND ssr.tenant_id = 1
            ORDER BY ssr.requested_at DESC
        ", array_merge([$employeeId, $employeeId, $employeeId], $this->tVal()));
    }

    /**
     * Get swap details
     */
    public function getSwap(int $swapId): ?array
    {
        return $this->db->fetchOne("
            SELECT ssr.*,
                   u_r.name as requester_name, ep_r.employee_code as requester_code,
                   u_t.name as target_name, ep_t.employee_code as target_code
            FROM shift_swap_requests ssr
            LEFT JOIN employee_profiles ep_r ON ep_r.legacy_id = ssr.requester_id OR ep_r.user_id = ssr.requester_id OR ep_r.id = ssr.requester_id
            LEFT JOIN users u_r ON u_r.id = ep_r.user_id
            LEFT JOIN employee_profiles ep_t ON ep_t.legacy_id = ssr.target_id OR ep_t.user_id = ssr.target_id OR ep_t.id = ssr.target_id
            LEFT JOIN users u_t ON u_t.id = ep_t.user_id
            WHERE ssr.id = ? AND ssr.tenant_id = 1
        ", [$swapId]);
    }

    /**
     * Respond to swap request (accept/reject)
     */
    public function respondToSwap(int $swapId, int $employeeId, string $action, string $rejectionReason = ''): array
    {
        $swap = $this->getSwap($swapId);
        if (!$swap) return ['success' => false, 'message' => 'Swap request not found'];

        // Only target can respond
        if ($swap['target_id'] != $employeeId) {
            return ['success' => false, 'message' => 'Not authorized to respond to this request'];
        }

        if ($swap['status'] !== 'pending') {
            return ['success' => false, 'message' => 'This request has already been ' . $swap['status']];
        }

        if ($action === 'accept') {
            $this->execute("
                UPDATE shift_swap_requests
                SET status = 'approved', responded_at = NOW(), responded_by = ?
                WHERE id = ?{$this->tenantSql()}
            ", array_merge([$employeeId, $swapId], $this->tVal()));

            return ['success' => true, 'message' => 'Shift swap approved'];
        } elseif ($action === 'reject') {
            if (empty($rejectionReason)) {
                return ['success' => false, 'message' => 'Rejection reason is required'];
            }

            $this->execute("
                UPDATE shift_swap_requests
                SET status = 'rejected', responded_at = NOW(), responded_by = ?, rejection_reason = ?
                WHERE id = ?{$this->tenantSql()}
            ", array_merge([$employeeId, $rejectionReason, $swapId], $this->tVal()));

            return ['success' => true, 'message' => 'Shift swap rejected'];
        } elseif ($action === 'cancel') {
            // Requester can cancel their own request
            $swap = $this->getSwap($swapId);
            if ($swap['requester_id'] != $employeeId) {
                return ['success' => false, 'message' => 'Only requester can cancel'];
            }
            if ($swap['status'] !== 'pending') {
                return ['success' => false, 'message' => 'Cannot cancel non-pending request'];
            }

            $this->execute("
                UPDATE shift_swap_requests
                SET status = 'cancelled', responded_at = NOW(), responded_by = ?
                WHERE id = ?{$this->tenantSql()}
            ", array_merge([$employeeId, $swapId], $this->tVal()));

            return ['success' => true, 'message' => 'Shift swap cancelled'];
        }

        return ['success' => false, 'message' => 'Invalid action'];
    }

    /**
     * Get pending swaps for employee (as target)
     */
    public function getPendingSwaps(int $employeeId): array
    {
        return $this->db->fetchAll("
            SELECT ssr.*, u_r.name as requester_name, ep_r.employee_code as requester_code
            FROM shift_swap_requests ssr
            LEFT JOIN employee_profiles ep_r ON ep_r.legacy_id = ssr.requester_id OR ep_r.user_id = ssr.requester_id OR ep_r.id = ssr.requester_id
            LEFT JOIN users u_r ON u_r.id = ep_r.user_id
            WHERE ssr.target_id = ? AND ssr.status = 'pending' AND ssr.tenant_id = 1
            ORDER BY ssr.requested_at ASC
        ", [$employeeId]);
    }

    /**
     * Get all pending swaps (admin)
     */
    public function getAllPendingSwaps(): array
    {
        return $this->db->fetchAll("
            SELECT ssr.*,
                   u_r.name as requester_name, ep_r.employee_code as requester_code,
                   u_t.name as target_name, ep_t.employee_code as target_code
            FROM shift_swap_requests ssr
            LEFT JOIN employee_profiles ep_r ON ep_r.legacy_id = ssr.requester_id OR ep_r.user_id = ssr.requester_id OR ep_r.id = ssr.requester_id
            LEFT JOIN users u_r ON u_r.id = ep_r.user_id
            LEFT JOIN employee_profiles ep_t ON ep_t.legacy_id = ssr.target_id OR ep_t.user_id = ssr.target_id OR ep_t.id = ssr.target_id
            LEFT JOIN users u_t ON u_t.id = ep_t.user_id
            WHERE ssr.status = 'pending' AND ssr.tenant_id = 1
            ORDER BY ssr.requested_at ASC
        ");
    }

    /**
     * Get all swaps for admin
     */
    public function getAllSwaps(): array
    {
        return $this->db->fetchAll("
            SELECT ssr.*,
                   u_r.name as requester_name, ep_r.employee_code as requester_code,
                   u_t.name as target_name, ep_t.employee_code as target_code
            FROM shift_swap_requests ssr
            LEFT JOIN employee_profiles ep_r ON ep_r.legacy_id = ssr.requester_id OR ep_r.user_id = ssr.requester_id OR ep_r.id = ssr.requester_id
            LEFT JOIN users u_r ON u_r.id = ep_r.user_id
            LEFT JOIN employee_profiles ep_t ON ep_t.legacy_id = ssr.target_id OR ep_t.user_id = ssr.target_id OR ep_t.id = ssr.target_id
            LEFT JOIN users u_t ON u_t.id = ep_t.user_id
            WHERE ssr.tenant_id = 1
            ORDER BY ssr.requested_at DESC
        ");
    }
}