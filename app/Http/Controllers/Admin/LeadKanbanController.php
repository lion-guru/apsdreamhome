<?php

namespace App\Http\Controllers\Admin;

use App\Services\CRMService;
use App\Services\CRMGuard;
use \App\Traits\TenantAwareTrait;

/**
 * Lead Kanban Controller — Professional Pipeline Board
 * Visual drag-drop pipeline with CRMService backend
 */
class LeadKanbanController extends AdminController
{
    use TenantAwareTrait;
    /** @var CRMService */
    private $crm;

    public function __construct()
    {
        parent::__construct();
        $this->crm = new CRMService();
    }

    /**
     * Pipeline board — all stages with leads, stats, filters
     */
    public function index()
    {
        $this->requireAdmin();

        $userId = (int)($_SESSION['admin_id'] ?? 0);
        $role   = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';

        // Filters
        $filters = [
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'source'      => $_GET['source'] ?? null,
            'role'        => $role,
        ];

        // Pipeline board from CRMService
        $board = $this->crm->getPipelineBoard($filters);

        // Dashboard stats
        $stats = $this->crm->getDashboardStats($userId, $role);

        // Pipeline totals
        $totalLeads    = 0;
        $totalValue    = 0;
        $wonValue      = 0;
        $wonCount      = 0;
        $activeLeads   = 0;

        foreach ($board as $col) {
            $totalLeads += $col['count'];
            $totalValue += $col['total_value'];
            if ($col['stage']['slug'] === 'won') {
                $wonValue = $col['total_value'];
                $wonCount = $col['count'];
            }
            if (!in_array($col['stage']['slug'], ['won', 'lost'])) {
                $activeLeads += $col['count'];
            }
        }

        $conversionRate = $totalLeads > 0 ? round(($wonCount / $totalLeads) * 100, 1) : 0;

        // Assignable users (for filter dropdown)
        $users = [];
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $users = $this->db->fetchAll(
                "SELECT id, name FROM users WHERE status = 'active'{$tidSql} ORDER BY name",
                $tidParams
            ) ?: [];
        } catch (\Throwable $e) {
            // fallback
        }

        // Source list
        $sources = [];
        try {
            $sources = $this->db->fetchAll(
                "SELECT DISTINCT source FROM leads WHERE source IS NOT NULL AND source != '' AND deleted_at IS NULL ORDER BY source"
            ) ?: [];
            $sources = array_column($sources, 'source');
        } catch (\Throwable $e) {
            $sources = ['website', 'referral', 'facebook', 'google', 'walk_in', 'call_in'];
        }

        return $this->render('admin/lead_kanban/index', [
            'page_title'      => 'Pipeline Board',
            'board'           => $board,
            'stats'           => $stats,
            'totalLeads'      => $totalLeads,
            'totalValue'      => $totalValue,
            'wonValue'        => $wonValue,
            'activeLeads'     => $activeLeads,
            'conversionRate'  => $conversionRate,
            'users'           => $users,
            'sources'         => $sources,
            'currentFilters'  => $filters,
        ]);
    }

    /**
     * AJAX: Move lead to new stage (from drag-drop)
     */
    public function updateStage()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $leadId   = (int)($_POST['lead_id'] ?? 0);
        $newStage = $_POST['status'] ?? '';
        $userId   = (int)($_SESSION['admin_id'] ?? 0);

        if (!$leadId || !$newStage) {
            echo json_encode(['error' => 'Missing lead_id or status']);
            exit;
        }

        $result = $this->crm->moveLeadToStage($leadId, $newStage, $userId);

        // WebSocket broadcast
        if ($result['success']) {
            try {
                \App\Services\WebSocketBroadcaster::broadcastKanban([
                    'event'      => 'stage_change',
                    'lead_id'    => $leadId,
                    'new_stage'  => $newStage,
                    'moved_by'   => $userId,
                    'moved_at'   => date('Y-m-d H:i:s'),
                ], 'kanban_global');
            } catch (\Throwable $e) {
                error_log("LeadKanbanController: WS broadcast failed: " . $e->getMessage());
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * AJAX: Get lead quick-view data (for modal on card click)
     */
    public function leadQuickView()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $leadId = (int)($_GET['id'] ?? 0);
        if (!$leadId) {
            echo json_encode(['error' => 'Missing lead ID']);
            exit;
        }

        $lead = $this->crm->getLeadById($leadId);
        echo json_encode($lead);
        exit;
    }

    /**
     * AJAX: Get pipeline stats (for live stat refresh)
     */
    public function pipelineStats()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $filters = [
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'source'      => $_GET['source'] ?? null,
        ];

        $board = $this->crm->getPipelineBoard($filters);

        $stats = [
            'total'   => 0,
            'active'  => 0,
            'won'     => 0,
            'lost'    => 0,
            'value'   => 0,
            'won_value' => 0,
            'columns' => [],
        ];

        foreach ($board as $col) {
            $slug = $col['stage']['slug'];
            $stats['total'] += $col['count'];
            $stats['value'] += $col['total_value'];

            if ($slug === 'won') {
                $stats['won'] = $col['count'];
                $stats['won_value'] = $col['total_value'];
            } elseif ($slug === 'lost') {
                $stats['lost'] = $col['count'];
            } else {
                $stats['active'] += $col['count'];
            }

            $stats['columns'][$slug] = [
                'count' => $col['count'],
                'value' => $col['total_value'],
            ];
        }

        $stats['conversion'] = $stats['total'] > 0
            ? round(($stats['won'] / $stats['total']) * 100, 1)
            : 0;

        echo json_encode($stats);
        exit;
    }
}
