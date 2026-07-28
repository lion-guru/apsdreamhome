<?php

namespace App\Http\Controllers\Admin;

class AIChatbotController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'AI Chatbot';
        $this->data['chatbot_stats'] = [];
        try {
            $this->data['conversations'] = $this->db->fetchAll("SELECT * FROM chatbot_conversations ORDER BY created_at DESC LIMIT 10");
        } catch (\Exception $e) {
            $this->data['conversations'] = [];
        }
        $this->render('admin/ai/chatbot');
    }

    public function settings()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Chatbot Settings';
        try {
            $this->data['config'] = $this->db->fetch("SELECT * FROM ai_chatbot_config LIMIT 1");
        } catch (\Exception $e) {
            $this->data['config'] = [];
        }
        $this->render('admin/ai/chatbot-settings');
    }

    public function saveSettings()
    {
        $this->requireAdmin();
        $botName = $_POST['bot_name'] ?? 'APS Assistant';
        $welcomeMsg = $_POST['welcome_message'] ?? '';
        $fallbackMsg = $_POST['fallback_message'] ?? '';
        $language = $_POST['language'] ?? 'en';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        try {
            $existing = $this->db->fetch("SELECT id FROM ai_chatbot_config LIMIT 1");
            if ($existing) {
                [$tw, $tp] = $this->tenantWhere();
                $this->db->query("UPDATE ai_chatbot_config SET bot_name=?, welcome_message=?, fallback_message=?, language=?, is_active=? WHERE id=?" . $tw, array_merge([$botName, $welcomeMsg, $fallbackMsg, $language, $isActive, $existing['id']], $tp));
            } else {
                $this->db->insert('ai_chatbot_config', ['bot_name' => $botName, 'welcome_message' => $welcomeMsg, 'fallback_message' => $fallbackMsg, 'language' => $language, 'is_active' => $isActive, 'provider' => 'internal']);
            }
            $this->setFlash('success', 'Chatbot settings saved');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to save settings');
        }
        $this->redirect(BASE_URL . '/admin/chatbot/settings');
    }

    public function analytics()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Chatbot Analytics';
        $this->data['stats'] = [];
        $this->data['popular_questions'] = [];
        $this->data['satisfaction_data'] = [];
        try {
            $this->data['stats']['total_conversations'] = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM chatbot_conversations");
            $this->data['stats']['total_queries'] = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM ai_chatbot_interactions");
            $avgSat = $this->db->fetchColumn("SELECT AVG(satisfaction_score) FROM ai_chatbot_interactions WHERE satisfaction_score IS NOT NULL");
            $this->data['stats']['avg_satisfaction'] = round((float)$avgSat, 1);
            $unanswered = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM ai_chatbot_interactions WHERE response LIKE '%Sorry%' OR response LIKE '%not understand%'");
            $total = max(1, (int)$this->db->fetchColumn("SELECT COUNT(*) FROM ai_chatbot_interactions"));
            $this->data['stats']['resolution_rate'] = round((1 - $unanswered / $total) * 100);
            $this->data['popular_questions'] = $this->db->fetchAll("SELECT query as question, COUNT(*) as count FROM ai_chatbot_interactions GROUP BY query ORDER BY count DESC LIMIT 10");
        } catch (\Exception $e) {
            $this->data['stats'] = ['total_conversations' => 0, 'total_queries' => 0, 'avg_satisfaction' => 0, 'resolution_rate' => 0];
        }
        $this->render('admin/ai/chatbot-analytics');
    }

    public function train()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Train Chatbot';
        try {
            $this->data['training_data'] = $this->db->fetchAll("SELECT * FROM chatbot_training_data ORDER BY category, intent");
        } catch (\Exception $e) {
            $this->data['training_data'] = [];
        }
        $this->render('admin/ai/chatbot-train');
    }

    public function storeTraining()
    {
        $this->requireAdmin();
        $intent = $_POST['intent'] ?? '';
        $category = $_POST['category'] ?? 'general';
        $question = $_POST['question'] ?? '';
        $answer = $_POST['answer'] ?? '';
        try {
            $this->db->insert('chatbot_training_data', ['intent' => $intent, 'category' => $category, 'question' => $question, 'answer' => $answer, 'is_active' => 1, 'frequency' => 0]);
            $this->setFlash('success', 'Training data added');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add training data');
        }
        $this->redirect(BASE_URL . '/admin/chatbot/train');
    }

    public function toggleTraining($id)
    {
        $this->requireAdmin();
        try {
            $item = $this->db->fetch("SELECT id, is_active FROM chatbot_training_data WHERE id=?", [$id]);
            if ($item) {
                $newStatus = $item['is_active'] ? 0 : 1;
                [$tw, $tp] = $this->tenantWhere();
                $this->db->query("UPDATE chatbot_training_data SET is_active=? WHERE id=?" . $tw, array_merge([$newStatus, $id], $tp));
            }
        } catch (\Exception $e) { error_log('AIChatbotController toggleTraining: ' . $e->getMessage()); }
        $this->redirect(BASE_URL . '/admin/chatbot/train');
    }

    public function deleteTraining($id)
    {
        $this->requireAdmin();
        try {
            $this->db->delete('chatbot_training_data', ['id' => $id]);
        } catch (\Exception $e) { error_log('AIChatbotController deleteTraining: ' . $e->getMessage()); }
        $this->redirect(BASE_URL . '/admin/chatbot/train');
    }
}
