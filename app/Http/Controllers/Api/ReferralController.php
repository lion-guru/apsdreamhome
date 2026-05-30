<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Services\ReferralService;

class ReferralController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('csrf', ['only' => ['store']]);
    }

    /**
     * Create a new referral
     */
    public function store()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $email = \trim($this->request()->input('email', ''));
            if (empty($email)) {
                return $this->jsonError('Email is required', 400);
            }

            $referralModel = $this->model('Referral');
            $referralCode = \App\Models\Referral::generateCode();

            $referral = new \App\Models\Referral([
                'referrer_id' => $user->uid,
                'referred_email' => $email,
                'referral_code' => $referralCode
            ]);

            if ($referral->save()) {
                return $this->jsonSuccess([
                    'referral_code' => $referralCode,
                    'share_link' => "https://apsdreamhome.com/register.php?ref=" . $referralCode
                ], 'Referral created successfully', 201);
            }

            return $this->jsonError('Failed to create referral', 500);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * List user referrals
     */
    public function index()
    {
        try {
            $user = $this->auth->user();
            $referralModel = $this->model('Referral');
            $referrals = $this->db->fetchAll(
                "SELECT * FROM referrals WHERE referrer_id = ? ORDER BY created_at DESC",
                [$user->uid]
            );

            return $this->jsonSuccess($referrals);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get referral stats
     */
    public function stats()
    {
        try {
            $user = $this->auth->user();
            $stats = $this->db->fetch(
                "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                 FROM referrals WHERE referrer_id = ?",
                [$user->uid]
            );

            return $this->jsonSuccess($stats);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get referral dashboard (uses ReferralService for MLM tables)
     */
    public function dashboard()
    {
        try {
            $userId = $_SESSION['user_id'] ?? $this->getCurrentUserId();
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $referralService = new ReferralService();

            $referralLink = $referralService->getReferralLink($userId);
            $stats = $referralService->getNetworkStats($userId);
            $directReferrals = $referralService->getDirectReferrals($userId);
            $analytics = $referralService->getReferralAnalytics($userId);

            $referralCode = null;
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($userData) {
                $referralCode = $userData['referral_code'];
            }

            $walletStmt = $this->db->prepare("SELECT points_balance, total_earned, referral_earnings FROM wallet_points WHERE user_id = ?");
            $walletStmt->execute([$userId]);
            $wallet = $walletStmt->fetch(\PDO::FETCH_ASSOC);

            return $this->jsonSuccess([
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'wallet' => $wallet ?: null,
                'stats' => $stats,
                'direct_referrals' => $directReferrals,
                'analytics' => $analytics
            ]);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
