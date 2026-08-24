<?php
/**
 * AssignmentApprovalService — Lead assignment approval workflow
 * Phase 6: Managers approve/reject lead reassignment requests
 */
namespace App\Services;

use App\Core\Database;

use \App\Traits\ServiceTenantTrait;

class AssignmentApprovalService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Request an assignment change (creates pending approval).
     */
    public function requestAssignment(int $leadId, int $requestedFrom, int $requestedTo, int $requestedBy, string $reason = ''): array
    {
        try {
            // If requester is admin/super_admin, auto-approve
            $requesterRole = $this->getUserRole($requestedBy);
            if (in_array($requesterRole, ['admin', 'super_admin'])) {
                return $this->approveRequest(0, $leadId, $requestedTo, $requestedBy, 'Auto-approved: admin request', true);
            }

            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";

            // Check for duplicate pending request
            $existing = $this->db->fetch(
                "SELECT id FROM lead_assignment_approvals WHERE lead_id = ? AND status = 'pending' AND requested_by = ?" . $tidSql,
                $tid > 1 ? [$leadId, $requestedBy, $tid] : [$leadId, $requestedBy]
            );
            if ($existing) {
                return ['success' => false, 'error' => 'You already have a pending request for this lead'];
            }

            $this->db->query(
                "INSERT INTO lead_assignment_approvals (lead_id, requested_by, requested_to, notes, status, created_at" . $tenantCol . ")
                 VALUES (?, ?, ?, ?, 'pending', NOW()" . $tenantVal . ")",
                array_merge([$leadId, $requestedBy, $requestedTo, $reason], $tid > 1 ? [$tid] : [])
            );
            $approvalId = $this->db->lastInsertId();

            // Log activity on the lead
            $crm = new CRMService();
            $fromName = $this->getUserName($requestedFrom);
            $toName = $this->getUserName($requestedTo);
            $crm->logActivity($leadId, $requestedBy, 'assignment_request',
                "Assignment change requested",
                "From: $fromName → To: $toName | Reason: $reason | Status: Pending approval"
            );

            return ['success' => true, 'approval_id' => $approvalId, 'auto_approved' => false];
        } catch (\Exception $e) {
            error_log('AssignmentApprovalService::requestAssignment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Approve a pending assignment request.
     */
    public function approveRequest(int $approvalId, int $leadId = 0, int $requestedTo = 0, int $approvedBy = 0, string $notes = '', bool $skipLookup = false): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            if (!$skipLookup && $approvalId > 0) {
                $approval = $this->db->fetch("SELECT * FROM lead_assignment_approvals WHERE id = ? AND status = 'pending'" . $tidSql, $tid > 1 ? [$approvalId, $tid] : [$approvalId]);
                if (!$approval) {
                    return ['success' => false, 'error' => 'Request not found or already processed'];
                }
                $leadId = (int)$approval['lead_id'];
                $requestedTo = (int)$approval['requested_to'];
            }

            // Execute the assignment
            $crm = new CRMService();
            $assignResult = $crm->assignLead($leadId, $requestedTo, $approvedBy, "Approved assignment");

            if (!$assignResult['success']) {
                return ['success' => false, 'error' => 'Assignment failed: ' . ($assignResult['error'] ?? 'Unknown')];
            }

            // Update approval status
            if ($approvalId > 0) {
                $this->db->query(
                    "UPDATE lead_assignment_approvals SET status = 'approved', approved_by = ?, approved_at = NOW(), notes = ? WHERE id = ?" . $tidSql,
                    array_merge([$approvedBy, $notes, $approvalId], $tid > 1 ? [$tid] : [])
                );
            }

            // Log
            $crm->logActivity($leadId, $approvedBy, 'assignment_approved',
                "Assignment approved",
                "Lead assigned to " . $this->getUserName($requestedTo)
            );

            return ['success' => true];
        } catch (\Exception $e) {
            error_log('AssignmentApprovalService::approveRequest error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reject a pending assignment request.
     */
    public function rejectRequest(int $approvalId, int $rejectedBy, string $reason = ''): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $approval = $this->db->fetch("SELECT * FROM lead_assignment_approvals WHERE id = ? AND status = 'pending'" . $tidSql, $tid > 1 ? [$approvalId, $tid] : [$approvalId]);
            if (!$approval) {
                return ['success' => false, 'error' => 'Request not found or already processed'];
            }

            $this->db->query(
                "UPDATE lead_assignment_approvals SET status = 'rejected', approved_by = ?, approved_at = NOW(), notes = ? WHERE id = ?" . $tidSql,
                array_merge([$rejectedBy, $reason, $approvalId], $tid > 1 ? [$tid] : [])
            );

            // Log
            $crm = new CRMService();
            $crm->logActivity((int)$approval['lead_id'], $rejectedBy, 'assignment_rejected',
                "Assignment request rejected",
                "Reason: " . ($reason ?: 'No reason provided')
            );

            return ['success' => true];
        } catch (\Exception $e) {
            error_log('AssignmentApprovalService::rejectRequest error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all pending approval requests.
     */
    public function getPendingRequests(): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND l.tenant_id = ?" : "";
            return $this->db->fetchAll(
                "SELECT laa.*,
                    l.name as lead_name, l.phone as lead_phone, l.status as lead_status,
                    u_from.name as from_name, u_to.name as to_name,
                    u_req.name as requested_by_name
                 FROM lead_assignment_approvals laa
                 LEFT JOIN leads l ON l.id = laa.lead_id
                 LEFT JOIN users u_from ON u_from.id = laa.requested_from
                 LEFT JOIN users u_to ON u_to.id = laa.requested_to
                 LEFT JOIN users u_req ON u_req.id = laa.requested_by
                 WHERE laa.status = 'pending'" . $tidSql . "
                 ORDER BY laa.created_at ASC",
                $tid > 1 ? [$tid] : []
            ) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get approval history.
     */
    public function getHistory(int $limit = 50): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND laa.tenant_id = ?" : "";
            return $this->db->fetchAll(
                "SELECT laa.*,
                    l.name as lead_name,
                    u_from.name as from_name, u_to.name as to_name,
                    u_req.name as requested_by_name, u_app.name as approved_by_name
                 FROM lead_assignment_approvals laa
                 LEFT JOIN leads l ON l.id = laa.lead_id
                 LEFT JOIN users u_from ON u_from.id = laa.requested_from
                 LEFT JOIN users u_to ON u_to.id = laa.requested_to
                 LEFT JOIN users u_req ON u_req.id = laa.requested_by
                 LEFT JOIN users u_app ON u_app.id = laa.approved_by
                 WHERE 1=1" . $tidSql . "
                 ORDER BY laa.created_at DESC LIMIT " . ($tid > 1 ? "?" : "") . " ",
                $tid > 1 ? array_merge([$limit, $tid]) : [$limit]
            ) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get approval stats.
     */
    public function getStats(): array
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " WHERE tenant_id = ?" : "";
            $stats = $this->db->fetch(
                "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                 FROM lead_assignment_approvals" . $tidSql,
                $tid > 1 ? [$tid] : []
            );
            return $stats ?: ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        } catch (\Exception $e) {
            return ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        }
    }

    private function getUserRole(int $userId): string
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $user = $this->db->fetch("SELECT role FROM users WHERE id = ?" . $tidSql, $tid > 1 ? [$userId, $tid] : [$userId]);
            return $user['role'] ?? 'user';
        } catch (\Exception $e) { return 'user'; }
    }

    private function getUserName(int $userId): string
    {
        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $user = $this->db->fetch("SELECT name FROM users WHERE id = ?" . $tidSql, $tid > 1 ? [$userId, $tid] : [$userId]);
            return $user['name'] ?? 'Unknown';
        } catch (\Exception $e) { return 'Unknown'; }
    }
}
