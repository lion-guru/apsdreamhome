<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - AI Learning System
 * Advanced AI that learns from user interactions and becomes a personal assistant
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    exit('Direct access not allowed');
}

class AILearningSystem {
    private $db;
    private $user_id;
    private $session_id;
    private $ai_personality;

    public function __construct($db = null, $user_id = null) {
        if ($db === null) {
            $this->db = \App\Core\App::database();
        } else {
            $this->db = $db;
        }

        $this->user_id = $user_id ?? $this->getCurrentUserId();
        $this->session_id = session_id() ?: $this->generateSessionId();
        $this->ai_personality = $this->loadAIPersonality();
    }

    /**
     * Learn from user interaction
     */
    public function learnFromInteraction($user_input, $ai_response, $interaction_type = 'question', $context = []) {
        // Store interaction
        $this->storeInteraction($user_input, $ai_response, $interaction_type, $context);

        // Analyze patterns
        $this->analyzeInteractionPatterns($user_input, $context);

        // Update user profile
        $this->updateUserProfile();

        // Learn new knowledge
        $this->extractAndStoreKnowledge($user_input, $ai_response, $context);

        // Update workflow patterns
        $this->updateWorkflowPatterns($user_input, $context);

        // Update AI personality based on feedback
        $this->adaptAIPersonality($context);

        return true;
    }

    /**
     * Analyze user interaction patterns
     */
    private function analyzeInteractionPatterns($user_input, $context) {
        // Extract keywords and topics
        $keywords = $this->extractKeywords($user_input);
        $topics = $this->identifyTopics($user_input);

        // Analyze time patterns
        $hour = date('H');
        $day_of_week = date('w');
        $time_context = $this->analyzeTimeContext($hour, $day_of_week);

        // Analyze communication style
        $communication_style = $this->analyzeCommunicationStyle($user_input);

        // Store pattern analysis
        $this->storePatternAnalysis([
            'keywords' => $keywords,
            'topics' => $topics,
            'time_context' => $time_context,
            'communication_style' => $communication_style,
            'context' => $context
        ]);

        // Update user preferences
        $this->updateUserPreferences($keywords, $topics, $communication_style);
    }

    /**
     * Extract keywords from user input
     */
    private function extractKeywords($text) {
        // Remove common stop words
        $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them'];

        $words = str_word_count(strtolower($text), 1);
        $keywords = array_diff($words, $stop_words);

        // Filter by length and relevance
        $keywords = array_filter($keywords, function($word) {
            return strlen($word) > 3 && !is_numeric($word);
        });

        return array_values($keywords);
    }

    /**
     * Identify topics from user input
     */
    private function identifyTopics($text) {
        $topics = [];

        // Technical topics
        $tech_keywords = ['php', 'database', 'mysql', 'javascript', 'html', 'css', 'api', 'server', 'deployment', 'security', 'performance', 'error', 'bug', 'code', 'function', 'class', 'framework', 'bootstrap'];
        if ($this->containsKeywords($text, $tech_keywords)) {
            $topics[] = 'technical';
        }

        // Business topics
        $business_keywords = ['property', 'customer', 'client', 'sale', 'commission', 'agent', 'associate', 'crm', 'marketing', 'business', 'revenue', 'profit', 'strategy'];
        if ($this->containsKeywords($text, $business_keywords)) {
            $topics[] = 'business';
        }

        // Development workflow topics
        $workflow_keywords = ['setup', 'install', 'configure', 'deploy', 'test', 'debug', 'fix', 'implement', 'create', 'build', 'design', 'plan'];
        if ($this->containsKeywords($text, $workflow_keywords)) {
            $topics[] = 'development';
        }

        // AI and learning topics
        $ai_keywords = ['ai', 'artificial intelligence', 'machine learning', 'automation', 'smart', 'intelligent', 'bot', 'assistant'];
        if ($this->containsKeywords($text, $ai_keywords)) {
            $topics[] = 'ai_integration';
        }

        return array_unique($topics);
    }

