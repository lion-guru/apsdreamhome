<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\BaseController;
use Exception;

class ProfessionalDashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Main Dashboard View
     */
    public function index()
    {
        $user = $this->auth->user();
        
        // Determine the professional type for specialized tools
        $professionalType = $this->getProfessionalType($user);
        
        $data = [
            'page_title' => 'Professional Dashboard - ' . $this->getConfig('app_name'),
            'user' => $user,
            'professional_type' => $professionalType,
            'stats' => $this->getOverviewStats($user),
            'recent_leads' => $this->getRecentLeads($user),
            'upcoming_visits' => $this->getUpcomingVisits($user),
            'accounting' => $this->getAccountingSummary($user),
            'system_alerts' => $this->getSystemAlerts($user)
        ];

        return $this->render('saas/dashboard', $data);
    }

    /**
     * Determine professional role for UI customization
     */
    private function getProfessionalType($user)
    {
        $role = $user->utype ?? $user->role ?? 'freelancer';
        $jobRole = strtolower($user->job_role ?? '');

        if (strpos($jobRole, 'builder') !== false) return 'builder';
        if (strpos($jobRole, 'contractor') !== false) return 'contractor';
        if (strpos($jobRole, 'rental') !== false) return 'rental';
        if (strpos($jobRole, 'resell') !== false) return 'resell';
        
        return $role === 'associate' ? 'agent' : 'freelancer';
    }

    /**
     * Overview Statistics
     */
    private function getOverviewStats($user)
    {
        $leadModel = $this->model('Lead');
        $propertyModel = $this->model('Property');
        
        return [
            'total_leads' => $leadModel->count(['assigned_to' => $user->uid]),
            'new_leads' => $leadModel->count(['assigned_to' => $user->uid, 'status' => 'new']),
            'active_listings' => $propertyModel->count(['added_by' => $user->uid, 'status' => 'active']),
            'conversions' => $leadModel->count(['assigned_to' => $user->uid, 'status' => 'converted'])
        ];
    }

    /**
     * Recent Leads for the Dashboard
     */
    private function getRecentLeads($user)
    {
        $leadModel = $this->model('Lead');
        return $leadModel->all([
            'where' => ['assigned_to' => $user->uid],
            'order' => 'created_at DESC',
            'limit' => 5
        ]);
    }

    /**
     * Upcoming Property Visits
     */
    private function getUpcomingVisits($user)
    {
        $visitModel = $this->model('PropertyVisit');
        return $visitModel->all([
            'where' => [
                'associate_id' => $user->uid,
                'visit_date >=' => date('Y-m-d')
            ],
            'order' => 'visit_date ASC, visit_time ASC',
            'limit' => 5
        ]);
    }

    /**
     * "Lekha-Jhokha" - Basic Accounting Summary
     */
    private function getAccountingSummary($user)
    {
        $payoutModel = $this->model('Payout');
        
        return [
            'pending_commission' => $payoutModel->sum('amount', ['user_id' => $user->uid, 'status' => 'pending']),
            'total_earned' => $payoutModel->sum('amount', ['user_id' => $user->uid, 'status' => 'paid']),
            'recent_transactions' => $payoutModel->all([
                'where' => ['user_id' => $user->uid],
                'order' => 'created_at DESC',
                'limit' => 5
            ])
        ];
    }

    /**
     * Recent System Alerts/Notifications
     */
    private function getSystemAlerts($user)
    {
        $alertModel = $this->model('SystemAlert');
        return $alertModel->all([
            'where' => [
                'user_id' => $user->uid,
                'is_read' => 0
            ],
            'order' => 'created_at DESC',
            'limit' => 5
        ]);
    }
}
