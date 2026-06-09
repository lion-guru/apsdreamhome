<?php
namespace App\Http\Controllers\Admin;

class ActivityLogController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $logs = $db->query("SELECT al.*, u.name as user_name FROM audit_log al LEFT JOIN users u ON al.user_id=u.id ORDER BY al.created_at DESC LIMIT 200")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $stats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM audit_log")->fetchColumn(),
                'today' => (int)$db->query("SELECT COUNT(*) FROM audit_log WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
                'unique_users' => (int)$db->query("SELECT COUNT(DISTINCT user_id) FROM audit_log WHERE user_id IS NOT NULL")->fetchColumn(),
            ];
            $top_actions = $db->query("SELECT action, COUNT(*) as cnt FROM audit_log GROUP BY action ORDER BY cnt DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $logs = [];
            $stats = ['total' => 0, 'today' => 0, 'unique_users' => 0];
            $top_actions = [];
        }
        return $this->render('admin/activity-log/index', ['logs' => $logs, 'stats' => $stats, 'top_actions' => $top_actions]);
    }
}
