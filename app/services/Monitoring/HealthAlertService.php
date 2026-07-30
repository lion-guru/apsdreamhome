<?php

namespace App\Services\Monitoring;

use App\Core\Database\Database;
use Throwable;

/**
 * HealthAlertService
 *
 * Runs lightweight health checks and emits alerts into `monitoring_alerts`.
 *
 * - checkAll()      -> [status => ok|warning|critical, checks => [...]]
 * - checkDisk()
 * - checkMemory()
 * - checkDatabase()
 * - checkPhpErrors()
 * - sendAlert($severity, $source, $message, $metadata)
 *
 * Designed to be called by /scripts/monitoring_cron.php every 5 minutes.
 */
class HealthAlertService
{
    /** Disk usage thresholds (percent). */
    const DISK_WARN = 80;
    const DISK_CRIT = 90;

    /** Memory thresholds (percent of memory_limit). */
    const MEM_WARN = 80;
    const MEM_CRIT = 90;

    /** Slow DB query response (ms) above which we warn. */
    const DB_SLOW_MS_WARN = 500;
    const DB_SLOW_MS_CRIT = 2000;

    /** PHP error log: errors in last hour above which we warn / page. */
    const PHP_ERRORS_WARN = 100;
    const PHP_ERRORS_CRIT = 500;

    /** Max alerts of the same source+severity per hour (de-dup). */
    const ALERT_DEDUP_WINDOW_MIN = 60;

    /**
     * Run every check and return aggregated status.
     *
     * @return array
     */
    public function checkAll()
    {
        $checks = [
            'disk'       => $this->checkDisk(),
            'memory'     => $this->checkMemory(),
            'database'   => $this->checkDatabase(),
            'php_errors' => $this->checkPhpErrors(),
        ];

        $overall = 'ok';
        foreach ($checks as $c) {
            $st = $c['status'] ?? 'ok';
            if ($st === 'critical') { $overall = 'critical'; break; }
            if ($st === 'warning' && $overall === 'ok') { $overall = 'warning'; }
        }

        // Emit alerts for any failing check (de-duplicated by source).
        foreach ($checks as $name => $c) {
            $st = $c['status'] ?? 'ok';
            if ($st === 'warning' || $st === 'critical') {
                $this->sendAlert($st, $name, $c['message'] ?? "Check {$name} failed", $c);
            }
        }

        return [
            'status'    => $overall,
            'checked_at' => date('Y-m-d H:i:s'),
            'checks'    => $checks,
        ];
    }

    /**
     * Disk usage check (project drive).
     */
    public function checkDisk()
    {
        $path = realpath(__DIR__ . '/../../../') ?: getcwd();
        $total = @disk_total_space($path);
        $free  = @disk_free_space($path);

        if (!$total || $total <= 0) {
            return ['status' => 'warning', 'message' => 'Unable to read disk stats', 'path' => $path];
        }

        $usedPct = round((($total - $free) / $total) * 100, 2);
        $status = 'ok';
        $msg = "Disk usage {$usedPct}% on {$path}";
        if ($usedPct >= self::DISK_CRIT) { $status = 'critical'; $msg = "CRITICAL: Disk {$usedPct}% used on {$path}"; }
        elseif ($usedPct >= self::DISK_WARN) { $status = 'warning'; $msg = "WARN: Disk {$usedPct}% used on {$path}"; }

        return [
            'status'    => $status,
            'message'   => $msg,
            'used_pct'  => $usedPct,
            'free_bytes' => $free,
            'total_bytes' => $total,
            'path'      => $path,
        ];
    }

    /**
     * Memory usage check (current process vs memory_limit).
     */
    public function checkMemory()
    {
        $used = memory_get_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        if ($limit <= 0) {
            // -1 means unlimited; nothing to check.
            return ['status' => 'ok', 'message' => 'memory_limit is unlimited', 'used_bytes' => $used];
        }
        $pct = round(($used / $limit) * 100, 2);
        $status = 'ok';
        $msg = "Memory usage {$pct}%";
        if ($pct >= self::MEM_CRIT) { $status = 'critical'; $msg = "CRITICAL: Memory {$pct}% of limit"; }
        elseif ($pct >= self::MEM_WARN) { $status = 'warning'; $msg = "WARN: Memory {$pct}% of limit"; }
        return [
            'status'      => $status,
            'message'     => $msg,
            'used_pct'    => $pct,
            'used_bytes'  => $used,
            'limit_bytes' => $limit,
        ];
    }

