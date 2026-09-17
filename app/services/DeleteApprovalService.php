<?php
namespace App\Services;

use App\Traits\ServiceTenantTrait;

/**
 * Super-admin approval gate for destructive deletes.
 *
 * Critical entities can only be hard-deleted by super_admin. Everyone else
 * files an approval request; a super_admin approves (executes) or rejects.
 * All requests, decisions, IPs and reasons land in delete_approvals.
 */
class DeleteApprovalService
{
    use ServiceTenantTrait;

    private $db;

    /** entity_type => [table, pk, label_sql] */
    private const ENTITIES = [
        'plot_bookings' => ['table' => 'plot_bookings', 'pk' => 'id', 'label' => 'booking_number'],
        'bookings' => ['table' => 'bookings', 'pk' => 'id', 'label' => 'booking_number'],
        'plots' => ['table' => 'plots', 'pk' => 'id', 'label' => 'plot_number'],
        'users' => ['table' => 'users', 'pk' => 'id', 'label' => 'name'],
        'booking_payment_receipts' => ['table' => 'booking_payment_receipts', 'pk' => 'id', 'label' => 'receipt_number'],
        'mlm_commission_ledger' => ['table' => 'mlm_commission_ledger', 'pk' => 'id', 'label' => 'id'],
    ];

    public function __construct($db = null)
    {
        $this->db = $db ?? \App\Core\Database\Database::getInstance()->getConnection();
    }

    public static function isCritical(string $entityType): bool
    {
        return isset(self::ENTITIES[$entityType]);
    }

    public static function isSuperAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'super_admin';
    }

    /** DDL guard — call outside transactions (implicit commit). */
    public function ensureTable(): void
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS delete_approvals (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(60) NOT NULL,
                entity_id INT UNSIGNED NOT NULL,
                entity_label VARCHAR(255) NULL,
                reason VARCHAR(500) NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                requested_by INT UNSIGNED NULL,
                request_ip VARCHAR(45) NULL,
                reviewed_by INT UNSIGNED NULL,
                review_notes VARCHAR(500) NULL,
                reviewed_at DATETIME NULL,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_da_status (status),
                UNIQUE KEY uq_da_entity (entity_type, entity_id, status, tenant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Throwable $e) { error_log('[DeleteApprovalService::ensureTable] ' . $e->getMessage()); }
    }

    /**
     * File (or execute, for super_admin) a delete.
     * Returns ['executed'=>bool, 'approval_id'=>int|null, 'message'=>string].
     */
    public function requestDelete(string $entityType, int $entityId, string $reason = ''): array
    {
        if (!self::isCritical($entityType) || $entityId <= 0) {
            return ['executed' => false, 'approval_id' => null, 'message' => 'Invalid delete request'];
        }
        $this->ensureTable();
        $tid = $this->tenantId();
        $me = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);

        if (self::isSuperAdmin()) {
            $ok = $this->executeDelete($entityType, $entityId);
            return $ok
                ? ['executed' => true, 'approval_id' => null, 'message' => 'Deleted by super admin']
                : ['executed' => false, 'approval_id' => null, 'message' => 'Delete failed'];
        }

        try {
            $label = $this->entityLabel($entityType, $entityId);
            $cols = "entity_type, entity_id, entity_label, reason, status, requested_by, request_ip";
            $vals = "?, ?, ?, ?, 'pending', ?, ?";
            $params = [$entityType, $entityId, $label, substr($reason, 0, 500), $me ?: null, $_SERVER['REMOTE_ADDR'] ?? null];
            if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $this->db->prepare("INSERT INTO delete_approvals ($cols) VALUES ($vals)")->execute($params);
            $aid = (int)$this->db->lastInsertId();
            return ['executed' => false, 'approval_id' => $aid, 'message' => 'Delete request sent for super-admin approval'];
        } catch (\Throwable $e) {
            // Duplicate pending request (unique key) — report, don't delete.
            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                return ['executed' => false, 'approval_id' => null, 'message' => 'A delete request for this record is already pending approval'];
            }
            error_log('[DeleteApprovalService::requestDelete] ' . $e->getMessage());
            return ['executed' => false, 'approval_id' => null, 'message' => 'Could not file delete request'];
        }
    }

    public function approve(int $approvalId, string $notes = ''): array
    {
        if (!self::isSuperAdmin()) {
            return ['success' => false, 'error' => 'Only super admin can approve deletes'];
        }
        $this->ensureTable();
        try {
            $stmt = $this->db->prepare("SELECT * FROM delete_approvals WHERE id = ? AND status = 'pending'");
            $stmt->execute([$approvalId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) return ['success' => false, 'error' => 'Approval not found or already decided'];
            $ok = $this->executeDelete((string)$row['entity_type'], (int)$row['entity_id']);
            if (!$ok) return ['success' => false, 'error' => 'Underlying delete failed'];
            $me = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
            $this->db->prepare("UPDATE delete_approvals SET status = 'approved', reviewed_by = ?, review_notes = ?, reviewed_at = NOW() WHERE id = ?")
                ->execute([$me ?: null, substr($notes, 0, 500), $approvalId]);
            return ['success' => true];
        } catch (\Throwable $e) {
            error_log('[DeleteApprovalService::approve] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function reject(int $approvalId, string $notes = ''): array
    {
        if (!self::isSuperAdmin()) {
            return ['success' => false, 'error' => 'Only super admin can reject deletes'];
        }
        try {
            $me = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
            $stmt = $this->db->prepare("UPDATE delete_approvals SET status = 'rejected', reviewed_by = ?, review_notes = ?, reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
            $stmt->execute([$me ?: null, substr($notes, 0, 500), $approvalId]);
            return $stmt->rowCount() > 0
                ? ['success' => true]
                : ['success' => false, 'error' => 'Approval not found or already decided'];
        } catch (\Throwable $e) {
            error_log('[DeleteApprovalService::reject] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function pendingList(): array
    {
        $this->ensureTable();
        try {
            $sql = "SELECT da.*, u.name AS requested_by_name
                    FROM delete_approvals da
                    LEFT JOIN users u ON u.id = da.requested_by
                    WHERE da.status = 'pending'" . $this->tenantSqlForAlias('da') . "
                    ORDER BY da.created_at DESC LIMIT 200";
            return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[DeleteApprovalService::pendingList] ' . $e->getMessage());
            return [];
        }
    }

    private function entityLabel(string $entityType, int $entityId): ?string
    {
        try {
            $meta = self::ENTITIES[$entityType];
            $stmt = $this->db->prepare("SELECT {$meta['label']} AS lbl FROM {$meta['table']} WHERE {$meta['pk']} = ? LIMIT 1");
            $stmt->execute([$entityId]);
            $v = $stmt->fetchColumn();
            return $v !== false ? (string)$v : null;
        } catch (\Throwable $e) { return null; }
    }

    private function executeDelete(string $entityType, int $entityId): bool
    {
        try {
            $meta = self::ENTITIES[$entityType] ?? null;
            if (!$meta) return false;
            $sql = "DELETE FROM {$meta['table']} WHERE {$meta['pk']} = ?";
            $params = [$entityId];
            if ($this->tenantId() > 1) { $sql .= " AND tenant_id = ?"; $params[] = $this->tenantId(); }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log('[DeleteApprovalService::executeDelete] ' . $e->getMessage());
            return false;
        }
    }
}
