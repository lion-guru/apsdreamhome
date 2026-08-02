<?php
namespace App\Services\CRM;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use \App\Traits\ServiceTenantTrait;

class LeadAssignmentService
{
    use \App\Traits\ServiceTenantTrait;

    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Auto-assign unassigned leads to available handlers round-robin
     */
    public function autoAssign(int $batchSize = 50): array
    {
        $handlers = $this->getAvailableHandlers();
        if (empty($handlers)) {
            return ['assigned' => 0, 'message' => 'No available handlers found'];
        }

        $tid = TenantContext::getId();
        $unassigned = $this->pdo->prepare(
            "SELECT id FROM leads WHERE assigned_to IS NULL AND status='new'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at ASC LIMIT ?"
        );
        $params = $tid > 1 ? [$tid, $batchSize] : [$batchSize];
        $unassigned->execute($params);
        $leads = $unassigned->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($leads)) {
            return ['assigned' => 0, 'message' => 'No unassigned leads'];
        }

        $assignments = [];
        $idx = 0;
        $stmt = $this->pdo->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));

        $this->pdo->beginTransaction();
        try {
            foreach ($leads as $leadId) {
                $handler = $handlers[$idx % count($handlers)];
                $updParams = $tid > 1 ? [$handler['id'], $leadId, $tid] : [$handler['id'], $leadId];
                $stmt->execute($updParams);
                $assignments[] = ['lead_id' => $leadId, 'assigned_to' => $handler['id'], 'handler_name' => $handler['name']];
                $idx++;
            }
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['assigned' => 0, 'error' => $e->getMessage()];
        }

        return [
            'assigned' => count($assignments),
            'handlers_used' => count($handlers),
            'assignments' => $assignments,
        ];
    }

    /**
     * Get available handlers sorted by current workload (least-loaded first)
     */
    public function getAvailableHandlers(): array
    {
        $roles = "'employee','telecaller','agent','sales'";
        $tid = TenantContext::getId();
        $subSql = "SELECT COUNT(*) FROM leads WHERE assigned_to = u.id AND status = 'new'";
        if ($tid > 1) { $subSql .= " AND tenant_id = ?"; }
        $sql = "SELECT u.id, u.name, u.role,
                       ($subSql) as workload
                FROM users u
                WHERE u.role IN ($roles) AND u.status = 'active'" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                ORDER BY workload ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tid > 1 ? [$tid, $tid] : []);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get lead count for dashboard stats
     */
    public function getStats(): array
    {
        $tid = TenantContext::getId();
        $total = $new = $unassigned = $assigned = 0;

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads" . ($tid > 1 ? " WHERE tenant_id = ?" : ""));
        $stmt->execute($tid > 1 ? [$tid] : []);
        $total = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE status='new'" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $stmt->execute($tid > 1 ? [$tid] : []);
        $new = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $stmt->execute($tid > 1 ? [$tid] : []);
        $unassigned = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to IS NOT NULL" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $stmt->execute($tid > 1 ? [$tid] : []);
        $assigned = (int)$stmt->fetchColumn();

        return [
            'total' => $total,
            'new' => $new,
            'unassigned' => $unassigned,
            'assigned' => $assigned,
            'handlers' => count($this->getAvailableHandlers()),
        ];
    }
}
