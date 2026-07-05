<?php
namespace App\Services\CRM;

use App\Core\Database\Database;
use Exception;

class LeadAssignmentService
{
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

        $unassigned = $this->pdo->prepare(
            "SELECT id FROM leads WHERE assigned_to IS NULL AND status='new' ORDER BY created_at ASC LIMIT ?"
        );
        $unassigned->execute([$batchSize]);
        $leads = $unassigned->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($leads)) {
            return ['assigned' => 0, 'message' => 'No unassigned leads'];
        }

        $assignments = [];
        $idx = 0;
        $stmt = $this->pdo->prepare("UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?");

        $this->pdo->beginTransaction();
        try {
            foreach ($leads as $leadId) {
                $handler = $handlers[$idx % count($handlers)];
                $stmt->execute([$handler['id'], $leadId]);
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
        $sql = "SELECT u.id, u.name, u.role,
                       (SELECT COUNT(*) FROM leads WHERE assigned_to = u.id AND status = 'new') as workload
                FROM users u
                WHERE u.role IN ($roles) AND u.status = 'active'
                ORDER BY workload ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get lead count for dashboard stats
     */
    public function getStats(): array
    {
        return [
            'total' => $this->pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
            'new' => $this->pdo->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn(),
            'unassigned' => $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NULL")->fetchColumn(),
            'assigned' => $this->pdo->query("SELECT COUNT(*) FROM leads WHERE assigned_to IS NOT NULL")->fetchColumn(),
            'handlers' => count($this->getAvailableHandlers()),
        ];
    }
}
