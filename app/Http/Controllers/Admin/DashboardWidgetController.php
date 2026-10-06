<?php

namespace App\Http\Controllers\Admin;

use App\Traits\TenantAwareTrait;

class DashboardWidgetController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    private function currentUserId(): int
    {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $layouts = $this->db->fetchAll(
                "SELECT * FROM user_dashboard_layouts WHERE user_id = ? ORDER BY is_default DESC, id ASC",
                [$this->currentUserId()]
            );
        } catch (\Throwable $e) {
            $layouts = [];
        }

        $widgets = $this->getAvailableWidgets();

        return $this->render('admin/dashboard/customize', [
            'page_title' => 'Customize Dashboard',
            'layouts' => $layouts,
            'widgets' => $widgets,
        ]);
    }

    public function getWidgets()
    {
        $this->requireAdmin();
        try {
            $layout = $this->db->fetchOne(
                "SELECT * FROM user_dashboard_layouts WHERE user_id = ? AND is_default = 1 ORDER BY id DESC LIMIT 1",
                [$this->currentUserId()]
            );
            $widgets = $layout && !empty($layout['layout_config'])
                ? (json_decode($layout['layout_config'], true) ?: $this->getDefaultLayout())
                : $this->getDefaultLayout();
        } catch (\Throwable $e) {
            $widgets = $this->getDefaultLayout();
        }

        return $this->jsonResponse(['success' => true, 'widgets' => $widgets]);
    }

    public function getLayout($id)
    {
        $this->requireAdmin();
        try {
            $layout = $this->db->fetchOne(
                "SELECT * FROM user_dashboard_layouts WHERE id = ? AND user_id = ? LIMIT 1",
                [(int)$id, $this->currentUserId()]
            );
        } catch (\Throwable $e) {
            $layout = null;
        }

        if (!$layout) {
            return $this->jsonError('Layout not found', 404);
        }
        if (!empty($layout['layout_config']) && is_string($layout['layout_config'])) {
            $layout['layout_config'] = json_decode($layout['layout_config'], true);
        }
        return $this->jsonResponse(['success' => true, 'layout' => $layout]);
    }

    public function saveLayout()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $userId = $this->currentUserId();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $layoutConfig = $data['layout_config'] ?? [];
        $layoutName = trim($data['layout_name'] ?? 'Default') ?: 'Default';
        $isDefault = isset($data['is_default']) ? (int)(bool)$data['is_default'] : 1;

        try {
            if ($isDefault) {
                $this->db->execute(
                    "UPDATE user_dashboard_layouts SET is_default = 0 WHERE user_id = ?",
                    [$userId]
                );
            }
            $existing = $this->db->fetchOne(
                "SELECT id FROM user_dashboard_layouts WHERE user_id = ? AND layout_name = ? LIMIT 1",
                [$userId, $layoutName]
            );
            $row = [
                'layout_config' => json_encode($layoutConfig),
                'widgets_order' => json_encode($data['widgets_order'] ?? []),
                'is_default' => $isDefault,
            ];
            if ($existing) {
                $row['updated_at'] = date('Y-m-d H:i:s');
                $this->db->update('user_dashboard_layouts', $row, 'id = :wid', ['wid' => $existing['id']]);
                $layoutId = (int)$existing['id'];
            } else {
                $row['user_id'] = $userId;
                $row['layout_name'] = $layoutName;
                $row['created_at'] = date('Y-m-d H:i:s');
                $row['updated_at'] = date('Y-m-d H:i:s');
                $this->db->insert('user_dashboard_layouts', $row);
                $layoutId = (int)$this->db->lastInsertId();
            }
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Layout saved successfully',
            'layout_id' => $layoutId,
        ]);
    }

    public function deleteLayout($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $deleted = $this->db->execute(
                "DELETE FROM user_dashboard_layouts WHERE id = ? AND user_id = ?",
                [(int)$id, $this->currentUserId()]
            )->rowCount() > 0;
        } catch (\Throwable $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }

        if (!$deleted) {
            return $this->jsonError('Layout not found', 404);
        }

        return $this->jsonResponse(['success' => true, 'message' => 'Layout deleted']);
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