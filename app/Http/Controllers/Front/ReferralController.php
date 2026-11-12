<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ReferralService;
use App\Traits\TenantAwareTrait;

/**
 * Customer Referral Dashboard
 * View referral code, share, see earnings and team
 */
class ReferralController extends BaseController
{
    use TenantAwareTrait;
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
        } catch (\Throwable $e) { error_log("ReferralController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

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
        } catch (\Throwable $e) { error_log("ReferralController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

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
        } catch (\Throwable $e) { error_log("ReferralController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

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

    /**
     * S457: 1-Click Customer-to-Associate Upgrade
     * Converts a customer into an official Associate Partner:
     * - Changes user.role to 'associate'
     * - Generates or provisions associate_profiles
     * - Credits ₹500 welcome bonus to wallet
     * - Preserves customer's referral tree as their initial downline!
     */
    public function upgradeToAssociate()
    {
        // NOTE (S460): a GET MUST NOT MUTATE.
        //
        // The route registers BOTH GET and POST to this action and the method
        // had no method branch, so simply VISITING the URL upgraded the
        // logged-in account: users.role -> 'associate', an associate_profiles
        // row, and a Rs.500 wallet credit.  A link preview, a crawler or a
        // browser prefetch was therefore enough to change someone's account -
        // and a GET-sweep probe hit an admin account and turned it into an
        // associate.
        //
        // GET is now read-only: it sends the visitor back to the page that
        // carries the upgrade form (/user/referral).
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            if ($this->isAjax()) {
                return $this->json([
                    'success'    => false,
                    'error'      => 'Use POST to upgrade to an Associate Partner.',
                    'csrf_field' => \App\Helpers\SecurityHelper::csrfField(),
                ], 405);
            }
            $this->setFlash('info', 'कृपया नीचे दिए गए बटन से अपग्रेड की पुष्टि करें।');
            return $this->redirect('/user/referral');
        }

        if (empty($_SESSION['user_id'])) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'Please login to continue'], 401);
            }
            return $this->redirect('/login');
        }

        $userId = (int)$_SESSION['user_id'];
        $user = $this->loadCurrentUser();

        if (empty($user)) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'User not found'], 404);
            }
            return $this->redirect('/user/dashboard');
        }

        // Already associate?
        if (($user['role'] ?? '') === 'associate') {
            if ($this->isAjax()) {
                return $this->json(['success' => true, 'already_associate' => true, 'message' => 'You are already an Associate Partner!']);
            }
            $this->setFlash('info', 'आप पहले से ही APS Dream Homes के एसोसिएट पार्टनर हैं।');
            return $this->redirect('/associate/dashboard');
        }

        // NOTE (S460): the POST had NO CSRF check at all, so any third-party
        // page could POST this form for a logged-in customer and silently
        // turn them into an associate.  The form posts `csrf_token`, which is
        // the same $_SESSION key validateCsrfToken() compares against.
        $csrfToken = (string)($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!$this->validateCsrfToken($csrfToken)) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            }
            $this->setFlash('error', 'सुरक्षा टोकन अमान्य है। कृपया पेज रीफ्रेश करके पुनः प्रयास करें।');
            return $this->redirect('/user/referral');
        }

        try {
            $this->db->beginTransaction();

            // 1. Update user role
            $this->db->prepare("UPDATE users SET role = 'associate', mlm_rank = 'associate', updated_at = NOW() WHERE id = ?")
                ->execute([$userId]);

            // 2. Ensure associate profile
            $stmt = $this->db->prepare("SELECT id FROM associate_profiles WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $existing = $stmt->fetch();

            $service = new ReferralService();
            $refCode = $service->getReferralCode($userId) ?: ('AP-' . str_pad((string)$userId, 5, '0', STR_PAD_LEFT));

            if (!$existing) {
                $this->db->prepare("
                    INSERT INTO associate_profiles 
                    (tenant_id, user_id, referral_code, level, status, created_at, updated_at)
                    VALUES (1, ?, ?, 'associate', 'active', NOW(), NOW())
                ")->execute([$userId, $refCode]);
            } else {
                $this->db->prepare("UPDATE associate_profiles SET status = 'active', updated_at = NOW() WHERE user_id = ?")
                    ->execute([$userId]);
            }

            // NOTE (S460): the Rs.500 welcome bonus is credited AFTER the
            // commit, on purpose.
            //
            // WalletService::credit() opens its OWN transaction, and a nested
            // one raises "There is already an active transaction" - which the
            // surrounding catch swallowed into a log line.  The bonus was
            // therefore NEVER credited, while the success message below
            // cheerfully claimed it had been.
            $this->db->commit();

            $bonusCredited = false;
            try {
                $walletService = new \App\Services\WalletService();
                $walletService->credit(
                    $userId,
                    500.0,
                    'bonus',
                    'Welcome bonus on upgrading to Official Associate Partner',
                    $userId,
                    'user'
                );
                $bonusCredited = true;
            } catch (\Throwable $wEx) {
                error_log("upgradeToAssociate: Wallet bonus credit FAILED: " . $wEx->getMessage());
            }

            // Update active session roles
            $_SESSION['user_role'] = 'associate';
            $_SESSION['role'] = 'associate';

            $message = $bonusCredited
                ? 'बधाई हो! आप APS Dream Homes के आधिकारिक एसोसिएट पार्टनर बन चुके हैं। ₹500 वेलकम बोनस आपके वॉलेट में जमा कर दिया गया है।'
                : 'बधाई हो! आप APS Dream Homes के आधिकारिक एसोसिएट पार्टनर बन चुके हैं। वेलकम बोनस जमा नहीं हो सका - कृपया सहायता से संपर्क करें।';

            if ($this->isAjax()) {
                return $this->json([
                    'success'       => true,
                    'message'       => $message,
                    'bonus_credited' => $bonusCredited,
                    'redirect_url'  => BASE_URL . '/associate/dashboard',
                ]);
            }

            $this->setFlash('success', $message);
            return $this->redirect('/associate/dashboard');

        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("ReferralController::upgradeToAssociate failed: " . $e->getMessage());

            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'Upgrade failed: ' . $e->getMessage()], 500);
            }
            $this->setFlash('error', 'एसोसिएट अपग्रेड विफल रहा: ' . $e->getMessage());
            return $this->redirect('/user/referral');
        }
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
