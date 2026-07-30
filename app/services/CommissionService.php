<?php
namespace App\Services;

use PDO;

/**
 * CommissionService - multi-tier commission engine (agent, hybrid, farmer, MLM rank)
 */
class CommissionService
{
    use \App\Traits\ServiceTenantTrait;

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
        $acrCols = "agent_id, tier, commission_rate, effective_from, created_at";
        $acrVals = ":a, :t, :r, :e, NOW()";
        $acrParams = [':a' => $agentId, ':t' => $tier, ':r' => $rate, ':e' => $eff];
        if ($this->tenantId() > 1) {
            $acrCols .= ", tenant_id";
            $acrVals .= ", :stid";
            $acrParams[':stid'] = $this->tenantId();
        }
        $st = $this->db->prepare("INSERT INTO agent_commission_rates ({$acrCols}) VALUES ({$acrVals})
                                  ON DUPLICATE KEY UPDATE commission_rate = VALUES(commission_rate), effective_from = VALUES(effective_from)");
        $st->execute($acrParams);
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
        $hcrCols = "agent_id, booking_id, sale_amount, commission_rate, commission_amount, tier, status, created_at";
        $hcrVals = ":a, :b, :s, NULL, :c, :t, 'pending', NOW()";
        $hcrParams = [':a' => $agentId, ':b' => $bookingId, ':s' => $saleAmount, ':c' => $amt, ':t' => $tier];
        if ($this->tenantId() > 1) {
            $hcrCols .= ", tenant_id";
            $hcrVals .= ", :stid";
            $hcrParams[':stid'] = $this->tenantId();
        }
        $st = $this->db->prepare("INSERT INTO hybrid_commission_records ({$hcrCols}) VALUES ({$hcrVals})");
        $st->execute($hcrParams);
        return ['ok' => true, 'amount' => $amt, 'id' => (int)$this->db->lastInsertId()];
    }

    public function createHybridPlan(int $agentId, float $fixedAmount, float $variableRate, float $threshold, string $validFrom, ?string $validTo = null): array
    {
        $hcpCols = "agent_id, fixed_amount, variable_rate, sales_threshold, valid_from, valid_to, status, created_at";
        $hcpVals = ":a, :f, :v, :t, :frm, :to, 'active', NOW()";
        $hcpParams = [':a' => $agentId, ':f' => $fixedAmount, ':v' => $variableRate, ':t' => $threshold, ':frm' => $validFrom, ':to' => $validTo];
        if ($this->tenantId() > 1) {
            $hcpCols .= ", tenant_id";
            $hcpVals .= ", :stid";
            $hcpParams[':stid'] = $this->tenantId();
        }
        $st = $this->db->prepare("INSERT INTO hybrid_commission_plans ({$hcpCols}) VALUES ({$hcpVals})");
        $st->execute($hcpParams);
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
        $fcsCols = "tier, base_rate, bonus_rate, min_sales, active, created_at";
        $fcsVals = ":t, :b, :bo, :m, 1, NOW()";
        $fcsParams = [':t' => $tier, ':b' => $baseRate, ':bo' => $bonusRate, ':m' => $minSales];
        if ($this->tenantId() > 1) {
            $fcsCols .= ", tenant_id";
            $fcsVals .= ", :stid";
            $fcsParams[':stid'] = $this->tenantId();
        }
        $st = $this->db->prepare("INSERT INTO farmer_commission_structures ({$fcsCols}) VALUES ({$fcsVals})
                                  ON DUPLICATE KEY UPDATE base_rate = VALUES(base_rate), bonus_rate = VALUES(bonus_rate), min_sales = VALUES(min_sales)");
        $st->execute($fcsParams);
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

        $fcCols = "farmer_id, referral_id, sale_amount, tier, base_commission, bonus_amount, total_commission, status, created_at";
        $fcVals = ":f, :r, :s, :t, :b, :bo, :tot, 'pending', NOW()";
        $fcParams = [':f' => $farmerId, ':r' => $referralId, ':s' => $saleAmount, ':t' => $tier, ':b' => $base, ':bo' => $bonus, ':tot' => $total];
        if ($this->tenantId() > 1) {
            $fcCols .= ", tenant_id";
            $fcVals .= ", :stid";
            $fcParams[':stid'] = $this->tenantId();
        }
        $st2 = $this->db->prepare("INSERT INTO farmer_commissions ({$fcCols}) VALUES ({$fcVals})");
        $st2->execute($fcParams);
        return ['ok' => true, 'amount' => $total, 'id' => (int)$this->db->lastInsertId()];
    }

    /**
     * Get MLM rank commission rates.
     * Delegates to MLMCommissionEngine::getCanonicalRates() for the canonical source.
     * If no rank is specified, returns all ranks from mlm_rank_benefits DB table.
     */
    public function getMlmRankRates(string $rank = ''): array
    {
        // Single-rank query: use the canonical helper
        if ($rank) {
            $rates = \App\Services\MLM\MLMCommissionEngine::getCanonicalRates($rank);
            return [$rank => $rates];
        }
        // All ranks: query the DB table directly (canonical source)
        try {
            $sql = "SELECT id, rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct, perks, color_code, badge_icon FROM mlm_rank_benefits WHERE is_active = 1";
            $sql .= " ORDER BY rank_order ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setMlmRank(string $rank, int $minDownline, float $commissionPct, float $bonusAmount, array $perks = []): array
    {
        try {
            $st = $this->pdo->prepare("INSERT INTO mlm_rank_benefits (rank_name, rank_order, min_leg_count, min_qualifying_volume, is_active) VALUES (:r, :l, :m, :c, 1)
                                      ON DUPLICATE KEY UPDATE min_leg_count = VALUES(min_leg_count), min_qualifying_volume = VALUES(min_qualifying_volume)");
            $st->execute([':r' => $rank, ':l' => $minDownline, ':m' => $minDownline, ':c' => $bonusAmount]);
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
        $ccrCols = "rule_type, rule_name, conditions, output_amount, priority, active, created_at";
        $ccrVals = ":t, :n, :c, :a, :p, 1, NOW()";
        $ccrParams = [':t' => $type, ':n' => $name, ':c' => json_encode($conditions, JSON_UNESCAPED_UNICODE), ':a' => $amount, ':p' => $priority];
        if ($this->tenantId() > 1) {
            $ccrCols .= ", tenant_id";
            $ccrVals .= ", :stid";
            $ccrParams[':stid'] = $this->tenantId();
        }
        $st = $this->db->prepare("INSERT INTO commission_calculation_rules ({$ccrCols}) VALUES ({$ccrVals})");
        $st->execute($ccrParams);
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
        $hcruSql = "UPDATE hybrid_commission_records SET status = 'approved', approved_by = :a, approved_at = NOW() WHERE id = :id";
        $hcruSql .= $this->tenantSql();
        $hcruParams = [':a' => $approverId, ':id' => $id];
        if ($this->tenantId() > 1) $hcruParams[':stid'] = $this->tenantId();
        $st = $this->db->prepare($hcruSql);
        $st->execute($hcruParams);
        return ['ok' => true];
    }
}
