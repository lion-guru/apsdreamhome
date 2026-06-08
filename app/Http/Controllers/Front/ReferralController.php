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
        $shareLinks = [
            'url' => $shareUrl,
            'whatsapp' => 'https://wa.me/?text=' . urlencode("Join APS Dream Home! Use my referral code: {$referralCode}\nRegister at: {$shareUrl}"),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrl),
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode("Join APS Dream Home! Referral: {$referralCode}") . '&url=' . urlencode($shareUrl),
            'telegram' => 'https://t.me/share/url?url=' . urlencode($shareUrl) . '&text=' . urlencode("Join APS Dream Home! Referral: {$referralCode}"),
            'sms' => 'sms:?body=' . urlencode("Join APS Dream Home! Use my referral code {$referralCode}: {$shareUrl}"),
            'email' => 'mailto:?subject=' . urlencode('Join APS Dream Home') . '&body=' . urlencode("Use my referral code {$referralCode} to register: {$shareUrl}"),
        ];

        $this->layout = 'layouts/customer';
        $this->render('pages/customer_referral', [
            'page_title' => 'Refer & Earn - APS Dream Home',
            'user' => $this->loadCurrentUser(),
            'referral_code' => $referralCode,
            'stats' => $stats,
            'referrals' => $referrals,
            'earnings' => $earnings,
            'share_links' => $shareLinks,
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
