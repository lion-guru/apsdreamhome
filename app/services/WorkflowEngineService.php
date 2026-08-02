<?php

namespace App\Services;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * Workflow Engine Service - Approval Workflows
 * Manage multi-step approval processes for bookings, commissions, etc.
 */
class WorkflowEngineService
{
    use \App\Traits\ServiceTenantTrait;

    private $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }

    /**
     * Ensure workflow tables exist
     */
    private function ensureTablesExist(): void
    {
        // Workflow definitions
        $sql = "CREATE TABLE IF NOT EXISTS workflow_definitions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            workflow_code VARCHAR(50) NOT NULL,
            workflow_name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            entity_type VARCHAR(50) NOT NULL COMMENT 'booking, commission, refund, etc.',
            is_active TINYINT(1) DEFAULT 1,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_entity (entity_type),
            INDEX idx_active (is_active),
            INDEX idx_tenant (tenant_id),
            UNIQUE KEY uk_code_tenant (workflow_code, tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->database->getConnection()->exec($sql);

        // Workflow steps
        $sql2 = "CREATE TABLE IF NOT EXISTS workflow_steps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            workflow_id INT NOT NULL,
            step_order INT NOT NULL,
            step_name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            approver_type ENUM('user', 'role', 'department', 'auto') NOT NULL,
            approver_id INT NULL,
            approver_role VARCHAR(50) NULL,
            is_parallel TINYINT(1) DEFAULT 0,
            can_reject TINYINT(1) DEFAULT 1,
            can_send_back TINYINT(1) DEFAULT 1,
            auto_approve_after_hours INT NULL,
            is_active TINYINT(1) DEFAULT 1,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_workflow (workflow_id),
            INDEX idx_order (step_order),
            INDEX idx_tenant (tenant_id),
            FOREIGN KEY (workflow_id) REFERENCES workflow_definitions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->database->getConnection()->exec($sql2);

        // Workflow instances
        $sql3 = "CREATE TABLE IF NOT EXISTS workflow_instances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            workflow_id INT NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            current_step INT DEFAULT 1,
            status ENUM('pending', 'in_progress', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
            requested_by INT NOT NULL,
            requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            notes TEXT NULL,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_entity_tenant (entity_type, entity_id, tenant_id),
            INDEX idx_workflow (workflow_id),
            INDEX idx_status (status),
            INDEX idx_requested_by (requested_by),
            INDEX idx_tenant (tenant_id),
            FOREIGN KEY (workflow_id) REFERENCES workflow_definitions(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->database->getConnection()->exec($sql3);

        // Seed default workflows
        $this->seedDefaultWorkflows();
    }

    /**
     * Seed default workflows
     */
    private function seedDefaultWorkflows(): void
    {
        // Booking Approval Workflow
        $workflowId = $this->createWorkflow('booking_approval', 'Booking Approval', 'booking',
            'Multi-level approval process for property bookings');

        if ($workflowId) {
            $this->addWorkflowStep($workflowId, 1, 'Sales Manager Review', 'user', null, 'sales_manager');
            $this->addWorkflowStep($workflowId, 2, 'Finance Verification', 'role', null, 'finance');
            $this->addWorkflowStep($workflowId, 3, 'Final Approval', 'role', null, 'admin');
        }

        // Commission Approval Workflow
        $workflowId2 = $this->createWorkflow('commission_approval', 'Commission Approval', 'commission',
            'Approval process for associate commission payouts');

        if ($workflowId2) {
            $this->addWorkflowStep($workflowId2, 1, 'Sales Verification', 'role', null, 'sales_manager');
            $this->addWorkflowStep($workflowId2, 2, 'Finance Approval', 'role', null, 'finance');
        }

        // Refund Approval Workflow
        $workflowId3 = $this->createWorkflow('refund_approval', 'Refund Approval', 'refund',
            'Approval process for booking cancellations and refunds');

        if ($workflowId3) {
            $this->addWorkflowStep($workflowId3, 1, 'Initial Review', 'role', null, 'customer_service');
            $this->addWorkflowStep($workflowId3, 2, 'Manager Approval', 'role', null, 'manager');
            $this->addWorkflowStep($workflowId3, 3, 'Finance Processing', 'role', null, 'finance');
        }
    }

    /**
     * Create workflow
     */
    public function createWorkflow(string $code, string $name, string $entityType,
                                    string $description = ''): ?int
    {
        try {
            $insertData = $this->tenantInsertData();
            $cols = "workflow_code, workflow_name, entity_type, description" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO workflow_definitions ($cols)
                    VALUES ($ph)
                    ON DUPLICATE KEY UPDATE
                    workflow_name = VALUES(workflow_name),
                    description = VALUES(description)";

            $stmt = $this->database->prepare($sql);
            $params = [$code, $name, $entityType, $description];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            $stmt->execute($params);

            return $this->database->lastInsertId() ?: ($this->getWorkflowByCode($code)['id'] ?? null);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Add workflow step
     */
    public function addWorkflowStep(int $workflowId, int $order, string $name,
                                    string $approverType, ?int $approverId = null,
                                    ?string $approverRole = null): bool
    {
        try {
            $insertData = $this->tenantInsertData();
            $cols = "workflow_id, step_order, step_name, approver_type, approver_id, approver_role" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO workflow_steps ($cols)
                    VALUES ($ph)";

            $stmt = $this->database->prepare($sql);
            $params = [$workflowId, $order, $name, $approverType, $approverId, $approverRole];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            $stmt->execute($params);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Start workflow instance
     */
    public function startWorkflow(string $workflowCode, string $entityType, int $entityId,
                                  int $requestedBy, string $notes = ''): array
    {
        try {
            $workflow = $this->getWorkflowByCode($workflowCode);

            if (!$workflow || !$workflow['is_active']) {
                return ['success' => false, 'message' => 'Workflow not found or inactive'];
            }

            // Check if already has active workflow
            $existing = $this->getActiveWorkflowInstance($entityType, $entityId);
            if ($existing) {
                return ['success' => false, 'message' => 'Workflow already in progress'];
            }

            $insertData = $this->tenantInsertData();
            $cols = "workflow_id, entity_type, entity_id, requested_by, notes" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
            $ph = "?, ?, ?, ?, ?" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
            $sql = "INSERT INTO workflow_instances ($cols)
                    VALUES ($ph)";

            $stmt = $this->database->prepare($sql);
            $params = [$workflow['id'], $entityType, $entityId, $requestedBy, $notes];
            if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
            $stmt->execute($params);

            $instanceId = $this->database->lastInsertId();

            // Log start
            $auditService = new AuditTrailService();
            $auditService->log('workflow_started', 'workflow_instance', $instanceId, [], [],
                "Workflow '{$workflow['workflow_name']}' started for {$entityType} #{$entityId}");

            return [
                'success' => true,
                'instance_id' => $instanceId,
                'workflow_id' => $workflow['id'],
                'current_step' => 1
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process workflow action
     */
    public function processAction(int $instanceId, string $action, int $userId,
                                    string $userType, string $comments = ''): array
    {
        try {
            $instance = $this->getInstance($instanceId);

            if (!$instance) {
                return ['success' => false, 'message' => 'Workflow instance not found'];
            }

            if ($instance['status'] !== 'pending' && $instance['status'] !== 'in_progress') {
                return ['success' => false, 'message' => 'Workflow already completed'];
            }

            // Get current step
            $step = $this->getCurrentStep($instance['workflow_id'], $instance['current_step']);

            if (!$step) {
                return ['success' => false, 'message' => 'Workflow step not found'];
            }

            // Validate action
            if ($action === 'reject' && !$step['can_reject']) {
                return ['success' => false, 'message' => 'Reject not allowed at this step'];
            }

            // Log action
            $auditService = new AuditTrailService();
            $auditService->log('workflow_action', 'workflow_instance', $instanceId, [], [],
                "Action: {$action}, by: {$userType} #{$userId}" . ($comments ? ', comments: ' . $comments : ''));

            // Process based on action
            switch ($action) {
                case 'approve':
                    return $this->processApproval($instance, $step);

                case 'reject':
                    return $this->processRejection($instance, $comments);

                case 'send_back':
                    return $this->processSendBack($instance, $comments);

                default:
                    return ['success' => true, 'message' => 'Action recorded'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process approval
     */
    private function processApproval(array $instance, array $step): array
    {
        // Get next step
        $nextStep = $this->getNextStep($instance['workflow_id'], $instance['current_step']);

        $tid = $this->tenantId();
        $tsql = $this->tenantSql();

        if ($nextStep) {
            $sql = "UPDATE workflow_instances
                    SET current_step = current_step + 1, status = 'in_progress'
                    WHERE id = ?" . $tsql;
            $params = [$instance['id']];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'action' => 'advanced',
                'current_step' => $instance['current_step'] + 1,
                'step_name' => $nextStep['step_name']
            ];
        } else {
            $sql = "UPDATE workflow_instances
                    SET status = 'approved', completed_at = NOW()
                    WHERE id = ?" . $tsql;
            $params = [$instance['id']];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);

            // Log completion
            $auditService = new AuditTrailService();
            $auditService->log('workflow_completed', 'workflow_instance', $instance['id'],
                ['status' => 'in_progress'], ['status' => 'approved'],
                'Workflow approved and completed');

            return [
                'success' => true,
                'action' => 'completed',
                'status' => 'approved'
            ];
        }
    }

    /**
     * Process rejection
     */
    private function processRejection(array $instance, string $comments): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "UPDATE workflow_instances
                SET status = 'rejected', completed_at = NOW()
                WHERE id = ?" . $tsql;
        $params = [$instance['id']];
        if ($tid > 1) $params[] = $tid;
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);

        return [
            'success' => true,
            'action' => 'completed',
            'status' => 'rejected'
        ];
    }

    /**
     * Process send back
     */
    private function processSendBack(array $instance, string $comments): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        if ($instance['current_step'] > 1) {
            $sql = "UPDATE workflow_instances
                    SET current_step = current_step - 1
                    WHERE id = ?" . $tsql;
            $params = [$instance['id']];
            if ($tid > 1) $params[] = $tid;
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'action' => 'sent_back',
                'current_step' => $instance['current_step'] - 1
            ];
        }

        return ['success' => false, 'message' => 'Cannot send back from first step'];
    }

    /**
     * Get workflow by code
     */
    public function getWorkflowByCode(string $code): ?array
    {
        try {
            $sql = "SELECT * FROM workflow_definitions WHERE workflow_code = ?" . $this->tenantSql();
            $params = [$code];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->database->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Get workflow steps
     */
    public function getWorkflowSteps(int $workflowId): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT * FROM workflow_steps
                WHERE workflow_id = ? AND is_active = 1" . $tsql . "
                ORDER BY step_order";

        $stmt = $this->database->prepare($sql);
        $params = [$workflowId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get active workflow instance
     */
    public function getActiveWorkflowInstance(string $entityType, int $entityId): ?array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT * FROM workflow_instances
                WHERE entity_type = ? AND entity_id = ?" . $tsql . "
                AND status IN ('pending', 'in_progress')";

        $stmt = $this->database->prepare($sql);
        $params = [$entityType, $entityId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get instance
     */
    public function getInstance(int $instanceId): ?array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT * FROM workflow_instances WHERE id = ?" . $tsql;
        $stmt = $this->database->prepare($sql);
        $params = [$instanceId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get current step
     */
    public function getCurrentStep(int $workflowId, int $stepOrder): ?array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT * FROM workflow_steps
                WHERE workflow_id = ? AND step_order = ? AND is_active = 1" . $tsql;

        $stmt = $this->database->prepare($sql);
        $params = [$workflowId, $stepOrder];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get next step
     */
    public function getNextStep(int $workflowId, int $currentOrder): ?array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT * FROM workflow_steps
                WHERE workflow_id = ? AND step_order > ? AND is_active = 1" . $tsql . "
                ORDER BY step_order LIMIT 1";

        $stmt = $this->database->prepare($sql);
        $params = [$workflowId, $currentOrder];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all workflows
     */
    public function getAllWorkflows(): array
    {
        try {
            $tid = $this->tenantId();
            $tsql = $this->tenantSql();
            $sql = "SELECT * FROM workflow_definitions WHERE is_active = 1" . $tsql;
            $stmt = $this->database->prepare($sql);
            $params = $tid > 1 ? [$tid] : [];
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Get pending approvals for user
     */
    public function getPendingForUser(int $userId, string $userRole): array
    {
        $tid = $this->tenantId();
        $tsql = $this->tenantSql();
        $sql = "SELECT wi.*, wd.workflow_name, ws.step_name, ws.approver_role
                FROM workflow_instances wi
                JOIN workflow_definitions wd ON wi.workflow_id = wd.id
                JOIN workflow_steps ws ON ws.workflow_id = wd.id AND ws.step_order = wi.current_step
                WHERE wi.status IN ('pending', 'in_progress')" . $tsql . "
                AND (ws.approver_id = ? OR ws.approver_role = ?)
                ORDER BY wi.requested_at DESC";

        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userRole];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
