<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class HelpdeskService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber(): string
    {
        $prefix = 'HD';
        $year = date('Y');
        $month = date('m');
        
        $count = $this->db->fetchOne("
            SELECT COUNT(*) as cnt FROM helpdesk_tickets 
            WHERE tenant_id = ? AND ticket_number LIKE ?
        ", [$this->tenantId(), "{$prefix}-{$year}{$month}%"])['cnt'] ?? 0;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $count + 1);
    }

    /**
     * Create a new ticket
     */
    public function createTicket(int $employeeId, array $data): array
    {
        $category = $data['category'] ?? 'other';
        $allowedCategories = ['it', 'hr', 'finance', 'facilities', 'security', 'other'];
        if (!in_array($category, $allowedCategories)) {
            return ['success' => false, 'message' => 'Invalid category'];
        }

        $subject = trim($data['subject'] ?? '');
        if (empty($subject)) {
            return ['success' => false, 'message' => 'Subject is required'];
        }

        $description = trim($data['description'] ?? '');
        if (empty($description)) {
            return ['success' => false, 'message' => 'Description is required'];
        }

        $priority = $data['priority'] ?? 'normal';
        $allowedPriorities = ['low', 'normal', 'high', 'urgent', 'critical'];
        if (!in_array($priority, $allowedPriorities)) {
            $priority = 'normal';
        }

        $ticketNumber = $this->generateTicketNumber();

        $slaHours = 24;
        switch ($priority) {
            case 'critical': $slaHours = 2; break;
            case 'urgent': $slaHours = 4; break;
            case 'high': $slaHours = 8; break;
            case 'normal': $slaHours = 24; break;
            case 'low': $slaHours = 48; break;
        }

        $this->execute("
            INSERT INTO helpdesk_tickets
            (tenant_id, ticket_number, employee_id, category, sub_category, priority, subject, description, status, sla_hours, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open', ?, NOW())
        ", [$this->tenantId(), $ticketNumber, $employeeId, $category, $data['sub_category'] ?? null, $priority, $subject, $description, $slaHours]);

        $ticketId = $this->db->lastInsertId();

        // Notify assignee if category has default assignee
        // (could be implemented based on category mapping)

        return [
            'success' => true,
            'message' => 'Ticket created successfully',
            'ticket_id' => $ticketId,
            'ticket_number' => $ticketNumber
        ];
    }

    /**
     * Get employee's tickets
     */
    public function getEmployeeTickets(int $employeeId, $status = null): array
    {
        $query = "
            SELECT * FROM helpdesk_tickets
            WHERE employee_id = ?{$this->tenantSql()}
        ";
        $params = array_merge([$employeeId], $this->tVal());

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY created_at DESC";
        return $this->fetchAll($query, $params);
    }

    /**
     * Get ticket by ID
     */
    public function getTicket(int $ticketId): ?array
    {
        return $this->db->fetchOne("
            SELECT ht.*, e.name as employee_name, e.employee_code, e.email, e.phone,
                   u.name as assigned_to_name
            FROM helpdesk_tickets ht
            LEFT JOIN employees e ON ht.employee_id = e.id
            LEFT JOIN users u ON ht.assigned_to = u.id
            WHERE ht.id = ? AND ht.tenant_id = 1
        ", [$ticketId]);
    }

    /**
     * Get ticket by ticket number
     */
    public function getTicketByNumber(string $ticketNumber): ?array
    {
        return $this->db->fetchOne("
            SELECT ht.*, e.name as employee_name, e.employee_code
            FROM helpdesk_tickets ht
            JOIN employees e ON ht.employee_id = e.id
            WHERE ht.ticket_number = ? AND ht.tenant_id = 1
        ", [$ticketNumber]);
    }

    /**
     * Get ticket comments
     */
    public function getTicketComments(int $ticketId): array
    {
        return $this->db->fetchAll("
            SELECT htc.*, u.name as author_name, u.role as author_role
            FROM helpdesk_ticket_comments htc
            LEFT JOIN users u ON htc.employee_id = u.id
            WHERE htc.ticket_id = ? AND htc.tenant_id = 1
            ORDER BY htc.created_at ASC
        ", [$ticketId]);
    }

    /**
     * Add comment to ticket
     */
    public function addComment(int $ticketId, int $employeeId, array $data): array
    {
        $ticket = $this->getTicket($ticketId);
        if (!$ticket) return ['success' => false, 'message' => 'Ticket not found'];

        $comment = trim($data['comment'] ?? '');
        if (empty($comment)) {
            return ['success' => false, 'message' => 'Comment cannot be empty'];
        }

        $isInternal = !empty($data['is_internal']) && in_array(($data['user_role'] ?? ''), ['admin', 'super_admin', 'hr_manager', 'it_manager']);

        $this->execute("
            INSERT INTO helpdesk_ticket_comments
            (tenant_id, ticket_id, employee_id, comment, is_internal, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$this->tenantId(), $ticketId, $employeeId, $comment, $isInternal ? 1 : 0]);

        // If employee is not the ticket owner, notify them
        // (notification logic can be added)

        return ['success' => true, 'message' => 'Comment added'];
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus(int $ticketId, string $status, int $userId = null): array
    {
        $ticket = $this->getTicket($ticketId);
        if (!$ticket) return ['success' => false, 'message' => 'Ticket not found'];

        $allowedStatuses = ['open', 'in_progress', 'on_hold', 'resolved', 'closed', 'reopened'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $updates = ['status = ?'];
        $params = [$status];

        if ($status === 'resolved' || $status === 'closed') {
            $updates[] = 'resolved_at = NOW()';
            $updates[] = 'resolved_by = ?';
            $params[] = $userId;
        }

        if ($status === 'in_progress' && !$ticket['assigned_to']) {
            $updates[] = 'assigned_to = ?';
            $params[] = $userId;
        }

        if ($status === 'open' && $ticket['status'] === 'resolved') {
            $updates[] = 'resolved_at = NULL';
            $updates[] = 'resolved_by = NULL';
        }

        $params[] = $ticketId;
        $params[] = $this->tenantId();

        $this->execute(
            "UPDATE helpdesk_tickets SET " . implode(', ', $updates) . " WHERE id = ? AND tenant_id = ?",
            $params
        );

        return ['success' => true, 'message' => 'Ticket status updated'];
    }

    /**
     * Assign ticket
     */
    public function assignTicket(int $ticketId, int $assigneeId, int $assignedBy): array
    {
        $ticket = $this->getTicket($ticketId);
        if (!$ticket) return ['success' => false, 'message' => 'Ticket not found'];

        // Verify assignee exists and is active
        $assignee = $this->fetchOne("SELECT id FROM users WHERE id = ? AND status = 'active'", [$assigneeId]);
        if (!$assignee) return ['success' => false, 'message' => 'Assignee not found or inactive'];

        $this->execute(
            "UPDATE helpdesk_tickets SET assigned_to = ?, assigned_at = NOW() WHERE id = ? AND tenant_id = 1",
            [$assigneeId, $ticketId]
        );

        // Add comment
        $this->execute("
            INSERT INTO helpdesk_ticket_comments (tenant_id, ticket_id, employee_id, comment, is_internal, created_at)
            VALUES (?, ?, ?, 'Ticket assigned', 1, NOW())
        ", [$this->tenantId(), $ticketId, $assignedBy]);

        return ['success' => true, 'message' => 'Ticket assigned'];
    }

    /**
     * Get all tickets (admin/manager)
     */
    public function getAllTickets(array $filters = []): array
    {
        $query = "
            SELECT ht.*, e.name as employee_name, e.employee_code, e.department,
                   u.name as assigned_to_name
            FROM helpdesk_tickets ht
            JOIN employees e ON ht.employee_id = e.id
            LEFT JOIN users u ON ht.assigned_to = u.id
            WHERE ht.tenant_id = 1
        ";
        $params = [1];

        if (!empty($filters['status'])) {
            $query .= " AND ht.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $query .= " AND ht.category = ?";
            $params[] = $filters['category'];
        }
        if (!empty($filters['priority'])) {
            $query .= " AND ht.priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['assigned_to'])) {
            $query .= " AND ht.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        if (!empty($filters['employee_id'])) {
            $query .= " AND ht.employee_id = ?";
            $params[] = $filters['employee_id'];
        }

        $query .= " ORDER BY 
            CASE ht.priority 
                WHEN 'critical' THEN 1 
                WHEN 'urgent' THEN 2 
                WHEN 'high' THEN 3 
                WHEN 'normal' THEN 4 
                WHEN 'low' THEN 5 
            END, ht.created_at DESC";

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics(): array
    {
        $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM helpdesk_tickets WHERE tenant_id = 1")['cnt'] ?? 0;
        $open = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM helpdesk_tickets WHERE tenant_id = 1 AND status = 'open'")['cnt'] ?? 0;
        $inProgress = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM helpdesk_tickets WHERE tenant_id = 1 AND status = 'in_progress'")['cnt'] ?? 0;
        $resolved = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM helpdesk_tickets WHERE tenant_id = 1 AND status IN ('resolved','closed')")['cnt'] ?? 0;

        $byCategory = $this->db->fetchAll("
            SELECT category, COUNT(*) as cnt
            FROM helpdesk_tickets
            WHERE tenant_id = 1
            GROUP BY category
        ");

        $byPriority = $this->fetchAll("
            SELECT priority, COUNT(*) as cnt
            FROM helpdesk_tickets
            WHERE tenant_id = 1
            GROUP BY priority
        ");

        $avgResolution = $this->fetchOne("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
            FROM helpdesk_tickets
            WHERE tenant_id = 1 AND status IN ('resolved','closed') AND resolved_at IS NOT NULL
        ");

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'by_category' => $byCategory,
            'by_priority' => $byPriority,
            'avg_resolution_hours' => round($avgResolution['avg_hours'] ?? 0, 1)
        ];
    }

    /**
     * Get SLA breaches
     */
    public function getSLABreaches(): array
    {
        return $this->db->fetchAll("
            SELECT ht.*, e.name as employee_name
            FROM helpdesk_tickets ht
            JOIN employees e ON ht.employee_id = e.id
            WHERE ht.tenant_id = 1
              AND ht.status IN ('open','in_progress')
              AND ht.sla_breached = 0
              AND TIMESTAMPDIFF(HOUR, ht.created_at, NOW()) > ht.sla_hours
        ");
    }

    /**
     * Mark SLA breaches
     */
    public function checkAndMarkSLABreaches(): int
    {
        $breaches = $this->getSLABreaches();
        $count = 0;

        foreach ($breaches as $b) {
            $this->execute("
                UPDATE helpdesk_tickets 
                SET sla_breached = 1 
                WHERE id = ? AND sla_breached = 0
            ", [$b['id']]);
            $count++;
        }

        return $count;
    }
}