<?php
namespace App\Services;

use PDO;
use \App\Traits\ServiceTenantTrait;

/**
 * AnalyticsService - KPIs, dashboards, forecasting, performance metrics
 */
class AnalyticsService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }

    public function listKpis(string $category = ''): array
    {
        try {
            $sql = "SELECT * FROM kpis WHERE is_active = 1";
            $params = [];
            if ($category) { $sql .= " AND category = :c"; $params[':c'] = $category; }
            $sql .= " ORDER BY category, name";
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function createKpi(string $code, string $name, string $category, string $unit, float $target, string $frequency = 'monthly'): array
    {
        $st = $this->db->prepare("INSERT INTO kpis (name, category, unit, default_target, is_active, created_at) VALUES (:n, :cat, :u, :t, 1, NOW())
                                  ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category), unit = VALUES(unit), default_target = VALUES(default_target), is_active = 1");
        $st->execute([':n' => $name, ':cat' => $category, ':u' => $unit, ':t' => $target]);
        return ['ok' => true];
    }

    public function recordKpi(int $kpiId, float $actual, ?int $employeeId = null, string $period = null, array $metadata = []): array
    {
        $insertData = $this->tenantInsertData();
        // Live schema: period_start/period_end (DATE), target_value / achievement_percentage / score /
        // status (enum active|completed|cancelled) / comments / created_at. The designed `period`,
        // `metadata` and `recorded_at` columns were never migrated to employee_kpis; we map honestly.
        $label = preg_match('/^\d{4}-\d{2}$/', (string)$period) ? (string)$period : date('Y-m');
        $start = $label . '-01';
        $end = date('Y-m-t', strtotime($start));
        $kpi = $this->db->fetchOne("SELECT default_target, unit FROM kpis WHERE id = ?", [$kpiId]);
        $target = $kpi ? (float)$kpi['default_target'] : 0.0;
        $ach = $target > 0 ? min(999.99, round(($actual / $target) * 100, 2)) : 0.0;
        // `score` repurposed as normalized achievement ratio (0.00-1.99); live column is decimal(3,2)
        // with no documented semantics upstream, so we bound it here rather than store garbage.
        $score = min(round($ach / 100, 2), 1.99);
        $cols = "kpi_id, employee_id, period_start, period_end, target_value, actual_value, achievement_percentage, score, status, comments, created_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $params = [$kpiId, (int)$employeeId, $start, $end, $target, $actual, $ach, min($score, 9.99), empty($metadata) ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE)];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st = $this->db->prepare("INSERT INTO employee_kpis ($cols) VALUES ($ph)");
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getKpiPerformance(int $kpiId, int $months = 6): array
    {
        $cutoff = date('Y-m-d', strtotime('-' . max(1, (int)$months) . ' months'));
        $st = $this->db->prepare("SELECT period_start, AVG(actual_value) as avg_val, MIN(actual_value) as min_val, MAX(actual_value) as max_val, COUNT(*) as sample_count, AVG(achievement_percentage) as avg_ach
                                  FROM employee_kpis WHERE kpi_id = ? AND period_start > ?" . $this->tenantSql() . " GROUP BY period_start ORDER BY period_start DESC");
        $st->execute([$kpiId, $cutoff]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordDailyMetric(string $metricName, float $value, string $category = 'general', array $dimensions = []): array
    {
        $insertData = $this->tenantInsertData();
        $cols = "metric_name, category, value, dimensions, metric_date, created_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $params = [$metricName, $category, $value, json_encode($dimensions, JSON_UNESCAPED_UNICODE), date('Y-m-d')];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st = $this->db->prepare("INSERT INTO daily_metrics_summary ($cols) VALUES ($ph)
                                  ON DUPLICATE KEY UPDATE value = VALUES(value), dimensions = VALUES(dimensions)");
        $st->execute($params);

        // WebSocket broadcast - real-time dashboards subscribed to analytics_global
        // receive an event the moment a metric is recorded. Best-effort, never throws.
        try {
            \App\Services\WebSocketBroadcaster::broadcastAnalytics([
                'event' => 'metric_recorded',
                'metric' => $metricName,
                'category' => $category,
                'value' => (float)$value,
                'dimensions' => $dimensions,
                'ts' => time()
            ], 'analytics_global');
        } catch (\Throwable $e) {
            // Log only; never propagate.
            error_log("AnalyticsService::recordDailyMetric WS broadcast failed: " . $e->getMessage());
        }

        return ['ok' => true];
    }

    public function getDailyMetrics(string $name, int $days = 30): array
    {
        $cutoff = date('Y-m-d', strtotime('-' . max(1, (int)$days) . ' days'));
        $st = $this->db->prepare("SELECT metric_date, value FROM daily_metrics_summary WHERE metric_name = ? AND metric_date > ?" . $this->tenantSql() . " ORDER BY metric_date DESC");
        $st->execute([$name, $cutoff]);
        if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listBenchmarks(string $category = ''): array
    {
        // performance_benchmarks actually holds (benchmark_name, role, metric, baseline_value,
        // target_value, excellent_value, ...) — no `active`, `category`, or `name` columns.
        // Return the full list ordered by metric; callers that filter must use the live columns.
        $st = $this->db->prepare("SELECT * FROM performance_benchmarks WHERE 1=1" . $this->tenantSql() . " ORDER BY metric, benchmark_name");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setBenchmark(string $category, string $name, float $target, float $min, float $max, string $unit = ''): array
    {
        $t = $this->tenantId() > 1 ? $this->tenantId() : 1;
        $stm = $this->db->prepare("INSERT INTO performance_benchmarks (metric, benchmark_name, baseline_value, target_value, excellent_value, tenant_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stm->execute([$category, $name, $min, $target, $max, (int)$t]);
        return ['ok' => true];
    }

    public function generateForecast(string $metric, int $periods = 6, string $method = 'linear'): array
    {
        $history = $this->getDailyMetrics($metric, 90);
        if (count($history) < 3) return ['error' => 'Insufficient data for forecast'];

        $values = array_reverse(array_column($history, 'value'));
        $n = count($values);

        $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumX += $i; $sumY += $values[$i];
            $sumXY += $i * $values[$i]; $sumX2 += $i * $i;
        }
        $slope = ($n * $sumXY - $sumX * $sumY) / max(1, $n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $predictions = [];
        for ($i = 0; $i < $periods; $i++) {
            $x = $n + $i;
            $predictions[] = ['period' => $i + 1, 'predicted' => round($intercept + $slope * $x, 2)];
        }

        $ssRes = 0; $ssTot = 0; $meanY = $sumY / $n;
        for ($i = 0; $i < $n; $i++) {
            $predicted = $intercept + $slope * $i;
            $ssRes += pow($values[$i] - $predicted, 2);
            $ssTot += pow($values[$i] - $meanY, 2);
        }
        $rSquared = $ssTot > 0 ? round(1 - $ssRes / $ssTot, 4) : 0;

        $st = $this->db->prepare("INSERT INTO forecast_results (metric_name, method, periods, predictions, r_squared, created_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:m, :me, :p, :pr, :r, NOW()" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
        $fparams = [':m' => $metric, ':me' => $method, ':p' => $periods, ':pr' => json_encode($predictions, JSON_UNESCAPED_UNICODE), ':r' => $rSquared];
        if (!empty($insertData = $this->tenantInsertData())) $fparams = array_merge($fparams, $insertData);
        $st->execute($fparams);
        $id = (int)$this->db->lastInsertId();

        return ['ok' => true, 'id' => $id, 'metric' => $metric, 'predictions' => $predictions, 'r_squared' => $rSquared, 'method' => $method];
    }

    public function listForecasts(int $limit = 20): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM forecast_results" . $this->tenantSql() . " ORDER BY created_at DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    // S778: getMarketSummary()/recordMarketSummary() were removed — both had
    // ZERO callers and referenced market_analytics_summary, a table that was
    // never migrated (S682 'designed but never wired'). Creating a table for
    // dead code would be S221-class duplication; the schema gate now stays
    // green without manufacturing a feature nobody calls.

    public function listDashboards(int $userId = 0): array
    {
        try {
            // Live schema: analytics_dashboards(id, tenant_id, user_id, name, widgets, is_public, created_at, updated_at)
            // Tenant scoping via tenantWhere() (interpolated literal matches bind ?), not tenantSql() + named parameter mix.
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [];
            if ($tid > 1) $params[] = $tid;
            if ($userId > 0) {
                $sql = "SELECT * FROM analytics_dashboards WHERE (user_id = ? OR is_public = 1)" . $tidSql . " ORDER BY created_at DESC";
                array_unshift($params, $userId);
            } else {
                $sql = "SELECT * FROM analytics_dashboards WHERE 1=1" . $tidSql . " ORDER BY created_at DESC";
            }
            $st = $this->db->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AnalyticsService::listDashboards failed: " . $e->getMessage());
            return [];
        }
    }

    public function createDashboard(int $userId, string $name, array $widgets, bool $isPublic = false): array
    {
        $insertData = $this->tenantInsertData();
        $cols = "user_id, name, widgets, is_public, created_at, updated_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, NOW(), NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $params = [$userId, $name, json_encode($widgets, JSON_UNESCAPED_UNICODE), $isPublic ? 1 : 0];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st = $this->db->prepare("INSERT INTO analytics_dashboards ($cols) VALUES ($ph)");
        $st->execute($params);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getDashboard(int $id): ?array
    {
        $tid = $this->tenantId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$id];
        if ($tid > 1) $params[] = $tid;
        $st = $this->db->prepare("SELECT * FROM analytics_dashboards WHERE id = ?" . $tidSql);
        $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        $r['widgets'] = json_decode($r['widgets'] ?? '[]', true) ?: [];
        return $r;
    }

    public function comprehensiveDashboard(): array
    {
        try {
            $st = $this->db->query("SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 'active'" . $this->tenantSqlForAlias('users') . ") as customers,
                (SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active'" . $this->tenantSqlForAlias('users') . ") as agents,
                (SELECT COUNT(*) FROM users WHERE role = 'associate' AND status = 'active'" . $this->tenantSqlForAlias('users') . ") as associates,
                (SELECT COUNT(*) FROM users WHERE role = 'employee' AND status = 'active'" . $this->tenantSqlForAlias('users') . ") as employees,
                (SELECT COUNT(*) FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSqlForAlias('leads') . ") as leads_30d,
                (SELECT COUNT(*) FROM bookings WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSqlForAlias('bookings') . ") as bookings_30d,
                (SELECT COUNT(*) FROM plots WHERE status = 'available'" . $this->tenantSqlForAlias('plots') . ") as available_plots,
                (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" . $this->tenantSqlForAlias('payments') . ") as revenue_30d");
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
