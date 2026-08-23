<?php

namespace App\Http\Controllers\Admin;

use App\Traits\TenantAwareTrait;

class AdminSecurityController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();

        $blockedCount = $activeBlocks = $failed24h = $failed7d = $tfaEnabled = $totalUsers = 0;
        $recentEvents = $topIPs = $recentBlocked = [];

        try {
            $blockedCount = (int)($this->db->query("SELECT COUNT(*) FROM blocked_ips")->fetchColumn());
            $activeBlocks = (int)($this->db->query("SELECT COUNT(*) FROM blocked_ips WHERE (expires_at IS NULL OR expires_at > NOW()) AND unblocked_at IS NULL")->fetchColumn());
            $failed24h = (int)($this->db->query("SELECT COUNT(*) FROM failed_login_attempts WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn());
            $failed7d = (int)($this->db->query("SELECT COUNT(*) FROM failed_login_attempts WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn());
            $tfaEnabled = (int)($this->db->fetch("SELECT COUNT(*) as c FROM users WHERE two_factor_enabled = 1 {$tidSql}", $tidParams)['c'] ?? 0);
            $totalUsers = (int)($this->db->fetch("SELECT COUNT(*) as c FROM users WHERE deleted_at IS NULL {$tidSql}", $tidParams)['c'] ?? 0);
            $recentEvents = $this->db->query("SELECT u.name, a.action, a.details, a.ip_address, a.created_at FROM audit_log a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
            $topIPs = $this->db->query("SELECT ip_address, COUNT(*) as cnt FROM failed_login_attempts WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY ip_address ORDER BY cnt DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            $recentBlocked = $this->db->query("SELECT * FROM blocked_ips ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('AdminSecurityController::index error: ' . $e->getMessage());
        }

        return $this->render('admin/security/index', [
            'page_title' => 'Security - APS Dream Home',
            'page_heading' => 'Security Center',
            'blockedCount' => $blockedCount,
            'activeBlocks' => $activeBlocks,
            'failed24h' => $failed24h,
            'failed7d' => $failed7d,
            'tfaEnabled' => $tfaEnabled,
            'totalUsers' => $totalUsers,
            'recentEvents' => $recentEvents,
            'topIPs' => $topIPs,
            'recentBlocked' => $recentBlocked,
        ]);
    }
}
