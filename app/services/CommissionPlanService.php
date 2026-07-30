<?php

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

/**
 * Commission Plan Service — Versioned Plan Management
 * ────────────────────────────────────────────────────
 * CRUD for versioned commission plans with audit trail.
 * Plans track rank-based commission rates across versions.
 *
 * Tables: mlm_commission_plans, mlm_plan_levels, commission_plan_audit
 */
class CommissionPlanService
{
    use ServiceTenantTrait;

    /** @var PDO */
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
        }
    }

    /* ================================================================
       CRUD — PLANS
       ================================================================ */

    /**
     * Get all plans with level count and total commission percentage.
     */
    public function getAllPlans(): array
    {
        return $this->pdo->query("
            SELECT p.*,
                (SELECT COUNT(*) FROM mlm_plan_levels WHERE plan_id = p.id) as level_count,
                (SELECT COALESCE(SUM(direct_commission + team_commission + level_bonus + matching_bonus + leadership_bonus + performance_bonus), 0)
                 FROM mlm_plan_levels WHERE plan_id = p.id) as total_commission_pct
            FROM mlm_commission_plans p
            ORDER BY p.status = 'active' DESC, p.version DESC, p.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single plan by ID with its levels.
     */
    public function getPlanById(int $id): ?array
    {
        $plan = $this->pdo->prepare("SELECT * FROM mlm_commission_plans WHERE id = ?");
        $plan->execute([$id]);
        $plan = $plan->fetch(PDO::FETCH_ASSOC);
        if (!$plan) return null;

        $plan['levels'] = $this->getLevelsForPlan($id);
        return $plan;
    }

    /**
     * Get the currently active plan (latest version).
     */
    public function getActivePlan(): ?array
    {
        $plan = $this->pdo->query("
            SELECT * FROM mlm_commission_plans WHERE status = 'active'
            ORDER BY version DESC LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        if (!$plan) return null;

        $plan['levels'] = $this->getLevelsForPlan((int)$plan['id']);
        return $plan;
    }

    /**
     * Get all versions of a plan by plan_code.
     */
    public function getPlanVersions(string $planCode): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*,
                (SELECT COUNT(*) FROM mlm_plan_levels WHERE plan_id = p.id) as level_count
            FROM mlm_commission_plans p
            WHERE p.plan_code = ?
            ORDER BY p.version DESC
        ");
        $stmt->execute([$planCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new commission plan with default levels.
     */
    public function createPlan(array $data, int $createdBy): int
    {
        $this->pdo->beginTransaction();
        try {
            $planCode = strtoupper(trim($data['plan_code']));

            // Check code uniqueness
            $existing = $this->pdo->prepare("SELECT id FROM mlm_commission_plans WHERE plan_code = ?");
            $existing->execute([$planCode]);
            if ($existing->fetch()) {
                throw new Exception("Plan code '{$planCode}' already exists");
            }

            // Determine next version
            $version = 1;

            $planId = $this->insertPlan([
                'plan_name'              => trim($data['plan_name']),
                'plan_code'              => $planCode,
                'description'            => trim($data['description'] ?? ''),
                'plan_type'              => $data['plan_type'] ?? 'hybrid',
                'status'                 => 'draft',
                'version'                => $version,
                'effective_date'         => $data['effective_date'] ?? null,
                'created_by'             => $createdBy,
                'global_cap_pct'         => (float)($data['global_cap_pct'] ?? 20),
                'track_a_pct'            => (float)($data['track_a_pct'] ?? 15),
                'track_b_pct'            => (float)($data['track_b_pct'] ?? 3),
                'track_c_pct'            => (float)($data['track_c_pct'] ?? 2),
                'royalty_pool_pct'       => (float)($data['royalty_pool_pct'] ?? 2),
                'same_level_override_gen1' => (float)($data['same_level_override_gen1'] ?? 2),
                'same_level_override_gen2' => (float)($data['same_level_override_gen2'] ?? 1),
            ]);

            // Create default rank levels
            $this->createDefaultLevels($planId, $data);

            // Audit
            $this->logAudit($planId, $data['plan_name'], $planCode, $version, 'create', null, $data, $createdBy);

            $this->pdo->commit();
            return $planId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing plan (only draft/inactive plans).
     */
    public function updatePlan(int $id, array $data, int $updatedBy): bool
    {
        $this->pdo->beginTransaction();
        try {
            $plan = $this->getPlanById($id);
            if (!$plan) throw new Exception("Plan not found");
            if ($plan['status'] === 'active') throw new Exception("Cannot edit an active plan. Deactivate it first.");

            $oldData = $plan;

            $this->pdo->prepare("
                UPDATE mlm_commission_plans SET
                    plan_name = ?,
                    description = ?,
                    plan_type = ?,
                    effective_date = ?,
                    expiry_date = ?,
                    global_cap_pct = ?,
                    track_a_pct = ?,
                    track_b_pct = ?,
                    track_c_pct = ?,
                    royalty_pool_pct = ?,
                    same_level_override_gen1 = ?,
                    same_level_override_gen2 = ?,
                    updated_by = ?,
                    updated_at = NOW()
                WHERE id = ?
            ")->execute([
                trim($data['plan_name'] ?? $plan['plan_name']),
                trim($data['description'] ?? $plan['description']),
                $data['plan_type'] ?? $plan['plan_type'],
                $data['effective_date'] ?? $plan['effective_date'],
                $data['expiry_date'] ?? $plan['expiry_date'],
                (float)($data['global_cap_pct'] ?? $plan['global_cap_pct']),
                (float)($data['track_a_pct'] ?? $plan['track_a_pct']),
                (float)($data['track_b_pct'] ?? $plan['track_b_pct']),
                (float)($data['track_c_pct'] ?? $plan['track_c_pct']),
                (float)($data['royalty_pool_pct'] ?? $plan['royalty_pool_pct']),
                (float)($data['same_level_override_gen1'] ?? $plan['same_level_override_gen1']),
                (float)($data['same_level_override_gen2'] ?? $plan['same_level_override_gen2']),
                $updatedBy,
                $id,
            ]);

            // Update level percentages if provided
            if (!empty($data['levels']) && is_array($data['levels'])) {
                foreach ($data['levels'] as $levelId => $levelData) {
                    $this->pdo->prepare("
                        UPDATE mlm_plan_levels SET
                            direct_commission = ?,
                            team_commission = ?,
                            level_bonus = ?,
                            matching_bonus = ?,
                            leadership_bonus = ?,
                            performance_bonus = ?,
                            monthly_target = ?
                        WHERE id = ? AND plan_id = ?
                    ")->execute([
                        (float)($levelData['direct_commission'] ?? 0),
                        (float)($levelData['team_commission'] ?? 0),
                        (float)($levelData['level_bonus'] ?? 0),
                        (float)($levelData['matching_bonus'] ?? 0),
                        (float)($levelData['leadership_bonus'] ?? 0),
                        (float)($levelData['performance_bonus'] ?? 0),
                        (float)($levelData['monthly_target'] ?? 0),
                        $levelId,
                        $id,
                    ]);
                }
            }

            // Compute changed fields
            $changed = $this->computeChanges($oldData, $data);
            $this->logAudit($id, $plan['plan_name'], $plan['plan_code'], $plan['version'], 'update', $oldData, $data, $updatedBy, $changed);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Clone a plan as a new version (increment version number).
     */
    public function clonePlanAsNewVersion(int $sourcePlanId, array $overrides, int $createdBy): int
    {
        $this->pdo->beginTransaction();
        try {
            $source = $this->getPlanById($sourcePlanId);
            if (!$source) throw new Exception("Source plan not found");

            // Get next version for this plan_code
            $maxVer = $this->pdo->prepare("SELECT COALESCE(MAX(version), 0) FROM mlm_commission_plans WHERE plan_code = ?");
            $maxVer->execute([$source['plan_code']]);
            $nextVersion = (int)$maxVer->fetchColumn() + 1;

            $newData = array_merge([
                'plan_name'              => $source['plan_name'] . " v{$nextVersion}",
                'plan_code'              => $source['plan_code'],
                'description'            => $source['description'],
                'plan_type'              => $source['plan_type'],
                'effective_date'         => $overrides['effective_date'] ?? date('Y-m-d'),
                'global_cap_pct'         => $source['global_cap_pct'],
                'track_a_pct'            => $source['track_a_pct'],
                'track_b_pct'            => $source['track_b_pct'],
                'track_c_pct'            => $source['track_c_pct'],
                'royalty_pool_pct'       => $source['royalty_pool_pct'],
                'same_level_override_gen1' => $source['same_level_override_gen1'],
                'same_level_override_gen2' => $source['same_level_override_gen2'],
            ], $overrides);

            $newPlanId = $this->insertPlan([
                'plan_name'              => $newData['plan_name'],
                'plan_code'              => $newData['plan_code'],
                'description'            => $newData['description'],
                'plan_type'              => $newData['plan_type'],
                'status'                 => 'draft',
                'version'                => $nextVersion,
                'effective_date'         => $newData['effective_date'],
                'created_by'             => $createdBy,
                'global_cap_pct'         => $newData['global_cap_pct'],
                'track_a_pct'            => $newData['track_a_pct'],
                'track_b_pct'            => $newData['track_b_pct'],
                'track_c_pct'            => $newData['track_c_pct'],
                'royalty_pool_pct'       => $newData['royalty_pool_pct'],
                'same_level_override_gen1' => $newData['same_level_override_gen1'],
                'same_level_override_gen2' => $newData['same_level_override_gen2'],
            ]);

            // Copy levels from source (with optional overrides)
            foreach ($source['levels'] as $level) {
                $levelOverrides = $overrides['levels'][$level['level_order']] ?? [];
                $this->pdo->prepare("
                    INSERT INTO mlm_plan_levels (plan_id, level_name, level_order, direct_commission, team_commission,
                        level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([
                    $newPlanId,
                    $levelOverrides['level_name'] ?? $level['level_name'],
                    $level['level_order'],
                    $levelOverrides['direct_commission'] ?? $level['direct_commission'],
                    $levelOverrides['team_commission'] ?? $level['team_commission'],
                    $levelOverrides['level_bonus'] ?? $level['level_bonus'],
                    $levelOverrides['matching_bonus'] ?? $level['matching_bonus'],
                    $levelOverrides['leadership_bonus'] ?? $level['leadership_bonus'],
                    $levelOverrides['performance_bonus'] ?? $level['performance_bonus'],
                    $levelOverrides['monthly_target'] ?? $level['monthly_target'],
                ]);
            }

            $this->logAudit($newPlanId, $newData['plan_name'], $newData['plan_code'], $nextVersion, 'clone', $source, $newData, $createdBy);

            $this->pdo->commit();
            return $newPlanId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Activate a plan (deactivate all others).
     */
    public function activatePlan(int $planId, int $activatedBy): bool
    {
        $this->pdo->beginTransaction();
        try {
            $plan = $this->getPlanById($planId);
            if (!$plan) throw new Exception("Plan not found");

            // Deactivate all
            $this->pdo->exec("UPDATE mlm_commission_plans SET status = 'inactive', updated_at = NOW() WHERE status = 'active'");

            // Activate selected
            $this->pdo->prepare("UPDATE mlm_commission_plans SET status = 'active', effective_date = COALESCE(effective_date, CURDATE()), updated_at = NOW() WHERE id = ?")->execute([$planId]);

            $this->logAudit($planId, $plan['plan_name'], $plan['plan_code'], $plan['version'], 'activate', $plan, ['status' => 'active'], $activatedBy);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Deactivate a plan.
     */
    public function deactivatePlan(int $planId, int $deactivatedBy): bool
    {
        $this->pdo->beginTransaction();
        try {
            $plan = $this->getPlanById($planId);
            if (!$plan) throw new Exception("Plan not found");

            $this->pdo->prepare("UPDATE mlm_commission_plans SET status = 'inactive', updated_at = NOW() WHERE id = ?")->execute([$planId]);

            $this->logAudit($planId, $plan['plan_name'], $plan['plan_code'], $plan['version'], 'deactivate', $plan, ['status' => 'inactive'], $deactivatedBy);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a plan (only draft/inactive).
     */
    public function deletePlan(int $planId, int $deletedBy): bool
    {
        $this->pdo->beginTransaction();
        try {
            $plan = $this->getPlanById($planId);
            if (!$plan) throw new Exception("Plan not found");
            if ($plan['status'] === 'active') throw new Exception("Cannot delete an active plan.");

            $this->pdo->prepare("DELETE FROM mlm_plan_levels WHERE plan_id = ?")->execute([$planId]);
            $this->pdo->prepare("DELETE FROM mlm_commission_plans WHERE id = ?")->execute([$planId]);

            $this->logAudit($planId, $plan['plan_name'], $plan['plan_code'], $plan['version'], 'delete', $plan, null, $deletedBy);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /* ================================================================
       CRUD — LEVELS
       ================================================================ */

    /**
     * Get all levels for a plan, ordered by level_order.
     */
    public function getLevelsForPlan(int $planId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order");
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new rank level to a plan.
     */
    public function addLevel(int $planId, array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mlm_plan_levels (plan_id, level_name, level_order, direct_commission, team_commission,
                level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $planId,
            $data['level_name'],
            (int)$data['level_order'],
            (float)$data['direct_commission'],
            (float)$data['team_commission'],
            (float)($data['level_bonus'] ?? 0),
            (float)($data['matching_bonus'] ?? 0),
            (float)($data['leadership_bonus'] ?? 0),
            (float)($data['performance_bonus'] ?? 0),
            (float)($data['monthly_target'] ?? 0),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update an existing rank level.
     */
    public function updateLevel(int $levelId, int $planId, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE mlm_plan_levels SET
                level_name = ?,
                direct_commission = ?,
                team_commission = ?,
                level_bonus = ?,
                matching_bonus = ?,
                leadership_bonus = ?,
                performance_bonus = ?,
                monthly_target = ?
            WHERE id = ? AND plan_id = ?
        ");
        return $stmt->execute([
            $data['level_name'],
            (float)$data['direct_commission'],
            (float)$data['team_commission'],
            (float)($data['level_bonus'] ?? 0),
            (float)($data['matching_bonus'] ?? 0),
            (float)($data['leadership_bonus'] ?? 0),
            (float)($data['performance_bonus'] ?? 0),
            (float)($data['monthly_target'] ?? 0),
            $levelId,
            $planId,
        ]);
    }

    /**
     * Delete a rank level from a plan.
     */
    public function deleteLevel(int $levelId, int $planId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM mlm_plan_levels WHERE id = ? AND plan_id = ?");
        return $stmt->execute([$levelId, $planId]);
    }

    /* ================================================================
       AUDIT
       ================================================================ */

    /**
     * Get audit log for a plan.
     */
    public function getAuditLog(int $planId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.name as changer_name
            FROM commission_plan_audit a
            LEFT JOIN users u ON a.changed_by = u.id
            WHERE a.plan_id = ?
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$planId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get full audit log across all plans.
     */
    public function getFullAuditLog(int $limit = 100): array
    {
        $stmt = $this->pdo->query("
            SELECT a.*, u.name as changer_name
            FROM commission_plan_audit a
            LEFT JOIN users u ON a.changed_by = u.id
            ORDER BY a.created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================================
       COMPARISON
       ================================================================ */

    /**
     * Compare two plan versions side by side.
     */
    public function comparePlans(int $planIdA, int $planIdB): array
    {
        $planA = $this->getPlanById($planIdA);
        $planB = $this->getPlanById($planIdB);
        if (!$planA || !$planB) return [];

        $comparison = [
            'plan_a' => [
                'id' => $planA['id'],
                'name' => $planA['plan_name'],
                'version' => $planA['version'],
                'code' => $planA['plan_code'],
                'caps' => [
                    'global' => $planA['global_cap_pct'],
                    'track_a' => $planA['track_a_pct'],
                    'track_b' => $planA['track_b_pct'],
                    'track_c' => $planA['track_c_pct'],
                    'royalty' => $planA['royalty_pool_pct'],
                ],
            ],
            'plan_b' => [
                'id' => $planB['id'],
                'name' => $planB['plan_name'],
                'version' => $planB['version'],
                'code' => $planB['plan_code'],
                'caps' => [
                    'global' => $planB['global_cap_pct'],
                    'track_a' => $planB['track_a_pct'],
                    'track_b' => $planB['track_b_pct'],
                    'track_c' => $planB['track_c_pct'],
                    'royalty' => $planB['royalty_pool_pct'],
                ],
            ],
            'levels' => [],
        ];

        // Compare level by level (by level_order)
        $levelsA = $planA['levels'];
        $levelsB = $planB['levels'];
        $maxLevels = max(count($levelsA), count($levelsB));

        for ($i = 0; $i < $maxLevels; $i++) {
            $la = $levelsA[$i] ?? null;
            $lb = $levelsB[$i] ?? null;
            $comparison['levels'][] = [
                'level_order' => $i + 1,
                'name_a' => $la ? $la['level_name'] : '—',
                'name_b' => $lb ? $lb['level_name'] : '—',
                'direct_a' => $la ? (float)$la['direct_commission'] : 0,
                'direct_b' => $lb ? (float)$lb['direct_commission'] : 0,
                'team_a' => $la ? (float)$la['team_commission'] : 0,
                'team_b' => $lb ? (float)$lb['team_commission'] : 0,
                'level_a' => $la ? (float)$la['level_bonus'] : 0,
                'level_b' => $lb ? (float)$lb['level_bonus'] : 0,
                'match_a' => $la ? (float)$la['matching_bonus'] : 0,
                'match_b' => $lb ? (float)$lb['matching_bonus'] : 0,
                'leadership_a' => $la ? (float)$la['leadership_bonus'] : 0,
                'leadership_b' => $lb ? (float)$lb['leadership_bonus'] : 0,
                'performance_a' => $la ? (float)$la['performance_bonus'] : 0,
                'performance_b' => $lb ? (float)$lb['performance_bonus'] : 0,
                'target_a' => $la ? (float)$la['monthly_target'] : 0,
                'target_b' => $lb ? (float)$lb['monthly_target'] : 0,
            ];
        }

        return $comparison;
    }

    /* ================================================================
       STATS
       ================================================================ */

    public function getStats(): array
    {
        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM mlm_commission_plans")->fetchColumn();
        $active = (int)$this->pdo->query("SELECT COUNT(*) FROM mlm_commission_plans WHERE status = 'active'")->fetchColumn();
        $draft = (int)$this->pdo->query("SELECT COUNT(*) FROM mlm_commission_plans WHERE status = 'draft'")->fetchColumn();
        $inactive = (int)$this->pdo->query("SELECT COUNT(*) FROM mlm_commission_plans WHERE status = 'inactive'")->fetchColumn();
        $maxVersion = (int)$this->pdo->query("SELECT COALESCE(MAX(version), 0) FROM mlm_commission_plans")->fetchColumn();
        $totalLevels = (int)$this->pdo->query("SELECT COUNT(*) FROM mlm_plan_levels")->fetchColumn();
        $totalAudits = (int)$this->pdo->query("SELECT COUNT(*) FROM commission_plan_audit")->fetchColumn();

        return compact('total', 'active', 'draft', 'inactive', 'maxVersion', 'totalLevels', 'totalAudits');
    }

    /* ================================================================
       PRIVATE HELPERS
       ================================================================ */

    private function insertPlan(array $data): int
    {
        $this->pdo->prepare("
            INSERT INTO mlm_commission_plans
                (plan_name, plan_code, description, plan_type, status, version, effective_date, created_by,
                 global_cap_pct, track_a_pct, track_b_pct, track_c_pct, royalty_pool_pct,
                 same_level_override_gen1, same_level_override_gen2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $data['plan_name'], $data['plan_code'], $data['description'] ?? '',
            $data['plan_type'] ?? 'hybrid', $data['status'] ?? 'draft',
            $data['version'] ?? 1, $data['effective_date'] ?? null, $data['created_by'],
            $data['global_cap_pct'] ?? 20, $data['track_a_pct'] ?? 15,
            $data['track_b_pct'] ?? 3, $data['track_c_pct'] ?? 2,
            $data['royalty_pool_pct'] ?? 2,
            $data['same_level_override_gen1'] ?? 2, $data['same_level_override_gen2'] ?? 1,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    private function createDefaultLevels(int $planId, array $data): void
    {
        $defaults = $data['levels'] ?? [
            ['Associate', 1, 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000],
            ['Sr. Associate', 2, 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000],
            ['BDM', 3, 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000],
            ['Sr. BDM', 4, 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000],
            ['Vice President', 5, 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000],
            ['President', 6, 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000],
            ['Site Manager', 7, 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999],
        ];

        foreach ($defaults as $d) {
            if (is_array($d)) {
                $this->addLevel($planId, [
                    'level_name' => $d[0],
                    'level_order' => $d[1],
                    'direct_commission' => $d[2],
                    'team_commission' => $d[3],
                    'level_bonus' => $d[4],
                    'matching_bonus' => $d[5],
                    'leadership_bonus' => $d[6],
                    'performance_bonus' => $d[7],
                    'monthly_target' => $d[8],
                ]);
            }
        }
    }

    private function logAudit(int $planId, string $planName, string $planCode, int $version, string $action, ?array $oldValues, ?array $newValues, int $changedBy, ?array $changedFields = null): void
    {
        $this->pdo->prepare("
            INSERT INTO commission_plan_audit (plan_id, plan_name, plan_code, version, action, changed_fields, old_values, new_values, changed_by, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $planId, $planName, $planCode, $version, $action,
            $changedFields ? json_encode($changedFields) : null,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $changedBy,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);
    }

    private function computeChanges(array $old, array $new): array
    {
        $changes = [];
        $compareKeys = ['plan_name', 'description', 'plan_type', 'effective_date', 'expiry_date',
                        'global_cap_pct', 'track_a_pct', 'track_b_pct', 'track_c_pct',
                        'royalty_pool_pct', 'same_level_override_gen1', 'same_level_override_gen2'];
        foreach ($compareKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? $oldVal;
            if ($oldVal != $newVal) {
                $changes[$key] = ['from' => $oldVal, 'to' => $newVal];
            }
        }
        return $changes;
    }
}
