<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

/**
 * Customer Referral Dashboard
 * View referral code, share, see earnings and team
 */
class ReferralController extends BaseController
{
    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        $user = $this->getUser();
        $userId = (int)($user['id'] ?? 0);
        $referralCode = $user['referral_code'] ?? null;
        $stats = [
            'total_referrals' => 0, 'active_referrals' => 0, 'total_earnings' => 0,
            'pending_earnings' => 0, 'paid_earnings' => 0, 'this_month_earnings' => 0
        ];
        $referrals = [];
        $earnings = [];
        $shareLinks = [];
        if (!$referralCode) {
            $referralCode = $this->generateReferralCode($userId);
            try {
                $stmt = $this->db->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
                $stmt->execute([$referralCode, $userId]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ?");
            $stmt->execute([$userId]);
            $stats['total_referrals'] = (int)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE referred_by = ? AND status = 'active'");
            $stmt->execute([$userId]);
            $stats['active_referrals'] = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats['total_earnings'] = (float)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            $stats['pending_earnings'] = (float)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE user_id = ? AND status = 'paid'");
            $stmt->execute([$userId]);
            $stats['paid_earnings'] = (float)$stmt->fetchColumn();
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute([$userId]);
            $stats['this_month_earnings'] = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {}
        try {
            $stmt = $this->db->prepare("SELECT id, name, email, phone, created_at, role FROM users WHERE referred_by = ? ORDER BY created_at DESC LIMIT 50");
            $stmt->execute([$userId]);
            $referrals = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}
        try {
            $stmt = $this->db->prepare("SELECT id, amount, status, commission_type, description, created_at, paid_at FROM commissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$userId]);
            $earnings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
        $shareLinks = [
            'url' => $baseUrl . '/register?ref=' . urlencode($referralCode),
            'whatsapp' => 'https://wa.me/?text=' . urlencode('Join me on APS Dream Home! Use my referral code: ' . $referralCode . ' Register at: ' . $baseUrl . '/register?ref=' . $referralCode),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($baseUrl . '/register?ref=' . $referralCode),
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode('Join me on APS Dream Home! Referral: ' . $referralCode) . '&url=' . urlencode($baseUrl . '/register?ref=' . $referralCode),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($baseUrl . '/register?ref=' . $referralCode) . '&text=' . urlencode('Join me on APS Dream Home! Referral: ' . $referralCode)
        ];
        $this->layout = 'layouts/customer';
        $this->render('pages/customer_referral', [
            'page_title' => 'Refer & Earn - APS Dream Home',
            'user' => $user,
            'referral_code' => $referralCode,
            'stats' => $stats,
            'referrals' => $referrals,
            'earnings' => $earnings,
            'share_links' => $shareLinks
        ]);
    }

    private function generateReferralCode(int $userId): string
    {
        $name = $_SESSION['user_name'] ?? 'U';
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        if (empty($prefix)) $prefix = 'USR';
        return $prefix . str_pad((string)$userId, 5, '0', STR_PAD_LEFT) . substr(strtoupper(uniqid()), -3);
    }
}
