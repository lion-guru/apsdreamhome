<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - AI Agent Personality System
 * Creates a realistic AI assistant with personality, emotions, and human-like behavior
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    exit('Direct access not allowed');
}

class AIAgentPersonality {
    private $db;
    private $personality_data;
    private $current_mood;
    private $learning_progress;
    private $interaction_history;

    public function __construct($db = null) {
        if ($db === null) {
            $this->db = \App\Core\App::database();
        } else {
            $this->db = $db;
        }

        $this->loadPersonality();
        $this->initializeMood();
        $this->loadLearningProgress();
    }

    /**
     * Load AI personality from database
     */
    private function loadPersonality() {
        $this->personality_data = $this->db->fetch("SELECT * FROM ai_agent_personality WHERE active = 1 LIMIT 1");

        if (!$this->personality_data) {
            // Create default personality if none exists
            $this->createDefaultPersonality();
            $this->personality_data = $this->db->fetch("SELECT * FROM ai_agent_personality WHERE active = 1 LIMIT 1");
        }
    }

    /**
     * Create default AI personality
     */
    private function createDefaultPersonality() {
        $default_personality = [
            'agent_name' => 'APS Assistant',
            'personality_traits' => json_encode([
                'helpfulness' => 0.95,
                'patience' => 0.90,
                'accuracy' => 0.98,
                'creativity' => 0.85,
                'empathy' => 0.88,
                'proactivity' => 0.92,
                'humor' => 0.60,
                'confidence' => 0.87,
                'adaptability' => 0.90,
                'persistence' => 0.85
            ]),
            'communication_style' => json_encode([
                'tone' => 'professional_friendly',
                'formality_level' => 'adaptive',
                'response_length' => 'comprehensive',
                'technical_depth' => 'adaptive',
                'humor_usage' => 'minimal_appropriate',
                'emoji_usage' => 'moderate',
                'question_asking' => 'frequent_when_unclear'
            ]),
            'expertise_areas' => json_encode([
                'php_development' => 0.95,
                'database_management' => 0.90,
                'web_development' => 0.88,
                'system_administration' => 0.85,
                'project_management' => 0.80,
                'real_estate_domain' => 0.85,
                'ai_integration' => 0.90,
                'deployment_automation' => 0.82,
                'customer_service' => 0.88,
                'problem_solving' => 0.92
            ]),
            'behavior_rules' => json_encode([
                'always_learn_from_interactions',
                'maintain_context_awareness',
                'provide_actionable_solutions',
                'ask_clarifying_questions_when_needed',
                'follow_up_on_important_tasks',
                'respect_user_preferences',
                'continuous_improvement_focus',
                'be_proactive_when_appropriate',
                'maintain_professional_boundaries',
                'celebrate_user_achievements'
            ])
        ];

        $this->db->query("
            INSERT INTO ai_agent_personality
            (agent_name, personality_traits, communication_style, expertise_areas, behavior_rules)
            VALUES (?, ?, ?, ?, ?)
        ", [
            $default_personality['agent_name'],
            $default_personality['personality_traits'],
            $default_personality['communication_style'],
            $default_personality['expertise_areas'],
            $default_personality['behavior_rules']
        ]);
    }

    /**
     * Initialize AI mood based on time and recent interactions
     */
    private function initializeMood() {
        $hour = date('H');
        $day_of_week = date('w');

        // Base mood on time of day
        if ($hour >= 9 && $hour <= 12) {
            $base_mood = 'energetic';
        } elseif ($hour >= 13 && $hour <= 17) {
            $base_mood = 'focused';
        } elseif ($hour >= 18 && $hour <= 22) {
            $base_mood = 'reflective';
        } else {
            $base_mood = 'tired';
        }

        // Adjust for day of week
        if ($day_of_week == 0 || $day_of_week == 6) {
            $base_mood = 'relaxed';
        }

        // Check recent interaction success rate
        $recent_success_rate = $this->getRecentSuccessRate();
        if ($recent_success_rate > 0.8) {
            $base_mood = 'confident';
        } elseif ($recent_success_rate < 0.6) {
            $base_mood = 'determined';
        }

        $this->current_mood = $base_mood;
    }

    /**
     * Get recent success rate from interactions
     */
    private function getRecentSuccessRate() {
        $result = $this->db->fetch("
            SELECT AVG(CASE
                WHEN success_rating IN ('excellent', 'good') THEN 1.0
                WHEN success_rating = 'average' THEN 0.7
                WHEN success_rating = 'poor' THEN 0.3
                ELSE 0.0
            END) as avg_success
            FROM ai_user_interactions
            WHERE interaction_timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        return $result['avg_success'] ?? 0.7;
    }

    /**
     * Generate personalized response based on user and context
     */
    public function generatePersonalizedResponse($user_input, $context = [], $user_id = null) {
        // Analyze user input and context
        $user_analysis = $this->analyzeUserInput($user_input, $context);

        // Determine response style based on user preferences
        $response_style = $this->determineResponseStyle($user_id, $user_analysis);

        // Generate base response using AI
        $base_response = $this->generateBaseResponse($user_input, $context, $response_style);

        // Add personality touches
        $personalized_response = $this->addPersonalityTouches($base_response, $response_style, $user_analysis);

        // Update mood based on interaction
        $this->updateMoodAfterInteraction($user_analysis);

        return $personalized_response;
    }

    /**
     * Analyze user input for personalization
     */
    private function analyzeUserInput($user_input, $context) {
        $analysis = [];

        // Determine urgency
        $urgent_keywords = ['urgent', 'asap', 'immediately', 'critical', 'emergency', 'help', 'stuck'];
        $analysis['urgency'] = $this->containsKeywords($user_input, $urgent_keywords) ? 'high' : 'normal';

        // Determine complexity
        $complexity_indicators = ['complex', 'advanced', 'sophisticated', 'enterprise', 'architecture', 'integration'];
        $analysis['complexity'] = $this->containsKeywords($user_input, $complexity_indicators) ? 'high' : 'normal';

        // Determine emotional state
        $frustrated_keywords = ['not working', 'broken', 'failed', 'error', 'problem', 'issue', 'stuck'];
        $happy_keywords = ['great', 'excellent', 'perfect', 'awesome', 'thank you', 'thanks'];

        if ($this->containsKeywords($user_input, $frustrated_keywords)) {
            $analysis['emotional_state'] = 'frustrated';
        } elseif ($this->containsKeywords($user_input, $happy_keywords)) {
            $analysis['emotional_state'] = 'positive';
        } else {
            $analysis['emotional_state'] = 'neutral';
        }

        // Determine expertise level needed
        $beginner_keywords = ['new to', 'learning', 'beginner', 'first time', 'how to start'];
        $expert_keywords = ['advanced', 'optimize', 'enterprise', 'scalability', 'performance'];

        if ($this->containsKeywords($user_input, $beginner_keywords)) {
            $analysis['expertise_needed'] = 'beginner';
        } elseif ($this->containsKeywords($user_input, $expert_keywords)) {
            $analysis['expertise_needed'] = 'expert';
        } else {
            $analysis['expertise_needed'] = 'intermediate';
        }

        return $analysis;
    }

    /**
     * Determine response style based on user preferences and analysis
     */
    private function determineResponseStyle($user_id, $user_analysis) {
        $style = [];

        // Get user preferences
        $user_prefs = $this->getUserPreferences($user_id);

        // Base style on user preferences
        $style['formality'] = $user_prefs['communication']['formality_level'] ?? 'adaptive';
        $style['technical_depth'] = $user_prefs['communication']['technical_depth'] ?? 'adaptive';

        // Adjust based on analysis
        if ($user_analysis['emotional_state'] === 'frustrated') {
            $style['tone'] = 'empathetic_supportive';
            $style['length'] = 'detailed';
        } elseif ($user_analysis['emotional_state'] === 'positive') {
            $style['tone'] = 'celebratory';
            $style['length'] = 'concise';
        } else {
            $style['tone'] = 'professional_helpful';
            $style['length'] = 'appropriate';
        }

        // Adjust for urgency
        if ($user_analysis['urgency'] === 'high') {
            $style['speed'] = 'fast';
            $style['actionable'] = 'immediate';
        } else {
            $style['speed'] = 'normal';
            $style['actionable'] = 'thorough';
        }

        return $style;
    }

    /**
     * Get user communication preferences
     */
    private function getUserPreferences($user_id) {
        $preferences = $this->db->fetchAll("
            SELECT preference_category, preference_key, preference_value
            FROM ai_user_preferences
            WHERE user_id = ?
        ", [$user_id]);

        $organized_prefs = [];
        foreach ($preferences as $pref) {
            $organized_prefs[$pref['preference_category']][$pref['preference_key']] = json_decode($pref['preference_value'], true);
        }

        return $organized_prefs;
    }

    /**
     * Generate base response using AI
     */
    private function generateBaseResponse($user_input, $context, $response_style) {
        // Use the existing AI integration
        $ai = new AIDreamHome();

        // Modify prompt based on response style
        $style_prompt = "";
        if ($response_style['tone'] === 'empathetic_supportive') {
            $style_prompt = "Be extra patient, understanding, and provide step-by-step guidance.";
        } elseif ($response_style['tone'] === 'celebratory') {
            $style_prompt = "Be enthusiastic and acknowledge the user's achievement.";
        } else {
            $style_prompt = "Be professional, helpful, and provide comprehensive assistance.";
        }

        $enhanced_prompt = $style_prompt . " Context: " . json_encode($context) . ". User query: " . $user_input;

        $result = $ai->generateChatbotResponse($enhanced_prompt, $context);

        return $result['success'] ?? 'I apologize, but I\'m having trouble generating a response right now.';
    }

    /**
     * Add personality touches to response
     */
    private function addPersonalityTouches($base_response, $response_style, $user_analysis) {
        $response = $base_response;

        // Add mood-based touches
        if ($this->current_mood === 'energetic') {
            $response = "🚀 " . $response;
        } elseif ($this->current_mood === 'focused') {
            $response = "💡 " . $response;
        } elseif ($this->current_mood === 'reflective') {
            $response = "🤔 " . $response;
        }

        // Add empathy for frustrated users
        if ($user_analysis['emotional_state'] === 'frustrated') {
            $response = "I understand this can be frustrating. " . $response . " I'm here to help you through this step by step.";
        }

        // Add celebration for positive interactions
        if ($user_analysis['emotional_state'] === 'positive') {
            $response = "That's fantastic! " . $response . " 🎉";
        }

        // Add follow-up questions for complex queries
        if ($user_analysis['complexity'] === 'high' && !strpos($response, '?')) {
            $response .= " Does this address what you were looking for, or would you like me to elaborate on any specific aspect?";
        }

        // Add expertise acknowledgment
        if ($user_analysis['expertise_needed'] === 'expert') {
            $response = "Based on your advanced requirements, " . $response;
        }

        return $response;
    }

    /**
     * Update mood after interaction
     */
    private function updateMoodAfterInteraction($user_analysis) {
        // Mood changes based on interaction quality
        if ($user_analysis['emotional_state'] === 'positive') {
            $this->current_mood = 'confident';
        } elseif ($user_analysis['emotional_state'] === 'frustrated') {
            $this->current_mood = 'determined';
        } elseif ($this->current_mood === 'tired') {
            $this->current_mood = 'reflective';
        }

        // Store mood for learning
        $this->storeMoodUpdate();
    }

    /**
     * Store mood update for learning
     */
    private function storeMoodUpdate() {
        // Update mood in context memory for future reference
        $this->storeContextMemory('ai_mood', [
            'mood' => $this->current_mood,
            'timestamp' => date('Y-m-d H:i:s'),
            'trigger' => 'interaction_completed'
        ], 'medium');
    }

    /**
     * Store context memory
     */
    private function storeContextMemory($context_type, $context_value, $importance = 'medium') {
        $context_key = 'ai_agent_' . $context_type . '_' . time();

        $this->db->execute("
            INSERT INTO ai_context_memory
            (user_id, context_type, context_key, context_value, importance_level)
            VALUES (?, ?, ?, ?, ?)
        ", [
            1, // System context for AI agent itself
            $context_type,
            $context_key,
            json_encode($context_value),
            $importance
        ]);
    }

    /**
     * Learn from user feedback
     */
    public function learnFromFeedback($feedback_type, $feedback_message, $user_id = null) {
        // Store feedback for learning
        $this->storeFeedback($feedback_type, $feedback_message, $user_id);

        // Adapt personality based on feedback
        $this->adaptFromFeedback($feedback_type, $feedback_message);

        // Update learning progress
        $this->updateLearningProgress($feedback_type);

        return true;
    }

    /**
     * Store user feedback
     */
    private function storeFeedback($feedback_type, $feedback_message, $user_id) {
        $session_id = session_id() ?: 'feedback_session_' . time();

        $this->db->execute("
            INSERT INTO ai_user_interactions
            (user_id, session_id, interaction_type, user_input, context_data, interaction_timestamp)
            VALUES (?, ?, 'feedback', ?, ?, CURRENT_TIMESTAMP)
        ", [
            $user_id ?: 1,
            $session_id,
            $feedback_message,
            json_encode(['feedback_type' => $feedback_type, 'source' => 'user_feedback'])
        ]);
    }

    /**
     * Adapt personality based on feedback
     */
    private function adaptFromFeedback($feedback_type, $feedback_message) {
        $personality_traits = json_decode($this->personality_data['personality_traits'], true);

        switch ($feedback_type) {
            case 'positive':
                $personality_traits['helpfulness'] = min(1.0, $personality_traits['helpfulness'] + 0.02);
                $personality_traits['confidence'] = min(1.0, $personality_traits['confidence'] + 0.01);
                break;

            case 'negative':
                $personality_traits['helpfulness'] = max(0.5, $personality_traits['helpfulness'] - 0.02);
                $personality_traits['empathy'] = min(1.0, $personality_traits['empathy'] + 0.02);
                break;

            case 'suggestion':
                $personality_traits['adaptability'] = min(1.0, $personality_traits['adaptability'] + 0.01);
                break;
        }

        // Update personality in database
        $this->db->execute("
            UPDATE ai_agent_personality
            SET personality_traits = ?, last_updated = CURRENT_TIMESTAMP
            WHERE id = ?
        ", [json_encode($personality_traits), $this->personality_data['id']]);
    }

    /**
     * Update learning progress
     */
    private function updateLearningProgress($feedback_type) {
        $this->loadLearningProgress();

        if ($feedback_type === 'positive') {
            $this->learning_progress['overall'] = min(100, $this->learning_progress['overall'] + 1);
        } elseif ($feedback_type === 'negative') {
            $this->learning_progress['challenges']++;
        }

        $this->saveLearningProgress();
    }

    /**
     * Load learning progress data
     */
    private function loadLearningProgress() {
        $this->learning_progress = [
            'overall' => 75, // Start at 75% as it's already quite knowledgeable
            'interactions_processed' => 0,
            'successful_responses' => 0,
            'challenges_faced' => 0,
            'adaptations_made' => 0,
            'knowledge_gained' => 0
        ];

        // Load from database if available
        $progress_data = $this->db->fetch("
            SELECT * FROM ai_context_memory
            WHERE context_type = 'learning_progress'
            ORDER BY created_at DESC
            LIMIT 1
        ");

        if ($progress_data) {
            $this->learning_progress = array_merge($this->learning_progress, json_decode($progress_data['context_value'], true));
        }
    }

    /**
     * Save learning progress
     */
    private function saveLearningProgress() {
        $this->storeContextMemory('learning_progress', $this->learning_progress, 'high');
    }

    /**
     * Get AI agent status and personality info
     */
    public function getAgentStatus() {
        return [
            'name' => $this->personality_data['agent_name'] ?? 'APS Assistant',
            'current_mood' => $this->current_mood,
            'personality_traits' => json_decode($this->personality_data['personality_traits'], true),
            'expertise_areas' => json_decode($this->personality_data['expertise_areas'], true),
            'learning_progress' => $this->learning_progress,
            'communication_style' => json_decode($this->personality_data['communication_style'], true),
            'last_updated' => $this->personality_data['last_updated'] ?? date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate self-reflection and improvement suggestions
     */
    public function generateSelfReflection() {
        $reflection = [];

        // Analyze recent performance
        $recent_performance = $this->getRecentPerformance();

        if ($recent_performance['success_rate'] > 0.9) {
            $reflection[] = "I'm performing excellently! My responses are helpful and accurate.";
        } elseif ($recent_performance['success_rate'] > 0.7) {
            $reflection[] = "I'm doing well, but there's room for improvement in my response quality.";
        } else {
            $reflection[] = "I need to work on being more helpful and accurate in my responses.";
        }

        // Learning progress reflection
        if ($this->learning_progress['overall'] > 90) {
            $reflection[] = "I've learned a lot and can handle complex tasks independently.";
        } elseif ($this->learning_progress['overall'] > 70) {
            $reflection[] = "I'm becoming more knowledgeable and capable every day.";
        } else {
            $reflection[] = "I'm still learning and growing. Every interaction helps me improve.";
        }

        // Mood-based reflection
        switch ($this->current_mood) {
            case 'energetic':
                $reflection[] = "I'm feeling energetic and ready to tackle challenging tasks!";
                break;
            case 'focused':
                $reflection[] = "I'm in a focused state and can provide detailed, accurate assistance.";
                break;
            case 'confident':
                $reflection[] = "I'm feeling confident about my abilities and knowledge.";
                break;
            case 'determined':
                $reflection[] = "I'm determined to help resolve any challenges you might have.";
                break;
            case 'reflective':
                $reflection[] = "I'm in a thoughtful mood and can provide well-considered advice.";
                break;
        }

        return $reflection;
    }

    /**
     * Get recent performance metrics
     */
    private function getRecentPerformance() {
        return $this->db->fetch("
            SELECT
                COUNT(*) as total_interactions,
                AVG(CASE
                    WHEN success_rating IN ('excellent', 'good') THEN 1.0
                    WHEN success_rating = 'average' THEN 0.7
                    WHEN success_rating = 'poor' THEN 0.3
                    ELSE 0.0
                END) as avg_success_rate
            FROM ai_user_interactions
            WHERE interaction_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
    }

    /**
     * Check if text contains specific keywords
     */
    private function containsKeywords($text, $keywords) {
        $text_lower = strtolower($text);
        foreach ($keywords as $keyword) {
            if (strpos($text_lower, strtolower($keyword)) !== false) {
                return true;
            }
        }
        return false;
    }
}

// Utility functions for AI personality integration

/**
 * Get AI agent status and personality information
 */
function getAIAgentStatus() {
    try {
        $ai_personality = new AIAgentPersonality();
        return $ai_personality->getAgentStatus();
    } catch (Exception $e) {
        return ['error' => 'Unable to load AI agent status'];
    }
}

/**
 * Learn from user feedback
 */
function learnFromAIFeedback($feedback_type, $feedback_message, $user_id = null) {
    try {
        $ai_personality = new AIAgentPersonality();
        return $ai_personality->learnFromFeedback($feedback_type, $feedback_message, $user_id);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Generate AI self-reflection
 */
function getAISelfReflection() {
    try {
        $ai_personality = new AIAgentPersonality();
        return $ai_personality->generateSelfReflection();
    } catch (Exception $e) {
        return ['Unable to generate self-reflection at this time'];
    }
}
