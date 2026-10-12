<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

class TownshipGrievanceService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Submit a resident infrastructure grievance
     */
    public function submitGrievance(array $data): array
    {
        $tenantId = (int)($data['tenant_id'] ?? 1);
        $customerId = (int)($data['customer_id'] ?? 0);
        $colonyId = (int)($data['colony_id'] ?? 0);
        $plotId = !empty($data['plot_id']) ? (int)$data['plot_id'] : null;
        $title = trim((string)($data['title'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $category = $data['category'] ?? 'water_supply';
        $priority = $data['priority'] ?? 'medium';

        if ($customerId <= 0 || $colonyId <= 0 || empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Customer, colony, title, and description are required.'];
        }

        $randSeq = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $ticketToken = 'APS-GRV-GOR-' . date('Y') . '-' . $randSeq;

        // Calculate SLA deadline based on priority
        $slaHours = match ($priority) {
            'emergency' => 4,
            'high' => 24,
            'medium' => 48,
            default => 72,
        };
        $slaDeadline = date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"));

        $sql = "INSERT INTO township_resident_grievances (
                    tenant_id, ticket_token, customer_id, colony_id, plot_id,
                    category, priority, title, description, status,
                    sla_deadline, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, 'open',
                    ?, NOW()
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $tenantId, $ticketToken, $customerId, $colonyId, $plotId,
            $category, $priority, $title, $description,
            $slaDeadline
        ]);

        $id = (int)$this->db->getConnection()->lastInsertId();

        return [
            'success' => true,
            'id' => $id,
            'ticket_token' => $ticketToken,
            'sla_deadline' => $slaDeadline,
            'message' => 'Infrastructure grievance registered. Estate officer notified.'
        ];
    }

    /**
     * Assign AMC contractor / technician to a grievance
     */
    public function assignContractor(int $id, array $assignmentData): bool
    {
        $contractor = trim((string)($assignmentData['assigned_contractor_name'] ?? ''));
        $contact = trim((string)($assignmentData['assigned_contact'] ?? ''));
        $cost = (float)($assignmentData['estimated_cost'] ?? 0.0);

        $sql = "UPDATE township_resident_grievances 
                SET assigned_contractor_name = ?,
                    assigned_contact = ?,
                    estimated_cost = ?,
                    status = 'in_progress',
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contractor, $contact, $cost, $id]);
    }

    /**
     * Mark grievance resolved with site engineering notes
     */
    public function resolveGrievance(int $id, string $notes): bool
    {
        $sql = "UPDATE township_resident_grievances 
                SET status = 'resolved',
                    resolution_notes = ?,
                    resolved_at = NOW(),
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notes, $id]);
    }

    /**
     * Resident closes ticket with 1-5 star satisfaction rating
     */
    public function closeWithRating(int $id, int $rating, ?string $feedback = null): bool
    {
        $rating = max(1, min(5, $rating));
        $sql = "UPDATE township_resident_grievances 
                SET status = 'closed',
                    resident_rating = ?,
                    resident_feedback = ?,
                    updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$rating, $feedback, $id]);
    }

    /**
     * Get details of a single grievance ticket
     */
    public function getGrievanceDetails(int $id): ?array
    {
        $sql = "SELECT trg.*,
                       c.name AS colony_name, c.location AS colony_location,
                       p.plot_number,
                       u.name AS resident_name, u.phone AS resident_phone, u.email AS resident_email
                FROM township_resident_grievances trg
                JOIN colonies c ON trg.colony_id = c.id
                LEFT JOIN plots p ON trg.plot_id = p.id
                JOIN users u ON trg.customer_id = u.id
                WHERE trg.id = ?
                LIMIT 1";
        return $this->db->fetch($sql, [$id]) ?: null;
    }

    /**
     * Get tickets for a specific resident
     */
    public function getCustomerGrievances(int $customerId): array
    {
        $sql = "SELECT trg.*, c.name AS colony_name, p.plot_number
                FROM township_resident_grievances trg
                JOIN colonies c ON trg.colony_id = c.id
                LEFT JOIN plots p ON trg.plot_id = p.id
                WHERE trg.customer_id = ?
                ORDER BY trg.id DESC";
        return $this->db->fetchAll($sql, [$customerId]) ?: [];
    }

    /**
     * Summary metrics for Estate Management Desk
     */
    public function getSummaryStats(): array
    {
        $row = $this->db->fetch("SELECT 
                    COUNT(*) AS total_tickets,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                    SUM(CASE WHEN status IN ('resolved','closed') THEN 1 ELSE 0 END) AS resolved_count,
                    SUM(CASE WHEN status = 'open' AND sla_deadline < NOW() THEN 1 ELSE 0 END) AS sla_breached_count,
                    COALESCE(AVG(CASE WHEN resident_rating IS NOT NULL THEN resident_rating ELSE NULL END), 0) AS avg_rating
                FROM township_resident_grievances");

        return [
            'total' => (int)($row['total_tickets'] ?? 0),
            'open' => (int)($row['open_count'] ?? 0),
            'in_progress' => (int)($row['in_progress_count'] ?? 0),
            'resolved' => (int)($row['resolved_count'] ?? 0),
            'sla_breached' => (int)($row['sla_breached_count'] ?? 0),
            'avg_rating' => round((float)($row['avg_rating'] ?? 0.0), 1)
        ];
    }
}
