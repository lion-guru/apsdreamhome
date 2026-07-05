<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ReferralService;

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

        $userId = (int)$_SESSION['user_id'];
        $service = new ReferralService();

        // Ensure user has a referral code
        $referralCode = $service->getReferralCode($userId);
        if (empty($referralCode)) {
            $this->redirect('/user/dashboard');
            return;
        }

        $stats = $service->getReferralStats($userId);
        $referrals = $service->getReferredUsers($userId);

        // Get earnings from mlm_commission_ledger
        $earnings = [];
        try {
            $stmt = $this->db->prepare("
                SELECT ml.*, u.name as referred_name
                FROM mlm_commission_ledger ml
                LEFT JOIN users u ON ml.source_user_id = u.id
                WHERE ml.beneficiary_user_id = ? AND ml.commission_type = 'referral'
                ORDER BY ml.created_at DESC LIMIT 50
            ");
            $stmt->execute([$userId]);
            $earnings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        $shareUrl = $service->getShareUrl($referralCode);
        $tierInfo = $service->getUserTier($userId);
        $shareLinks = [
            'url' => $shareUrl,
            'whatsapp' => 'https://wa.me/?text=' . urlencode("Join APS Dream Home! Use my referral code: {$referralCode}\nRegister at: {$shareUrl}"),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrl),
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode("Join APS Dream Home! Referral: {$referralCode}") . '&url=' . urlencode($shareUrl),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($shareUrl) . '&text=' . urlencode("Join APS Dream Home! Referral: {$referralCode}"),
            'sms' => 'sms:?body=' . urlencode("Join APS Dream Home! Use my referral code {$referralCode}: {$shareUrl}"),
            'email' => 'mailto:?subject=' . urlencode('Join APS Dream Home') . '&body=' . urlencode("Use my referral code {$referralCode} to register: {$shareUrl}"),
        ];

        // Share analytics
        $shareStats = ['total' => 0, 'by_platform' => [], 'recent' => []];
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM whatsapp_lead_shares WHERE shared_by_user_id = ?");
            $stmt->execute([$userId]);
            $shareStats['total'] = (int)$stmt->fetchColumn();

            $stmt2 = $this->db->prepare("SELECT share_method, COUNT(*) as cnt FROM whatsapp_lead_shares WHERE shared_by_user_id = ? GROUP BY share_method ORDER BY cnt DESC");
            $stmt2->execute([$userId]);
            $shareStats['by_platform'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $stmt3 = $this->db->prepare("SELECT * FROM whatsapp_lead_shares WHERE shared_by_user_id = ? ORDER BY created_at DESC LIMIT 10");
            $stmt3->execute([$userId]);
            $shareStats['recent'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {}

        // Referral leaderboard (top 10 referrers)
        $leaderboard = [];
        try {
            $leaderboard = $this->db->fetchAll("
                SELECT u.name, COUNT(DISTINCT ml.source_user_id) as referral_count,
                       COALESCE(SUM(ml.amount), 0) as total_earned
                FROM mlm_commission_ledger ml
                JOIN users u ON u.id = ml.beneficiary_user_id
                WHERE ml.commission_type = 'referral' AND ml.status IN ('paid', 'approved')
                GROUP BY ml.beneficiary_user_id
                ORDER BY referral_count DESC
                LIMIT 10
            ") ?: [];
        } catch (\Throwable $e) {}

        $this->layout = 'layouts/customer';
        $this->render('pages/customer_referral', [
            'page_title' => 'Refer & Earn - APS Dream Home',
            'user' => $this->loadCurrentUser(),
            'referral_code' => $referralCode,
            'stats' => $stats,
            'referrals' => $referrals,
            'earnings' => $earnings,
            'share_links' => $shareLinks,
            'share_stats' => $shareStats,
            'leaderboard' => $leaderboard,
            'tier_info' => $tierInfo,
        ]);
    }

    /**
     * AJAX: Generate share link for a given channel
     */
    public function share()
    {
        if (empty($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Login required'], 401);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $channel = $_GET['channel'] ?? $_POST['channel'] ?? 'copy';
        $service = new ReferralService();
        $code = $service->getReferralCode($userId);

        if (empty($code)) {
            $this->json(['success' => false, 'error' => 'No referral code'], 400);
            return;
        }

        $url = $service->getShareUrl($code);
        $message = "Join APS Dream Home! Use my referral code: {$code}\nRegister at: {$url}";

        $channels = [
            'whatsapp' => 'https://wa.me/?text=' . urlencode($message),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode("Referral: {$code}") . '&url=' . urlencode($url),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($url) . '&text=' . urlencode($message),
            'sms' => 'sms:?body=' . urlencode("Referral code: {$code} - {$url}"),
            'email' => 'mailto:?subject=' . urlencode('Join APS Dream Home') . '&body=' . urlencode($message),
            'copy' => $url,
        ];

        $this->json([
            'success' => true,
            'url' => $channels[$channel] ?? $url,
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function loadCurrentUser(): array
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) return [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
