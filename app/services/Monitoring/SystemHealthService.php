<?php
namespace App\Services\Monitoring;

class SystemHealthService
{
    private $db;
    private $pdo;
    private $startTime;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
        $this->startTime = microtime(true);
    }

    public function getFullReport(): array
    {
        return [
            'php' => $this->checkPhp(),
            'database' => $this->checkDatabase(),
            'disk' => $this->checkDisk(),
            'memory' => $this->checkMemory(),
            'cache' => $this->checkCache(),
            'tables' => $this->checkTables(),
            'services' => $this->checkServices(),
            'execution_time_ms' => round((microtime(true) - $this->startTime) * 1000, 2),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function checkPhp(): array
    {
        return [
            'version' => PHP_VERSION,
            'os' => PHP_OS,
            'sapi' => PHP_SAPI,
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'curl' => extension_loaded('curl'),
                'gd' => extension_loaded('gd'),
                'zip' => extension_loaded('zip'),
                'json' => extension_loaded('json'),
            ],
            'status' => 'ok'
        ];
    }

    public function checkDatabase(): array
    {
        $result = ['status' => 'ok', 'tables' => 0, 'size_mb' => 0, 'queries_per_sec' => 0, 'uptime_days' => 0];
        try {
            $st = $this->db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
            $result['tables'] = (int)$st->fetchColumn();

            $st = $this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_schema = DATABASE()");
            $result['size_mb'] = (float)$st->fetchColumn();

            $start = microtime(true);
            for ($i = 0; $i < 10; $i++) {
                $this->db->query("SELECT 1");
            }
            $elapsed = microtime(true) - $start;
            $result['queries_per_sec'] = $elapsed > 0 ? round(10 / $elapsed, 1) : 0;

            $st = $this->db->query("SHOW STATUS LIKE 'Uptime'");
            $uptime = (int)($st->fetch(PDO::FETCH_ASSOC)['Value'] ?? 0);
            $result['uptime_days'] = round($uptime / 86400, 1);

            $result['version'] = $this->db->query("SELECT VERSION()")->fetchColumn();
        } catch (\Throwable $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }
        return $result;
    }

    public function checkDisk(): array
    {
        $total = disk_total_space(__DIR__);
        $free = disk_free_space(__DIR__);
        $used = $total - $free;
        return [
            'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            'used_gb' => round($used / 1024 / 1024 / 1024, 2),
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'used_pct' => round(($used / $total) * 100, 1),
            'status' => $used / $total > 0.9 ? 'warning' : 'ok'
        ];
    }

    public function checkMemory(): array
    {
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        return [
            'used_mb' => round($used / 1024 / 1024, 2),
            'peak_mb' => round($peak / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit'),
            'status' => 'ok'
        ];
    }

    public function checkCache(): array
    {
        $cacheDir = __DIR__ . '/../storage/cache';
        $files = is_dir($cacheDir) ? glob($cacheDir . '/*') : [];
        $totalSize = 0;
        foreach ($files as $f) {
            if (is_file($f)) $totalSize += filesize($f);
        }
        return [
            'files' => count($files),
            'size_mb' => round($totalSize / 1024 / 1024, 2),
            'writable' => is_writable($cacheDir),
            'path' => $cacheDir,
            'status' => is_writable($cacheDir) ? 'ok' : 'warning'
        ];
    }

    public function checkTables(): array
    {
        $tables = ['users', 'leads', 'properties', 'plots', 'bookings', 'commissions', 'audit_log', 'webhook_endpoints'];
        $result = ['checked' => 0, 'ok' => 0, 'missing' => []];
        foreach ($tables as $t) {
            $result['checked']++;
            try {
                $st = $this->db->query("SELECT 1 FROM `$t` LIMIT 1");
                $st->fetch();
                $result['ok']++;
            } catch (\Throwable $e) {
                $result['missing'][] = $t;
            }
        }
        $result['status'] = empty($result['missing']) ? 'ok' : 'warning';
        return $result;
    }

    public function checkServices(): array
    {
        $services = [
            'AI' => 'app/Services/AI/AIManager.php',
            'Webhooks' => 'app/Services/WebhookService.php',
            'Notifications' => 'app/Services/NotificationCenter.php',
            'Audit' => 'app/Services/AuditService.php',
            '2FA' => 'app/Services/TotpService.php',
            'Bulk Ops' => 'app/Services/BulkOperationsService.php',
            'API Keys' => 'app/Services/ApiKeyService.php',
            'Cron' => 'app/Http/Controllers/System/CronController.php',
        ];
        $result = [];
        foreach ($services as $name => $path) {
            $fullPath = realpath(__DIR__ . '/../../' . $path);
            $result[$name] = [
                'loaded' => $fullPath && file_exists($fullPath),
                'path' => $path,
                'size_kb' => $fullPath ? round(filesize($fullPath) / 1024, 1) : 0
            ];
        }
        return $result;
    }
}
