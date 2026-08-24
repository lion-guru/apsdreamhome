<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Agent Ledger Service
 * Handles agent GBV, upline chain, commission reversal, and ledger queries
 */
class AgentLedgerService
{
    use ServiceTenantTrait;

    private \PDO $pdo;
    private \App\Services\MLM\CommissionLedgerService $ledgerService;
    private \App\Services\MLM\RankService $rankService;

    public function __construct(
        ?\PDO $pdo = null,
        ?\App\Services\MLM\CommissionLedgerService $ledgerService = null,
        ?\App\Services\MLM\RankService $rankService = null
    ) {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getConnection();
        $this->ledgerService = $ledgerService ?? new \App\Services\MLM\CommissionLedgerService();
        $this->rankService = $rankService ?? new \App\Services\MLM\RankService();
    }

    /**
     * Get agent's cumulative Group Business Volume (GBV)
     */
    public function getGbv(int $agentId): float
    {
        try {
            $stmt = $this->pdo->prepare("SELECT lifetime_sales FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$agentId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (float) $row['lifetime_sales'] : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Increment agent's lifetime_sales after a payment
     */
    public function incrementGbv(int $agentId, float $amount): void
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE mlm_profiles
                SET lifetime_sales = lifetime_sales + ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$amount, $agentId]);
        } catch (\Throwable $e) {
            error_log("[AgentLedgerService] incrementGbv: " . $e->getMessage());
        }
    }

    /**
     * Get upline chain from mlm_network_tree
     */
    public function getUplineChain(int $agentId, int $maxLevels = 7): array
    {
        $upline = [];
        $current = $agentId;
        $level = 0;

        while ($level < $maxLevels) {
            $stmt = $this->pdo->prepare("
                SELECT parent_id, level FROM mlm_network_tree
                WHERE associate_id = ? LIMIT 1
            ");
            $stmt->execute([$current]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || !$row['parent_id']) {
                break;
            }

            $level++;
            $parentId = (int) $row['parent_id'];
            $rank = $this->rankService->resolveRank($parentId);

            $upline[] = [
                'user_id' => $parentId,
                'rank'    => $rank,
                'level'   => $level,
            ];

            $current = $parentId;
        }

        return $upline;
    }

    /**
     * Get agent's commission ledger entries
     */
    public function getAgentLedger(int $agentId, ?string $from = null, ?string $to = null): array
    {
        try {
            $sql = "
                SELECT * FROM mlm_commission_ledger
                WHERE beneficiary_user_id = ?
                AND tenant_id = ?
            ";
            $params = [$agentId, $this->getTenantId()];

            if ($from) {
                $sql .= " AND created_at >= ?";
                $params[] = $from;
            }
            if ($to) {
                $sql .= " AND created_at <= ?";
                $params[] = $to;
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("[AgentLedgerService] getAgentLedger error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get agent status (cached per request)
     */
    private array $agentStatusCache = [];

    public function getAgentStatus(int $userId): string
    {
        if (isset($this->agentStatusCache[$userId])) {
            return $this->agentStatusCache[$userId];
        }
        $stmt = $this->pdo->prepare("SELECT status FROM associates WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $status = $stmt->fetchColumn();
        $this->agentStatusCache[$userId] = $status ?: 'inactive';
        return $this->agentStatusCache[$userId];
    }

    /**
     * Verify upline meets monthly side-volume requirement
     */
    public function verifyUplineSideVolume(int $uplineUserId, string $month): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS side_volume
                FROM plot_bookings pb
                JOIN plots p ON p.id = pb.plot_id
                WHERE pb.associate_id = ? 
                AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                AND pb.status IN ('confirmed', 'completed')
            ");
            $stmt->execute([$uplineUserId, $month]);
            $sideVolume = (float) $stmt->fetchColumn();
            return $sideVolume >= 50000;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Count consecutive qualifying months for Track B
     */
    public function countConsecutiveQualifyingMonths(int $agentId): int
    {
        $consecutive = 0;
        $currentMonth = date('Y-m');

        if ($this->isQualifyingMonth($agentId, $currentMonth)) {
            $consecutive++;
            $checkMonth = date('Y-m', strtotime('-1 month', strtotime($currentMonth . '-01')));
            while ($this->isQualifyingMonth($agentId, $checkMonth)) {
                $consecutive++;
                $checkMonth = date('Y-m', strtotime('-1 month', strtotime($checkMonth . '-01')));
            }
        }

        return $consecutive;
    }

    private function isQualifyingMonth(int $agentId, string $month): bool
    {
        try {
            $downlineIds = $this->getDownlineUserIds($agentId);
            $downlineIds[] = $agentId;
            $placeholders = implode(',', array_fill(0, count($downlineIds), '?'));

            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS total_volume
                FROM plot_bookings pb
                WHERE pb.associate_id IN ($placeholders)
                AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                AND pb.status IN ('confirmed', 'completed')
            ");
            $params = array_merge($downlineIds, [$month]);
            $stmt->execute($params);
            $totalVolume = (float) $stmt->fetchColumn();

            return $totalVolume >= 50000;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function getDownlineUserIds(int $agentId): array
    {
        $ids = [];
        $queue = [$agentId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $stmt = $this->pdo->prepare("
                SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?
            ");
            $stmt->execute([$current]);
            $children = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($children as $childId) {
                $ids[] = (int) $childId;
                $queue[] = (int) $childId;
            }
        }

        return $ids;
    }

    protected function getTenantId(): int
    {
        try {
            return \App\Core\Middleware\TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }
}