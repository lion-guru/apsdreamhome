<?php
/**
 * Associate MLM Dashboard Controller
 * Modern implementation of the legacy associate dashboard with MLM features
 */

namespace App\Http\Controllers\Associate;

use App\Models\User;
use App\Models\Associate;
use App\Models\MLMProfile;
use App\Models\UserPoints;
use App\Models\TrainingEnrollment;
use App\Models\PropertyComparison;
use App\Services\CommissionService;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssociateDashboardController
{
    protected $commissionService;
    protected $mlmService;

    public function __construct()
    {
        $this->commissionService = new CommissionService();
        $this->mlmService = new MLMService();
    }

    /**
     * Display the associate MLM dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $associateId = $user->id;

        // Get associate profile
        $associate = Associate::where('user_id', $associateId)->first();

        if (!$associate) {
            return redirect()->route('associate.profile.setup')->with('error', 'Please complete your profile setup first.');
        }

        // MLM Profile and statistics
        $mlmProfile = MLMProfile::where('user_id', $associateId)->first();

        // Team statistics
        $teamStats = $this->getTeamStatistics($associateId);

        // Earnings data
        $earningsData = $this->getEarningsData($associateId);

        // Business reports
        $businessReports = $this->getBusinessReports($associateId);

        // Recent activities
        $recentActivities = $this->getRecentActivities($associateId);

        // Training progress
        $trainingProgress = $this->getTrainingProgress($associateId);

        // Commission summary
        $commissionSummary = $this->commissionService->getCommissionSummary($associateId);

        // Performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($associateId);

        return view('associates.dashboard', compact(
            'associate',
            'mlmProfile',
            'teamStats',
            'earningsData',
            'businessReports',
            'recentActivities',
            'trainingProgress',
            'commissionSummary',
            'performanceMetrics'
        ));
    }

    /**
     * Get team statistics for the associate
     */
    protected function getTeamStatistics($associateId)
    {
        $mlmProfile = MLMProfile::where('user_id', $associateId)->first();

        if (!$mlmProfile) {
            return [
                'total_team' => 0,
                'direct_referrals' => 0,
                'total_levels' => 0,
                'active_members' => 0,
                'inactive_members' => 0,
                'level_breakdown' => []
            ];
        }

        // Get direct referrals
        $directReferrals = MLMProfile::where('sponsor_id', $associateId)->count();

        // Get all downline members (recursive)
        $allDownline = $this->getAllDownlineMembers($associateId);

        // Count active vs inactive
        $activeMembers = 0;
        $inactiveMembers = 0;

        foreach ($allDownline as $member) {
            $user = User::find($member['user_id']);
            if ($user && $user->status === 'active') {
                $activeMembers++;
            } else {
                $inactiveMembers++;
            }
        }

        // Get level breakdown
        $levelBreakdown = [];
        for ($level = 1; $level <= 10; $level++) {
            $levelMembers = MLMProfile::where('sponsor_id', $associateId)
                ->where('current_level', $level)
                ->count();
            if ($levelMembers > 0) {
                $levelBreakdown[$level] = $levelMembers;
            }
        }

        return [
            'total_team' => count($allDownline),
            'direct_referrals' => $directReferrals,
            'total_levels' => count($levelBreakdown),
            'active_members' => $activeMembers,
            'inactive_members' => $inactiveMembers,
            'level_breakdown' => $levelBreakdown
        ];
    }

    /**
     * Get all downline members recursively
     */
    protected function getAllDownlineMembers($associateId, $maxLevel = 10, $currentLevel = 1)
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $directMembers = MLMProfile::where('sponsor_id', $associateId)->get();
        $allMembers = [];

        foreach ($directMembers as $member) {
            $allMembers[] = [
                'user_id' => $member->user_id,
                'level' => $currentLevel,
                'mlm_profile' => $member
            ];

            // Get deeper levels recursively
            $deeperMembers = $this->getAllDownlineMembers($member->user_id, $maxLevel, $currentLevel + 1);
            $allMembers = array_merge($allMembers, $deeperMembers);
        }

        return $allMembers;
    }

    /**
     * Get earnings data for the associate
     */
    protected function getEarningsData($associateId)
    {
        // Total earnings from commissions
        $totalEarnings = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('status', 'paid')
            ->sum('amount');

        // Monthly earnings for the last 12 months
        $monthlyEarnings = DB::table('commissions')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as earnings')
            ->where('associate_id', $associateId)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('earnings', 'month');

        // Pending commissions
        $pendingCommissions = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('status', 'pending')
            ->sum('amount');

        // Recent payouts
        $recentPayouts = DB::table('payouts')
            ->where('associate_id', $associateId)
            ->where('status', 'completed')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_earnings' => $totalEarnings ?? 0,
            'monthly_earnings' => $monthlyEarnings,
            'pending_commissions' => $pendingCommissions ?? 0,
            'recent_payouts' => $recentPayouts
        ];
    }

    /**
     * Get business reports data
     */
    protected function getBusinessReports($associateId)
    {
        // Self business volume
        $selfBusiness = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('commission_type', 'direct')
            ->sum('amount');

        // Team business volume
        $teamBusiness = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('commission_type', 'team')
            ->sum('amount');

        // Payment collection from agents
        $agentPayments = DB::table('payments')
            ->where('collected_by', $associateId)
            ->sum('amount');

        // Monthly business growth
        $monthlyBusiness = DB::table('commissions')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as volume')
            ->where('associate_id', $associateId)
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('volume', 'month');

        return [
            'self_business' => $selfBusiness ?? 0,
            'team_business' => $teamBusiness ?? 0,
            'agent_payments' => $agentPayments ?? 0,
            'monthly_business' => $monthlyBusiness,
            'total_business' => ($selfBusiness ?? 0) + ($teamBusiness ?? 0)
        ];
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities($associateId)
    {
        $activities = [];

        // Recent commissions
        $recentCommissions = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'type' => 'commission',
                'title' => 'Commission Earned',
                'description' => 'Earned ₹' . number_format($commission->amount, 2) . ' from ' . $commission->commission_type,
                'date' => $commission->created_at,
                'icon' => 'fa-money'
            ];
        }

        // Recent referrals
        $recentReferrals = MLMProfile::where('sponsor_id', $associateId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentReferrals as $referral) {
            $activities[] = [
                'type' => 'referral',
                'title' => 'New Team Member',
                'description' => $referral->user->name . ' joined your team',
                'date' => $referral->created_at,
                'icon' => 'fa-user-plus'
            ];
        }

        // Sort by date and limit to 10
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get training progress
     */
    protected function getTrainingProgress($associateId)
    {
        $enrollments = TrainingEnrollment::where('user_id', $associateId)->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'active')->count();

        $averageProgress = $enrollments->avg('progress_percentage') ?? 0;

        return [
            'total_courses' => $totalCourses,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'average_progress' => round($averageProgress, 1),
            'enrollments' => $enrollments->take(5)
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics($associateId)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        // Current month performance
        $currentMonthEarnings = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('status', 'paid')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->sum('amount');

        $lastMonthEarnings = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('status', 'paid')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$lastMonth])
            ->sum('amount');

        // Growth calculation
        $earningsGrowth = 0;
        if ($lastMonthEarnings > 0) {
            $earningsGrowth = (($currentMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100;
        }

        // Team growth
        $currentTeamCount = $this->getTeamStatistics($associateId)['total_team'];

        return [
            'current_month_earnings' => $currentMonthEarnings ?? 0,
            'earnings_growth' => round($earningsGrowth, 1),
            'team_size' => $currentTeamCount,
            'active_referrals' => MLMProfile::where('sponsor_id', $associateId)
                ->whereHas('user', function($query) {
                    $query->where('status', 'active');
                })->count(),
            'rank_progress' => $this->calculateRankProgress($associateId)
        ];
    }

    /**
     * Calculate rank progress
     */
    protected function calculateRankProgress($associateId)
    {
        $totalEarnings = DB::table('commissions')
            ->where('associate_id', $associateId)
            ->where('status', 'paid')
            ->sum('amount');

        // Simple rank calculation based on earnings
        $ranks = [
            'Bronze' => 0,
            'Silver' => 50000,
            'Gold' => 200000,
            'Platinum' => 500000,
            'Diamond' => 1000000
        ];

        $currentRank = 'Bronze';
        $nextRank = 'Silver';
        $progressToNext = 0;

        foreach ($ranks as $rank => $threshold) {
            if ($totalEarnings >= $threshold) {
                $currentRank = $rank;
                $nextRankKey = array_search($rank, array_keys($ranks));
                $nextRankKeys = array_keys($ranks);
                if (isset($nextRankKeys[$nextRankKey + 1])) {
                    $nextRank = $nextRankKeys[$nextRankKey + 1];
                    $nextThreshold = $ranks[$nextRank];
                    $currentThreshold = $threshold;
                    $progressToNext = min(100, (($totalEarnings - $currentThreshold) / ($nextThreshold - $currentThreshold)) * 100);
                } else {
                    $nextRank = $rank; // Already at highest rank
                    $progressToNext = 100;
                }
            }
        }

        return [
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'progress_percentage' => round($progressToNext, 1),
            'total_earnings' => $totalEarnings ?? 0
        ];
    }

    /**
     * Get associate profile data
     */
    public function getProfile()
    {
        $user = Auth::user();
        $associate = Associate::where('user_id', $user->id)->first();

        if (!$associate) {
            return response()->json(['error' => 'Associate profile not found'], 404);
        }

        return response()->json([
            'associate' => $associate,
            'mlm_profile' => MLMProfile::where('user_id', $user->id)->first(),
            'bank_details' => [
                'bank_name' => $associate->bank_name,
                'account_number' => '****' . substr($associate->account_number, -4),
                'ifsc_code' => $associate->ifsc_code
            ]
        ]);
    }

    /**
     * Get team tree data
     */
    public function getTeamTree()
    {
        $user = Auth::user();
        $treeData = $this->mlmService->getTeamTree($user->id);

        return response()->json($treeData);
    }

    /**
     * Get downline members
     */
    public function getDownline()
    {
        $user = Auth::user();
        $downline = $this->mlmService->getDownlineMembers($user->id);

        return response()->json($downline);
    }
}
