<?php
namespace App\Services;

use PDO;

/**
 * CommissionService - multi-tier commission engine (agent, hybrid, farmer, MLM rank)
 */
class CommissionService
{
    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function getAgentRate(int $agentId, string $tier = ''): ?array
    {
        $sql = "SELECT * FROM agent_commission_rates WHERE agent_id = :a";
        $params = [':a' => $agentId];
        if ($tier) { $sql .= " AND tier = :t"; $params[':t'] = $tier; }
        $sql .= " ORDER BY effective_from DESC LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function setAgentRate(int $agentId, string $tier, float $rate, string $effectiveFrom = null): array
    {
        $eff = $effectiveFrom ?: date('Y-m-d');
        $st = $this->db->prepare("INSERT INTO agent_commission_rates (agent_id, tier, commission_rate, effective_from, created_at) VALUES (:a, :t, :r, :e, NOW())
                                  ON DUPLICATE KEY UPDATE commission_rate = VALUES(commission_rate), effective_from = VALUES(effective_from)");
        $st->execute([':a' => $agentId, ':t' => $tier, ':r' => $rate, ':e' => $eff]);
        return ['ok' => true];
    }

    public function calculateAgentCommission(int $agentId, float $saleAmount, string $tier = 'standard'): float
    {
        $rate = $this->getAgentRate($agentId, $tier);
        if (!$rate) {
            $st = $this->db->prepare("SELECT commission_rate FROM agent_commission_rates WHERE tier = :t ORDER BY effective_from DESC LIMIT 1");
            $st->execute([':t' => $tier]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            $pct = (float)($r['commission_rate'] ?? 2.0);
        } else {
            $pct = (float)$rate['commission_rate'];
        }
        return round($saleAmount * $pct / 100, 2);
    }

    public function recordAgentCommission(int $agentId, int $bookingId, float $saleAmount, string $tier = 'standard'): array
    {
        $amt = $this->calculateAgentCommission($agentId, $saleAmount, $tier);
        $st = $this->db->prepare("INSERT INTO hybrid_commission_records (agent_id, booking_id, sale_amount, commission_rate, commission_amount, tier, status, created_at) VALUES (:a, :b, :s, NULL, :c, :t, 'pending', NOW())");
        $st->execute([':a' => $agentId, ':b' => $bookingId, ':s' => $saleAmount, ':c' => $amt, ':t' => $tier]);
        return ['ok' => true, 'amount' => $amt, 'id' => (int)$this->db->lastInsertId()];
    }

    public function createHybridPlan(int $agentId, float $fixedAmount, float $variableRate, float $threshold, string $validFrom, ?string $validTo = null): array
    {
        $st = $this->db->prepare("INSERT INTO hybrid_commission_plans (agent_id, fixed_amount, variable_rate, sales_threshold, valid_from, valid_to, status, created_at) VALUES (:a, :f, :v, :t, :frm, :to, 'active', NOW())");
        $st->execute([':a' => $agentId, ':f' => $fixedAmount, ':v' => $variableRate, ':t' => $threshold, ':frm' => $validFrom, ':to' => $validTo]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getActiveHybridPlan(int $agentId): ?array
    {
        $st = $this->db->prepare("SELECT * FROM hybrid_commission_plans WHERE agent_id = :a AND status = 'active' AND valid_from <= CURDATE() AND (valid_to IS NULL OR valid_to >= CURDATE()) ORDER BY valid_from DESC LIMIT 1");
        $st->execute([':a' => $agentId]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function calculateHybridCommission(int $agentId, float $saleAmount, float $totalSalesThisPeriod): float
    {
        $plan = $this->getActiveHybridPlan($agentId);
        if (!$plan) return $this->calculateAgentCommission($agentId, $saleAmount);
        $variable = $saleAmount * (float)$plan['variable_rate'] / 100;
        $bonus = $totalSalesThisPeriod >= (float)$plan['sales_threshold'] ? (float)$plan['fixed_amount'] : 0;
        return round((float)$plan['fixed_amount'] + $variable + $bonus, 2);
    }

    public function setFarmerStructure(string $tier, float $baseRate, float $bonusRate, float $minSales): array
    {
        $st = $this->db->prepare("INSERT INTO farmer_commission_structures (tier, base_rate, bonus_rate, min_sales, active, created_at) VALUES (:t, :b, :bo, :m, 1, NOW())
                                  ON DUPLICATE KEY UPDATE base_rate = VALUES(base_rate), bonus_rate = VALUES(bonus_rate), min_sales = VALUES(min_sales)");
        $st->execute([':t' => $tier, ':b' => $baseRate, ':bo' => $bonusRate, ':m' => $minSales]);
        return ['ok' => true];
    }

    public function getFarmerStructures(): array
    {
        try {
            $st = $this->db->query("SELECT id, structure_name AS tier, commission_type, base_rate_pct AS base_rate, tier_rules, is_active, base_rate_pct AS bonus_rate, 0 AS min_sales FROM farmer_commission_structures WHERE is_active = 1 ORDER BY base_rate_pct");
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getHybridPlans(int $limit = 50): array
    {
        try {
            $st = $this->db->prepare("SELECT id, plan_name, '—' AS agent_id, 0 AS fixed_amount, 0 AS variable_rate, 0 AS sales_threshold, effective_from AS valid_from, effective_to AS valid_to, plan_type, level_rates, override_levels, performance_tiers, IF(is_active = 1, 'active', 'inactive') AS status, effective_from, is_active FROM hybrid_commission_plans ORDER BY effective_from DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function recordFarmerCommission(int $farmerId, int $referralId, float $saleAmount, string $tier = 'tier1'): array
    {
        $st = $this->db->prepare("SELECT * FROM farmer_commission_structures WHERE tier = :t");
        $st->execute([':t' => $tier]);
        $struct = $st->fetch(PDO::FETCH_ASSOC);
        if (!$struct) return ['error' => 'Tier not found'];

        $base = $saleAmount * (float)$struct['base_rate'] / 100;
        $bonus = (float)$struct['bonus_rate'];
        $total = $base + $bonus;

        $st2 = $this->db->prepare("INSERT INTO farmer_commissions (farmer_id, referral_id, sale_amount, tier, base_commission, bonus_amount, total_commission, status, created_at) VALUES (:f, :r, :s, :t, :b, :bo, :tot, 'pending', NOW())");
        $st2->execute([':f' => $farmerId, ':r' => $referralId, ':s' => $saleAmount, ':t' => $tier, ':b' => $base, ':bo' => $bonus, ':tot' => $total]);
        return ['ok' => true, 'amount' => $total, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getMlmRankRates(string $rank = ''): array
    {
        try {
            $sql = "SELECT id, rank_name AS rank, rank_name, rank_level, min_qualification_volume, min_downline_count AS min_downline, commission_multiplier AS commission_pct, bonus_amount, '' AS perks FROM mlm_rank_rates WHERE 1=1";
            $params = [];
            if ($rank) { $sql .= " AND rank_name = :r"; $params[':r'] = $rank; }
            $sql .= " ORDER BY rank_level DESC";
            $st = $this->pdo->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setMlmRank(string $rank, int $minDownline, float $commissionPct, float $bonusAmount, array $perks = []): array
    {
        try {
            $st = $this->pdo->prepare("INSERT INTO mlm_rank_rates (rank_name, rank_level, min_downline_count, commission_multiplier, bonus_amount) VALUES (:r, :l, :m, :c, :b)");
            $st->execute([':r' => $rank, ':l' => $minDownline, ':m' => $minDownline, ':c' => $commissionPct / 10, ':b' => $bonusAmount]);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getRules(string $ruleType = ''): array
    {
        try {
            $sql = "SELECT id, rule_type, rule_name, formula, conditions, priority, is_active, formula AS output_amount FROM commission_calculation_rules WHERE is_active = 1";
            $params = [];
            if ($ruleType) { $sql .= " AND rule_type = :r"; $params[':r'] = $ruleType; }
            $sql .= " ORDER BY priority";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function addRule(string $type, string $name, array $conditions, float $amount, int $priority = 100): array
    {
        $st = $this->db->prepare("INSERT INTO commission_calculation_rules (rule_type, rule_name, conditions, output_amount, priority, active, created_at) VALUES (:t, :n, :c, :a, :p, 1, NOW())");
        $st->execute([':t' => $type, ':n' => $name, ':c' => json_encode($conditions, JSON_UNESCAPED_UNICODE), ':a' => $amount, ':p' => $priority]);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getAgentCommissions(int $agentId, string $status = ''): array
    {
        $sql = "SELECT h.*, u.name as agent_name, b.booking_code FROM hybrid_commission_records h LEFT JOIN users u ON h.agent_id = u.id LEFT JOIN bookings b ON h.booking_id = b.id WHERE h.agent_id = :a";
        $params = [':a' => $agentId];
        if ($status) { $sql .= " AND h.status = :s"; $params[':s'] = $status; }
        $sql .= " ORDER BY h.created_at DESC LIMIT 100";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveCommission(int $id, int $approverId): array
    {
        $st = $this->db->prepare("UPDATE hybrid_commission_records SET status = 'approved', approved_by = :a, approved_at = NOW() WHERE id = :id");
        $st->execute([':a' => $approverId, ':id' => $id]);
        return ['ok' => true];
    }
}
