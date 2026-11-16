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

        // Global AI health flag (written by scripts/cron_ai_health.php)
        $degraded = false;
        try {
            $row = $this->db->fetchOne("SELECT config_value FROM service_configs WHERE service_name = 'ai_health' AND config_key = 'degraded' LIMIT 1");
            $degraded = (int)($row['config_value'] ?? 0) === 1;
        } catch (\Throwable $e) {
            error_log('AiHub: degraded flag read failed: ' . $e->getMessage());
        }

        // Churn cache counters — silent empty when nothing scored yet
        $churn = ['scored' => 0, 'high_critical' => 0];
        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) AS n, SUM(risk_level IN ('high','critical')) AS hc FROM lead_churn_predictions");
            $churn['scored'] = (int)($row['n'] ?? 0);
            $churn['high_critical'] = (int)($row['hc'] ?? 0);
        } catch (\Throwable $e) {
            // table may not exist in older dev DBs
            error_log('AiHub: churn counters failed: ' . $e->getMessage());
        }

        // Unassigned open leads (drives the Smart Assign card)
        $unassigned = 0;
        try {
            $row = $this->db->fetchOne("SELECT COUNT(*) AS n FROM leads WHERE assigned_to IS NULL AND deleted_at IS NULL AND status NOT IN ('closed_won','closed_lost','converted','dead','cancelled')");
            $unassigned = (int)($row['n'] ?? 0);
        } catch (\Throwable $e) {
            error_log('AiHub: unassigned count failed: ' . $e->getMessage());
        }

        // Colony inventory pressure — count colonies holding < 20% available plots (running thin)
        $lowStockColonies = 0;
        try {
            $row = $this->db->fetchOne("
                SELECT COUNT(*) AS n FROM (
                    SELECT colony_id, COALESCE(SUM(status = 'available'),0) AS avail, COUNT(*) AS total
                    FROM plots WHERE deleted_at IS NULL AND is_active = 1 GROUP BY colony_id
                ) t WHERE total > 0 AND (avail / total) < 0.20
            ");
            $lowStockColonies = (int)($row['n'] ?? 0);
        } catch (\Throwable $e) {
            error_log('AiHub: low-stock colonies failed: ' . $e->getMessage());
        }

        // Executive assistant role list - what roles can open the board-level assistant
        $roles = [];
        try {
            $roles = (new ExecutiveAIService())->getSupportedRoles();
        } catch (\Throwable $e) {
            // non-fatal
        }

        $data = [
            'page_title' => 'AI Command Center - APS Dream Home',
            'page_description' => 'Every AI capability in one place, RBAC-aware',
            'rag_health' => $health,
            'kb_counts' => $visCounts,
            'supported_roles' => $roles,
            'ai_degraded' => $degraded,
            'churn_stats' => $churn,
            'unassigned_leads' => $unassigned,
            'low_stock_colonies' => $lowStockColonies,
        ];

        $this->render('admin/ai/hub', $data);
    }
}
