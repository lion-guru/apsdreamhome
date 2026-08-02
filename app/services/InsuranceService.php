<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class InsuranceService
{
    use ServiceTenantTrait;
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
            return;
        }
        $this->pdo = $this->resolvePdo();
    }

    public function listPlans(?string $category = null, bool $featuredOnly = false): array
    {
        $sql = "SELECT * FROM insurance_plans WHERE is_active = 1";
        $params = [];
        if ($category) {
            $sql .= " AND plan_category = ?";
            $params[] = $category;
        }
        if ($featuredOnly) {
            $sql .= " AND is_featured = 1";
        }
        $sql .= " ORDER BY display_order, plan_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['features'] = $r['features'] ? json_decode($r['features'], true) : [];
        }
        return $rows;
    }

    public function getPlan(int $planId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM insurance_plans WHERE id = ? LIMIT 1");
        $stmt->execute([$planId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['features'] = $row['features'] ? json_decode($row['features'], true) : [];
        return $row;
    }

    public function getUserPolicies(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT p.*, pl.plan_name, pl.plan_category FROM insurance_policies p JOIN insurance_plans pl ON p.plan_id = pl.id WHERE p.user_id = ? {$this->tenantSqlForAlias('p')} ORDER BY p.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(int $userId): array
    {
        $rows = $this->pdo->prepare("SELECT pl.plan_category, COUNT(*) as cnt FROM insurance_policies p JOIN insurance_plans pl ON p.plan_id = pl.id WHERE p.user_id = ? AND p.status = 'active' {$this->tenantSqlForAlias('p')} GROUP BY pl.plan_category");
        $rows->execute([$userId]);
        $out = ['home' => 0, 'health' => 0, 'term_life' => 0, 'vehicle' => 0, 'travel' => 0, 'total' => 0];
        foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $cat = $r['plan_category'];
            $out[$cat] = (int)$r['cnt'];
            $out['total'] += (int)$r['cnt'];
        }
        return $out;
    }

    public function enrol(int $userId, int $planId, array $data): array
    {
        $plan = $this->getPlan($planId);
        if (!$plan) return ['success' => false, 'error' => 'Plan not found'];

        $policyNumber = 'APS-INS-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate . ' + 1 year'));
        $nomineeName = $data['nominee_name'] ?? null;
        $nomineeRelation = $data['nominee_relation'] ?? null;
        $sumInsured = (float)($data['sum_insured'] ?? $plan['coverage_amount']);
        $premium = (float)($plan['premium_yearly'] ?? 0);

        $tid = $this->tenantId();
        $stmt = $this->pdo->prepare("INSERT INTO insurance_policies (user_id, plan_id, policy_number, nominee_name, nominee_relation, sum_insured, premium_amount, start_date, end_date, status, payment_status" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending'" . ($tid > 1 ? ", ?" : "") . ")");
        $params = [$userId, $planId, $policyNumber, $nomineeName, $nomineeRelation, $sumInsured, $premium, $startDate, $endDate];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);
        return ['success' => true, 'policy_id' => $this->pdo->lastInsertId(), 'policy_number' => $policyNumber];
    }

    private function resolvePdo(): PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }
}
