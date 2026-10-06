<?php
namespace App\Http\Controllers\Admin;

/**
 * Database Monitor (read-only)
 * Table sizes, row counts, server stats — no writes, no raw SQL execution.
 */
class DatabaseMonitorController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $overview = ['version' => '?', 'uptime' => null, 'threads' => null, 'slow_queries' => null, 'db_size_mb' => 0, 'table_count' => 0];
        $tables = [];
        $error = null;

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            $v = $db->query("SHOW VARIABLES LIKE 'version'")->fetch(\PDO::FETCH_ASSOC);
            if ($v) $overview['version'] = $v['Value'];

            foreach (['Uptime' => 'uptime', 'Threads_connected' => 'threads', 'Slow_queries' => 'slow_queries'] as $k => $to) {
                try {
                    $r = $db->query("SHOW GLOBAL STATUS LIKE '$k'")->fetch(\PDO::FETCH_ASSOC);
                    if ($r) $overview[$to] = $r['Value'];
                } catch (\Throwable $e) { /* restricted on some hosts */ }
            }

            $sql = "SELECT table_name, table_rows,
                        ROUND((data_length + index_length) / 1048576, 2) AS size_mb,
                        ROUND(data_length / 1048576, 2) AS data_mb,
                        ROUND(index_length / 1048576, 2) AS idx_mb
                    FROM information_schema.tables
                    WHERE table_schema = DATABASE()";
            $params = [];
            if ($search !== '') {
                $sql .= " AND table_name LIKE ?";
                $params[] = '%' . $search . '%';
            }
            $sql .= " ORDER BY (data_length + index_length) DESC LIMIT 100";
            $st = $db->prepare($sql);
            $st->execute($params);
            $tables = $st->fetchAll(\PDO::FETCH_ASSOC);

            $overview['table_count'] = (int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
            $overview['db_size_mb'] = round(array_sum(array_map(fn($t) => (float)$t['size_mb'], $tables)), 2);
        } catch (\Throwable $e) {
            $error = 'Could not read database statistics: ' . $e->getMessage();
            error_log('DatabaseMonitor: ' . $e->getMessage());
        }

        return $this->render('admin/database/index', [
            'page_title' => 'Database Monitor',
            'overview' => $overview,
            'tables' => $tables,
            'search' => $search,
            'error' => $error,
        ]);
    }
}
