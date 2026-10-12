<?php

namespace App\Services\Pricing;

use App\Core\Database\Database;

/**
 * A/B Pricing Experiment Lab (completes the Session 218 table pair).
 *
 * Draft an experiment (control + priced variants with traffic splits) →
 * start (deterministic plot assignment) → optionally apply variant prices →
 * live results (conversion = assigned plot moved to booked/sold/registered;
 * no booking-path hooks needed — status IS the conversion signal) →
 * restore control prices or archive.
 *
 * Safety mirrors DynamicPricingService::apply(): available plots only,
 * PlcService recalculation, one price_history row per plot
 * (reference_type='pricing_experiment'), transactional.
 */
class PricingExperimentService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Colonies with available plots (experiment candidates). */
    public function colonies(): array
    {
        try {
            return $this->pdo->query(
                "SELECT c.id, c.name, c.min_price_per_sqft,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available' AND p.deleted_at IS NULL AND p.is_active = 1) AS available
                 FROM colonies c
                 WHERE c.is_active = 1 AND c.deleted_at IS NULL
                 HAVING available > 0
                 ORDER BY c.name ASC"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getExperiment(int $id): ?array
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT e.*, c.name AS colony_name
                 FROM pricing_experiments e
                 LEFT JOIN colonies c ON c.id = e.colony_id
                 WHERE e.id = ? LIMIT 1"
            );
            $st->execute([$id]);
            $e = $st->fetch(\PDO::FETCH_ASSOC);
            if ($e) {
                $e['variants'] = json_decode($e['variant_configs'] ?: '[]', true) ?: [];
                $e['traffic_allocation'] = json_decode($e['traffic_allocation'] ?: '{}', true) ?: [];
            }
            return $e ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function listExperiments(int $limit = 50): array
    {
        try {
            return $this->pdo->query(
                "SELECT e.*, c.name AS colony_name,
                        (SELECT COUNT(*) FROM pricing_experiment_assignments a WHERE a.experiment_id = e.id) AS assigned
                 FROM pricing_experiments e
                 LEFT JOIN colonies c ON c.id = e.colony_id
                 ORDER BY e.created_at DESC
                 LIMIT " . (int)$limit
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Create a draft. Variants: [['label' => 'A', 'base_pps' => 950, 'split_pct' => 50], ...].
     * Splits must sum with control to 100 (control_split = 100 - sum(variants)).
     */
    public function create(array $d, int $userId): array
    {
        $colonyId = (int)($d['colony_id'] ?? 0);
        $name = trim((string)($d['experiment_name'] ?? ''));
        $control = (float)($d['control_base_pps'] ?? 0);
        $minSample = max(10, (int)($d['min_sample_size'] ?? 30));

        if ($colonyId <= 0 || $name === '') {
            return ['success' => false, 'error' => 'Colony and experiment name are required'];
        }
        if ($control <= 0) {
            return ['success' => false, 'error' => 'Control base price must be positive'];
        }

        $variants = [];
        $raw = $d['variants'] ?? [];
        if (!is_array($raw) || count($raw) < 1) {
            return ['success' => false, 'error' => 'At least one variant is required'];
        }
        $splitSum = 0.0;
        foreach ($raw as $i => $v) {
            $label = trim((string)($v['label'] ?? ''));
            $pps = (float)($v['base_pps'] ?? 0);
            $split = (float)($v['split_pct'] ?? 0);
            if ($label === '' || $pps <= 0 || $split <= 0) {
                return ['success' => false, 'error' => 'Variant ' . ($i + 1) . ': label, positive price and positive split % required'];
            }
            $splitSum += $split;
            $variants[] = ['key' => 'variant_' . chr(97 + $i), 'label' => $label, 'base_pps' => round($pps, 2), 'split_pct' => $split];
        }
        if ($splitSum >= 100) {
            return ['success' => false, 'error' => 'Variant splits total ' . number_format($splitSum, 1) . '% — must leave room for control (max 99%)'];
        }
        $controlSplit = round(100 - $splitSum, 2);

        try {
            $this->pdo->prepare(
                "INSERT INTO pricing_experiments
                 (tenant_id, colony_id, experiment_name, experiment_type, control_base_pps, variant_configs,
                  traffic_allocation, min_sample_size, created_by, status)
                 VALUES (1, ?, ?, 'ab_test', ?, ?, ?, ?, ?, 'draft')"
            )->execute([
                $colonyId, $name, $control,
                json_encode($variants),
                json_encode(['control_split_pct' => $controlSplit]),
                $minSample, $userId,
            ]);
            return ['success' => true, 'id' => (int)$this->pdo->lastInsertId(), 'control_split_pct' => $controlSplit];
        } catch (\Throwable $e) {
            error_log('[PricingExperimentService::create] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Start a draft: assign every available plot of the colony to control or
     * a variant by deterministic cumulative-split thresholds on plot id
     * (stable, reproducible, no randomness to audit later).
     */
    public function start(int $id, int $userId): array
    {
        $e = $this->getExperiment($id);
        if (!$e) {
            return ['success' => false, 'error' => 'Experiment not found'];
        }
        if ($e['status'] !== 'draft') {
            return ['success' => false, 'error' => "Only draft experiments can be started (status: {$e['status']})"];
        }

        try {
            $plots = $this->pdo->prepare(
                "SELECT id, base_price_per_sqft, price_per_sqft, total_price
                 FROM plots
                 WHERE colony_id = ? AND status = 'available' AND deleted_at IS NULL AND is_active = 1
                 ORDER BY id ASC"
            );
            $plates = null;
            $plots->execute([$e['colony_id']]);
            $plotRows = $plots->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $n = count($plotRows);
            if ($n < 2) {
                return ['success' => false, 'error' => 'Need at least 2 available plots in this colony to run an experiment'];
            }

            $ta = is_array($e['traffic_allocation']) ? $e['traffic_allocation'] : (json_decode((string)$e['traffic_allocation'], true) ?: []);
            $controlSplit = (float)($ta['control_split_pct'] ?? 0);
            $thresholds = [];
            $cum = $controlSplit;
            $thresholds[] = ['control', $cum];
            foreach ($e['variants'] as $v) {
                $cum += (float)$v['split_pct'];
                $thresholds[] = [$v['key'], $cum, (float)$v['base_pps']];
            }

            $this->beginTransaction();
            $ins = $this->pdo->prepare(
                "INSERT INTO pricing_experiment_assignments
                 (tenant_id, experiment_id, plot_id, variant_key, assigned_base_pps, original_base_pps, original_pps, original_total)
                 VALUES (1, ?, ?, ?, ?, ?, ?, ?)"
            );
            $counts = ['control' => 0];
            foreach ($e['variants'] as $v) $counts[$v['key']] = 0;

            foreach ($plotRows as $i => $pr) {
                // Deterministic bucket: position of plot in the ordered list as
                // a percentage midpoint of the slot.
                $pct = (($i + 0.5) / $n) * 100.0;
                foreach ($thresholds as $th) {
                    if ($pct <= $th[1]) {
                        $key = $th[0];
                        $pps = $key === 'control' ? (float)$e['control_base_pps'] : (float)$th[2];
                        $ins->execute([
                            $id, (int)$pr['id'], $key, $pps,
                            $pr['base_price_per_sqft'], $pr['price_per_sqft'], $pr['total_price'],
                        ]);
                        $counts[$key]++;
                        break;
                    }
                }
            }

            $this->pdo->prepare(
                "UPDATE pricing_experiments SET status = 'running', start_date = CURDATE(), updated_at = NOW() WHERE id = ?"
            )->execute([$id]);
            $this->commit();

            return ['success' => true, 'assigned' => array_sum($counts), 'by_variant' => $counts];
        } catch (\Throwable $ex) {
            $this->rollBack();
            error_log('[PricingExperimentService::start] ' . $ex->getMessage());
            return ['success' => false, 'error' => $ex->getMessage()];
        }
    }

    /**
     * Apply each assigned plot's variant price (control keeps control pps).
     * Available plots only; PlcService recalculation; price_history per plot.
     */
    public function applyPrices(int $id, int $userId): array
    {
        $e = $this->getExperiment($id);
        if (!$e || $e['status'] !== 'running') {
            return ['success' => false, 'error' => 'Experiment must be running'];
        }
        try {
            $st = $this->pdo->prepare(
                "SELECT a.plot_id, a.variant_key, a.assigned_base_pps,
                        p.area_sqft, p.corner_plot, p.park_facing, p.road_width_ft, p.facing, p.plot_number,
                        p.base_price_per_sqft, p.price_per_sqft, p.total_price
                 FROM pricing_experiment_assignments a
                 JOIN plots p ON p.id = a.plot_id
                 WHERE a.experiment_id = ? AND p.status = 'available' AND p.deleted_at IS NULL"
            );
            $st->execute([$id]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            if (!$rows) {
                return ['success' => false, 'error' => 'No available assigned plots to price'];
            }

            $this->beginTransaction();
            $upd = $this->pdo->prepare(
                "UPDATE plots SET base_price_per_sqft = ?, price_per_sqft = ?, total_price = ?, updated_at = NOW() WHERE id = ?"
            );
            $ph = $this->pdo->prepare(
                "INSERT INTO price_history
                 (tenant_id, plot_id, colony_id, old_price, new_price, old_price_per_sqft, new_price_per_sqft,
                  change_type, reason, changed_by, reference_type, reference_id)
                 VALUES (1, ?, ?, ?, ?, ?, ?, 'base', ?, ?, 'pricing_experiment', ?)"
            );
            $affected = 0;
            foreach ($rows as $r) {
                $base = (float)$r['assigned_base_pps'];
                $calc = PlcService::calculate(
                    $base, (float)$r['area_sqft'], !empty($r['corner_plot']), !empty($r['park_facing']),
                    (float)$r['road_width_ft'],
                    ['id' => (int)$r['plot_id'], 'plot_number' => $r['plot_number'], 'colony_id' => (int)$e['colony_id'], 'facing' => $r['facing']]
                );
                $upd->execute([$base, $calc['final_pps'], $calc['total'], (int)$r['plot_id']]);
                $ph->execute([
                    (int)$r['plot_id'], (int)$e['colony_id'],
                    (float)$r['total_price'], $calc['total'],
                    (float)$r['price_per_sqft'], $calc['final_pps'],
                    "A/B experiment #{$id} ({$e['experiment_name']}) variant {$r['variant_key']} @ {$base}",
                    $userId, $id,
                ]);
                $affected++;
            }
            $this->commit();
            return ['success' => true, 'affected' => $affected];
        } catch (\Throwable $ex) {
            $this->rollBack();
            error_log('[PricingExperimentService::applyPrices] ' . $ex->getMessage());
            return ['success' => false, 'error' => $ex->getMessage()];
        }
    }

    /**
     * Live results per variant: assigned, converted (plot now booked/sold/
     * registered/hold/reserved), conversion rate, avg assigned & realized
     * pps, revenue. Honest note when below the experiment's min_sample_size.
     * Also syncs converted_at/converted_pps on assignment rows.
     */
    public function results(int $id): array
    {
        $e = $this->getExperiment($id);
        if (!$e) {
            return ['error' => 'not_found'];
        }
        try {
            $st = $this->pdo->prepare(
                "SELECT a.variant_key, a.plot_id, a.assigned_base_pps, a.converted_at,
                        p.status, p.price_per_sqft, p.total_price, p.sale_date
                 FROM pricing_experiment_assignments a
                 JOIN plots p ON p.id = a.plot_id
                 WHERE a.experiment_id = ?"
            );
            $st->execute([$id]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $ex) {
            return ['error' => $ex->getMessage()];
        }

        $convStatuses = ['booked', 'sold', 'registered', 'hold', 'reserved'];
        $stats = [
            'control' => [
                'label' => 'Control', 'base_pps' => (float)$e['control_base_pps'],
                'assigned' => 0, 'converted' => 0, 'revenue' => 0.0, 'realized_pps_sum' => 0.0,
            ],
        ];
        foreach ($e['variants'] as $v) {
            $stats[$v['key']] = [
                'label' => $v['label'], 'base_pps' => (float)$v['base_pps'],
                'assigned' => 0, 'converted' => 0, 'revenue' => 0.0, 'realized_pps_sum' => 0.0,
            ];
        }

        $sync = $this->pdo->prepare(
            "UPDATE pricing_experiment_assignments SET converted_at = ?, converted_pps = ?
             WHERE experiment_id = ? AND plot_id = ? AND converted_at IS NULL"
        );

        foreach ($rows as $r) {
            $k = $r['variant_key'];
            if (!isset($stats[$k])) {
                $stats[$k] = ['label' => $k, 'base_pps' => (float)$r['assigned_base_pps'], 'assigned' => 0, 'converted' => 0, 'revenue' => 0.0, 'realized_pps_sum' => 0.0];
            }
            $stats[$k]['assigned']++;
            if (in_array($r['status'], $convStatuses, true)) {
                $stats[$k]['converted']++;
                $stats[$k]['revenue'] += (float)$r['total_price'];
                $stats[$k]['realized_pps_sum'] += (float)$r['price_per_sqft'];
                if ($r['converted_at'] === null) {
                    try {
                        $sync->execute([$r['sale_date'] ?: date('Y-m-d H:i:s'), (float)$r['price_per_sqft'], $id, (int)$r['plot_id']]);
                    } catch (\Throwable $ignore) {
                    }
                }
            }
        }

        $out = [];
        foreach ($stats as $k => $s) {
            $out[$k] = [
                'label' => $s['label'],
                'base_pps' => $s['base_pps'],
                'assigned' => $s['assigned'],
                'converted' => $s['converted'],
                'conv_rate' => $s['assigned'] > 0 ? round($s['converted'] / $s['assigned'] * 100, 1) : 0.0,
                'revenue' => round($s['revenue'], 2),
                'avg_realized_pps' => $s['converted'] > 0 ? round($s['realized_pps_sum'] / $s['converted'], 2) : null,
            ];
        }

        $totalAssigned = array_sum(array_column($out, 'assigned'));
        $note = null;
        $controlRate = $out['control']['conv_rate'];
        $bestKey = null; $bestRate = -1.0;
        foreach ($out as $k => $s) {
            if ($k !== 'control' && $s['conv_rate'] > $bestRate) { $bestRate = $s['conv_rate']; $bestKey = $k; }
        }
        if ($bestKey !== null) {
            $delta = round($bestRate - $controlRate, 1);
            if ($totalAssigned < (int)$e['min_sample_size']) {
                $note = "Below minimum sample size ({$totalAssigned}/{$e['min_sample_size']} assigned) — differences are directional, not statistically trustworthy yet.";
            } elseif (abs($delta) < 1.0) {
                $note = "Best variant leads control by only {$delta}pp — within noise at current sample.";
            } else {
                $note = "{$out[$bestKey]['label']} leads control by {$delta}pp conversion at {$totalAssigned} assigned plots.";
            }
        }

        // Persist a results snapshot on the experiment row (audit trail).
        try {
            $this->pdo->prepare("UPDATE pricing_experiments SET results = ?, updated_at = NOW() WHERE id = ?")
                ->execute([json_encode(['variants' => $out, 'note' => $note, 'computed_at' => date('Y-m-d H:i:s')]), $id]);
        } catch (\Throwable $ignore) {
        }

        return ['variants' => $out, 'note' => $note, 'total_assigned' => $totalAssigned];
    }

    /**
     * Restore all still-available assigned plots to their ORIGINAL pre-experiment
     * prices (captured on start — including legacy NULL-base rows), then archive.
     * Fallback for rows missing originals: recompute through PlcService at control.
     */
    public function restoreOriginalPrices(int $id, int $userId): array
    {
        $e = $this->getExperiment($id);
        if (!$e) {
            return ['success' => false, 'error' => 'Experiment not found'];
        }
        try {
            $st = $this->pdo->prepare(
                "SELECT a.plot_id, a.original_base_pps, a.original_pps, a.original_total,
                        p.area_sqft, p.corner_plot, p.park_facing, p.road_width_ft, p.facing, p.plot_number,
                        p.price_per_sqft, p.total_price
                 FROM pricing_experiment_assignments a
                 JOIN plots p ON p.id = a.plot_id
                 WHERE a.experiment_id = ? AND p.status = 'available' AND p.deleted_at IS NULL"
            );
            $st->execute([$id]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            if (!$rows) {
                return ['success' => false, 'error' => 'No available assigned plots to restore'];
            }
            $this->beginTransaction();
            $upd = $this->pdo->prepare("UPDATE plots SET base_price_per_sqft = ?, price_per_sqft = ?, total_price = ?, updated_at = NOW() WHERE id = ?");
            $ph = $this->pdo->prepare(
                "INSERT INTO price_history
                 (tenant_id, plot_id, colony_id, old_price, new_price, old_price_per_sqft, new_price_per_sqft,
                  change_type, reason, changed_by, reference_type, reference_id)
                 VALUES (1, ?, ?, ?, ?, ?, ?, 'base', ?, ?, 'pricing_experiment', ?)"
            );
            $control = (float)$e['control_base_pps'];
            $affected = 0;
            foreach ($rows as $r) {
                if ($r['original_pps'] !== null) {
                    // Exact pre-experiment restore (works for legacy NULL-base rows too).
                    $newBase = $r['original_base_pps'];
                    $newPps = (float)$r['original_pps'];
                    $newTotal = (float)$r['original_total'];
                    $reason = "A/B experiment #{$id} restore to original price";
                } else {
                    // Safety fallback: recompute through the live engine at control.
                    $calc = PlcService::calculate(
                        $control, (float)$r['area_sqft'], !empty($r['corner_plot']), !empty($r['park_facing']),
                        (float)$r['road_width_ft'],
                        ['id' => (int)$r['plot_id'], 'plot_number' => $r['plot_number'], 'colony_id' => (int)$e['colony_id'], 'facing' => $r['facing']]
                    );
                    $newBase = $control;
                    $newPps = $calc['final_pps'];
                    $newTotal = $calc['total'];
                    $reason = "A/B experiment #{$id} restore to control @ {$control} (no original captured)";
                }
                $upd->execute([$newBase, $newPps, $newTotal, (int)$r['plot_id']]);
                $ph->execute([
                    (int)$r['plot_id'], (int)$e['colony_id'],
                    (float)$r['total_price'], $newTotal, (float)$r['price_per_sqft'], $newPps,
                    $reason,
                    $userId, $id,
                ]);
                $affected++;
            }
            $this->pdo->prepare("UPDATE pricing_experiments SET status = 'archived', end_date = CURDATE(), updated_at = NOW() WHERE id = ?")->execute([$id]);
            $this->commit();
            return ['success' => true, 'affected' => $affected, 'status' => 'archived'];
        } catch (\Throwable $ex) {
            $this->rollBack();
            return ['success' => false, 'error' => $ex->getMessage()];
        }
    }

    /** Delete a draft experiment (nothing assigned yet). */
    public function deleteDraft(int $id): array
    {
        $e = $this->getExperiment($id);
        if (!$e) {
            return ['success' => false, 'error' => 'Experiment not found'];
        }
        if ($e['status'] !== 'draft') {
            return ['success' => false, 'error' => "Only drafts can be deleted (status: {$e['status']}) — running experiments use Restore & Archive"];
        }
        try {
            $this->pdo->prepare("DELETE FROM pricing_experiments WHERE id = ?")->execute([$id]);
            return ['success' => true];
        } catch (\Throwable $ex) {
            return ['success' => false, 'error' => $ex->getMessage()];
        }
    }

    private function beginTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    private function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    private function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
