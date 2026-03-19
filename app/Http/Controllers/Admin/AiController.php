<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * AI Controller - Custom MVC Implementation
 * Handles AI-powered features and analytics
 */
class AiController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
    }

    /**
     * AI Hub - Main dashboard for AI features
     */
    public function hub()
    {
        try {
            $data = [
                'page_title' => 'AI Hub - APS Dream Home',
                'active_page' => 'ai_hub',
                'ai_stats' => $this->getAIStats(),
                'recent_activities' => $this->getRecentAIActivities()
            ];

            return $this->render('admin/ai/hub', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Hub error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Hub');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * AI Analytics Dashboard
     */
    public function analytics()
    {
        try {
            $data = [
                'page_title' => 'AI Analytics - APS Dream Home',
                'active_page' => 'ai_analytics',
                'analytics_data' => $this->getAnalyticsData(),
                'predictions' => $this->getPredictions()
            ];

            return $this->render('admin/ai/analytics', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Analytics');
            return $this->redirect('admin/ai/hub');
        }
    }

    /**
     * Lead Scoring AI
     */
    public function leadScoring()
    {
        try {
            $data = [
                'page_title' => 'AI Lead Scoring - APS Dream Home',
                'active_page' => 'ai_lead_scoring',
                'leads' => $this->getLeadsForScoring(),
                'scoring_models' => $this->getScoringModels()
            ];

            return $this->render('admin/ai/lead_scoring', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Lead Scoring error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Lead Scoring');
            return $this->redirect('admin/ai/hub');
        }
    }

    /**
     * Property Recommendations
     */
    public function propertyRecommendations()
    {
        try {
            $data = [
                'page_title' => 'AI Property Recommendations - APS Dream Home',
                'active_page' => 'ai_property_recommendations',
                'recommendations' => $this->getPropertyRecommendations(),
                'customer_segments' => $this->getCustomerSegments()
            ];

            return $this->render('admin/ai/property_recommendations', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Property Recommendations error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Property Recommendations');
            return $this->redirect('admin/ai/hub');
        }
    }

    /**
     * Chatbot Management
     */
    public function chatbot()
    {
        try {
            $data = [
                'page_title' => 'AI Chatbot Management - APS Dream Home',
                'active_page' => 'ai_chatbot',
                'chatbot_stats' => $this->getChatbotStats(),
                'conversations' => $this->getRecentConversations()
            ];

            return $this->render('admin/ai/chatbot', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Chatbot error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Chatbot');
            return $this->redirect('admin/ai/hub');
        }
    }

    /**
     * AI Settings
     */
    public function settings()
    {
        try {
            $data = [
                'page_title' => 'AI Settings - APS Dream Home',
                'active_page' => 'ai_settings',
                'ai_config' => $this->getAIConfig()
            ];

            return $this->render('admin/ai/settings', $data);
        } catch (Exception $e) {
            $this->loggingService->error("AI Settings error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load AI Settings');
            return $this->redirect('admin/ai/hub');
        }
    }

    /**
     * Process AI request
     */
    public function processRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 400);
        }

        try {
            $action = $_POST['action'] ?? '';
            $data = $_POST['data'] ?? [];

            $result = ['success' => true, 'data' => []];

            switch ($action) {
                case 'analyze_lead':
                    $result['data'] = $this->analyzeLead($data);
                    break;
                case 'recommend_property':
                    $result['data'] = $this->recommendProperty($data);
                    break;
                case 'predict_sales':
                    $result['data'] = $this->predictSales($data);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Unknown action'];
            }

            return $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->loggingService->error("AI Process Request error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Processing failed'], 500);
        }
    }

    /**
     * Get AI statistics
     */
    private function getAIStats(): array
    {
        try {
            $stats = [];

            // Get lead scoring stats
            $sql = "SELECT COUNT(*) as total_leads, AVG(score) as avg_score FROM ai_lead_scores";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['lead_scoring'] = [
                'total_leads' => (int)($result['total_leads'] ?? 0),
                'avg_score' => round((float)($result['avg_score'] ?? 0), 2)
            ];

            // Get recommendation stats
            $sql = "SELECT COUNT(*) as total_recommendations FROM ai_property_recommendations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['recommendations'] = [
                'weekly_recommendations' => (int)($result['total_recommendations'] ?? 0)
            ];

            // Get chatbot stats
            $sql = "SELECT COUNT(*) as total_conversations FROM ai_chatbot_conversations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['chatbot'] = [
                'daily_conversations' => (int)($result['total_conversations'] ?? 0)
            ];

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get AI Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent AI activities
     */
    private function getRecentAIActivities(): array
    {
        try {
            $sql = "SELECT * FROM ai_activity_log 
                    ORDER BY created_at DESC 
                    LIMIT 10";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent AI Activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get analytics data
     */
    private function getAnalyticsData(): array
    {
        try {
            $data = [];

            // Sales prediction accuracy
            $sql = "SELECT AVG(accuracy_percentage) as avg_accuracy FROM ai_prediction_accuracy 
                    WHERE prediction_type = 'sales' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $data['prediction_accuracy'] = round((float)($result['avg_accuracy'] ?? 0), 2);

            // Lead conversion rates
            $sql = "SELECT 
                        COUNT(CASE WHEN converted = 1 THEN 1 END) as converted,
                        COUNT(*) as total
                    FROM ai_lead_scores 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $total = (int)($result['total'] ?? 0);
            $converted = (int)($result['converted'] ?? 0);
            $data['conversion_rate'] = $total > 0 ? round(($converted / $total) * 100, 2) : 0;

            return $data;
        } catch (Exception $e) {
            $this->loggingService->error("Get Analytics Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get predictions
     */
    private function getPredictions(): array
    {
        try {
            $sql = "SELECT * FROM ai_predictions 
                    WHERE prediction_date >= CURDATE() 
                    ORDER BY confidence_score DESC 
                    LIMIT 20";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Predictions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get leads for scoring
     */
    private function getLeadsForScoring(): array
    {
        try {
            $sql = "SELECT l.*, als.score, als.confidence_level
                    FROM leads l
                    LEFT JOIN ai_lead_scores als ON l.id = als.lead_id
                    ORDER BY l.created_at DESC
                    LIMIT 50";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Leads For Scoring error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get scoring models
     */
    private function getScoringModels(): array
    {
        try {
            $sql = "SELECT * FROM ai_scoring_models WHERE is_active = 1";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Scoring Models error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property recommendations
     */
    private function getPropertyRecommendations(): array
    {
        try {
            $sql = "SELECT pr.*, p.title, p.location, p.price
                    FROM ai_property_recommendations pr
                    JOIN properties p ON pr.property_id = p.id
                    WHERE pr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    ORDER BY pr.confidence_score DESC
                    LIMIT 30";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Property Recommendations error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get customer segments
     */
    private function getCustomerSegments(): array
    {
        try {
            $sql = "SELECT * FROM ai_customer_segments WHERE is_active = 1";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Customer Segments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get chatbot stats
     */
    private function getChatbotStats(): array
    {
        try {
            $stats = [];

            // Total conversations
            $sql = "SELECT COUNT(*) as total FROM ai_chatbot_conversations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['daily_conversations'] = (int)($result['total'] ?? 0);

            // Average satisfaction
            $sql = "SELECT AVG(satisfaction_score) as avg_satisfaction FROM ai_chatbot_feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['avg_satisfaction'] = round((float)($result['avg_satisfaction'] ?? 0), 2);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Chatbot Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent conversations
     */
    private function getRecentConversations(): array
    {
        try {
            $sql = "SELECT cc.*, u.name as user_name
                    FROM ai_chatbot_conversations cc
                    LEFT JOIN users u ON cc.user_id = u.id
                    ORDER BY cc.created_at DESC
                    LIMIT 20";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Conversations error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get AI configuration
     */
    private function getAIConfig(): array
    {
        try {
            $sql = "SELECT * FROM ai_config WHERE is_active = 1";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get AI Config error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze lead
     */
    private function analyzeLead(array $data): array
    {
        try {
            $leadId = (int)($data['lead_id'] ?? 0);
            if ($leadId <= 0) {
                return ['error' => 'Invalid lead ID'];
            }

            // Simulate AI analysis
            $score = rand(60, 95);
            $confidence = rand(70, 95);

            // Save analysis
            $sql = "INSERT INTO ai_lead_scores (lead_id, score, confidence_level, analysis_data, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE score = VALUES(score), confidence_level = VALUES(confidence_level), analysis_data = VALUES(analysis_data)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $leadId,
                $score,
                $confidence,
                json_encode(['factors' => ['budget', 'timeline', 'location', 'source']])
            ]);

            return [
                'lead_id' => $leadId,
                'score' => $score,
                'confidence' => $confidence,
                'recommendation' => $score > 80 ? 'High Priority' : ($score > 60 ? 'Medium Priority' : 'Low Priority')
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Analyze Lead error: " . $e->getMessage());
            return ['error' => 'Analysis failed'];
        }
    }

    /**
     * Recommend property
     */
    private function recommendProperty(array $data): array
    {
        try {
            $customerId = (int)($data['customer_id'] ?? 0);
            if ($customerId <= 0) {
                return ['error' => 'Invalid customer ID'];
            }

            // Get customer preferences
            $sql = "SELECT * FROM customer_preferences WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $preferences = $stmt->fetch();

            // Get recommended properties
            $sql = "SELECT p.*, 
                           (CASE WHEN p.location LIKE ? THEN 30 ELSE 0 END +
                            CASE WHEN p.price BETWEEN ? AND ? THEN 40 ELSE 0 END +
                            CASE WHEN p.property_type = ? THEN 30 ELSE 0 END) as match_score
                    FROM properties p
                    WHERE p.status = 'available'
                    ORDER BY match_score DESC
                    LIMIT 5";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                '%' . ($preferences['preferred_location'] ?? '') . '%',
                $preferences['min_budget'] ?? 0,
                $preferences['max_budget'] ?? 999999999,
                $preferences['property_type'] ?? ''
            ]);

            $recommendations = $stmt->fetchAll();

            // Save recommendations
            foreach ($recommendations as $property) {
                $sql = "INSERT INTO ai_property_recommendations (customer_id, property_id, confidence_score, recommendation_data, created_at)
                        VALUES (?, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE confidence_score = VALUES(confidence_score), recommendation_data = VALUES(recommendation_data)";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $customerId,
                    $property['id'],
                    $property['match_score'],
                    json_encode(['factors' => ['location', 'price', 'type']])
                ]);
            }

            return ['recommendations' => $recommendations];
        } catch (Exception $e) {
            $this->loggingService->error("Recommend Property error: " . $e->getMessage());
            return ['error' => 'Recommendation failed'];
        }
    }

    /**
     * Predict sales
     */
    private function predictSales(array $data): array
    {
        try {
            $period = $data['period'] ?? '30'; // days

            // Get historical data
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as sales
                    FROM bookings
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";

            $stmt = $this->db->query($sql);
            $historicalData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Simple prediction (in real implementation, this would use ML algorithms)
            $totalSales = array_sum(array_column($historicalData, 'sales'));
            $avgDailySales = count($historicalData) > 0 ? $totalSales / count($historicalData) : 0;
            $predictedSales = round($avgDailySales * (int)$period);

            return [
                'period' => $period,
                'predicted_sales' => $predictedSales,
                'confidence' => rand(75, 90),
                'historical_avg' => round($avgDailySales, 2)
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Predict Sales error: " . $e->getMessage());
            return ['error' => 'Prediction failed'];
        }
    }
}