    /**
     * Check if text contains any of the given keywords
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

    /**
     * Analyze time context of interaction
     */
    private function analyzeTimeContext($hour, $day_of_week) {
        $context = [];

        // Time of day
        if ($hour >= 9 && $hour <= 18) {
            $context['time_of_day'] = 'working_hours';
        } elseif ($hour >= 19 && $hour <= 23) {
            $context['time_of_day'] = 'evening';
        } else {
            $context['time_of_day'] = 'night';
        }

        // Day of week
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            $context['day_type'] = 'weekday';
        } else {
            $context['day_type'] = 'weekend';
        }

        // Urgency indicators
        $urgent_keywords = ['urgent', 'asap', 'immediately', 'quickly', 'fast', 'emergency'];
        if ($this->containsKeywords($text ?? '', $urgent_keywords)) {
            $context['urgency'] = 'high';
        } else {
            $context['urgency'] = 'normal';
        }

        return $context;
    }

    /**
     * Analyze communication style
     */
    private function analyzeCommunicationStyle($text) {
        $style = [];

        // Formality indicators
        $formal_indicators = ['please', 'thank you', 'could you', 'would you', 'i would like', 'i need', 'help me'];
        $informal_indicators = ['hey', 'hi', 'yo', 'dude', 'bro', 'wanna', 'gonna', 'kinda'];

        $formal_count = $this->countKeywordMatches($text, $formal_indicators);
        $informal_count = $this->countKeywordMatches($text, $informal_indicators);

        if ($formal_count > $informal_count) {
            $style['formality'] = 'formal';
        } elseif ($informal_count > $formal_count) {
            $style['formality'] = 'informal';
        } else {
            $style['formality'] = 'neutral';
        }

        // Technical depth
        $technical_terms = ['php', 'mysql', 'javascript', 'api', 'database', 'server', 'deployment', 'framework', 'class', 'function', 'variable', 'algorithm'];
        $technical_count = $this->countKeywordMatches($text, $technical_terms);

        if ($technical_count > 3) {
            $style['technical_depth'] = 'high';
        } elseif ($technical_count > 1) {
            $style['technical_depth'] = 'medium';
        } else {
            $style['technical_depth'] = 'low';
        }

        return $style;
    }

    /**
     * Count keyword matches in text
     */
    private function countKeywordMatches($text, $keywords) {
        $count = 0;
        $text_lower = strtolower($text);

        foreach ($keywords as $keyword) {
            if (strpos($text_lower, strtolower($keyword)) !== false) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Store interaction data
     */
    private function storeInteraction($user_input, $ai_response, $interaction_type, $context) {
        $this->db->query("
            INSERT INTO ai_user_interactions
            (user_id, session_id, interaction_type, user_input, ai_response, context_data, interaction_timestamp)
            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ", [
            $this->user_id,
            $this->session_id,
            $interaction_type,
            $user_input,
            $ai_response,
            json_encode($context)
        ]);

        return $this->db->getConnection()->lastInsertId();
    }

    /**
     * Store pattern analysis
     */
    private function storePatternAnalysis($pattern_data) {
        // Update user interaction patterns in context memory
        $this->storeContextMemory('interaction_patterns', $pattern_data, 'high');
    }

    /**
     * Update user preferences based on learning
     */
    private function updateUserPreferences($keywords, $topics, $communication_style) {
        // Update communication style preference
        if (isset($communication_style['formality'])) {
            $this->storeUserPreference('communication', 'formality_level', $communication_style['formality']);
        }

        // Update technical depth preference
        if (isset($communication_style['technical_depth'])) {
            $this->storeUserPreference('communication', 'technical_depth', $communication_style['technical_depth']);
        }

        // Update topic interests
        foreach ($topics as $topic) {
            $this->storeUserPreference('interests', 'topic_' . $topic, true);
        }

        // Update keyword expertise
        foreach ($keywords as $keyword) {
            $this->storeUserPreference('expertise', 'keyword_' . $keyword, true);
        }
    }

    /**
     * Store user preference
     */
    private function storeUserPreference($category, $key, $value) {
        $this->db->query("
            INSERT INTO ai_user_preferences (user_id, preference_category, preference_key, preference_value)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            preference_value = VALUES(preference_value),
            usage_count = usage_count + 1,
            last_updated = CURRENT_TIMESTAMP
        ", [$this->user_id, $category, $key, json_encode($value)]);
    }

    /**
     * Extract and store knowledge from interactions
     */
    private function extractAndStoreKnowledge($user_input, $ai_response, $context) {
        // Look for code snippets or technical solutions
        if ($this->containsCodeSnippet($user_input) || $this->containsCodeSnippet($ai_response)) {
            $this->storeTechnicalKnowledge($user_input, $ai_response, $context);
        }

        // Look for business processes or workflows
        if ($this->containsWorkflowInformation($user_input) || $this->containsWorkflowInformation($ai_response)) {
            $this->storeWorkflowKnowledge($user_input, $ai_response, $context);
        }

        // Look for problem-solution pairs
        if ($this->isProblemSolutionPair($user_input, $ai_response)) {
            $this->storeProblemSolution($user_input, $ai_response, $context);
        }
    }

    /**
     * Check if text contains code snippets
     */
    private function containsCodeSnippet($text) {
        $code_indicators = ['<?php', 'function', 'class', '$', '->', '=>', 'SELECT', 'INSERT', 'UPDATE', 'DELETE'];
        return $this->containsKeywords($text, $code_indicators);
    }

    /**
     * Check if text contains workflow information
     */
    private function containsWorkflowInformation($text) {
        $workflow_indicators = ['step', 'process', 'workflow', 'procedure', 'method', 'approach', 'strategy', 'plan'];
        return $this->containsKeywords($text, $workflow_indicators);
    }

    /**
     * Check if interaction is a problem-solution pair
     */
    private function isProblemSolutionPair($user_input, $ai_response) {
        $problem_indicators = ['error', 'problem', 'issue', 'not working', 'failed', 'broken', 'stuck', 'help'];
        $solution_indicators = ['solution', 'fix', 'resolve', 'solution', 'answer', 'here is', 'try this'];

        $has_problem = $this->containsKeywords($user_input, $problem_indicators);
        $has_solution = $this->containsKeywords($ai_response, $solution_indicators);

        return $has_problem && $has_solution;
    }

    /**
     * Store technical knowledge
     */
    private function storeTechnicalKnowledge($user_input, $ai_response, $context) {
        $knowledge_data = [
            'topic' => 'Technical Solution',
            'category' => 'technical',
            'content_type' => 'code',
            'title' => 'Technical Implementation',
            'content' => "User Query: $user_input\n\nAI Solution: $ai_response",
            'tags' => $this->extractKeywords($user_input),
            'source' => 'user_interaction'
        ];

        $this->storeKnowledgeEntry($knowledge_data);
    }

    /**
     * Store workflow knowledge
     */
    private function storeWorkflowKnowledge($user_input, $ai_response, $context) {
        $knowledge_data = [
            'topic' => 'Workflow Process',
            'category' => 'procedural',
            'content_type' => 'text',
            'title' => 'Process Documentation',
            'content' => "Process Query: $user_input\n\nProcess Explanation: $ai_response",
            'tags' => ['workflow', 'process', 'procedure'],
            'source' => 'user_interaction'
        ];

        $this->storeKnowledgeEntry($knowledge_data);
    }

    /**
     * Store problem-solution pairs
     */
    private function storeProblemSolution($user_input, $ai_response, $context) {
        $knowledge_data = [
            'topic' => 'Problem Solution',
            'category' => 'reference',
            'content_type' => 'text',
            'title' => 'Problem Resolution',
            'content' => "Problem: $user_input\n\nSolution: $ai_response",
            'tags' => ['problem', 'solution', 'troubleshooting'],
            'source' => 'user_interaction'
        ];

        $this->storeKnowledgeEntry($knowledge_data);
    }

    /**
     * Store knowledge entry
     */
    private function storeKnowledgeEntry($data) {
        $this->db->query("
            INSERT INTO ai_knowledge_base
            (topic, category, content_type, title, content, tags, source)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['topic'],
            $data['category'],
            $data['content_type'],
            $data['title'],
            $data['content'],
            json_encode($data['tags']),
            $data['source']
        ]);
    }

    /**
     * Update workflow patterns
     */
    private function updateWorkflowPatterns($user_input, $context) {
        // Identify if this is part of a recurring workflow
        $workflow_keywords = ['setup', 'configure', 'install', 'deploy', 'test', 'debug', 'fix', 'create', 'build'];

        if ($this->containsKeywords($user_input, $workflow_keywords)) {
            $this->recordWorkflowUsage($user_input, $context);
        }
    }

    /**
     * Record workflow usage for pattern recognition
     */
    private function recordWorkflowUsage($user_input, $context) {
        // Extract workflow type
        $workflow_type = $this->identifyWorkflowType($user_input);

        if ($workflow_type) {
            $this->db->query("
                INSERT INTO ai_workflow_patterns
                (pattern_name, pattern_category, trigger_conditions, action_sequence, frequency_count, last_used)
                VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                frequency_count = frequency_count + 1,
                last_used = CURRENT_TIMESTAMP
            ", [
                $workflow_type['name'],
                $workflow_type['category'],
                json_encode(['user_request']),
                json_encode(['analyze_request', 'provide_solution', 'follow_up']),
            ]);
        }
    }

    /**
     * Identify workflow type from user input
     */
    private function identifyWorkflowType($user_input) {
        $input_lower = strtolower($user_input);

        if (strpos($input_lower, 'setup') !== false || strpos($input_lower, 'install') !== false) {
            return ['name' => 'System Setup', 'category' => 'deployment'];
        }

        if (strpos($input_lower, 'debug') !== false || strpos($input_lower, 'fix') !== false || strpos($input_lower, 'error') !== false) {
            return ['name' => 'Debugging and Fixes', 'category' => 'maintenance'];
        }

        if (strpos($input_lower, 'deploy') !== false || strpos($input_lower, 'production') !== false) {
            return ['name' => 'Deployment Process', 'category' => 'deployment'];
        }

        if (strpos($input_lower, 'test') !== false) {
            return ['name' => 'Testing Procedures', 'category' => 'development'];
        }

        return null;
    }

    /**
     * Adapt AI personality based on user feedback
     */
    private function adaptAIPersonality($context) {
        // Check for user satisfaction indicators
        if (isset($context['satisfaction'])) {
            $satisfaction = $context['satisfaction'];

            // Adjust response style based on feedback
            if ($satisfaction >= 4) {
                // Positive feedback - maintain current style
                $this->reinforceCurrentPersonality('positive');
            } elseif ($satisfaction <= 2) {
                // Negative feedback - adapt style
                $this->reinforceCurrentPersonality('negative');
            }
        }
    }

    /**
     * Reinforce current personality based on feedback
     */
    private function reinforceCurrentPersonality($feedback_type) {
        // Update AI personality traits based on feedback
        $current_traits = json_decode($this->ai_personality['personality_traits'], true);

        if ($feedback_type === 'positive') {
            // Slightly increase positive traits
            $current_traits['helpfulness'] = min(1.0, $current_traits['helpfulness'] + 0.01);
            $current_traits['accuracy'] = min(1.0, $current_traits['accuracy'] + 0.01);
        } else {
            // Slightly decrease traits that might need improvement
            $current_traits['helpfulness'] = max(0.5, $current_traits['helpfulness'] - 0.01);
        }

        // Update personality in database
        $this->db->query("
            UPDATE ai_agent_personality
            SET personality_traits = ?, last_updated = CURRENT_TIMESTAMP
            WHERE id = ?
        ", [json_encode($current_traits), $this->ai_personality['id']]);
    }

    /**
     * Store context memory for future reference
     */
    private function storeContextMemory($context_type, $context_value, $importance = 'medium') {
        $context_key = 'user_' . $this->user_id . '_' . $context_type . '_' . time();

        $this->db->query("
            INSERT INTO ai_context_memory
            (user_id, context_type, context_key, context_value, importance_level)
            VALUES (?, ?, ?, ?, ?)
        ", [
            $this->user_id,
            $context_type,
            $context_key,
            json_encode($context_value),
            $importance
        ]);
    }

    /**
     * Update user profile with learning data
     */
    private function updateUserProfile() {
        // Get current interaction count
        $result = $this->db->fetch("SELECT COUNT(*) as total_interactions FROM ai_user_interactions WHERE user_id = ?", [$this->user_id]);

        $total_interactions = $result['total_interactions'];

        // Update user profile
        $this->db->query("
            UPDATE ai_user_profiles
            SET total_interactions = ?, last_interaction = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
        ", [$total_interactions, $this->user_id]);
    }

    /**
     * Load AI personality from database
     */
    private function loadAIPersonality() {
        return $this->db->fetch("SELECT * FROM ai_agent_personality WHERE active = 1 LIMIT 1") ?: [];
    }

    /**
     * Get current user ID (implement based on your auth system)
     */
    private function getCurrentUserId() {
        // Check session for user ID
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        // Default to admin user (you should implement proper user detection)
        return 1;
    }

    /**
     * Generate unique session ID
     */
    private function generateSessionId() {
        return 'ai_session_' . \App\Helpers\SecurityHelper::generateRandomString(16, false) . '_' . time();
    }

    /**
     * Get AI learning statistics
     */
    public function getLearningStats() {
        $stats = [];

        // Total interactions
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM ai_user_interactions WHERE user_id = ?", [$this->user_id]);
        $stats['total_interactions'] = $result['total'];

        // Knowledge base size
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM ai_knowledge_base");
        $stats['knowledge_entries'] = $result['total'];

        // Workflow patterns
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM ai_workflow_patterns");
        $stats['workflow_patterns'] = $result['total'];

        // User preferences
        $result = $this->db->fetch("SELECT COUNT(*) as total FROM ai_user_preferences WHERE user_id = ?", [$this->user_id]);
        $stats['user_preferences'] = $result['total'];

        return $stats;
    }

    /**
     * Get personalized recommendations based on learning
     */
    public function getPersonalizedRecommendations() {
        $recommendations = [];

        // Get user's most frequent topics
        $interests = $this->db->fetchAll("
            SELECT preference_key, usage_count
            FROM ai_user_preferences
            WHERE user_id = ? AND preference_category = 'interests'
            ORDER BY usage_count DESC
            LIMIT 5
        ", [$this->user_id]);

        foreach ($interests as $interest) {
            $topic = str_replace('topic_', '', $interest['preference_key']);
            $recommendations[] = "Explore more {$topic} topics - you've shown high interest in this area";
        }

        // Get workflow suggestions
        $workflows = $this->db->fetchAll("
            SELECT pattern_name, automation_potential
            FROM ai_workflow_patterns
            WHERE automation_potential = 'high'
            ORDER BY frequency_count DESC
            LIMIT 3
        ");

        foreach ($workflows as $workflow) {
            $recommendations[] = "Consider automating {$workflow['pattern_name']} - high automation potential detected";
        }

        return $recommendations;
    }

    /**
     * Generate learning report for user
     */
    public function generateLearningReport() {
        $stats = $this->getLearningStats();
        $recommendations = $this->getPersonalizedRecommendations();

        $report = [
            'learning_summary' => $stats,
            'personalized_recommendations' => $recommendations,
            'ai_growth_metrics' => [
                'knowledge_base_growth' => $stats['knowledge_entries'],
                'workflow_automation_potential' => $stats['workflow_patterns'],
                'user_understanding_depth' => $stats['user_preferences']
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];

        return $report;
    }
}

// Utility functions for easy AI learning integration

/**
 * Quick AI learning from user interaction
 */
function learnFromUserInteraction($user_input, $ai_response, $interaction_type = 'question', $context = []) {
    try {
        $ai_learner = new AILearningSystem();
        return $ai_learner->learnFromInteraction($user_input, $ai_response, $interaction_type, $context);
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log('AI Learning Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get AI learning statistics
 */
function getAILearningStats($user_id = null) {
    try {
        $ai_learner = new AILearningSystem(null, $user_id);
        return $ai_learner->getLearningStats();
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Get personalized AI recommendations
 */
function getAIPersonalizedRecommendations() {
    try {
        $ai_learner = new AILearningSystem();
        return $ai_learner->getPersonalizedRecommendations();
    } catch (Exception $e) {
        return ['Unable to generate recommendations at this time'];
    }
}
