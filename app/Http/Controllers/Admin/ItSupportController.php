<?php
namespace App\Http\Controllers\Admin;

/**
 * IT Support Toolbox
 * Single GUI landing page for the fixer/admin: health, logs, cache,
 * users, flags, audit — links out to existing tools, no duplication.
 */
class ItSupportController extends AdminController
{
    public function toolbox()
    {
        $this->requireAdmin();

        $health = ['db' => false, 'tables' => 0, 'users' => 0, 'audit_events' => 0, 'disk_free_gb' => null, 'php' => PHP_VERSION];
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $health['db'] = (bool)$db->query('SELECT 1')->fetchColumn();
            $health['tables'] = (int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
            $health['users'] = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $health['audit_events'] = (int)$db->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
        } catch (\Throwable $e) {
            error_log('ItSupport toolbox health: ' . $e->getMessage());
        }
        $free = @disk_free_space('C:\\xampp\\htdocs\\apsdreamhome');
        if ($free !== false) $health['disk_free_gb'] = round($free / 1073741824, 2);

        $logTail = [];
        foreach (['C:\\xampp\\htdocs\\apsdreamhome\\logs\\php_error.log'] as $lf) {
            if (is_readable($lf)) {
                $lines = @file($lf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines) $logTail = array_slice($lines, -15);
                break;
            }
        }

        $tools = [
            ['name' => 'Feature Flags', 'url' => '/admin/feature-flags', 'icon' => 'fa-toggle-on', 'desc' => 'Runtime toggles & gradual rollouts'],
            ['name' => 'Audit Logs', 'url' => '/admin/audit-log', 'icon' => 'fa-clipboard-list', 'desc' => 'Who did what, with filters & timelines'],
            ['name' => 'Activity Log', 'url' => '/admin/activity-log', 'icon' => 'fa-history', 'desc' => 'Recent events + top actions'],
            ['name' => 'User Management', 'url' => '/admin/users', 'icon' => 'fa-users', 'desc' => 'CRUD, roles, impersonation, sessions'],
            ['name' => 'GodMode', 'url' => '/admin/godmode', 'icon' => 'fa-user-secret', 'desc' => 'Impersonate, role-switch, system health'],
            ['name' => 'Cache Admin', 'url' => '/admin/cache', 'icon' => 'fa-bolt', 'desc' => 'Flush cache, Redis, hotpath stats'],
            ['name' => 'Support Tickets', 'url' => '/admin/support-tickets', 'icon' => 'fa-life-ring', 'desc' => 'Customer/field issue queue'],
            ['name' => 'System Health', 'url' => '/admin/health', 'icon' => 'fa-heartbeat', 'desc' => 'PASS/WARN/FAIL across cron, queues, backups'],
            ['name' => 'Database Monitor', 'url' => '/admin/database', 'icon' => 'fa-database', 'desc' => 'Table sizes, rows, server stats (read-only)'],
            ['name' => 'Cron Health', 'url' => '/admin/cron-health', 'icon' => 'fa-clock', 'desc' => 'Cron runs, manual trigger, dry-run'],
            ['name' => 'Backups', 'url' => '/admin/backups', 'icon' => 'fa-save', 'desc' => 'Create, restore, download backups'],
            ['name' => 'API Docs', 'url' => '/admin/api-docs', 'icon' => 'fa-book', 'desc' => 'Endpoint reference + spec export'],
        ];

        return $this->render('admin/it-support/toolbox', [
            'page_title' => 'IT Support Toolbox',
            'health' => $health,
            'logTail' => $logTail,
            'tools' => $tools,
        ]);
    }
}
