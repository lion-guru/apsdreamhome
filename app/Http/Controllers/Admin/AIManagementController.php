<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class AIManagementController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'AI Management';
        $this->render('admin/ai-management/index');
    }

    public function integrations()
    {
        $this->requireAdmin();
        try {
            // Live column is `integration_name` (ai_integrations has no tool_name).
            $integrations = $this->db->fetchAll("SELECT * FROM ai_integrations ORDER BY integration_name ASC") ?: [];
            $stats = [
                'total' => count($integrations),
                'active' => count(array_filter($integrations, fn($r) => ($r['status'] ?? '') === 'active')),
                'inactive' => count(array_filter($integrations, fn($r) => ($r['status'] ?? '') === 'inactive')),
            ];
        } catch (\Throwable $e) {
            $integrations = [];
            $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
        return $this->render('admin/ai-management/integrations', [
            'page_title' => 'AI Integrations',
            'integrations' => $integrations,
            'stats' => $stats,
        ]);
    }

    public function toggleIntegration($id)
    {
        $this->requireAdmin();
        try {
            $row = $this->db->fetch("SELECT id, status FROM ai_integrations WHERE id = ?", [(int)$id]);
            if (!$row) {
                return $this->jsonResponse(['success' => false, 'message' => 'Integration not found'], 404);
            }
            $newStatus = ($row['status'] ?? '') === 'active' ? 'inactive' : 'active';
            [$tw, $tp] = $this->tenantWhere();
            $this->db->query("UPDATE ai_integrations SET status = ? WHERE id = ?" . $tw, array_merge([$newStatus, (int)$id], $tp));
            return $this->jsonResponse(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function suggestions()
    {
        $this->requireAdmin();
        try {
            $suggestions = $this->db->fetchAll("
                SELECT s.*, u.name as user_name, u.email as user_email,
                       COALESCE(p.title, up.name) as property_title
                FROM ai_property_suggestions s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN properties p ON s.property_id = p.id
                LEFT JOIN user_properties up ON s.property_id = up.id
                ORDER BY s.created_at DESC
                LIMIT 100
            ") ?: [];
        } catch (\Throwable $e) {
            $suggestions = [];
        }
        return $this->render('admin/ai-management/suggestions', [
            'page_title' => 'AI Property Suggestions',
            'suggestions' => $suggestions,
        ]);
    }

    public function chatbotLogs()
    {
        $this->requireAdmin();
        try {
            $logs = $this->db->fetchAll("
                SELECT c.*, u.name as user_name, u.email as user_email
                FROM ai_chatbot_interactions c
                LEFT JOIN users u ON c.user_id = u.id
                ORDER BY c.created_at DESC
                LIMIT 100
            ") ?: [];
            $stats = [
                'total' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_chatbot_interactions")['c'] ?? 0,
                'avg_satisfaction' => $this->db->fetch("SELECT COALESCE(AVG(satisfaction_score), 0) as avg FROM ai_chatbot_interactions WHERE satisfaction_score IS NOT NULL")['avg'] ?? 0,
                'avg_response_time' => $this->db->fetch("SELECT COALESCE(AVG(response_time), 0) as avg FROM ai_chatbot_interactions WHERE response_time IS NOT NULL")['avg'] ?? 0,
            ];
        } catch (\Throwable $e) {
            $logs = [];
            $stats = ['total' => 0, 'avg_satisfaction' => 0, 'avg_response_time' => 0];
        }
        return $this->render('admin/ai-management/chatbot', [
            'page_title' => 'Chatbot Logs',
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    public function leadScores()
    {
        $this->requireAdmin();
        try {
            $scores = $this->db->fetchAll("
                SELECT ls.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone, l.status as lead_status
                FROM ai_lead_scores ls
                LEFT JOIN leads l ON ls.lead_id = l.id
                ORDER BY ls.scored_at DESC
                LIMIT 100
            ") ?: [];
        } catch (\Throwable $e) {
            $scores = [];
        }
        return $this->render('admin/ai-management/lead-scores', [
            'page_title' => 'AI Lead Scores',
            'scores' => $scores,
        ]);
    }

    public function generatedContent()
    {
        $this->requireAdmin();
        try {
            $contents = $this->db->fetchAll("
                SELECT g.*
                FROM ai_generated_content g
                ORDER BY g.created_at DESC
                LIMIT 100
            ") ?: [];
            $stats = [
                'total' => count($contents),
                'published' => count(array_filter($contents, fn($r) => ($r['is_published'] ?? 0) == 1)),
                'draft' => count(array_filter($contents, fn($r) => ($r['is_published'] ?? 0) == 0)),
            ];
        } catch (\Throwable $e) {
            $contents = [];
            $stats = ['total' => 0, 'published' => 0, 'draft' => 0];
        }
        return $this->render('admin/ai-management/content', [
            'page_title' => 'AI Generated Content',
            'contents' => $contents,
            'stats' => $stats,
        ]);
    }

    public function toggleContent($id)
    {
        $this->requireAdmin();
        try {
            $row = $this->db->fetch("SELECT id, is_published FROM ai_generated_content WHERE id = ?", [(int)$id]);
            if (!$row) {
                return $this->jsonResponse(['success' => false, 'message' => 'Content not found'], 404);
            }
            $newStatus = ($row['is_published'] ?? 0) ? 0 : 1;
            [$tw, $tp] = $this->tenantWhere();
            $this->db->query("UPDATE ai_generated_content SET is_published = ? WHERE id = ?" . $tw, array_merge([$newStatus, (int)$id], $tp));
            return $this->jsonResponse(['success' => true, 'is_published' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function usageAnalytics()
    {
        $this->requireAdmin();
        // ai_usage_analytics in live DB is a 4-column stub (id/tenant_id/created_at/updated_at).
        // Any query here 1054/1146s and is silently swallowed. Hand the view honest
        // empty arrays — the page's designed empty state renders instead of a lie.
        return $this->render('admin/ai-management/usage', [
            'page_title' => 'AI Usage Analytics',
            'usage' => [],
            'byFeature' => [],
        ]);
    }

    public function contextMemory()
    {
        $this->requireAdmin();
        // ai_context_memory does not exist in the live database (AF1146 on every call,
        // swallowed by the catch). Hand the view its honest empty state.
        return $this->render('admin/ai-management/context', [
            'page_title' => 'AI Context Memory',
            'entries' => [],
        ]);
    }
}
