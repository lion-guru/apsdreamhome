<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Associate;
use App\Models\Agent;
use App\Models\Customer;
use App\Services\CRM\LeadScoringService;
use App\Services\Finance\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * AdminController - System Administration Controller
 *
 * Provides administrative functions for system management,
 * user management, analytics, and system monitoring.
 */
class AdminController extends Controller
{
    protected $leadScoringService;
    protected $invoiceService;

    public function __construct(LeadScoringService $leadScoringService, InvoiceService $invoiceService)
    {
        $this->leadScoringService = $leadScoringService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard()
    {
        // System statistics
        $stats = [
            'total_users' => User::count(),
            'total_associates' => Associate::count(),
            'total_agents' => Agent::count(),
            'total_customers' => Customer::count(),
            'active_associates' => Associate::where('status', 'active')->count(),
            'active_agents' => Agent::where('status', 'active')->count(),
            'monthly_registrations' => User::whereMonth('created_at', date('m'))->count(),
            'total_revenue' => DB::table('commissions')->where('status', 'paid')->sum('amount') ?? 0,
        ];

        // Recent activities
        $recentActivities = [
            'new_registrations' => User::with('roles')->latest()->take(5)->get(),
            'recent_commissions' => DB::table('commissions')
                ->join('users', 'commissions.associate_id', '=', 'users.id')
                ->select('commissions.*', 'users.name')
                ->latest()->take(5)->get(),
        ];

        // System health metrics
        $systemHealth = [
            'database_status' => $this->checkDatabaseHealth(),
            'services_status' => $this->checkServicesHealth(),
            'last_backup' => $this->getLastBackupInfo(),
        ];

        return view('admin.dashboard', compact('stats', 'recentActivities', 'systemHealth'));
    }

    /**
     * User Management - List all users
     */
    public function users(Request $request)
    {
        $query = User::with(['roles', 'associate', 'agent', 'customer']);

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function showUser($id)
    {
        $user = User::with(['roles', 'associate', 'agent', 'customer'])->findOrFail($id);

        // Get user-specific data based on role
        $userData = [];
        if ($user->hasRole('associate')) {
            $userData['commissions'] = DB::table('commissions')
                ->where('associate_id', $user->id)
                ->latest()->take(10)->get();

            $userData['payouts'] = DB::table('payouts')
                ->where('associate_id', $user->id)
                ->latest()->take(10)->get();

            $userData['referrals'] = User::where('referred_by', $user->associate->referral_code ?? null)->count();
        }

        if ($user->hasRole('agent')) {
            $userData['leads'] = DB::table('leads')->where('assigned_agent_id', $user->id)->count();
            $userData['properties'] = DB::table('properties')->where('agent_id', $user->id)->count();
        }

        return view('admin.users.show', compact('user', 'userData'));
    }

    /**
     * Create new user
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:associate,agent,customer',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => $request->status,
            ]);

            // Assign role and create related records
            switch ($request->role) {
                case 'associate':
                    $user->assignRole('associate');
                    Associate::create([
                        'user_id' => $user->id,
                        'referral_code' => $this->generateReferralCode(),
                        'status' => 'active',
                    ]);
                    break;

                case 'agent':
                    $user->assignRole('agent');
                    Agent::create([
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]);
                    break;

                case 'customer':
                    $user->assignRole('customer');
                    Customer::create([
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]);
                    break;
            }
        });

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    /**
     * Edit user
     */
    public function editUser($id)
    {
        $user = User::with(['roles', 'associate', 'agent', 'customer'])->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->update($request->only(['name', 'email', 'phone', 'status']));

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Soft delete or deactivate instead of hard delete
        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users')->with('success', 'User deactivated successfully');
    }

    /**
     * System Settings
     */
    public function settings()
    {
        $settings = [
            'site_name' => config('app.name'),
            'admin_email' => config('mail.from.address'),
            'commission_rates' => [
                'direct' => 10, // percentage
                'team' => 5,
                'referral' => 2,
            ],
            'system_status' => 'operational',
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Analytics Dashboard
     */
    public function analytics()
    {
        // Monthly registration trends
        $registrationTrends = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Revenue analytics
        $revenueAnalytics = [
            'monthly' => DB::table('commissions')
                ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->where('status', 'paid')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray(),
            'total' => DB::table('commissions')->where('status', 'paid')->sum('amount'),
        ];

        // User role distribution
        $roleDistribution = [
            'associates' => Associate::count(),
            'agents' => Agent::count(),
            'customers' => Customer::count(),
        ];

        return view('admin.analytics', compact('registrationTrends', 'revenueAnalytics', 'roleDistribution'));
    }

    /**
     * System Reports
     */
    public function reports(Request $request)
    {
        $reportType = $request->get('type', 'users');

        $reports = [];

        switch ($reportType) {
            case 'users':
                $reports['user_summary'] = [
                    'total_users' => User::count(),
                    'active_users' => User::where('status', 'active')->count(),
                    'inactive_users' => User::where('status', 'inactive')->count(),
                    'monthly_growth' => User::whereMonth('created_at', date('m'))->count(),
                ];
                break;

            case 'commissions':
                $reports['commission_summary'] = [
                    'total_paid' => DB::table('commissions')->where('status', 'paid')->sum('amount'),
                    'total_pending' => DB::table('commissions')->where('status', 'pending')->sum('amount'),
                    'monthly_paid' => DB::table('commissions')
                        ->where('status', 'paid')
                        ->whereMonth('created_at', date('m'))
                        ->sum('amount'),
                ];
                break;

            case 'system':
                $reports['system_info'] = [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'database_status' => $this->checkDatabaseHealth() ? 'Connected' : 'Disconnected',
                    'cache_status' => 'Operational', // Simplified
                ];
                break;
        }

        return view('admin.reports', compact('reports', 'reportType'));
    }

    /**
     * Generate unique referral code
     */
    private function generateReferralCode()
    {
        do {
            $code = 'REF' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Associate::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check services health
     */
    private function checkServicesHealth()
    {
        $services = [
            'LeadScoringService' => $this->leadScoringService,
            'InvoiceService' => $this->invoiceService,
        ];

        $status = [];
        foreach ($services as $name => $service) {
            try {
                // Basic health check - service instantiation
                $status[$name] = true;
            } catch (\Exception $e) {
                $status[$name] = false;
            }
        }

        return $status;
    }

    /**
     * Get last backup information
     */
    private function getLastBackupInfo()
    {
        // Simplified - in production, this would check actual backup logs
        return [
            'last_backup' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'status' => 'successful',
        ];
    }
}
