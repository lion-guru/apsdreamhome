<?php

namespace App\Services\Monitoring;

use App\Core\Database\Database;
use Throwable;

/**
 * ErrorTrackerService
 *
 * Lightweight error capture and persistence to the `monitoring_errors` table.
 *
 * Static facade so it can be called from anywhere (e.g., exception handlers)
 * without depending on a container or DI.
 *
 * - captureException($e, $context) — log a Throwable
 * - captureMessage($msg, $level, $context) — log an arbitrary message
 * - getRecent($limit) — most recent errors
 * - getStats() — counts by level for last 24h
 * - cleanup() — purge >30 days old (called by cron)
 */
class ErrorTrackerService
{
    /** Maximum age in days for retained error rows. */
    const RETENTION_DAYS = 30;

    /** Valid levels (any other value is normalized to 'error'). */
    const LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];

    /** Cached db instance, reused across calls. */
    private static $db = null;

    /** Last-resort fallback log file when DB is unavailable. */
    const FALLBACK_LOG = __DIR__ . '/../../../storage/logs/monitoring_fallback.log';

    /**
     * Get / lazily create the Database instance.
     */
    private static function db()
    {
        if (self::$db !== null) {
            return self::$db;
        }
        try {
            self::$db = Database::getInstance();
        } catch (Throwable $e) {
            self::$db = false;
        }
        return self::$db;
    }

    /**
     * Capture a Throwable (Exception or Error).
     *
     * @param Throwable $exception
     * @param array     $context  Extra metadata (request, user, etc.)
     * @return int|false Inserted row id, or false on failure.
     */
    public static function captureException($exception, array $context = [])
    {
        if (!($exception instanceof Throwable)) {
            return false;
        }

        $level = isset($context['level']) ? self::normalizeLevel($context['level']) : 'error';
        unset($context['level']);

        $context['exception_class'] = get_class($exception);
        if (method_exists($exception, 'getCode')) {
            $context['code'] = $exception->getCode();
        }

        return self::persist([
            'level'   => $level,
            'message' => $exception->getMessage() ?: get_class($exception),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'context' => $context,
            'trace'   => substr($exception->getTraceAsString(), 0, 16000),
            'user_id' => self::currentUserId(),
        ]);
    }

    /**
     * Capture an arbitrary message.
     *
     * @param string $message
     * @param string $level   debug|info|notice|warning|error|critical
     * @param array  $context
     * @return int|false
     */
    public static function captureMessage($message, $level = 'error', array $context = [])
    {
        $level = self::normalizeLevel($level);

        $file = null;
        $line = null;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (isset($trace[0])) {
            $file = $trace[0]['file'] ?? null;
            $line = $trace[0]['line'] ?? null;
        }

        return self::persist([
            'level'   => $level,
            'message' => (string)$message,
            'file'    => $file,
            'line'    => $line,
            'context' => $context,
            'trace'   => null,
            'user_id' => self::currentUserId(),
        ]);
    }

    /**
     * Get recent error rows, newest first.
     *
     * @param int $limit
     * @return array
     */
    public static function getRecent($limit = 50)
    {
        $limit = max(1, min(500, (int)$limit));
        $db = self::db();
        if (!$db) {
            return [];
        }
        try {
            $rows = $db->fetchAll(
                "SELECT id, level, message, file, line, context, user_id, environment, created_at
                 FROM monitoring_errors
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            foreach ($rows as &$row) {
                if (!empty($row['context']) && is_string($row['context'])) {
                    $decoded = json_decode($row['context'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $row['context'] = $decoded;
                    }
                }
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Aggregate counts by level for the last 24 hours.
     *
     * @return array [level => count, '_total' => N, '_window_hours' => 24]
     */
    public static function getStats()
    {
        $db = self::db();
        $stats = [
            '_total' => 0,
            '_window_hours' => 24,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0,
        ];
        if (!$db) {
            return $stats;
        }
        try {
            $rows = $db->fetchAll(
                "SELECT level, COUNT(*) AS cnt
                 FROM monitoring_errors
                 WHERE created_at >= (NOW() - INTERVAL 24 HOUR)
                 GROUP BY level"
            );
            foreach ($rows as $r) {
                $lvl = strtolower($r['level'] ?? '');
                if (!isset($stats[$lvl])) $stats[$lvl] = 0;
                $stats[$lvl] = (int)$r['cnt'];
                $stats['_total'] += (int)$r['cnt'];
            }
        } catch (Throwable $e) {
        // swallow
        error_log($e->getMessage());
        }
        return $stats;
    }

    /**
     * Auto-cleanup: delete rows older than RETENTION_DAYS.
     *
     * @return int Number of rows deleted.
     */
    public static function cleanup()
    {
        $db = self::db();
        if (!$db) return 0;
        try {
            $days = (int) self::RETENTION_DAYS;
            $stmt = $db->execute(
                "DELETE FROM monitoring_errors WHERE created_at < (NOW() - INTERVAL {$days} DAY)"
            );
            return $stmt ? $stmt->rowCount() : 0;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Persist a row to the DB. Falls back to a file log if DB is unreachable.
     *
     * @param array $row
     * @return int|false
     */
    private static function persist(array $row)
    {
        $db = self::db();
        if (!$db) {
            self::fallbackLog($row);
            return false;
        }

        $env = getenv('APP_ENV') ?: (defined('APP_ENV') ? APP_ENV : 'production');

        $data = [
            'level'       => $row['level'] ?? 'error',
            'message'     => substr((string)($row['message'] ?? ''), 0, 65000),
            'file'        => $row['file'] ?? null,
            'line'        => isset($row['line']) ? (int)$row['line'] : null,
            'context'     => !empty($row['context']) ? json_encode($row['context'], JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
            'user_id'     => $row['user_id'] ?? null,
            'trace'       => $row['trace'] ?? null,
            'environment' => substr((string)$env, 0, 32),
        ];

        try {
            $id = $db->insert('monitoring_errors', $data);
            return (int)$id;
        } catch (Throwable $e) {
            self::fallbackLog($row);
            return false;
        }
    }

    /**
     * Normalize an arbitrary level string to one of LEVELS.
     */
    private static function normalizeLevel($level)
    {
        $level = strtolower(trim((string)$level));
        return in_array($level, self::LEVELS, true) ? $level : 'error';
    }

    /**
     * Resolve current user id from $_SESSION (best-effort).
     */
    private static function currentUserId()
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) return null;
        foreach (['user_id', 'admin_id', 'customer_id', 'associate_id', 'employee_id'] as $key) {
            if (!empty($_SESSION[$key])) return (int)$_SESSION[$key];
        }
        return null;
    }

    /**
     * Fallback file log when DB persistence fails.
     */
    private static function fallbackLog(array $row)
    {
        $line = sprintf(
            "[%s] [%s] %s @ %s:%d %s\n",
            date('Y-m-d H:i:s'),
            $row['level'] ?? 'error',
            substr((string)($row['message'] ?? ''), 0, 500),
            $row['file'] ?? 'unknown',
            (int)($row['line'] ?? 0),
            !empty($row['context']) ? json_encode($row['context']) : ''
        );
        @file_put_contents(self::FALLBACK_LOG, $line, FILE_APPEND | LOCK_EX);
    }
}
