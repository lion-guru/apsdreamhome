<?php
/**
 * MLM Referral Service
 * Handles referral code validation and MLM operations
 */

namespace App\Services;

use App\Models\User;
use App\Models\MlmProfile;
use App\Models\MLMReferral;
use Illuminate\Support\Facades\DB;

class MLMReferralService
{
    /**
     * Validate referral code
     */
    public function validateReferralCode($code)
    {
        if (!$code) {
            return null;
        }

        $mlmProfile = MlmProfile::where('referral_code', $code)
                               ->where('status', 'active')
                               ->with('user')
                               ->first();

        if (!$mlmProfile || !$mlmProfile->user) {
            return null;
        }

        return [
            'user_id' => $mlmProfile->user_id,
            'name' => $mlmProfile->user->name,
            'email' => $mlmProfile->user->email,
            'referral_code' => $mlmProfile->referral_code,
            'user_type' => $mlmProfile->user_type
        ];
    }

    /**
     * Generate unique referral code
     */
    public function generateReferralCode($name, $email)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $suffix = strtoupper(substr(md5($email . time()), 0, 4));
        $code = $prefix . $suffix;

        // Ensure uniqueness
        $counter = 0;
        $originalCode = $code;

        while (MlmProfile::where('referral_code', $code)->exists() && $counter < 100) {
            $suffix = strtoupper(substr(md5($email . time() . $counter), 0, 4));
            $code = $prefix . $suffix;
            $counter++;
        }

