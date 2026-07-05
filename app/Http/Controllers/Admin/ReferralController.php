<?php

namespace App\Http\Controllers\Admin;

use App\Services\ReferralService;

class ReferralController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $referrals = $db->query("SELECT r.*, u.name as referrer_name, u.email as referrer_email FROM referrals r LEFT JOIN users u ON r.referrer_id=u.id ORDER BY r.created_at DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $stats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM referrals")->fetchColumn(),
                'pending' => (int)$db->query("SELECT COUNT(*) FROM referrals WHERE status='pending'")->fetchColumn(),
                'converted' => (int)$db->query("SELECT COUNT(*) FROM referrals WHERE status='converted'")->fetchColumn(),
                'rejected' => (int)$db->query("SELECT COUNT(*) FROM referrals WHERE status='rejected'")->fetchColumn(),
            ];
        } catch (\Exception $e) {
            $referrals = [];
            $stats = ['total' => 0, 'pending' => 0, 'converted' => 0, 'rejected' => 0];
        }
        return $this->render('admin/referrals/index', ['referrals' => $referrals, 'stats' => $stats]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $referral = $db->prepare("SELECT r.*, u.name as referrer_name, u.email as referrer_email FROM referrals r LEFT JOIN users u ON r.referrer_id=u.id WHERE r.id=?");
            $referral->execute([$id]);
            $referral = $referral->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $referral = null;
        }
        return $this->render('admin/referrals/show', ['referral' => $referral]);
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $users = $db->query("SELECT id, name, email FROM users WHERE role IN ('customer','associate') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/referrals/create', ['users' => $users]);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_email, referral_code, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$_POST['referrer_id'] ?? 0, $_POST['referred_email'] ?? '', $code]);
            $this->setFlash('success', 'Referral created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/referrals');
    }

    public function approve($id)
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->prepare("UPDATE referrals SET status='converted', converted_at=NOW() WHERE id=?")->execute([$id]);
            $this->setFlash('success', 'Referral approved');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed');
        }
        return $this->redirect('/admin/referrals');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $db->prepare("UPDATE referrals SET status='rejected' WHERE id=?")->execute([$id]);
            $this->setFlash('success', 'Referral rejected');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed');
        }
        return $this->redirect('/admin/referrals');
    }

    /**
     * Referral Leaderboard — top referrers ranked
     */
    public function leaderboard()
    {
        $this->requireAdmin();
        $service = new ReferralService();

        $period = $_GET['period'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 20);

        $leaderboard = $service->getLeaderboard($limit, $period);
        $tiers = ReferralService::getTiers();

        return $this->render('admin/referrals/leaderboard', [
            'leaderboard' => $leaderboard,
            'tiers' => $tiers,
            'current_period' => $period,
            'page_title' => 'Referral Leaderboard',
        ]);
    }

    /**
     * Share Analytics — conversion funnel + top sharers
     */
    public function shareAnalytics()
    {
        $this->requireAdmin();
        $service = new ReferralService();

        $funnel = $service->getShareConversionFunnel();
        $topSharers = $service->getTopSharers(10);

        return $this->render('admin/referrals/share_analytics', [
            'funnel' => $funnel,
            'top_sharers' => $topSharers,
            'page_title' => 'Share Analytics',
        ]);
    }

    /**
     * Tier Management — view all tiers and users in each
     */
    public function tiers()
    {
        $this->requireAdmin();
        $service = new ReferralService();
        $tiers = ReferralService::getTiers();

        // Count users per tier
        $tierCounts = [];
        foreach ($tiers as $tier) {
            $tierCounts[$tier['tier']] = 0;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $users = $db->query("SELECT id FROM users WHERE referral_code IS NOT NULL AND referral_code != ''")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($users as $u) {
                $userTier = $service->getUserTier((int)$u['id']);
                $tierCounts[$userTier['tier']] = ($tierCounts[$userTier['tier']] ?? 0) + 1;
            }
        } catch (\Throwable $e) {}

        return $this->render('admin/referrals/tiers', [
            'tiers' => $tiers,
            'tier_counts' => $tierCounts,
            'page_title' => 'Referral Tiers',
        ]);
    }
}
