<?php

namespace App\Services\Pricing;

use App\Services\ServiceConfigService;
use App\Core\Database\Database;

/**
 * AI Dynamic Pricing & Yield Optimization Engine.
 *
 * Data-driven price suggestions per colony from live signals:
 *  - 90-day sales velocity (plots booked/sold)
 *  - inventory tightness (available / total) and stock age
 *  - colony-tagged lead demand (60d)
 *  - calendar seasonality (wedding/festival vs monsoon vs summer)
 *  - trained time-series model over price_history (optional, honest about
 *    sample size - refuses to train below config min_samples)
 *
 * Safety model (S198 lesson): apply() ONLY rewrites `available` plots
 * (never booked/sold), recalculates every plot through the live
 * PlcService math, writes a price_history row per plot (rollback trail)
 * and an audit row in pricing_optimization_logs. Nothing happens without
 * an explicit admin click.
 */
class DynamicPricingService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Config defaults + service_configs rows (idempotent, PlcService pattern). */
    public static function config(): array
    {
        return [
            'enabled'         => (bool)ServiceConfigService::getVal('dynamic_pricing', 'dynamic_pricing_enabled', '0'),
            'max_change_pct'  => (float)ServiceConfigService::getVal('dynamic_pricing', 'dynamic_pricing_max_change_pct', 15),
            'min_samples'     => (int)ServiceConfigService::getVal('dynamic_pricing', 'dynamic_pricing_min_samples', 30),
            'lookback_days'   => (int)ServiceConfigService::getVal('dynamic_pricing', 'dynamic_pricing_lookback_days', 180),
            'min_confidence'  => (float)ServiceConfigService::getVal('dynamic_pricing', 'dynamic_pricing_min_confidence', 0.75),
        ];
    }

    public static function ensureConfigRows($db): void
    {
        $defaults = [
            'dynamic_pricing_enabled'        => ['0',   'Master switch for scheduled auto-suggestions (manual generate always available)'],
            'dynamic_pricing_max_change_pct' => ['15',  'Maximum % base-price change per suggestion (bounded)'],
            'dynamic_pricing_min_samples'    => ['30',  'Minimum price_history rows required before model training'],
            'dynamic_pricing_lookback_days'  => ['180', 'Days of price_history used for model training'],
            'dynamic_pricing_min_confidence' => ['0.75','Confidence below this marks suggestions low-confidence in UI'],
        ];
        try {
            foreach ($defaults as $key => [$val, $desc]) {
                $exists = $db->prepare("SELECT id FROM service_configs WHERE service_name = 'dynamic_pricing' AND config_key = ? LIMIT 1");
                $exists->execute([$key]);
                if (!$exists->fetchColumn()) {
                    $db->prepare("INSERT INTO service_configs (service_name, config_key, config_value, config_type, description, group_name) VALUES ('dynamic_pricing', ?, ?, 'text', ?, 'dynamic_pricing')")
                       ->execute([$key, $val, $desc]);
                }
            }
        } catch (\Throwable $e) {
            error_log('[DynamicPricingService::ensureConfigRows] ' . $e->getMessage());
        }
    }

    /**
     * Live feature set for one colony. Returns null when colony unknown.
     */
    public function computeFeatures(int $colonyId): ?array
    {
        try {
            $colony = $this->pdo->prepare("SELECT id, name, min_price_per_sqft, starting_price FROM colonies WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $colony->execute([$colonyId]);
            $colony = $colony->fetch(\PDO::FETCH_ASSOC);
            if (!$colony) {
                return null;
            }

            $st = $this->pdo->prepare(
                "SELECT COUNT(*) AS total,
                        COALESCE(SUM(status = 'available'), 0) AS available,
                        COALESCE(SUM(status IN ('booked','sold','registered','hold','reserved')), 0) AS engaged,
                        COALESCE(SUM(CASE WHEN booking_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                                           OR sale_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                                      THEN 1 ELSE 0 END), 0) AS sold_90d,
                        COALESCE(AVG(CASE WHEN status = 'available' THEN DATEDIFF(NOW(), created_at) END), 0) AS avg_age_days,
                        COALESCE(AVG(CASE WHEN status = 'available' AND corner_plot = 1 THEN 1 ELSE 0 END), 0) AS corner_ratio,
                        COALESCE(AVG(CASE WHEN status = 'available' AND park_facing = 1 THEN 1 ELSE 0 END), 0) AS park_ratio,
                        COALESCE(AVG(CASE WHEN status = 'available' THEN road_width_ft END), 0) AS avg_road_ft,
                        COALESCE(AVG(CASE WHEN status = 'available' THEN price_per_sqft END), 0) AS avg_pps,
                        COALESCE(AVG(CASE WHEN status = 'available' THEN base_price_per_sqft END), 0) AS avg_base_pps
                 FROM plots
                 WHERE colony_id = ? AND deleted_at IS NULL AND is_active = 1"
            );
            $st->execute([$colonyId]);
            $p = $st->fetch(\PDO::FETCH_ASSOC);

            // Colony-tagged lead demand, last 60 days (neutral when untagged).
            $leads = 0;
            try {
                $ls = $this->pdo->prepare(
                    "SELECT COUNT(*) FROM leads
                     WHERE deleted_at IS NULL
                       AND created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                       AND (location_preference LIKE ? OR city LIKE ? OR notes LIKE ?)"
                );
                $like = '%' . $colony['name'] . '%';
                $ls->execute([$like, $like, $like]);
                $leads = (int)$ls->fetchColumn();
            } catch (\Throwable $e) {
                $leads = 0;
            }

            $month = (int)date('n');
            $seasonFactor = in_array($month, [10, 11, 12, 1, 2, 3], true) ? 1.00
                          : (in_array($month, [4, 5, 6], true) ? 0.85 : 0.95);

            $currentBase = (float)$colony['min_price_per_sqft'] > 0
                ? (float)$colony['min_price_per_sqft']
                : ((float)$p['avg_base_pps'] > 0 ? (float)$p['avg_base_pps'] : 0.0);

            return [
                'colony_id'        => $colonyId,
                'colony_name'      => $colony['name'],
                'total_plots'      => (int)$p['total'],
                'available'        => (int)$p['available'],
                'engaged'          => (int)$p['engaged'],
                'sold_90d'         => (int)$p['sold_90d'],
                'avg_age_days'     => round((float)$p['avg_age_days'], 1),
                'corner_ratio'     => round((float)$p['corner_ratio'], 3),
                'park_ratio'       => round((float)$p['park_ratio'], 3),
                'avg_road_ft'      => round((float)$p['avg_road_ft'], 1),
                'avg_pps'          => round((float)$p['avg_pps'], 2),
                'current_base_pps' => round($currentBase, 2),
                'leads_60d'        => $leads,
                'season_index'     => round($seasonFactor, 2),
                'generated_at'     => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            error_log('[DynamicPricingService::computeFeatures] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Weighted demand score 0..1 + per-factor breakdown (weights renormalise
     * over available factors; untagged-lead colonies score neutral 0.5 there).
     * Returns [score, breakdown] where breakdown = [['factor','weight','value','contribution'], ...].
     */
    public function demandScore(array $f): array
    {
        $total = max(1, (int)$f['total_plots']);

        $velocity = min(1.0, (($f['sold_90d'] / $total) / 0.10));
        $tightness = 1.0 - ($f['available'] / $total);
        $freshness = max(0.0, 1.0 - ($f['avg_age_days'] / 365.0));
        $seasonality = max(0.0, min(1.0, ($f['season_index'] - 0.85) / 0.15));
        if ($f['leads_60d'] > 0) {
            $leadsScore = min(1.0, ($f['leads_60d'] / max(1, $f['available'])) / 0.5);
        } else {
            $leadsScore = 0.5; // neutral: absence of tagged leads is not absence of demand
        }

        $factors = [
            ['Sales velocity (90d)', 0.30, $velocity],
            ['Inventory tightness',  0.25, $tightness],
            ['Stock freshness',      0.15, $freshness],
            ['Seasonality',          0.15, $seasonality],
            ['Tagged lead demand',   0.15, $leadsScore],
        ];

        $wSum = 0.0;
        $score = 0.0;
        $breakdown = [];
        foreach ($factors as [$label, $w, $v]) {
            $wSum += $w;
            $score += $w * $v;
            $breakdown[] = ['factor' => $label, 'weight' => $w, 'value' => round($v, 3)];
        }
        $score = $wSum > 0 ? $score / $wSum : 0.5;

        return [round($score, 4), $breakdown];
    }

    /**
     * Generate one pending suggestion for a colony. Skips with a reason
     * (no plots / no available stock / pending already exists / within noise).
     */
    public function suggestForColony(int $colonyId): array
    {
        $cfg = self::config();
        $f = $this->computeFeatures($colonyId);
        if ($f === null) {
            return ['skipped' => 'colony_not_found'];
        }
        if ($f['total_plots'] < 1) {
            return ['skipped' => 'no_plots'];
        }
        if ($f['available'] < 1) {
            return ['skipped' => 'no_available_plots'];
        }
        if ($f['current_base_pps'] <= 0) {
            return ['skipped' => 'no_base_price'];
        }

        $pend = $this->pdo->prepare("SELECT id FROM dynamic_pricing_suggestions WHERE colony_id = ? AND status = 'pending' LIMIT 1");
        $pend->execute([$colonyId]);
        if ($pend->fetchColumn()) {
            return ['skipped' => 'pending_exists'];
        }

        [$score, $breakdown] = $this->demandScore($f);
        $max = max(1.0, $cfg['max_change_pct']);
        $delta = round(($score - 0.5) * 2 * $max, 2);
        if (abs($delta) < 1.0) {
            return ['skipped' => 'within_noise', 'score' => $score, 'delta_pct' => $delta];
        }

        $suggested = round($f['current_base_pps'] * (1 + $delta / 100), 2);

        $confidence = min(0.95, 0.55 + 0.40 * min(1.0, $f['total_plots'] / 100));
        if ($delta < 0) {
            $confidence = round(max(0.50, $confidence - 0.05), 3); // downside calls need more proof
        }
        $confidence = round($confidence, 3);

        $reasoning = sprintf(
            "Demand score %.2f (0-1). 90d sell-through %d/%d plots; %d of %d plots still available (avg age %.0f days); %d tagged leads in 60d; season factor %.2f. Bounded to +/-%.1f%% -> %+.2f%%.",
            $score, $f['sold_90d'], $f['total_plots'], $f['available'], $f['total_plots'],
            $f['avg_age_days'], $f['leads_60d'], $f['season_index'], $max, $delta
        );

        try {
            $this->beginTransaction();
            $st = $this->pdo->prepare(
                "INSERT INTO dynamic_pricing_suggestions
                 (tenant_id, colony_id, model_id, plot_id, current_base_pps, suggested_base_pps,
                  price_change_pct, confidence_score, reasoning, feature_snapshot, status)
                 VALUES (1, ?, NULL, NULL, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            $st->execute([
                $colonyId, $f['current_base_pps'], $suggested, $delta, $confidence,
                $reasoning, json_encode(['features' => $f, 'score_breakdown' => $breakdown]),
            ]);
            $sid = (int)$this->pdo->lastInsertId();

            $this->pdo->prepare(
                "INSERT INTO pricing_optimization_logs
                 (tenant_id, colony_id, suggestion_id, action, old_base_pps, new_base_pps, notes)
                 VALUES (1, ?, ?, 'suggestion_generated', ?, ?, ?)"
            )->execute([$colonyId, $sid, $f['current_base_pps'], $suggested, $reasoning]);
            $this->commit();

            return [
                'success' => true, 'id' => $sid, 'score' => $score,
                'delta_pct' => $delta, 'current' => $f['current_base_pps'],
                'suggested' => $suggested, 'confidence' => $confidence,
            ];
        } catch (\Throwable $e) {
            $this->rollBack();
            error_log('[DynamicPricingService::suggestForColony] ' . $e->getMessage());
            return ['skipped' => 'db_error', 'error' => $e->getMessage()];
        }
    }

    /** Generate suggestions for every active colony with plots. */
    public function generateAll(): array
    {
        $summary = ['generated' => 0, 'skipped' => 0, 'details' => []];
        try {
            $ids = $this->pdo->query(
                "SELECT c.id FROM colonies c
                 WHERE c.is_active = 1 AND c.deleted_at IS NULL
                   AND EXISTS (SELECT 1 FROM plots p WHERE p.colony_id = c.id AND p.deleted_at IS NULL AND p.is_active = 1)
                 ORDER BY c.id"
            )->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable $e) {
            return ['generated' => 0, 'skipped' => 0, 'details' => ['error' => $e->getMessage()]];
        }

        foreach ($ids as $id) {
            $r = $this->suggestForColony((int)$id);
            if (!empty($r['success'])) {
                $summary['generated']++;
                $summary['details'][] = ['colony_id' => (int)$id, 'delta_pct' => $r['delta_pct'], 'id' => $r['id']];
            } else {
                $summary['skipped']++;
                $summary['details'][] = ['colony_id' => (int)$id, 'skipped' => $r['skipped']];
            }
        }
        return $summary;
    }

    /**
     * Train an honest time-series model over this colony's price_history:
     * y = price_per_sqft, X = [trend, month_sin, month_cos]. Batch gradient
     * descent on standardised features. Refuses below config min_samples.
     */
    public function trainModel(int $colonyId): array
    {
        $cfg = self::config();
        try {
            $st = $this->pdo->prepare(
                "SELECT COALESCE(NULLIF(new_price_per_sqft, 0), old_price_per_sqft) AS y, created_at
                 FROM price_history
                 WHERE colony_id = ? AND COALESCE(NULLIF(new_price_per_sqft, 0), old_price_per_sqft) > 0
                   AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                 ORDER BY created_at ASC"
            );
            $st->execute([$colonyId, max(30, $cfg['lookback_days'])]);
            $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $n = count($rows);
        if ($n < max(10, $cfg['min_samples'])) {
            return [
                'success' => false,
                'error' => "Not enough history: {$n} samples, need " . max(10, $cfg['min_samples']) . " (config dynamic_pricing_min_samples).",
                'samples' => $n,
            ];
        }

        // Build standardised design matrix.
        $t0 = strtotime($rows[0]['created_at']);
        $X = [];
        $y = [];
        foreach ($rows as $r) {
            $ts = strtotime($r['created_at']);
            $m = (int)date('n', $ts);
            $X[] = [
                ($ts - $t0) / 86400 / 365.0,
                sin(2 * M_PI * $m / 12),
                cos(2 * M_PI * $m / 12),
            ];
            $y[] = (float)$r['y'];
        }

        $d = count($X[0]);
        $mean = array_fill(0, $d, 0.0);
        $std  = array_fill(0, $d, 1.0);
        for ($j = 0; $j < $d; $j++) {
            $sum = 0.0;
            foreach ($X as $row) $sum += $row[$j];
            $mu = $sum / $n;
            $var = 0.0;
            foreach ($X as $row) $var += ($row[$j] - $mu) ** 2;
            $mean[$j] = $mu;
            $std[$j] = sqrt($var / $n) > 1e-9 ? sqrt($var / $n) : 1.0;
        }
        $yMean = array_sum($y) / $n;
        $yVar = 0.0;
        foreach ($y as $v) $yVar += ($v - $yMean) ** 2;
        $yStd = sqrt($yVar / $n) > 1e-9 ? sqrt($yVar / $n) : 1.0;

        $Xs = [];
        foreach ($X as $i => $row) {
            $z = [];
            for ($j = 0; $j < $d; $j++) $z[] = ($row[$j] - $mean[$j]) / $std[$j];
            $Xs[] = $z;
        }
        $ys = array_map(fn($v) => ($v - $yMean) / $yStd, $y);

        // Batch gradient descent (intercept lives in the means: model has no
        // explicit intercept column; predictions add yMean back).
        $w = array_fill(0, $d, 0.0);
        $lr = 0.1;
        $iters = 800;
        for ($it = 0; $it < $iters; $it++) {
            $grad = array_fill(0, $d, 0.0);
            foreach ($Xs as $i => $row) {
                $pred = 0.0;
                for ($j = 0; $j < $d; $j++) $pred += $w[$j] * $row[$j];
                $err = $pred - $ys[$i];
                for ($j = 0; $d > 0 && $j < $d; $j++) $grad[$j] += $err * $row[$j];
            }
            for ($j = 0; $j < $d; $j++) $w[$j] -= $lr * $grad[$j] / $n;
        }

        // Metrics on original scale.
        $sse = 0.0;
        $sseStd = 0.0;
        $meanY = $yMean;
        $ssTot = 0.0;
        foreach ($Xs as $i => $row) {
            $pStd = 0.0;
            for ($j = 0; $j < $d; $j++) $pStd += $w[$j] * $row[$j];
            $pred = $pStd * $yStd + $yMean;
            $sse += ($y[$i] - $pred) ** 2;
            $sseStd += ($ys[$i] - $pStd) ** 2;
            $ssTot += ($y[$i] - $meanY) ** 2;
        }
        $mae = 0.0; // approx via RMSE
        $rmse = sqrt($sse / $n);
        $r2 = $ssTot > 1e-9 ? max(0.0, 1 - $sse / $ssTot) : 0.0;

        $modelData = json_encode([
            'weights' => array_map(fn($v) => round($v, 6), $w),
            'x_mean' => $mean, 'x_std' => $std,
            'y_mean' => $yMean, 'y_std' => $yStd,
        ]);
        $metrics = json_encode([
            'samples' => $n,
            'rmse' => round($rmse, 2),
            'r2' => round($r2, 4),
            'lookback_days' => $cfg['lookback_days'],
            'trained_at' => date('Y-m-d H:i:s'),
            'iterations' => $iters,
        ]);

        try {
            $ex = $this->pdo->prepare("SELECT id, model_version FROM dynamic_pricing_models WHERE colony_id = ? LIMIT 1");
            $ex->execute([$colonyId]);
            $existing = $ex->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                $newVer = 'v' . round(((float)str_replace('v', '', $existing['model_version'] ?? '1.0') + 0.1), 1);
                $this->pdo->prepare(
                    "UPDATE dynamic_pricing_models
                     SET model_name = ?, model_data = ?, feature_columns = ?, training_metrics = ?,
                         model_version = ?, is_active = 1, trained_at = NOW(), updated_at = NOW()
                     WHERE id = ?"
                )->execute([
                    'Trend + seasonality model — ' . ($rows[0] ? date('M Y', strtotime($rows[0]['created_at'])) : ''),
                    $modelData, json_encode(['trend_years', 'month_sin', 'month_cos']),
                    $metrics, $newVer, $existing['id'],
                ]);
                $modelId = (int)$existing['id'];
            } else {
                $this->pdo->prepare(
                    "INSERT INTO dynamic_pricing_models
                     (tenant_id, colony_id, model_name, model_type, model_version, model_data,
                      feature_columns, target_column, training_metrics, is_active, trained_at)
                     VALUES (1, ?, 'Trend + seasonality model', 'linear_regression', 'v1.0', ?, ?, 'price_per_sqft', ?, 1, NOW())"
                )->execute([
                    $colonyId, $modelData, json_encode(['trend_years', 'month_sin', 'month_cos']), $metrics,
                ]);
                $modelId = (int)$this->pdo->lastInsertId();
            }

            return [
                'success' => true, 'model_id' => $modelId, 'samples' => $n,
                'rmse' => round($rmse, 2), 'r2' => round($r2, 4),
            ];
        } catch (\Throwable $e) {
            error_log('[DynamicPricingService::trainModel] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listSuggestions(?string $status = null, int $limit = 50): array
    {
        try {
            $sql = "SELECT s.*, c.name AS colony_name,
                    u.name AS approved_by_name
                    FROM dynamic_pricing_suggestions s
                    LEFT JOIN colonies c ON c.id = s.colony_id
                    LEFT JOIN users u ON u.id = s.approved_by";
            $params = [];
            if ($status !== null && $status !== '') {
                $sql .= " WHERE s.status = ?";
                $params[] = $status;
            }
            $sql .= " ORDER BY s.created_at DESC, s.id DESC LIMIT " . (int)$limit;
            $st = $this->pdo->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function approve(int $id, int $userId): array
    {
        return $this->flipStatus($id, $userId, 'approved', 'approved');
    }

    public function reject(int $id, int $userId): array
    {
        return $this->flipStatus($id, $userId, 'rejected', 'rejected');
    }

    private function flipStatus(int $id, int $userId, string $to, string $logAction): array
    {
        try {
            $st = $this->pdo->prepare("SELECT id, colony_id, status, current_base_pps, suggested_base_pps FROM dynamic_pricing_suggestions WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            $s = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$s) {
                return ['success' => false, 'error' => 'Suggestion not found'];
            }
            if (!in_array($s['status'], ['pending', 'approved', 'rejected'], true)) {
                return ['success' => false, 'error' => "Already {$s['status']} — cannot change"];
            }
            $this->pdo->prepare(
                "UPDATE dynamic_pricing_suggestions SET status = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?"
            )->execute([$to, $userId, $id]);
            $this->pdo->prepare(
                "INSERT INTO pricing_optimization_logs (tenant_id, colony_id, suggestion_id, action, old_base_pps, new_base_pps, performed_by)
                 VALUES (1, ?, ?, ?, ?, ?, ?)"
            )->execute([$s['colony_id'], $id, $logAction, $s['current_base_pps'], $s['suggested_base_pps'], $userId]);

            return ['success' => true, 'status' => $to];
        } catch (\Throwable $e) {
            error_log('[DynamicPricingService::flipStatus] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Apply an approved (or pending) suggestion: rewrite ONLY `available`
     * plots of that colony through live PlcService math, write price_history
     * per plot, move colony min_price_per_sqft, audit the whole batch.
     */
    public function apply(int $id, int $userId): array
    {
        try {
            $st = $this->pdo->prepare("SELECT * FROM dynamic_pricing_suggestions WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            $s = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$s) {
                return ['success' => false, 'error' => 'Suggestion not found'];
            }
            if ($s['status'] === 'applied') {
                return ['success' => false, 'error' => 'Already applied'];
            }
            if ($s['status'] === 'rejected') {
                return ['success' => false, 'error' => 'Rejected suggestions cannot be applied'];
            }

            $newBase = (float)$s['suggested_base_pps'];
            $colonyId = (int)$s['colony_id'];

            $plots = $this->pdo->prepare(
                "SELECT id, plot_number, area_sqft, corner_plot, park_facing, road_width_ft, facing,
                        base_price_per_sqft, price_per_sqft, total_price
                 FROM plots
                 WHERE colony_id = ? AND status = 'available' AND deleted_at IS NULL AND is_active = 1"
            );
            $plots->execute([$colonyId]);
            $plots = $plots->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            if (!$plots) {
                return ['success' => false, 'error' => 'No available plots to reprice in this colony'];
            }

            $this->beginTransaction();

            $upd = $this->pdo->prepare(
                "UPDATE plots SET base_price_per_sqft = ?, price_per_sqft = ?, total_price = ?, updated_at = NOW() WHERE id = ?"
            );
            $ph = $this->pdo->prepare(
                "INSERT INTO price_history
                 (tenant_id, plot_id, colony_id, old_price, new_price, old_price_per_sqft, new_price_per_sqft,
                  change_type, reason, changed_by, reference_type, reference_id)
                 VALUES (1, ?, ?, ?, ?, ?, ?, 'base', ?, ?, 'dynamic_pricing', ?)"
            );

            $affected = 0;
            foreach ($plots as $p) {
                $calc = PlcService::calculate(
                    $newBase,
                    (float)$p['area_sqft'],
                    !empty($p['corner_plot']),
                    !empty($p['park_facing']),
                    (float)$p['road_width_ft'],
                    ['id' => (int)$p['id'], 'plot_number' => $p['plot_number'], 'colony_id' => $colonyId, 'facing' => $p['facing']]
                );
                $upd->execute([$newBase, $calc['final_pps'], $calc['total'], (int)$p['id']]);
                $ph->execute([
                    (int)$p['id'], $colonyId,
                    (float)$p['total_price'], $calc['total'],
                    (float)$p['price_per_sqft'], $calc['final_pps'],
                    "AI dynamic pricing suggestion #{$id} ({$s['price_change_pct']}%) — available plots only",
                    $userId, $id,
                ]);
                $affected++;
            }

            // Move colony anchor price.
            $this->pdo->prepare("UPDATE colonies SET min_price_per_sqft = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$newBase, $colonyId]);

            $this->pdo->prepare("UPDATE dynamic_pricing_suggestions SET status = 'applied', approved_by = COALESCE(approved_by, ?), approved_at = COALESCE(approved_at, NOW()), applied_at = NOW(), updated_at = NOW() WHERE id = ?")
                ->execute([$userId, $id]);
            $this->pdo->prepare(
                "INSERT INTO pricing_optimization_logs (tenant_id, colony_id, suggestion_id, action, old_base_pps, new_base_pps, affected_plots, performed_by, notes)
                 VALUES (1, ?, ?, 'applied', ?, ?, ?, ?, ?)"
            )->execute([$colonyId, $id, $s['current_base_pps'], $newBase, $affected, $userId, 'Rollback trail: per-plot rows in price_history (reference_type=dynamic_pricing)']);

            $this->commit();

            return ['success' => true, 'affected' => $affected, 'new_base' => $newBase];
        } catch (\Throwable $e) {
            $this->rollBack();
            error_log('[DynamicPricingService::apply] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function dashboardStats(): array
    {
        $out = [
            'pending' => 0, 'approved' => 0, 'applied_total' => 0, 'applied_month' => 0,
            'models' => 0, 'avg_confidence' => 0.0, 'colonies_covered' => 0,
        ];
        try {
            $r = $this->pdo->query(
                "SELECT
                    COALESCE(SUM(status = 'pending'), 0) AS pending,
                    COALESCE(SUM(status = 'approved'), 0) AS approved,
                    COALESCE(SUM(status = 'applied'), 0) AS applied_total,
                    COALESCE(SUM(status = 'applied' AND applied_at >= DATE_FORMAT(NOW(), '%Y-%m-01')), 0) AS applied_month,
                    COALESCE(AVG(CASE WHEN status = 'pending' THEN confidence_score END), 0) AS avg_conf,
                    COUNT(DISTINCT colony_id) AS colonies_covered
                 FROM dynamic_pricing_suggestions"
            )->fetch(\PDO::FETCH_ASSOC);
            if ($r) {
                $out['pending'] = (int)$r['pending'];
                $out['approved'] = (int)$r['approved'];
                $out['applied_total'] = (int)$r['applied_total'];
                $out['applied_month'] = (int)$r['applied_month'];
                $out['avg_confidence'] = round((float)$r['avg_conf'], 3);
                $out['colonies_covered'] = (int)$r['colonies_covered'];
            }
            $out['models'] = (int)$this->pdo->query("SELECT COUNT(*) FROM dynamic_pricing_models WHERE is_active = 1")->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[DynamicPricingService::dashboardStats] ' . $e->getMessage());
        }
        return $out;
    }

    public function getModels(): array
    {
        try {
            return $this->pdo->query(
                "SELECT m.*, c.name AS colony_name
                 FROM dynamic_pricing_models m
                 LEFT JOIN colonies c ON c.id = m.colony_id
                 ORDER BY m.trained_at DESC, m.id DESC"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Colony picker for generate/train forms. */
    public function colonies(): array
    {
        try {
            return $this->pdo->query(
                "SELECT c.id, c.name,
                        (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available' AND p.deleted_at IS NULL) AS available
                 FROM colonies c
                 WHERE c.is_active = 1 AND c.deleted_at IS NULL
                 ORDER BY c.name ASC"
            )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
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
