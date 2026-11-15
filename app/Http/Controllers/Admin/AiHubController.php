<?php
/**
 * AiHubController — AI Command Center
 *
 * Single entry point for every AI capability in the admin panel.
 * Shows RBAC-aware links + live provider/KB health so admins see at a glance
 * what AI can do and whether it's actually working.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\AI\RAGService;
use App\Services\ExecutiveAIService;

class AiHubController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();

        // RAG health snapshot
        $health = [];
        try {
            $health = RAGService::getInstance()->getHealth();
        } catch (\Throwable $e) {
            error_log('AiHub: RAG health failed: ' . $e->getMessage());
        }

        // Knowledge-base scope counts (RBAC-scoped)
        $visCounts = ['public' => 0, 'staff' => 0, 'admin' => 0];
        try {
            $rows = $this->db->fetchAll("SELECT visibility, COUNT(*) AS n FROM ai_knowledge_base GROUP BY visibility");
            foreach ($rows as $r) {
                if (isset($visCounts[$r['visibility']])) {
                    $visCounts[$r['visibility']] = (int)$r['n'];
                }
            }
        } catch (\Throwable $e) {
            error_log('AiHub: KB counts failed: ' . $e->getMessage());
        }

        // Executive assistant role list — what roles can open the board-level assistant
        $roles = [];
        try {
            $roles = (new ExecutiveAIService())->getSupportedRoles();
        } catch (\Throwable $e) {
            // non-fatal
        }

        $data = [
            'page_title' => 'AI Command Center — APS Dream Home',
            'page_description' => 'Every AI capability in one place, RBAC-aware',
            'rag_health' => $health,
            'kb_counts' => $visCounts,
            'supported_roles' => $roles,
        ];

        $this->render('admin/ai/hub', $data);
    }
}
