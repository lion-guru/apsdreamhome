<?php

namespace App\Http\Controllers\Admin;

class AdminPerformanceController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $phpVersion = phpversion();
            $mysqlVersion = $this->db->query("SELECT VERSION()")->fetchColumn();
            $memoryUsed = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryLimitBytes = $memoryLimit === '-1' ? PHP_INT_MAX : (int)$memoryLimit * 1024 * 1024;
            $diskFree = @disk_free_space('C:\\');
            $diskTotal = @disk_total_space('C:\\');
            $diskUsed = $diskTotal - $diskFree;
            $uptime = @php_uname('s') . ' ' . @php_uname('r');
            $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
            $activeConn = (int)($this->db->query("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE db = DATABASE()")->fetchColumn());
            $totalTables = (int)($this->db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()")->fetchColumn());
            $totalRows = (int)($this->db->query("SELECT SUM(TABLE_ROWS) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'")->fetchColumn());
            $dbSize = (float)($this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()")->fetchColumn());
            $extensions = ['pdo_mysql','mysqli','mbstring','openssl','curl','gd','zip','json','intl','sockets','redis','opcache'];
            $loadedExtensions = get_loaded_extensions();
            $opcacheEnabled = function_exists('opcache_get_status') ? @opcache_get_status()['opcache_enabled'] : false;
            $opcacheHits = 0;
            $opcacheMisses = 0;
            if ($opcacheEnabled) {
                $st = @opcache_get_status();
                $opcacheHits = $st['opcache_statistics']['num_hits'] ?? 0;
                $opcacheMisses = $st['opcache_statistics']['num_misses'] ?? 0;
            }
            $slowQueries = (int)($this->db->query("SHOW GLOBAL STATUS LIKE 'Slow_queries'")->fetchColumn());
            $totalQueries = (int)($this->db->query("SHOW GLOBAL STATUS LIKE 'Queries'")->fetchColumn());
        } catch (\Exception $e) {
            $phpVersion = phpversion();
            $mysqlVersion = $memoryUsed = $memoryPeak = $memoryLimitBytes = $diskFree = $diskTotal = $diskUsed = 0;
            $uptime = $serverSoftware = '';
            $activeConn = $totalTables = $totalRows = 0;
            $dbSize = 0;
            $extensions = [];
            $loadedExtensions = [];
            $opcacheEnabled = false;
            $opcacheHits = $opcacheMisses = $slowQueries = $totalQueries = 0;
        }
        return $this->render('admin/performance/index', [
            'page_title' => 'Performance - APS Dream Home',
            'page_heading' => 'System Performance',
            'phpVersion' => $phpVersion,
            'mysqlVersion' => $mysqlVersion,
            'memoryUsed' => $memoryUsed,
            'memoryPeak' => $memoryPeak,
            'memoryLimit' => $memoryLimit,
            'memoryLimitBytes' => $memoryLimitBytes,
            'diskFree' => $diskFree,
            'diskTotal' => $diskTotal,
            'diskUsed' => $diskUsed,
            'uptime' => $uptime,
            'serverSoftware' => $serverSoftware,
            'activeConn' => $activeConn,
            'totalTables' => $totalTables,
            'totalRows' => $totalRows,
            'dbSize' => $dbSize,
            'extensions' => $extensions,
            'loadedExtensions' => $loadedExtensions,
            'opcacheEnabled' => $opcacheEnabled,
            'opcacheHits' => $opcacheHits,
            'opcacheMisses' => $opcacheMisses,
            'slowQueries' => $slowQueries,
            'totalQueries' => $totalQueries,
        ]);
    }
}
