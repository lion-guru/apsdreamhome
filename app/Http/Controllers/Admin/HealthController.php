<?php
namespace App\Http\Controllers\Admin;

/**
 * System Health Dashboard (read-only aggregation)
 * One page: DB, cron, queues, scheduler, backups, disk, failures.
 */
class HealthController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        $checks = [];
        $ok = fn($label, $state, $detail) => ['label' => $label, 'state' => $state, 'detail' => $detail];

        // 1. Database
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->query('SELECT 1')->fetchColumn();
            $checks[] = $ok('Database', 'pass', 'Connection OK');
        } catch (\Throwable $e) {
            $checks[] = $ok('Database', 'fail', 'Unreachable: ' . $e->getMessage());
            return $this->render('admin/health/index', ['page_title' => 'System Health', 'checks' => $checks, 'summary' => $this->summarize($checks)]);
        }

        $cnt = function (string $sql, array $p = []) use ($db) {
            try {
                $st = $db->prepare($sql);
                $st->execute($p);
                return (int)$st->fetchColumn();
            } catch (\Throwable $e) { return null; }
        };

        // 2. Cron freshness
        $cronLog = (defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 4) . '/storage') . '/logs/master_cron.log';
        if (is_file($cronLog)) {
            $ageH = (time() - filemtime($cronLog)) / 3600;
            $checks[] = $ok('Cron (master log)', $ageH > 25 ? 'warn' : 'pass', 'Last write ' . round($ageH, 1) . 'h ago');
        } else {
            $checks[] = $ok('Cron (master log)', 'warn', 'Log file not found — cron may never have run on this host');
        }

        // 3. Queues
        $pending = $cnt("SELECT COUNT(*) FROM queue_jobs WHERE status NOT IN ('done','completed','failed')");
        $pending = $pending ?? $cnt("SELECT COUNT(*) FROM queue_jobs");
        $failed = $cnt("SELECT COUNT(*) FROM failed_jobs");
        $checks[] = $ok('Job queue', ($pending ?? 0) > 500 ? 'warn' : 'pass', ($pending === null ? '?' : number_format($pending)) . ' pending, ' . ($failed === null ? '?' : number_format($failed)) . ' failed');

        $emailQ = $cnt("SELECT COUNT(*) FROM email_queue");
        $smsQ = $cnt("SELECT COUNT(*) FROM sms_queue WHERE status = 'pending'");
        $smsQ = $smsQ ?? $cnt("SELECT COUNT(*) FROM sms_queue");
        $checks[] = $ok('Email / SMS backlog', 'pass', ($emailQ === null ? '?' : number_format($emailQ)) . ' emails, ' . ($smsQ === null ? '?' : number_format($smsQ)) . ' SMS pending');

        // 4. Scheduler
        $sched = $cnt("SELECT COUNT(*) FROM scheduled_tasks WHERE is_active = 1");
        $checks[] = $ok('Scheduled tasks', ($sched ?? 0) > 0 ? 'pass' : 'warn', ($sched === null ? '?' : $sched) . ' active tasks');

        // 5. Backups
        try {
            $last = $db->query("SELECT created_at FROM backup_logs ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($last) {
                $ageD = (time() - strtotime($last)) / 86400;
                $checks[] = $ok('Backups', $ageD > 8 ? 'warn' : 'pass', 'Latest ' . round($ageD, 1) . ' days ago');
            } else {
                $checks[] = $ok('Backups', 'warn', 'No backup records yet');
            }
        } catch (\Throwable $e) { $checks[] = $ok('Backups', 'warn', 'backup_logs unreadable'); }

        // 6. Failed audit events today
        $fails = $cnt("SELECT COUNT(*) FROM audit_logs WHERE status = 'failed' AND DATE(created_at) = CURDATE()");
        $checks[] = $ok('Failures today', ($fails ?? 0) > 20 ? 'warn' : 'pass', ($fails === null ? '?' : number_format($fails)) . ' failed audit events');

        // 7. Disk + PHP (BASE_PATH is not globally defined — derive root from STORAGE_PATH)
        $appRoot = defined('STORAGE_PATH') ? dirname(STORAGE_PATH) : dirname(__DIR__, 4);
        $free = @disk_free_space($appRoot);
        $checks[] = $ok('Disk free', ($free !== false && $free < 1073741824) ? 'warn' : 'pass', $free === false ? 'unknown' : round($free / 1073741824, 2) . ' GB free');
        $checks[] = $ok('PHP', version_compare(PHP_VERSION, '8.0', '>=') ? 'pass' : 'warn', 'PHP ' . PHP_VERSION);

        return $this->render('admin/health/index', [
            'page_title' => 'System Health',
            'checks' => $checks,
            'summary' => $this->summarize($checks),
        ]);
    }

    private function summarize(array $checks): array
    {
        $s = ['pass' => 0, 'warn' => 0, 'fail' => 0];
        foreach ($checks as $c) $s[$c['state']]++;
        return $s;
    }
}