    /**
     * Database connectivity + slow query check.
     */
    public function checkDatabase()
    {
        try {
            $db = Database::getInstance();
            $start = microtime(true);
            $db->fetchOne("SELECT 1");
            $ms = (int) round((microtime(true) - $start) * 1000);

            // Errors-table reachable as a separate readiness signal
            $tableOk = false;
            try {
                $db->fetchOne("SELECT 1 FROM monitoring_errors LIMIT 1");
                $tableOk = true;
            } catch (Throwable $e) {
                $tableOk = false;
            }

            $status = 'ok';
            $msg = "Database responded in {$ms}ms";
            if ($ms >= self::DB_SLOW_MS_CRIT) { $status = 'critical'; $msg = "CRITICAL: DB ping {$ms}ms"; }
            elseif ($ms >= self::DB_SLOW_MS_WARN) { $status = 'warning'; $msg = "WARN: DB ping {$ms}ms"; }

            if (!$tableOk) {
                $status = ($status === 'critical') ? 'critical' : 'warning';
                $msg .= '; monitoring_errors table missing';
            }

            return [
                'status'       => $status,
                'message'      => $msg,
                'response_ms'  => $ms,
                'tables_ready' => $tableOk,
            ];
        } catch (Throwable $e) {
            return [
                'status'  => 'critical',
                'message' => 'DB unreachable: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Count PHP error log entries written in the last hour.
     */
    public function checkPhpErrors()
    {
        $candidates = [
            ini_get('error_log') ?: null,
            'C:/xampp/php/logs/php_error_log',
            __DIR__ . '/../../../storage/logs/php_error.log',
            __DIR__ . '/../../../storage/logs/php-errors.log',
        ];

        $logFile = null;
        foreach ($candidates as $c) {
            if ($c && is_string($c) && @is_file($c) && @is_readable($c)) { $logFile = $c; break; }
        }
        if (!$logFile) {
            return ['status' => 'ok', 'message' => 'No PHP error log found (nothing to check)', 'count' => 0];
        }

        // Count lines from last hour (best-effort by tail-reading the last 256KB).
        try {
            $fp = @fopen($logFile, 'rb');
            if (!$fp) {
                return ['status' => 'ok', 'message' => 'Unable to open PHP error log', 'count' => 0];
            }
            $size = filesize($logFile);
            $read = min($size, 262144);
            if ($size > $read) @fseek($fp, -$read, SEEK_END);
            $buf = @fread($fp, $read) ?: '';
            @fclose($fp);

            $lines = preg_split("/\r?\n/", $buf);
            $cutoff = time() - 3600;
            $count = 0;
            foreach ($lines as $line) {
                if ($line === '' || strlen($line) < 5) continue;
                // Match: [05-Jun-2026 12:34:56 UTC] PHP Warning: ...
                if (preg_match('/^\[([^\]]+)\]/', $line, $m)) {
                    $ts = strtotime($m[1]);
                    if ($ts !== false && $ts >= $cutoff) $count++;
                }
            }

            $status = 'ok';
            $msg = "{$count} PHP errors in last hour";
            if ($count >= self::PHP_ERRORS_CRIT) { $status = 'critical'; $msg = "CRITICAL: {$count} PHP errors in last hour"; }
            elseif ($count >= self::PHP_ERRORS_WARN) { $status = 'warning'; $msg = "WARN: {$count} PHP errors in last hour"; }

            return ['status' => $status, 'message' => $msg, 'count' => $count, 'file' => $logFile];
        } catch (Throwable $e) {
            return ['status' => 'ok', 'message' => 'PHP error log read failed: ' . $e->getMessage(), 'count' => 0];
        }
    }

    /**
     * Persist an alert to monitoring_alerts and (best-effort) email admin.
     *
     * De-duplicates: silently no-ops if the same (source, severity) was logged
     * within ALERT_DEDUP_WINDOW_MIN.
     *
     * @return int|false Inserted row id, or false on no-op/failure.
     */
    public function sendAlert($severity, $source, $message, array $metadata = [])
    {
        $severity = $this->normalizeSeverity($severity);
        $source   = substr((string)$source, 0, 64);
        $message  = substr((string)$message, 0, 65000);

        $db = null;
        try {
            $db = Database::getInstance();
        } catch (Throwable $e) {
            $this->fallbackLog($severity, $source, $message);
            return false;
        }

        // De-dup window
        try {
            $window = (int) self::ALERT_DEDUP_WINDOW_MIN;
            $existing = $db->fetchOne(
                "SELECT id FROM monitoring_alerts
                 WHERE source = ? AND severity = ? AND created_at >= (NOW() - INTERVAL {$window} MINUTE)
                 ORDER BY id DESC LIMIT 1",
                [$source, $severity]
            );
            if ($existing) {
                return false;
            }
        } catch (Throwable $e) {
        // ignore; proceed to insert
        error_log($e->getMessage());
        }

        try {
            $id = $db->insert('monitoring_alerts', [
                'severity' => $severity,
                'source'   => $source,
                'message'  => $message,
                'metadata' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
            ]);
        } catch (Throwable $e) {
            $this->fallbackLog($severity, $source, $message);
            return false;
        }

        // Email admin (best-effort, never throw)
        if ($severity === 'critical' || $severity === 'warning') {
            $this->emailAdmin($severity, $source, $message);
            try {
                $db->execute("UPDATE monitoring_alerts SET notified_email = 1 WHERE id = ?", [(int)$id]);
            } catch (Throwable $e) { /* ignore */ error_log($e->getMessage()); }
        }

        return (int)$id;
    }

    /**
     * Get recent alerts.
     */
    public function getRecentAlerts($limit = 50)
    {
        $limit = max(1, min(500, (int)$limit));
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT id, severity, source, message, metadata, notified_email, resolved_at, created_at
                 FROM monitoring_alerts
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            foreach ($rows as &$row) {
                if (!empty($row['metadata']) && is_string($row['metadata'])) {
                    $decoded = json_decode($row['metadata'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $row['metadata'] = $decoded;
                    }
                }
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Cleanup: remove alerts older than 30 days.
     */
    public function cleanup($days = 30)
    {
        $days = max(1, (int)$days);
        try {
            $db = Database::getInstance();
            $stmt = $db->execute(
                "DELETE FROM monitoring_alerts WHERE created_at < (NOW() - INTERVAL {$days} DAY)"
            );
            return $stmt ? $stmt->rowCount() : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /* ---------------------------------------------------------------------
     *  Internal helpers
     * ------------------------------------------------------------------- */

    private function normalizeSeverity($severity)
    {
        $severity = strtolower(trim((string)$severity));
        if (!in_array($severity, ['info', 'warning', 'critical'], true)) {
            $severity = 'warning';
        }
        return $severity;
    }

    private function parseMemoryLimit($limit)
    {
        $limit = trim((string)$limit);
        if ($limit === '' || $limit === '-1') return -1;
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;
        switch ($last) {
            case 'g': $value *= 1024;
            // fallthrough
            case 'm': $value *= 1024;
            // fallthrough
            case 'k': $value *= 1024;
        }
        return $value;
    }

    /**
     * Best-effort email notification using PHP's mail().
     */
    private function emailAdmin($severity, $source, $message)
    {
        $to = getenv('MONITORING_ALERT_EMAIL') ?: (getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com');
        if (!$to) return;
        $subject = "[APS-Monitoring] {$severity} on {$source}";
        $body = sprintf(
            "Severity: %s\nSource: %s\nTime: %s\nHost: %s\n\n%s\n",
            strtoupper($severity), $source, date('Y-m-d H:i:s'),
            gethostname() ?: 'unknown', $message
        );
        $headers = "From: monitoring@apsdreamhome.com\r\nContent-Type: text/plain; charset=utf-8\r\n";
        @mail($to, $subject, $body, $headers);
    }

    private function fallbackLog($severity, $source, $message)
    {
        $file = __DIR__ . '/../../../storage/logs/monitoring_alerts.log';
        $line = sprintf("[%s] [%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($severity), $source, $message);
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
