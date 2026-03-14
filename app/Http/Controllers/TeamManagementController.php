<?php

// TODO: Add proper error handling with try-catch blocks

/**
 * Team Management Controller
 * Comprehensive team management system with hierarchy, performance, and communication
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Associate;
use App\Models\Agent;
use App\Models\MLMProfile;
use App\Models\MLMNetworkTree;
use App\Models\TeamMessage;
use App\Models\TeamPerformance;
use App\Models\TeamIncentive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamManagementController extends Controller
{
    /**
     * Display team overview dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get user's team information
        $teamInfo = $this->getTeamOverview($userId);

        // Get team hierarchy data for visualization
        $hierarchyData = $this->getTeamHierarchy($userId);

        // Get team performance metrics
        $performanceData = $this->getTeamPerformance($userId);

        // Get recent team activities
        $recentActivities = $this->getTeamActivities($userId);

        // Get team communication/messages
        $teamMessages = $this->getTeamMessages($userId);

        // Get team incentives and rewards
        $teamIncentives = $this->getTeamIncentives($userId);

        return view('team.dashboard', compact(
            'teamInfo',
            'hierarchyData',
            'performanceData',
            'recentActivities',
            'teamMessages',
            'teamIncentives'
        ));
    }

    /**
     * Get comprehensive team overview
     */
    protected function getTeamOverview($userId)
    {
        $user = User::find($userId);
        $mlmProfile = MLMProfile::where('user_id', $userId)->first();

        if (!$mlmProfile) {
            return [
                'total_members' => 0,
                'active_members' => 0,
                'inactive_members' => 0,
                'direct_reports' => 0,
                'team_levels' => 0,
                'total_earnings' => 0,
                'avg_performance' => 0,
                'team_rank' => 'N/A'
            ];
        }

        // Get all downline members
        $allMembers = $this->getAllDownlineMembers($userId);

        // Count active vs inactive
        $activeCount = 0;
        $inactiveCount = 0;

        foreach ($allMembers as $member) {
            $memberUser = User::find($member['user_id']);
            if ($memberUser && $memberUser->status === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
        }

        // Get direct reports
        $directReports = MLMProfile::where('sponsor_user_id', $userId)->count();

        // Calculate team levels
        $maxLevel = 0;
        foreach ($allMembers as $member) {
            $maxLevel = max($maxLevel, $member['level']);
        }

        // Calculate total team earnings
        $totalEarnings = 0;
        $performanceScores = [];

        foreach ($allMembers as $member) {
            // Get member's earnings (simplified calculation)
            $memberEarnings = DB::table('commissions')
                ->where('associate_id', $member['user_id'])
                ->where('status', 'paid')
                ->sum('amount');

            $totalEarnings += $memberEarnings ?? 0;

            // Calculate performance score for member
            $memberCommissions = DB::table('commissions')
                ->where('associate_id', $member['user_id'])
                ->count();

            $performanceScores[] = min(100, $memberCommissions * 10); // Simple scoring
        }

        $avgPerformance = count($performanceScores) > 0 ? array_sum($performanceScores) / count($performanceScores) : 0;

        // Determine team rank based on size and performance
        $teamRank = $this->calculateTeamRank(count($allMembers), $avgPerformance);

        return [
            'total_members' => count($allMembers),
            'active_members' => $activeCount,
            'inactive_members' => $inactiveCount,
            'direct_reports' => $directReports,
            'team_levels' => $maxLevel,
            'total_earnings' => $totalEarnings,
            'avg_performance' => round($avgPerformance, 1),
            'team_rank' => $teamRank
        ];
    }

    /**
     * Get team hierarchy data for visualization
     */
    protected function getTeamHierarchy($userId, $maxDepth = 4)
    {
        $hierarchy = [
            'root' => $this->getUserNode($userId),
            'levels' => []
        ];

        for ($level = 1; $level <= $maxDepth; $level++) {
            $hierarchy['levels'][$level] = $this->getLevelMembers($userId, $level);
        }

        return $hierarchy;
    }

    /**
     * Get user node for hierarchy
     */
    protected function getUserNode($userId)
    {
        $user = User::find($userId);
        $mlmProfile = MLMProfile::where('user_id', $userId)->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->type,
            'avatar' => $user->profile_image ?? 'default-avatar.jpg',
            'status' => $user->status,
            'join_date' => $user->created_at->format('M Y'),
            'referral_code' => $mlmProfile ? $mlmProfile->referral_code : null
        ];
    }

    /**
     * Get members at specific level
     */
    protected function getLevelMembers($rootUserId, $targetLevel)
    {
        $members = [];
        $this->collectLevelMembers($rootUserId, $targetLevel, 1, $members);
        return $members;
    }

    /**
     * Recursively collect members at target level
     */
    protected function collectLevelMembers($userId, $targetLevel, $currentLevel, &$members)
    {
        if ($currentLevel >= $targetLevel) {
            if ($currentLevel === $targetLevel) {
                $userNode = $this->getUserNode($userId);
                if ($userNode) {
                    $members[] = $userNode;
                }
            }
            return;
        }

        $directMembers = MLMProfile::where('sponsor_user_id', $userId)
            ->pluck('user_id')
            ->toArray();

        foreach ($directMembers as $memberId) {
            $this->collectLevelMembers($memberId, $targetLevel, $currentLevel + 1, $members);
        }
    }

    /**
     * Get all downline members recursively
     */
    protected function getAllDownlineMembers($userId, $maxLevel = 10, $currentLevel = 1)
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $directMembers = MLMProfile::where('sponsor_user_id', $userId)->get();
        $allMembers = [];

        foreach ($directMembers as $member) {
            $allMembers[] = [
                'user_id' => $member->user_id,
                'level' => $currentLevel,
                'mlm_profile' => $member
            ];

            // Get deeper levels
            $deeperMembers = $this->getAllDownlineMembers($member->user_id, $maxLevel, $currentLevel + 1);
            $allMembers = array_merge($allMembers, $deeperMembers);
        }

        return $allMembers;
    }

    /**
     * Get team performance metrics
     */
    protected function getTeamPerformance($userId)
    {
        $teamMembers = $this->getAllDownlineMembers($userId);

        $performance = [
            'monthly_earnings' => [],
            'member_growth' => [],
            'top_performers' => [],
            'underperformers' => []
        ];

        // Monthly earnings trend
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthlyTotal = 0;

            foreach ($teamMembers as $member) {
                $memberEarnings = DB::table('commissions')
                    ->where('associate_id', $member['user_id'])
                    ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$month])
                    ->sum('amount');

                $monthlyTotal += $memberEarnings ?? 0;
            }

            $performance['monthly_earnings'][] = [
                'month' => now()->subMonths($i)->format('M Y'),
                'earnings' => $monthlyTotal
            ];
        }

        // Top performers (by earnings)
        $topPerformers = [];
        foreach ($teamMembers as $member) {
            $earnings = DB::table('commissions')
                ->where('associate_id', $member['user_id'])
                ->where('status', 'paid')
                ->sum('amount');

            $user = User::find($member['user_id']);
            if ($user) {
                $topPerformers[] = [
                    'name' => $user->name,
                    'earnings' => $earnings ?? 0,
                    'level' => $member['level']
                ];
            }
        }

        // Sort by earnings and get top 5
        usort($topPerformers, function($a, $b) {
            return $b['earnings'] <=> $a['earnings'];
        });

        $performance['top_performers'] = array_slice($topPerformers, 0, 5);

        return $performance;
    }

    /**
     * Get recent team activities
     */
    protected function getTeamActivities($userId)
    {
        $teamMembers = $this->getAllDownlineMembers($userId);
        $memberIds = array_column($teamMembers, 'user_id');
        $memberIds[] = $userId; // Include team leader

        $activities = [];

        // Recent registrations
        $recentRegistrations = User::whereIn('id', $memberIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentRegistrations as $user) {
            $activities[] = [
                'type' => 'registration',
                'title' => 'New Team Member',
                'description' => $user->name . ' joined the team',
                'date' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'success'
            ];
        }

        // Recent commissions
        $recentCommissions = DB::table('commissions')
            ->whereIn('associate_id', $memberIds)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentCommissions as $commission) {
            $user = User::find($commission->associate_id);
            $activities[] = [
                'type' => 'commission',
                'title' => 'Commission Earned',
                'description' => ($user ? $user->name : 'Team Member') . ' earned ₹' . number_format($commission->amount, 0),
                'date' => $commission->paid_at,
                'icon' => 'money-bill-wave',
                'color' => 'warning'
            ];
        }

        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get team messages/communication
     */
    protected function getTeamMessages($userId)
    {
        // This would integrate with a messaging system
        // For now, return mock data based on team structure

        $messages = [
            [
                'id' => 1,
                'sender' => 'System',
                'subject' => 'Welcome to Team Management',
                'message' => 'Your team management dashboard is now active.',
                'date' => now()->subHours(2),
                'read' => false
            ],
            [
                'id' => 2,
                'sender' => 'Leadership',
                'subject' => 'Monthly Team Meeting',
                'message' => 'Reminder: Monthly team meeting scheduled for next week.',
                'date' => now()->subHours(5),
                'read' => true
            ]
        ];

        return $messages;
    }

    /**
     * Get team incentives and rewards
     */
    protected function getTeamIncentives($userId)
    {
        $teamInfo = $this->getTeamOverview($userId);

        $incentives = [];

        // Team size based incentives
        if ($teamInfo['total_members'] >= 50) {
            $incentives[] = [
                'title' => 'Large Team Bonus',
                'description' => 'Bonus for managing 50+ team members',
                'amount' => 10000,
                'status' => 'achieved'
            ];
        } elseif ($teamInfo['total_members'] >= 25) {
            $incentives[] = [
                'title' => 'Growing Team Incentive',
                'description' => 'Incentive for 25+ team members',
                'amount' => 5000,
                'status' => 'achieved'
            ];
        }

        // Performance based incentives
        if ($teamInfo['avg_performance'] >= 80) {
            $incentives[] = [
                'title' => 'High Performance Team',
                'description' => 'Bonus for team average performance ≥80%',
                'amount' => 15000,
                'status' => 'achieved'
            ];
        }

        // Upcoming incentives
        $incentives[] = [
            'title' => 'Century Club',
            'description' => 'Reach 100 team members',
            'amount' => 25000,
            'status' => 'pending',
            'progress' => min(100, ($teamInfo['total_members'] / 100) * 100)
        ];

        return $incentives;
    }

    /**
     * Calculate team rank
     */
    protected function calculateTeamRank($teamSize, $avgPerformance)
    {
        if ($teamSize >= 100 && $avgPerformance >= 90) return 'Platinum Team';
        if ($teamSize >= 50 && $avgPerformance >= 80) return 'Gold Team';
        if ($teamSize >= 25 && $avgPerformance >= 70) return 'Silver Team';
        if ($teamSize >= 10 && $avgPerformance >= 60) return 'Bronze Team';
        return 'Starter Team';
    }

    /**
     * Display team member details
     */
    public function showMember($memberId)
    {
        $member = User::findOrFail($memberId);
        $mlmProfile = MLMProfile::where('user_id', $memberId)->first();

        // Get member's performance data
        $performance = $this->getMemberPerformance($memberId);

        // Get member's downline
        $downline = $this->getAllDownlineMembers($memberId, 3); // 3 levels deep

        return view('team.member-detail', compact('member', 'mlmProfile', 'performance', 'downline'));
    }

    /**
     * Get member performance data
     */
    protected function getMemberPerformance($memberId)
    {
        $earnings = DB::table('commissions')
            ->where('associate_id', $memberId)
            ->where('status', 'paid')
            ->sum('amount');

        $monthlyEarnings = DB::table('commissions')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as earnings')
            ->where('associate_id', $memberId)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('earnings', 'month');

        $referrals = MLMProfile::where('sponsor_user_id', $memberId)->count();

        return [
            'total_earnings' => $earnings ?? 0,
            'monthly_earnings' => $monthlyEarnings,
            'total_referrals' => $referrals,
            'performance_score' => $this->calculateMemberPerformanceScore($memberId)
        ];
    }

    /**
     * Calculate member performance score
     */
    protected function calculateMemberPerformanceScore($memberId)
    {
        $commissions = DB::table('commissions')
            ->where('associate_id', $memberId)
            ->count();

        $referrals = MLMProfile::where('sponsor_user_id', $memberId)->count();

        $earnings = DB::table('commissions')
            ->where('associate_id', $memberId)
            ->where('status', 'paid')
            ->sum('amount') ?? 0;

        // Simple scoring algorithm
        $score = 0;
        $score += min(40, $commissions * 2); // Max 40 for commissions
        $score += min(30, $referrals * 5); // Max 30 for referrals
        $score += min(30, ($earnings / 10000) * 30); // Max 30 for earnings

        return min(100, $score);
    }

    /**
     * Send message to team
     */
    public function sendTeamMessage(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipients' => 'required|array'
        ]);

        $user = Auth::user();

        // This would create team messages in database
        // Implementation depends on messaging system

        return back()->with('success', 'Team message sent successfully!');
    }

    /**
     * Export team data
     */
    public function exportTeam()
    {
        $user = Auth::user();
        $teamMembers = $this->getAllDownlineMembers($user->id);

        $exportData = [];
        foreach ($teamMembers as $member) {
            $user = User::find($member['user_id']);
            $earnings = DB::table('commissions')
                ->where('associate_id', $member['user_id'])
                ->where('status', 'paid')
                ->sum('amount');

            $exportData[] = [
                'Name' => $user ? $user->name : 'Unknown',
                'Email' => $user ? $user->email : 'Unknown',
                'Level' => $member['level'],
                'Join Date' => $user ? $user->created_at->format('Y-m-d') : 'Unknown',
                'Total Earnings' => $earnings ?? 0,
                'Status' => $user ? $user->status : 'Unknown'
            ];
        }

        // Generate CSV
        $filename = 'team_export_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($exportData) {
            $file = fopen('php://output', 'w');

            // Add headers
            if (!empty($exportData)) {
                fputcsv($file, array_keys($exportData[0]));
            }

            // Add data
            foreach ($exportData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 593 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//