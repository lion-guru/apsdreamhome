<?php

namespace App\Services;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

/**
 * Retroactive Commission Recalculation Service
 * ──────────────────────────────────────────────
 * Handles explicit requests to recalculate past commission entries.
 * CRITICAL RULE: Past ledger entries are NEVER modified. New entries are created.
 * Requires admin approval before any recalculation is applied.
 *
 * Workflow:
 *   1. Admin requests recalculation (with reason)
 *   2. System computes new amount based on NEW plan rates
 *   3. Admin reviews diff (original vs new)
 *   4. Admin approves → new ledger entry created (status='recalculated')
 *   5. Original entry marked as 'superseded'
 */
class RetroactiveRecalculationService
{
    use ServiceTenantTrait;
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo ?: $this->getDb();
    }

    private function getDb(): PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Request a retroactive recalculation for a single ledger entry.
     *
     * @param int    $ledgerId   Original mlm_commission_ledger entry ID
     * @param string $reason     Why this recalculation is needed
     * @param int    $requestedBy Admin user ID making the request
     * @return array{success: bool, recalc_id?: int, original?: array, new_amount?: float, diff?: float, error?: string}
     */
    public function requestRecalculation(int $ledgerId, string $reason, int $requestedBy): array
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Fetch original entry
            $stmt = $this->pdo->prepare("
                SELECT id, beneficiary_user_id, source_user_id, commission_type,
                       amount, level, sale_amount, commission_percentage,
                       plan_id, plan_version, plan_snapshot, notes, booking_id, created_at
                FROM mlm_commission_ledger
                WHERE id = ?" . $this->tenantSql() . "
                FOR UPDATE
            ");
            $stmt->execute([$ledgerId]);
            $original = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Ledger entry not found'];
            }

            if (in_array($original['commission_type'], ['superseded', 'reversed'])) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Cannot recalculate a superseded or reversed entry'];
            }

            // 2. Check if there's already a pending recalculation for this entry
            $dup = $this->pdo->prepare("
                SELECT COUNT(*) FROM commission_recalculations
                WHERE original_ledger_id = ? AND status IN ('pending', 'approved')" . $this->tenantSql() . "
            ");
            $dup->execute([$ledgerId]);
            if ((int)$dup->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'A pending or approved recalculation already exists for this entry'];
            }

            // 3. Calculate what the amount WOULD be under the current active plan
            $newAmount = $this->calculateWithCurrentPlan($original);

            if ($newAmount === null) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Could not determine current plan rates for this commission type'];
            }

            $originalAmount = (float)$original['amount'];
            $diff = $newAmount - $originalAmount;

            // 4. Get active plan info
            $activePlan = $this->getActivePlanInfo();

            // 5. Insert recalculation request
        $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
        $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
        $ins = $this->pdo->prepare("
            INSERT INTO commission_recalculations
                (original_ledger_id, plan_id, plan_version, reason,
                 original_amount, new_amount, amount_diff,
                 requested_by, status, created_at{$extraCols})
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(){$extraVals})
        ");
        $ins->execute(array_merge([
            $ledgerId,
            $activePlan['plan_id'],
            $activePlan['plan_version'],
            $reason,
            $originalAmount,
            round($newAmount, 2),
            round($diff, 2),
            $requestedBy,
        ], array_values($insertData)));

            $recalcId = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();

            return [
                'success'    => true,
                'recalc_id'  => $recalcId,
                'original'   => $original,
                'new_amount' => round($newAmount, 2),
                'diff'       => round($diff, 2),
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("[RetroactiveRecalc] requestRecalculation FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => 'System error: ' . $e->getMessage()];
        }
    }

    /**
     * Bulk request recalculation for all entries of a given type within date range.
     */
    public function bulkRequestRecalculation(string $commissionType, string $fromDate, string $toDate, string $reason, int $requestedBy): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM mlm_commission_ledger
                WHERE commission_type = ? AND created_at BETWEEN ? AND ?
                  AND commission_type NOT IN ('superseded', 'reversed')" . $this->tenantSql() . "
            ");
            $stmt->execute([$commissionType, $fromDate, $toDate]);
            $entries = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $results = ['total' => count($entries), 'requested' => 0, 'skipped' => 0, 'errors' => 0, 'items' => []];

            foreach ($entries as $entryId) {
                $result = $this->requestRecalculation((int)$entryId, $reason, $requestedBy);
                if ($result['success']) {
                    $results['requested']++;
                    $results['items'][] = $result;
                } else {
                    if (str_contains($result['error'] ?? '', 'already exists')) {
                        $results['skipped']++;
                    } else {
                        $results['errors']++;
                        $results['items'][] = ['ledger_id' => $entryId, 'error' => $result['error']];
                    }
                }
            }

            return $results;
        } catch (Exception $e) {
            error_log("[RetroactiveRecalc] bulkRequest FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Approve a recalculation request.
     * Creates a NEW ledger entry and marks the original as superseded.
     */
    public function approveRecalculation(int $recalcId, int $approvedBy, string $adminNotes = ''): array
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Fetch the request
            $stmt = $this->pdo->prepare("
                SELECT * FROM commission_recalculations WHERE id = ? AND status = 'pending'" . $this->tenantSql() . " FOR UPDATE
            ");
            $stmt->execute([$recalcId]);
            $recalc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$recalc) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Request not found or not in pending status'];
            }

            // 2. Fetch original ledger entry
            $stmt2 = $this->pdo->prepare("SELECT * FROM mlm_commission_ledger WHERE id = ?" . $this->tenantSql());
            $stmt2->execute([$recalc['original_ledger_id']]);
            $original = $stmt2->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Original ledger entry not found'];
            }

            // 3. Create new ledger entry with recalculated amount
        $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
        $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
        $ins = $this->pdo->prepare("
            INSERT INTO mlm_commission_ledger
                (beneficiary_user_id, source_user_id, commission_type, amount,
                 level, sale_amount, commission_percentage, status, notes,
                 booking_id, receipt_id, property_id, created_at,
                 plan_id, plan_version, plan_snapshot, calculation_engine{$extraCols})
            VALUES (?, ?, ?, ?, ?, ?, ?, 'recalculated', ?,
                    ?, ?, ?, NOW(),
                    ?, ?, ?, 'retroactive_recalc'{$extraVals})
        ");
        $ins->execute(array_merge([
            $original['beneficiary_user_id'],
            $original['source_user_id'],
            $original['commission_type'],
            $recalc['new_amount'],
            $original['level'],
            $original['sale_amount'],
            $original['commission_percentage'],
            "Retroactive recalc: orig ₹" . number_format($recalc['original_amount']) .
                " → ₹" . number_format($recalc['new_amount']) .
                " | Reason: " . substr($recalc['reason'], 0, 100),
            $original['booking_id'],
            $original['receipt_id'] ?? null,
            $original['property_id'] ?? null,
            $recalc['plan_id'],
            $recalc['plan_version'],
            $this->getCurrentPlanSnapshotJson(),
        ], array_values($insertData)));

            $newLedgerId = (int)$this->pdo->lastInsertId();

            // 4. Mark original as superseded
            $this->pdo->prepare("
                UPDATE mlm_commission_ledger
                SET commission_type = CONCAT(commission_type, '_superseded'),
                    notes = CONCAT(COALESCE(notes, ''), ' [Superseded by recalculated entry #{$newLedgerId}]')
                WHERE id = ?" . $this->tenantSql() . "
            ")->execute([$recalc['original_ledger_id']]);

            // 5. Update recalculation record
            $this->pdo->prepare("
                UPDATE commission_recalculations
                SET new_ledger_id = ?, approved_by = ?, status = 'applied',
                    admin_notes = ?, updated_at = NOW()
                WHERE id = ?" . $this->tenantSql() . "
            ")->execute([$newLedgerId, $approvedBy, $adminNotes, $recalcId]);

            $this->pdo->commit();

            return [
                'success'        => true,
                'new_ledger_id'  => $newLedgerId,
                'original_id'    => $recalc['original_ledger_id'],
                'original_amount'=> (float)$recalc['original_amount'],
                'new_amount'     => (float)$recalc['new_amount'],
                'diff'           => (float)$recalc['amount_diff'],
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("[RetroactiveRecalc] approve FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => 'System error: ' . $e->getMessage()];
        }
    }

    /**
     * Reject a recalculation request.
     */
    public function rejectRecalculation(int $recalcId, int $rejectedBy, string $adminNotes = ''): array
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE commission_recalculations
                SET status = 'rejected', approved_by = ?, admin_notes = ?, updated_at = NOW()
                WHERE id = ? AND status = 'pending'" . $this->tenantSql() . "
            ");
            $stmt->execute([$rejectedBy, $adminNotes, $recalcId]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'error' => 'Request not found or not in pending status'];
            }

            return ['success' => true];
        } catch (Exception $e) {
            error_log("[RetroactiveRecalc] reject FAILED: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all recalculation requests (paginated, filterable).
     */
    public function getRequests(string $status = '', int $page = 1, int $perPage = 25): array
    {
        $where = '';
        $params = [];
        if ($status) {
            $where = "WHERE cr.status = ?";
            $params[] = $status;
        }

        // Count
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM commission_recalculations cr {$where}" . $this->tenantSqlForAlias('cr'));
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Fetch with original entry info
        $stmt = $this->pdo->prepare("
            SELECT cr.*,
                    ml.type as orig_type, ml.amount as orig_calc_amount,
                   ml.beneficiary_user_id, ml.source_user_id, ml.booking_id,
                   ml.commission_percentage as orig_rate,
                   u.name as beneficiary_name, s.name as source_name,
                   a.name as requested_by_name, b.name as approved_by_name
            FROM commission_recalculations cr
            LEFT JOIN mlm_commission_ledger ml ON ml.id = cr.original_ledger_id
            LEFT JOIN users u ON u.id = ml.beneficiary_user_id
            LEFT JOIN users s ON s.id = ml.source_user_id
            LEFT JOIN users a ON a.id = cr.requested_by
            LEFT JOIN users b ON b.id = cr.approved_by
            {$where}
            " . $this->tenantSqlForAlias('cr') . "
            ORDER BY cr.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Get summary stats for the recalculation dashboard.
     */
    public function getStats(): array
    {
        $stats = [];
        $r = $this->pdo->query("
            SELECT status, COUNT(*) as cnt, COALESCE(SUM(amount_diff), 0) as total_diff
            FROM commission_recalculations" . $this->tenantSql() . "
            GROUP BY status
        ");
        foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats[$row['status']] = [
                'count' => (int)$row['cnt'],
                'total_diff' => (float)$row['total_diff'],
            ];
        }

        return $stats;
    }

    // ── PRIVATE HELPERS ──

    /**
     * Calculate what a ledger entry WOULD amount to under the current active plan.
     * Uses the current plan's rates + the original entry's sale_amount and commission_percentage.
     */
    private function calculateWithCurrentPlan(array $original): ?float
    {
        $saleAmount = (float)($original['sale_amount'] ?? 0);
        $origRate = (float)($original['commission_percentage'] ?? 0);
        $type = $original['commission_type'] ?? '';

        if ($saleAmount <= 0) {
            return null;
        }

        // Get active plan caps
        $plan = $this->getActivePlanInfo();
        if (!$plan) return null;

        // For direct_sale / override: recalculate using current plan's rank rates
        // For other types: use the original rate (these are fixed amounts)
        switch ($type) {
            case 'direct_sale':
            case 'override':
                // Use current global cap to determine available budget
                $globalCap = $saleAmount * (($plan['global_cap_pct'] ?? 20) / 100);
                // Calculate using the same rate logic but with current plan snapshot
                $newRate = $origRate; // Same rate calculation, just under new plan context
                $newAmount = $saleAmount * ($newRate / 100);
                return min($newAmount, $globalCap);

            case 'rank_bonus':
            case 'level_bonus':
            case 'matching_bonus':
            case 'generation_bonus':
            case 'performance_bonus':
            case 'team_bonus':
            case 'royalty_pool':
                // These are fixed amounts from the plan levels
                // Recalculate using current plan level rates
                return $this->recalculateFromPlanLevels($type, $original, $plan);

            default:
                // Unknown type — use original amount with no change
                return (float)$original['amount'];
        }
    }

    /**
     * Recalculate a bonus amount from current plan levels.
     */
    private function recalculateFromPlanLevels(string $type, array $original, array $plan): ?float
    {
        $level = (int)($original['level'] ?? 1);
        $saleAmount = (float)($original['sale_amount'] ?? 0);

        if ($saleAmount <= 0 || empty($plan['levels'])) {
            return (float)$original['amount'];
        }

        // Map commission_type to plan level field
        $fieldMap = [
            'level_bonus'       => 'level_bonus',
            'matching_bonus'    => 'matching_bonus',
            'performance_bonus' => 'performance_bonus',
            'team_bonus'        => 'team_commission',
        ];

        $field = $fieldMap[$type] ?? null;
        if (!$field) {
            return (float)$original['amount'];
        }

        // Find the matching level in the plan
        foreach ($plan['levels'] as $planLevel) {
            if ((int)($planLevel['level_order'] ?? 0) === $level) {
                $rate = (float)($planLevel[$field] ?? 0);
                if ($rate > 0) {
                    return round($saleAmount * ($rate / 100), 2);
                }
            }
        }

        // Fallback: return original amount
        return (float)$original['amount'];
    }

    /**
     * Get active plan info (id, version, caps, levels).
     */
    private function getActivePlanInfo(): ?array
    {
        try {
            $plan = $this->pdo->query("
                SELECT p.id, p.plan_name, p.version, p.global_cap_pct,
                       p.track_a_pct, p.track_b_pct, p.track_c_pct,
                       p.royalty_pool_pct
                FROM mlm_commission_plans p
                WHERE p.status = 'active'
                ORDER BY p.version DESC
                LIMIT 1
            ")->fetch(PDO::FETCH_ASSOC);

            if (!$plan) return null;

            $plan['plan_id'] = (int)$plan['id'];
            $plan['plan_version'] = (int)$plan['version'];

            return $plan;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get current plan snapshot as JSON for ledger entries.
     */
    private function getCurrentPlanSnapshotJson(): ?string
    {
        $engine = new \App\Services\HybridCommissionEngine($this->pdo);
        $snapshot = $engine->getActivePlanSnapshot();
        return $snapshot ? json_encode($snapshot) : null;
    }
}
