<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace

/**
 * Lead Kanban Controller
 * Visual pipeline management for sales team
 */
class LeadKanbanController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $stages = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
        $stageLabels = [
            'new' => ['label' => 'New', 'icon' => 'fa-plus', 'color' => 'primary'],
            'contacted' => ['label' => 'Contacted', 'icon' => 'fa-phone', 'color' => 'info'],
            'qualified' => ['label' => 'Qualified', 'icon' => 'fa-check', 'color' => 'success'],
            'proposal' => ['label' => 'Proposal', 'icon' => 'fa-file-alt', 'color' => 'warning'],
            'negotiation' => ['label' => 'Negotiation', 'icon' => 'fa-handshake', 'color' => 'secondary'],
            'closed_won' => ['label' => 'Won', 'icon' => 'fa-trophy', 'color' => 'success'],
            'closed_lost' => ['label' => 'Lost', 'icon' => 'fa-times-circle', 'color' => 'danger'],
            'nurture' => ['label' => 'Nurture', 'icon' => 'fa-seedling', 'color' => 'info']
        ];
        $leadsByStage = [];
        try {
            $sql = "SELECT id, name, email, phone, status, source, score, created_at FROM leads ORDER BY created_at DESC LIMIT 500";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $allLeads = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($stages as $s) $leadsByStage[$s] = [];
            foreach ($allLeads as $lead) {
                $s = $lead['status'] ?? 'new';
                if (!isset($leadsByStage[$s])) $leadsByStage[$s] = [];
                $leadsByStage[$s][] = $lead;
            }
        } catch (\Throwable $e) {
            // Table may not exist; render empty
            foreach ($stages as $s) $leadsByStage[$s] = [];
        }
        $stats = ['total' => 0];
        foreach ($leadsByStage as $s => $arr) $stats['total'] += count($arr);
        $this->render('admin/lead_kanban/index', [
            'page_title' => 'Lead Pipeline - APS Dream Home',
            'leadsByStage' => $leadsByStage,
            'stageLabels' => $stageLabels,
            'stages' => $stages,
            'stats' => $stats
        ]);
    }

    public function updateStage()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        $leadId = (int)($_POST['lead_id'] ?? $_GET['lead_id'] ?? 0);
        $newStage = $_POST['status'] ?? $_GET['status'] ?? '';
        $allowed = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
        if (!$leadId || !in_array($newStage, $allowed, true)) {
            echo json_encode(['error' => 'Invalid input']);
            exit;
        }
        try {
            $stmt = $this->db->prepare("UPDATE leads SET status = ? WHERE id = ?");
            $stmt->execute([$newStage, $leadId]);
            $userId = (int)($_SESSION['admin_id'] ?? 0);
            $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
            $this->db->prepare("INSERT INTO audit_log (user_id, user_role, action, entity_type, entity_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$userId, $role, 'kanban_move', 'lead', $leadId, "Moved lead to $newStage", $_SERVER['REMOTE_ADDR'] ?? null]);

            // WebSocket broadcast - tells all open kanban boards to update in place
            try {
                \App\Services\WebSocketBroadcaster::broadcastKanban([
                    'event' => 'stage_change',
                    'lead_id' => $leadId,
                    'new_stage' => $newStage,
                    'moved_by' => $userId,
                    'moved_at' => date('Y-m-d H:i:s')
                ], 'kanban_global');
            } catch (\Throwable $e) {
                error_log("LeadKanbanController: WS broadcast failed: " . $e->getMessage());
            }

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
