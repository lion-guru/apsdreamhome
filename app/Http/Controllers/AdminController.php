<?php

namespace App\Http\Controllers;

use App\Services\Admin\AdminDashboardService;
use App\Http\Controllers\Controller;

/**
 * Admin Controller
 * Handles all admin panel operations
 */
class AdminController extends Controller
{
    private AdminDashboardService $adminService;

    public function __construct(AdminDashboardService $adminService)
    {
        $this->adminService = $adminService;
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->adminService->getDashboardStats();
            $recentActivities = $this->adminService->getRecentActivities();
            $propertyAnalytics = $this->adminService->getPropertyAnalytics();
            $userData = $this->adminService->getUserManagementData();
            $leadData = $this->adminService->getLeadManagementData();
            $bookingData = $this->adminService->getBookingManagementData();
            $systemHealth = $this->adminService->getSystemHealthStatus();

            return view('admin.dashboard', compact(
                'stats',
                'recentActivities',
                'propertyAnalytics',
                'userData',
                'leadData',
                'bookingData',
                'systemHealth'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard stats via AJAX
     */
    public function getDashboardStats()
    {
        try {
            $stats = $this->adminService->getDashboardStats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get recent activities via AJAX
     */
    public function getRecentActivities()
    {
        try {
            $limit = request('limit', 10);
            $activities = $this->adminService->getRecentActivities($limit);
            return response()->json(['success' => true, 'data' => $activities]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get property analytics via AJAX
     */
    public function getPropertyAnalytics()
    {
        try {
            $analytics = $this->adminService->getPropertyAnalytics();
            return response()->json(['success' => true, 'data' => $analytics]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get user management data via AJAX
     */
    public function getUserManagementData()
    {
        try {
            $data = $this->adminService->getUserManagementData();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get lead management data via AJAX
     */
    public function getLeadManagementData()
    {
        try {
            $data = $this->adminService->getLeadManagementData();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get booking management data via AJAX
     */
    public function getBookingManagementData()
    {
        try {
            $data = $this->adminService->getBookingManagementData();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get system health status via AJAX
     */
    public function getSystemHealthStatus()
    {
        try {
            $status = $this->adminService->getSystemHealthStatus();
            return response()->json(['success' => true, 'data' => $status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
