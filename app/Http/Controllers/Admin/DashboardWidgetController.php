<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\UserDashboardLayout;
use App\Models\User;

class DashboardWidgetController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    public function index()
    {
        $userId = session('admin_id') ?? session('user_id');
        $layouts = UserDashboardLayout::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->get();

        $widgets = $this->getAvailableWidgets();

        return $this->render('admin/dashboard/customize', [
            'page_title' => 'Customize Dashboard',
            'layouts' => $layouts,
            'widgets' => $widgets,
        ]);
    }

    public function getWidgets()
    {
        $userId = session('admin_id') ?? session('user_id');
        $layout = UserDashboardLayout::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        return response()->json([
            'success' => true,
            'widgets' => $layout ? $layout->layout_config : $this->getDefaultLayout(),
        ]);
    }

    public function saveLayout()
    {
        $userId = session('admin_id') ?? session('user_id');
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $layoutConfig = $data['layout_config'] ?? [];
        $layoutName = $data['layout_name'] ?? 'Default';
        $isDefault = isset($data['is_default']) ? (bool)$data['is_default'] : true;

        if ($isDefault) {
            UserDashboardLayout::where('user_id', $userId)
                ->update(['is_default' => false]);
        }

        $layout = UserDashboardLayout::updateOrCreate(
            ['user_id' => $userId, 'layout_name' => $layoutName],
            [
                'layout_config' => $layoutConfig,
                'widgets_order' => $data['widgets_order'] ?? [],
                'is_default' => $isDefault,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Layout saved successfully',
            'layout' => $layout,
        ]);
    }

    public function deleteLayout($id)
    {
        $userId = session('admin_id') ?? session('user_id');
        $layout = UserDashboardLayout::where('user_id', $userId)
            ->where('id', $id)
            ->first();

        if (!$layout) {
            return response()->json([
                'success' => false,
                'message' => 'Layout not found',
            ], 404);
        }

        $layout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layout deleted',
        ]);
    }

    private function getAvailableWidgets()
    {
        $userRole = session('role') ?? 'customer';
        $allWidgets = [
            'stats_cards' => [
                'name' => 'Stats Cards',
                'icon' => 'chart-bar',
                'roles' => ['admin', 'super_admin', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'manager', 'director'],
                'component' => 'widget-stats-cards',
            ],
            'recent_activity' => [
                'name' => 'Recent Activity',
                'icon' => 'history',
                'roles' => ['admin', 'super_admin', 'manager', 'employee', 'associate', 'agent'],
                'component' => 'widget-recent-activity',
            ],
            'kpi_chart' => [
                'name' => 'KPI Chart',
                'icon' => 'chart-line',
                'roles' => ['admin', 'super_admin', 'ceo', 'cfo', 'coo', 'director', 'manager'],
                'component' => 'widget-kpi-chart',
            ],
            'quick_actions' => [
                'name' => 'Quick Actions',
                'icon' => 'bolt',
                'roles' => ['admin', 'super_associate', 'agent', 'employee', 'associate'],
                'component' => 'widget-quick-actions',
            ],
            'team_performance' => [
                'name' => 'Team Performance',
                'icon' => 'users',
                'roles' => ['admin', 'super_admin', 'manager', 'director', 'team_lead'],
                'component' => 'widget-team-performance',
            ],
            'commission_summary' => [
                'name' => 'Commission Summary',
                'icon' => 'dollar-sign',
                'roles' => ['associate', 'agent', 'manager', 'director'],
                'component' => 'widget-commission-summary',
            ],
            'pending_approvals' => [
                'name' => 'Pending Approvals',
                'icon' => 'clock',
                'roles' => ['admin', 'manager', 'director', 'super_admin'],
                'component' => 'widget-pending-approvals',
            ],
            'recent_bookings' => [
                'name' => 'Recent Bookings',
                'icon' => 'home',
                'roles' => ['admin', 'associate', 'agent', 'manager'],
                'component' => 'widget-recent-bookings',
            ],
        ];

        return array_filter($allWidgets, function ($widget) use ($userRole) {
            return in_array($userRole, $widget['roles']) || $userRole === 'super_admin';
        });
    }

    private function getDefaultLayout()
    {
        return [
            ['type' => 'stats_cards', 'x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
            ['type' => 'recent_activity', 'x' => 0, 'y' => 2, 'w' => 6, 'h' => 4],
            ['type' => 'quick_actions', 'x' => 6, 'y' => 2, 'w' => 6, 'h' => 4],
        ];
    }
}