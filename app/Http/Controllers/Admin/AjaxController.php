<?php

namespace App\Http\Controllers\Admin;

class AjaxController extends AdminController
{
    /**
     * Advanced search across admin modules
     * GET /admin/ajax/advanced-search
     */
    public function advancedSearch()
    {
        require APP_PATH . '/views/admin/ajax/advanced_search.php';
    }

    /**
     * Consolidated dashboard API
     * GET /admin/ajax/consolidated-dashboard
     */
    public function consolidatedDashboard()
    {
        require APP_PATH . '/views/admin/ajax/consolidated_dashboard_api.php';
    }

    /**
     * Export dashboard data (CSV/PDF)
     * GET /admin/ajax/export-dashboard-data
     */
    public function exportDashboardData()
    {
        require APP_PATH . '/views/admin/ajax/export_dashboard_data.php';
    }

    /**
     * Generate AI follow-up message
     * POST /admin/ajax/generate-followup
     */
    public function generateFollowup()
    {
        require APP_PATH . '/views/admin/ajax/generate-followup.php';
    }

    /**
     * Get chart data for dashboards
     * GET /admin/ajax/get-chart-data
     */
    public function getChartData()
    {
        require APP_PATH . '/views/admin/ajax/get-chart-data.php';
    }

    /**
     * Get component by ID
     * GET /admin/ajax/get-component
     */
    public function getComponent()
    {
        require APP_PATH . '/views/admin/ajax/get-component.php';
    }

    /**
     * Get lead activity timeline & AI summary
     * GET /admin/ajax/get-lead-timeline
     */
    public function getLeadTimeline()
    {
        require APP_PATH . '/views/admin/ajax/get-lead-timeline.php';
    }

    /**
     * Get recent activity
     * GET /admin/ajax/get-recent-activity
     */
    public function getRecentActivity()
    {
        require APP_PATH . '/views/admin/ajax/get_recent_activity.php';
    }

    /**
     * Get system status
     * GET /admin/ajax/get-system-status
     */
    public function getSystemStatus()
    {
        require APP_PATH . '/views/admin/ajax/get_system_status.php';
    }

    /**
     * Global search across admin modules
     * GET /admin/ajax/global-search
     */
    public function globalSearch()
    {
        require APP_PATH . '/views/admin/ajax/global_search.php';
    }

    /**
     * Save content from visual editor
     * POST /admin/ajax/save-content
     */
    public function saveContent()
    {
        require APP_PATH . '/views/admin/ajax/save-content.php';
    }
}
