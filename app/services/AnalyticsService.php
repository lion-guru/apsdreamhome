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
        $st = $this->db->prepare("INSERT INTO kpis (kpi_code, name, category, unit, target_value, frequency, active, created_at) VALUES (:c, :n, :cat, :u, :t, :f, 1, NOW())
                                  ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category), unit = VALUES(unit), target_value = VALUES(target_value), frequency = VALUES(frequency), active = 1");
        $st->execute([':c' => $code, ':n' => $name, ':cat' => $category, ':u' => $unit, ':t' => $target, ':f' => $frequency]);
        return ['ok' => true];
    }

    public function recordKpi(int $kpiId, float $actual, ?int $employeeId = null, string $period = null, array $metadata = []): array
    {
        $insertData = $this->tenantInsertData();
        $cols = "kpi_id, employee_id, period, actual_value, metadata, recorded_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, ?, NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $p = [':k' => $kpiId, ':e' => $employeeId, ':p' => $period ?: date('Y-m'), ':a' => $actual, ':m' => json_encode($metadata, JSON_UNESCAPED_UNICODE)];
        if (!empty($insertData)) $p = array_merge($p, array_values($insertData));
        $st = $this->db->prepare("INSERT INTO employee_kpis ($cols) VALUES ($ph)
                                  ON DUPLICATE KEY UPDATE actual_value = VALUES(actual_value), metadata = VALUES(metadata), recorded_at = NOW()");
        $st->execute($p);
        return ['ok' => true, 'id' => (int)$this->db->lastInsertId()];
    }

    public function getKpiPerformance(int $kpiId, int $months = 6): array
    {
        $st = $this->db->prepare("SELECT period, AVG(actual_value) as avg_val, MIN(actual_value) as min_val, MAX(actual_value) as max_val, COUNT(*) as sample_count
                                  FROM employee_kpis WHERE kpi_id = :k AND recorded_at > DATE_SUB(NOW(), INTERVAL :m MONTH)" . $this->tenantSql() . " GROUP BY period ORDER BY period DESC");
        $st->execute([':k' => $kpiId, ':m' => $months]);
        if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
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
        $st = $this->db->prepare("SELECT metric_date, value FROM daily_metrics_summary WHERE metric_name = :n AND metric_date > DATE_SUB(CURDATE(), INTERVAL :d DAY)" . $this->tenantSql() . " ORDER BY metric_date DESC");
        $st->execute([':n' => $name, ':d' => $days]);
        if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listBenchmarks(string $category = ''): array
    {
        $sql = "SELECT * FROM performance_benchmarks WHERE active = 1";
        $params = [];
        if ($category) { $sql .= " AND category = :c"; $params[':c'] = $category; }
        $sql .= " ORDER BY category, name";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setBenchmark(string $category, string $name, float $target, float $min, float $max, string $unit = ''): array
    {
        $st = $this->db->prepare("INSERT INTO performance_benchmarks (category, name, target_value, min_value, max_value, unit, active, created_at) VALUES (:c, :n, :t, :mi, :ma, :u, 1, NOW())
                                  ON DUPLICATE KEY UPDATE target_value = VALUES(target_value), min_value = VALUES(min_value), max_value = VALUES(max_value), unit = VALUES(unit), active = 1");
        $st->execute([':c' => $category, ':n' => $name, ':t' => $target, ':mi' => $min, ':ma' => $max, ':u' => $unit]);
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

        $st = $this->db->prepare("INSERT INTO forecast_results (metric_name, method, periods, predictions, r_squared, generated_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ") VALUES (:m, :me, :p, :pr, :r, NOW()" . (count($this->tenantInsertData()) > 0 ? ', :tid' : '') . ")");
        $fparams = [':m' => $metric, ':me' => $method, ':p' => $periods, ':pr' => json_encode($predictions, JSON_UNESCAPED_UNICODE), ':r' => $rSquared];
        if (!empty($insertData = $this->tenantInsertData())) $fparams = array_merge($fparams, $insertData);
        $st->execute($fparams);
        $id = (int)$this->db->lastInsertId();

        return ['ok' => true, 'id' => $id, 'metric' => $metric, 'predictions' => $predictions, 'r_squared' => $rSquared, 'method' => $method];
    }

    public function listForecasts(int $limit = 20): array
    {
        try {
            $st = $this->db->prepare("SELECT * FROM forecast_results" . $this->tenantSql() . " ORDER BY generated_at DESC LIMIT :lim");
            $st->bindValue(':lim', $limit, PDO::PARAM_INT);
            if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getMarketSummary(int $days = 30): array
    {
        $st = $this->db->prepare("SELECT * FROM market_analytics_summary WHERE summary_date > DATE_SUB(CURDATE(), INTERVAL :d DAY)" . $this->tenantSql() . " ORDER BY summary_date DESC LIMIT 30");
        $st->execute([':d' => $days]);
        if ($this->tenantId() > 1) $st->bindValue(':stid', $this->tenantId(), PDO::PARAM_INT);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordMarketSummary(string $category, float $value, array $data = []): array
    {
        $insertData = $this->tenantInsertData();
        $cols = "category, value, summary_data, summary_date, created_at" . (count($insertData) > 0 ? ', ' . implode(', ', array_keys($insertData)) : '');
        $ph = "?, ?, ?, ?, NOW()" . (count($insertData) > 0 ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '');
        $params = [$category, $value, json_encode($data, JSON_UNESCAPED_UNICODE), date('Y-m-d')];
        if (!empty($insertData)) $params = array_merge($params, array_values($insertData));
        $st = $this->db->prepare("INSERT INTO market_analytics_summary ($cols) VALUES ($ph)
                                  ON DUPLICATE KEY UPDATE value = VALUES(value), summary_data = VALUES(summary_data)");
        $st->execute($params);
        return ['ok' => true];
    }

    public function listDashboards(int $userId = 0): array
    {
        try {
            $sql = "SELECT * FROM analytics_dashboards WHERE 1=1" . $this->tenantSql();
            $params = [];
            if ($userId) { $sql .= " AND (owner_user_id = :u OR is_public = 1)"; $params[':u'] = $userId; }
            $sql .= " ORDER BY created_at DESC";
            $st = $this->db->prepare($sql);
            if ($this->tenantId() > 1) $params[':stid'] = $this->tenantId();
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
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
        $st = $this->db->prepare("SELECT * FROM analytics_dashboards WHERE id = :id" . $this->tenantSql());
        $sparams = [':id' => $id];
        if ($this->tenantId() > 1) $sparams[] = $this->tenantId();
        $st->execute($sparams);
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
