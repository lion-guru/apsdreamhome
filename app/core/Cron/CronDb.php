<?php

namespace App\Core\Cron;

/**
 * CronDb — outage-aware DB connector for cron scripts.
 *
 * Problem it solves: when MySQL is down, every hourly cron error_logs an
 * identical "connection refused" line. That spam buries real application
 * errors in php_error_log (see S217 collation hunt).
 *
 * Contract:
 *   $db = CronDb::connection('low_stock');
 *   if ($db === null) { echo "DB unreachable, alert suppressed\n"; exit(0); }
 *
 * - Retries transient blips (default 2 retries, 400ms apart). A fresh
 *   Database::getInstance() is safe to retry: a throwing constructor
 *   never assigns the singleton.
 * - On sustained outage: logs ONCE per outage window (flag file in
 *   storage/), stays silent until recovery. On recovery: logs once.
 * - Exit code stays 0 to preserve existing scheduler semantics.
 * - App-level query failures are NOT suppressed — only connection
 *   establishment goes through here.
 */
class CronDb
{
    public static function connection(string $cron, int $retries = 2, int $delayMs = 400): ?\PDO
    {
        $last = null;
        for ($i = 0; $i <= $retries; $i++) {
            try {
                $pdo = \App\Core\Database\Database::getInstance()->getConnection();
                self::clearOutage($cron);
                return $pdo;
            } catch (\Throwable $e) {
                $last = $e;
                if ($i < $retries) usleep($delayMs * 1000);
            }
        }
        self::noteOutage($cron, $last);
        return null;
    }

    private static function flagFile(string $cron): string
    {
        $safe = preg_replace('/[^a-z0-9_-]/i', '', $cron);
        return dirname(__DIR__, 3) . '/storage/cron_db_outage_' . $safe . '.flag';
    }

    private static function noteOutage(string $cron, ?\Throwable $e): void
    {
        $flag = self::flagFile($cron);
        if (!file_exists($flag)) {
            @file_put_contents($flag, date('c') . ' ' . ($e ? $e->getMessage() : 'unknown'));
            error_log("cron_{$cron}: DB unreachable, suppressing further alerts until recovery: " . ($e ? $e->getMessage() : 'unknown'));
        }
    }

    private static function clearOutage(string $cron): void
    {
        $flag = self::flagFile($cron);
        if (file_exists($flag)) {
            @unlink($flag);
            error_log("cron_{$cron}: DB recovered, alerting resumed");
        }
    }
}
