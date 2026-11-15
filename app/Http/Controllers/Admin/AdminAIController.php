<?php

namespace App\Http\Controllers\Admin;

class AdminAIController extends AdminController
{
    public function training()
    {
        $this->requireAdmin();

        $intentCount = $totalIntents = $learningRecords = $priceModels = $totalPriceModels = 0;
        $avgAccuracy = $intentAccuracy = 0;
        $lastTrained = null;
        $totalHits = $totalSuccess = 0;
        $topIntents = $models = $recentLearning = [];

        try {
            $intentCount = (int)($this->db->query("SELECT COUNT(*) FROM ai_intent_patterns WHERE is_active = 1")->fetchColumn());
            $totalIntents = (int)($this->db->query("SELECT COUNT(*) FROM ai_intent_patterns")->fetchColumn());
            $learningRecords = (int)($this->db->query("SELECT COUNT(*) FROM ai_learning_data")->fetchColumn());
            $priceModels = (int)($this->db->query("SELECT COUNT(*) FROM ai_price_models WHERE is_active = 1")->fetchColumn());
            $totalPriceModels = (int)($this->db->query("SELECT COUNT(*) FROM ai_price_models")->fetchColumn());
            $avgAccuracy = (float)($this->db->query("SELECT COALESCE(AVG(r_squared),0) FROM ai_price_models WHERE is_active = 1")->fetchColumn());
            $lastTrained = $this->db->query("SELECT MAX(trained_at) FROM ai_price_models")->fetchColumn();
            $totalHits = (int)($this->db->query("SELECT COALESCE(SUM(hit_count),0) FROM ai_intent_patterns")->fetchColumn());
            $totalSuccess = (int)($this->db->query("SELECT COALESCE(SUM(success_count),0) FROM ai_intent_patterns")->fetchColumn());
            $intentAccuracy = $totalHits > 0 ? round($totalSuccess / $totalHits * 100, 1) : 0;
            $topIntents = $this->db->query("SELECT intent_name, hit_count, success_count, pattern_type, language FROM ai_intent_patterns ORDER BY hit_count DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            $models = $this->db->query("SELECT * FROM ai_price_models ORDER BY trained_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
            $recentLearning = $this->db->query("SELECT action_type, COUNT(*) as cnt, AVG(feedback_score) as avg_score FROM ai_learning_data GROUP BY action_type ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("AdminAIController::training() DB error: " . $e->getMessage());
        }

        // Knowledge base data for training view (live RAG schema: title/content/source/tags/visibility)
        $knowledgeBase = [];
        $analytics = [
            'total_qa' => 0,
            'categories' => [],
            'most_used' => [],
            'conversations_today' => 0,
            'total_conversations' => 0,
        ];

        try {
            // Preserve the view's expected keys (question_pattern/answer/category/usage_count) via aliases.
            $knowledgeBase = $this->db->fetchAll(
                "SELECT id, title AS question_pattern, content AS answer, source AS category, 0 AS usage_count, created_at
                 FROM ai_knowledge_base
                 ORDER BY created_at DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            $cats = $this->db->fetchAll("SELECT source AS category, COUNT(*) as count FROM ai_knowledge_base GROUP BY source");
            foreach ($cats as $cat) {
                $analytics['categories'][$cat['category']] = $cat['count'];
            }
            $analytics['total_qa'] = count($knowledgeBase);
            $analytics['most_used'] = $this->db->fetchAll(
                "SELECT title AS question_pattern, 0 AS usage_count FROM ai_knowledge_base
                 ORDER BY id DESC LIMIT 5"
            ) ?: [];
            $today = $this->db->fetch("SELECT COUNT(*) as count FROM ai_conversations WHERE DATE(created_at) = CURDATE()");
            $analytics['conversations_today'] = $today['count'] ?? 0;
            $total = $this->db->fetch("SELECT COUNT(*) as count FROM ai_conversations");
            $analytics['total_conversations'] = $total['count'] ?? 0;
        } catch (\Throwable $e) {
        // Ignore errors
        error_log($e->getMessage());
        }

        return $this->render('admin/ai/training', [
            'page_title' => 'AI Training - APS Dream Home',
            'page_heading' => 'AI Chatbot Training',
            'intentCount' => $intentCount,
            'totalIntents' => $totalIntents,
            'learningRecords' => $learningRecords,
            'priceModels' => $priceModels,
            'totalPriceModels' => $totalPriceModels,
            'avgAccuracy' => $avgAccuracy,
            'lastTrained' => $lastTrained,
            'totalHits' => $totalHits,
            'totalSuccess' => $totalSuccess,
            'intentAccuracy' => $intentAccuracy,
            'topIntents' => $topIntents,
            'models' => $models,
            'recentLearning' => $recentLearning,
            'knowledgeBase' => $knowledgeBase,
            'analytics' => $analytics,
        ]);
    }
}
