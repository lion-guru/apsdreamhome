<?php
namespace App\Services;

use App\Core\Database\Database;

use \App\Traits\ServiceTenantTrait;

class AuditService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $auditLogService;

    public function __construct($db)
    {
        $this->db = $db instanceof Database ? $db : Database::getInstance();
        $this->auditLogService = new AuditLogService($this->db);
    }

    public function log(string $action, ?int $userId = null, ?string $userRole = null, ?string $entityType = null, ?int $entityId = null, ?string $description = null, array $changes = [], string $status = 'success'): int
    {
        return $this->auditLogService->log([
            'user_id' => $userId ?? 0,
            'user_role' => $userRole ?? 'unknown',
            'action' => $action,
            'action_type' => $this->mapActionToType($action),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => null,
            'new_values' => $changes ?: null,
            'status' => $status,
        ]);
    }

    private function mapActionToType(string $action): string
    {
        $map = [
            'create' => 'create', 'insert' => 'create', 'store' => 'create', 'register' => 'create',
            'read' => 'read', 'view' => 'read', 'show' => 'read', 'list' => 'read', 'index' => 'read',
            'update' => 'update', 'edit' => 'update', 'modify' => 'update', 'change' => 'update',
            'delete' => 'delete', 'destroy' => 'delete', 'remove' => 'delete', 'trash' => 'delete',
            'login' => 'login', 'logout' => 'logout',
            'export' => 'export', 'import' => 'import',
            'print' => 'print',
            'approve' => 'approve', 'reject' => 'reject',
            'payment' => 'payment', 'commission' => 'commission',
        ];
        return $map[$action] ?? 'update';
    }

    public function getRecent(int $limit = 50, ?string $action = null, ?string $entityType = null, ?int $userId = null): array
    {
        $result = $this->auditLogService->getLogs([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
        ], $limit, 0);

        $rows = $result['logs'];
        // Format for backward compatibility
        foreach ($rows as &$r) {
            $r['user_name'] = $r['user_name'] ?? null;
            $r['details'] = json_encode([
                'entity_type' => $r['entity_type'] ?? null,
                'entity_id' => $r['entity_id'] ?? null,
                'description' => $r['description'] ?? null,
                'status' => $r['status'] ?? 'success',
                'method' => $r['request_method'] ?? null,
                'url' => $r['request_url'] ?? null,
                'user_agent' => $r['user_agent'] ?? null,
                'changes' => $r['new_values'] ?? null,
            ]);
        }
        return $rows;
    }

    public function getStats(int $days = 7): array
    {
        return $this->auditLogService->getStats($days);
    }

    public function cleanup(int $daysToKeep = 90): int
    {
        try {
            return (int)$this->db->query(
                "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$daysToKeep]
            );
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
