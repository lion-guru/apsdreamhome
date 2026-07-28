<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class AIManagementController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function integrations()
    {
        $this->requireAdmin();
        try {
            $integrations = $this->db->fetchAll("SELECT * FROM ai_integrations ORDER BY tool_name ASC") ?: [];
            $stats = [
                'total' => count($integrations),
                'active' => count(array_filter($integrations, fn($r) => ($r['status'] ?? '') === 'active')),
                'inactive' => count(array_filter($integrations, fn($r) => ($r['status'] ?? '') === 'inactive')),
            ];
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function suggestions()
    {
        $this->requireAdmin();
        try {
            $suggestions = $this->db->fetchAll("
                SELECT s.*, u.name as user_name, u.email as user_email,
                       COALESCE(p.title, up.title) as property_title
                FROM ai_property_suggestions s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN properties p ON s.property_id = p.id
                LEFT JOIN user_properties up ON s.property_id = up.id
                ORDER BY s.created_at DESC
                LIMIT 100
            ") ?: [];
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
                SELECT g.*, u.name as user_name, u.email as user_email
                FROM ai_generated_content g
                LEFT JOIN users u ON g.user_id = u.id
                ORDER BY g.created_at DESC
                LIMIT 100
            ") ?: [];
            $stats = [
                'total' => count($contents),
                'published' => count(array_filter($contents, fn($r) => ($r['is_published'] ?? 0) == 1)),
                'draft' => count(array_filter($contents, fn($r) => ($r['is_published'] ?? 0) == 0)),
            ];
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function usageAnalytics()
    {
        $this->requireAdmin();
        try {
            $usage = $this->db->fetchAll("
                SELECT a.*, u.name as user_name, u.email as user_email
                FROM ai_usage_analytics a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT 100
            ") ?: [];
            $byFeature = $this->db->fetchAll("
                SELECT feature_name, COUNT(*) as count, COUNT(DISTINCT user_id) as unique_users
                FROM ai_usage_analytics
                GROUP BY feature_name
                ORDER BY count DESC
            ") ?: [];
        } catch (\Exception $e) {
            $usage = [];
            $byFeature = [];
        }
        return $this->render('admin/ai-management/usage', [
            'page_title' => 'AI Usage Analytics',
            'usage' => $usage,
            'byFeature' => $byFeature,
        ]);
    }

    public function contextMemory()
    {
        $this->requireAdmin();
        try {
            try {
                $entries = $this->db->fetchAll("
                    SELECT c.*, u.name as user_name, u.email as user_email
                    FROM ai_context_memory c
                    LEFT JOIN users u ON c.user_id = u.id
                    ORDER BY c.created_at DESC
                    LIMIT 100
                ") ?: [];
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
        } catch (\Exception $e) {
            $entries = [];
        }
        return $this->render('admin/ai-management/context', [
            'page_title' => 'AI Context Memory',
            'entries' => $entries,
        ]);
    }
}