        return $code;
    }

    /**
     * Get referral statistics for a user
     */
    public function getReferralStats($userId)
    {
        $mlmProfile = MlmProfile::where('user_id', $userId)->first();

        if (!$mlmProfile) {
            return [
                'referral_code' => null,
                'direct_referrals' => 0,
                'total_network' => 0,
                'active_referrals' => 0,
                'commission_earned' => 0
            ];
        }

        // Direct referrals
        $directReferrals = MlmProfile::where('sponsor_user_id', $userId)->count();

        // Active referrals
        $activeReferrals = MlmProfile::where('sponsor_user_id', $userId)
                                   ->whereHas('user', function($query) {
                                       $query->where('status', 'active');
                                   })->count();

        // Total network size
        $totalNetwork = $this->getTotalNetworkSize($userId);

        // Commission earned from referrals
        $commissionEarned = DB::table('commissions')
                             ->where('associate_id', $userId)
                             ->where('commission_type', 'referral')
                             ->where('status', 'paid')
                             ->sum('amount');

        return [
            'referral_code' => $mlmProfile->referral_code,
            'direct_referrals' => $directReferrals,
            'total_network' => $totalNetwork,
            'active_referrals' => $activeReferrals,
            'commission_earned' => $commissionEarned ?? 0
        ];
    }

    /**
     * Get total network size (all levels)
     */
    protected function getTotalNetworkSize($userId, $maxLevel = 10)
    {
        $total = 0;
        $currentLevel = [$userId];
        $processed = [];

        for ($level = 1; $level <= $maxLevel && !empty($currentLevel); $level++) {
            $nextLevel = [];

            foreach ($currentLevel as $sponsorId) {
                if (in_array($sponsorId, $processed)) {
                    continue;
                }

                $directReferrals = MlmProfile::where('sponsor_user_id', $sponsorId)
                                           ->pluck('user_id')
                                           ->toArray();

                $total += count($directReferrals);
                $nextLevel = array_merge($nextLevel, $directReferrals);
                $processed[] = $sponsorId;
            }

            $currentLevel = $nextLevel;
        }

        return $total;
    }

    /**
     * Get downline members for a user
     */
    public function getDownlineMembers($userId, $maxLevel = 5)
    {
        $downline = [];
        $this->buildDownlineTree($userId, $downline, 1, $maxLevel);
        return $downline;
    }

    /**
     * Build downline tree recursively
     */
    protected function buildDownlineTree($userId, &$downline, $level, $maxLevel)
    {
        if ($level > $maxLevel) {
            return;
        }

        $directReferrals = MlmProfile::where('sponsor_user_id', $userId)
                                   ->with('user')
                                   ->get();

        foreach ($directReferrals as $referral) {
            if ($referral->user) {
                $downline[] = [
                    'user_id' => $referral->user_id,
                    'name' => $referral->user->name,
                    'email' => $referral->user->email,
                    'user_type' => $referral->user_type,
                    'level' => $level,
                    'joined_at' => $referral->created_at,
                    'status' => $referral->status
                ];

                // Get deeper levels
                $this->buildDownlineTree($referral->user_id, $downline, $level + 1, $maxLevel);
            }
        }
    }

    /**
     * Get referral hierarchy/tree
     */
    public function getReferralTree($userId, $maxLevel = 3)
    {
        $tree = [
            'root' => $this->getUserInfo($userId),
            'levels' => []
        ];

        for ($level = 1; $level <= $maxLevel; $level++) {
            $tree['levels'][$level] = $this->getLevelMembers($userId, $level);
        }

        return $tree;
    }

    /**
     * Get user basic info
     */
    protected function getUserInfo($userId)
    {
        $user = User::find($userId);
        $mlmProfile = MlmProfile::where('user_id', $userId)->first();

        return $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
            'referral_code' => $mlmProfile ? $mlmProfile->referral_code : null,
            'joined_at' => $user->created_at
        ] : null;
    }

    /**
     * Get members at specific level
     */
    protected function getLevelMembers($rootUserId, $targetLevel)
    {
        $members = [];
        $this->findLevelMembers($rootUserId, $targetLevel, 1, $members);
        return $members;
    }

    /**
     * Recursively find members at target level
     */
    protected function findLevelMembers($userId, $targetLevel, $currentLevel, &$members)
    {
        if ($currentLevel >= $targetLevel) {
            if ($currentLevel === $targetLevel) {
                $userInfo = $this->getUserInfo($userId);
                if ($userInfo) {
                    $members[] = $userInfo;
                }
            }
            return;
        }

        $directReferrals = MlmProfile::where('sponsor_user_id', $userId)
                                   ->pluck('user_id')
                                   ->toArray();

        foreach ($directReferrals as $referralId) {
            $this->findLevelMembers($referralId, $targetLevel, $currentLevel + 1, $members);
        }
    }

    /**
     * Process referral commission
     */
    public function processReferralCommission($referrerId, $newUserId, $userType)
    {
        // Define commission rates based on user type
        $commissionRates = [
            'customer' => 100,    // ₹100 for customer referral
            'agent' => 500,       // ₹500 for agent referral
            'associate' => 200,   // ₹200 for associate referral
            'builder' => 1000,    // ₹1000 for builder referral
            'investor' => 1500    // ₹1500 for investor referral
        ];

        $commissionAmount = $commissionRates[$userType] ?? 100;

        // Create commission record
        DB::table('commissions')->insert([
            'associate_id' => $referrerId,
            'referred_user_id' => $newUserId,
            'amount' => $commissionAmount,
            'commission_type' => 'referral',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $commissionAmount;
    }

    /**
     * Get referral leaderboard
     */
    public function getReferralLeaderboard($limit = 10)
    {
        return MlmProfile::select('mlm_profiles.*', 'users.name', 'users.email')
                        ->join('users', 'mlm_profiles.user_id', '=', 'users.id')
                        ->where('mlm_profiles.status', 'active')
                        ->orderBy('direct_referrals', 'desc')
                        ->orderBy('created_at', 'asc')
                        ->limit($limit)
                        ->get()
                        ->map(function($profile) {
                            return [
                                'name' => $profile->name,
                                'email' => $profile->email,
                                'referral_code' => $profile->referral_code,
                                'direct_referrals' => $profile->direct_referrals,
                                'user_type' => $profile->user_type
                            ];
                        });
    }

    /**
     * Update referral statistics
     */
    public function updateReferralStats($userId)
    {
        $stats = $this->getReferralStats($userId);

        // Update MLM profile with latest stats
        MlmProfile::where('user_id', $userId)->update([
            'direct_referrals' => $stats['direct_referrals'],
            'total_network' => $stats['total_network'],
            'updated_at' => now()
        ]);

        return $stats;
    }

    /**
     * Check if user can refer others
     */
    public function canRefer($userId)
    {
        $user = User::find($userId);
        $mlmProfile = MlmProfile::where('user_id', $userId)->first();

        if (!$user || !$mlmProfile) {
            return false;
        }

        // Only active users can refer
        return $user->status === 'active' && $mlmProfile->status === 'active';
    }

    /**
     * Get referral link for user
     */
    public function getReferralLink($userId, $baseUrl = null)
    {
        $mlmProfile = MlmProfile::where('user_id', $userId)->first();

        if (!$mlmProfile || !$mlmProfile->referral_code) {
            return null;
        }

        $baseUrl = $baseUrl ?? env('APP_URL', 'http://localhost');
        return $baseUrl . '/register?ref=' . $mlmProfile->referral_code;
    }
}
