<?php

/**
 * Agent Dashboard Controller
 * Advanced real estate agent dashboard with comprehensive features
 */

namespace App\Http\Controllers\Agent;

use App\Models\User;
use App\Models\Agent;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Property;
use App\Models\Commission;
use App\Models\AgentLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentDashboardController
{
    /**
     * Display the agent dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->first();

        if (!$agent) {
            return redirect()->route('agent.profile.setup')->with(
        ->with(['toArray'])
        ->with(['amount'])
        ->with(['title'])
        ->with(['name'])'error', 'Please complete your agent profile setup first.');
        }

        // Agent statistics
        $stats = $this->getAgentStats($user->id);

        // Recent activities
        $recentActivities = $this->getRecentActivities($user->id);

        // Performance metrics
        $performanceMetrics = $this->getPerformanceMetrics($user->id);

        // Upcoming tasks/appointments
        $upcomingTasks = $this->getUpcomingTasks($user->id);

        // Monthly performance chart data
        $monthlyData = $this->getMonthlyPerformance($user->id);

        return view('agents.dashboard', compact(
            'agent',
            'stats',
            'recentActivities',
            'performanceMetrics',
            'upcomingTasks',
            'monthlyData'
        ));
    }

    /**
     * Get comprehensive agent statistics
     */
    protected function getAgentStats($agentId)
    {
        // Total sales (properties sold)
        $totalSales = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->sum('price');

        // Commission earned
        $commissionEarned = Commission::where('agent_id', $agentId)
            ->where('status', 'paid')
            ->sum('amount');

        // Total customers
        $totalCustomers = Customer::where('agent_id', $agentId)->count();

        // Pending leads
        $pendingLeads = Lead::where('assigned_agent_id', $agentId)
            ->where('status', 'new')
            ->count();

        // Active properties
        $activeProperties = Property::where('agent_id', $agentId)
            ->where('status', 'active')
            ->count();

        // Conversion rate
        $totalLeads = Lead::where('assigned_agent_id', $agentId)->count();
        $convertedLeads = Lead::where('assigned_agent_id', $agentId)
            ->where('status', 'converted')
            ->count();
        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;

        // Average property price
        $avgPropertyPrice = Property::where('agent_id', $agentId)
            ->where('status', 'sold')
            ->avg('price') ?? 0;

        // Monthly target progress
        $currentMonth = now()->format('Y-m');
        $monthlySales = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->sum('price');

        $monthlyTarget = 5000000; // ₹50 lakh monthly target
        $targetProgress = min(100, ($monthlySales / $monthlyTarget) * 100);

        return [
            'total_sales' => $totalSales ?? 0,
            'commission_earned' => $commissionEarned ?? 0,
            'total_customers' => $totalCustomers,
            'pending_leads' => $pendingLeads,
            'active_properties' => $activeProperties,
            'conversion_rate' => round($conversionRate, 1),
            'avg_property_price' => round($avgPropertyPrice, 0),
            'monthly_sales' => $monthlySales ?? 0,
            'monthly_target' => $monthlyTarget,
            'target_progress' => round($targetProgress, 1)
        ];
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities($agentId)
    {
        $activities = [];

        // Recent leads
        $recentLeads = Lead::where('assigned_agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentLeads as $lead) {
            $activities[] = [
                'type' => 'lead',
                'title' => 'New Lead Assigned',
                'description' => $lead->name . ' - ' . $lead->phone,
                'date' => $lead->created_at,
                'icon' => 'fa-user-plus',
                'color' => 'primary'
            ];
        }

        // Recent property sales
        $recentSales = Property::where('agent_id', $agentId)
            ->where('status', 'sold')
            ->orderBy('updated_at', 'desc')
            ->limit(2)
            ->get();

        foreach ($recentSales as $property) {
            $activities[] = [
                'type' => 'sale',
                'title' => 'Property Sold',
                'description' => $property->title . ' - ₹' . number_format($property->price, 0),
                'date' => $property->updated_at,
                'icon' => 'fa-home',
                'color' => 'success'
            ];
        }

        // Recent commissions
        $recentCommissions = Commission::where('agent_id', $agentId)
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc')
            ->limit(2)
            ->get();

        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'type' => 'commission',
                'title' => 'Commission Paid',
                'description' => '₹' . number_format($commission->amount, 0) . ' credited',
                'date' => $commission->paid_at,
                'icon' => 'fa-money-bill-wave',
                'color' => 'warning'
            ];
        }

        // Sort by date and limit to 10
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics($agentId)
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        // Monthly comparison
        $currentMonthSales = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->sum('price');

        $lastMonthSales = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$lastMonth])
            ->sum('price');

        $salesGrowth = 0;
        if ($lastMonthSales > 0) {
            $salesGrowth = (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        }

        // Lead response time (average in hours)
        $avgResponseTime = DB::table('leads')
            ->where('assigned_agent_id', $agentId)
            ->whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_response')
            ->first();

        $avgResponseHours = round($avgResponseTime->avg_response ?? 24, 1);

        // Customer satisfaction (placeholder - would need actual ratings)
        $customerSatisfaction = 4.5; // Mock data

        // Rank based on performance
        $rank = $this->calculateAgentRank($agentId);

        return [
            'sales_growth' => round($salesGrowth, 1),
            'avg_response_time' => $avgResponseHours,
            'customer_satisfaction' => $customerSatisfaction,
            'rank' => $rank,
            'performance_score' => $this->calculatePerformanceScore($agentId)
        ];
    }

    /**
     * Get upcoming tasks and appointments
     */
    protected function getUpcomingTasks($agentId)
    {
        // Site visits scheduled
        $siteVisits = DB::table('lead_visits')
            ->join('leads', 'lead_visits.lead_id', '=', 'leads.id')
            ->where('leads.assigned_agent_id', $agentId)
            ->where('lead_visits.visit_date', '>=', now())
            ->where('lead_visits.visit_type', 'site_visit')
            ->orderBy('lead_visits.visit_date')
            ->limit(5)
            ->get();

        // Follow-up calls due
        $followUpsDue = Lead::where('assigned_agent_id', $agentId)
            ->where('next_followup_date', '>=', now())
            ->where('next_followup_date', '<=', now()->addDays(7))
            ->orderBy('next_followup_date')
            ->limit(3)
            ->get();

        $siteVisitsData = new \stdClass();
        $siteVisitsData->items = new \ArrayObject($siteVisits->toArray());
        $siteVisitsData->count = $siteVisits->count();

        $followUpsArray = $followUpsDue->toArray();
        $followUpsData = new \stdClass();
        $followUpsData->items = new \ArrayObject(array_map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }
            return is_object($item) ? get_object_vars($item) : $item;
        }, $followUpsArray));
        $followUpsData->count = $followUpsDue->count();

        return [
            'site_visits' => $siteVisitsData,
            'follow_ups' => $followUpsData,
            'total_upcoming' => $siteVisitsData->count + $followUpsData->count
        ];
    }

    /**
     * Get monthly performance data for charts
     */
    protected function getMonthlyPerformance($agentId)
    {
        $monthlyData = [];
        $startDate = now()->subMonths(11)->startOfMonth()->format('Y-m-d H:i:s');

        // Fetch sales grouped by month
        $salesData = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(price) as total_sales')
            ->groupBy('month')
            ->pluck('total_sales', 'month');

        // Fetch leads grouped by month
        $leadsData = Lead::where('assigned_agent_id', $agentId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        // Fetch commissions grouped by month
        $commissionsData = Commission::where('agent_id', $agentId)
            ->where('status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, SUM(amount) as total_commission')
            ->groupBy('month')
            ->pluck('total_commission', 'month');

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthYear = $date->format('Y-m');

            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'sales' => $salesData[$monthYear] ?? 0,
                'leads' => $leadsData[$monthYear] ?? 0,
                'commissions' => $commissionsData[$monthYear] ?? 0
            ];
        }

        return $monthlyData;
    }

    /**
     * Calculate agent rank
     */
    protected function calculateAgentRank($agentId)
    {
        $totalSales = DB::table('properties')
            ->where('agent_id', $agentId)
            ->where('status', 'sold')
            ->sum('price');

        if ($totalSales >= 50000000) return 'Diamond Agent'; // ₹5 crore+
        if ($totalSales >= 20000000) return 'Platinum Agent'; // ₹2 crore+
        if ($totalSales >= 10000000) return 'Gold Agent'; // ₹1 crore+
        if ($totalSales >= 5000000) return 'Silver Agent'; // ₹50 lakh+
        return 'Bronze Agent';
    }

    /**
     * Calculate performance score (0-100)
     */
    protected function calculatePerformanceScore($agentId)
    {
        $stats = $this->getAgentStats($agentId);

        $score = 0;

        // Sales volume (40 points)
        $salesScore = min(40, ($stats['total_sales'] / 10000000) * 40); // Max at ₹1 crore
        $score += $salesScore;

        // Conversion rate (20 points)
        $conversionScore = min(20, $stats['conversion_rate'] * 0.2);
        $score += $conversionScore;

        // Customer count (20 points)
        $customerScore = min(20, $stats['total_customers'] * 2); // Max at 10 customers
        $score += $customerScore;

        // Target achievement (20 points)
        $targetScore = min(20, ($stats['monthly_sales'] / $stats['monthly_target']) * 20);
        $score += $targetScore;

        return round($score, 1);
    }

    /**
     * Get agent profile data
     */
    public function getProfile()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->first();

        if (!$agent) {
            return response()->json(['error' => 'Agent profile not found'], 404);
        }

        $stats = $this->getAgentStats($user->id);

        return response()->json([
            'agent' => $agent,
            'user' => $user,
            'stats' => $stats,
            'rank' => $this->calculateAgentRank($user->id),
            'performance_score' => $this->calculatePerformanceScore($user->id)
        ]);
    }

    /**
     * Get agent's leads
     */
    public function getLeads()
    {
        $user = Auth::user();
        $leads = Lead::where('assigned_agent_id', $user->id)
            ->with(['customer', 'property'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($leads);
    }

    /**
     * Get agent's customers
     */
    public function getCustomers()
    {
        $user = Auth::user();
        $customers = Customer::where('agent_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($customers);
    }

    /**
     * Get agent's properties
     */
    public function getProperties()
    {
        $user = Auth::user();
        $properties = Property::where('agent_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($properties);
    }
}
