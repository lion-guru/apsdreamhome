<?php
/**
 * APS Dream Home - AI Agent Database Setup
 * Creates comprehensive database schema for AI learning and memory system
 */

// Prevent direct access
if (!defined('BASE_URL')) {
    exit('Direct access not allowed');
}

class AIDatabaseSetup {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo === null) {
            // Use existing database connection or create new one
            global $config;
            $host = $config['database']['host'] ?? 'localhost';
            $dbname = $config['database']['database'] ?? 'apsdreamhome';
            $username = $config['database']['username'] ?? 'root';
            $password = $config['database']['password'] ?? '';

            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            $this->pdo = $pdo;
        }
    }

    /**
     * Create complete AI agent database schema
     */
    public function createAISchema() {
        echo "🤖 Creating AI Agent Database Schema...\n";

        $this->createUserProfilesTable();
        $this->createUserInteractionsTable();
        $this->createWorkflowPatternsTable();
        $this->createAIKnowledgeBaseTable();
        $this->createLearningSessionsTable();
        $this->createAIAgentPersonalityTable();
        $this->createSystemTasksTable();
        $this->createUserPreferencesTable();
        $this->createContextMemoryTable();
        $this->createDecisionHistoryTable();
        $this->createPerformanceMetricsTable();

        echo "✅ AI Agent database schema created successfully!\n";
    }

    private function createUserProfilesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('admin', 'agent', 'customer', 'associate') DEFAULT 'admin',
            learning_style ENUM('visual', 'analytical', 'hands_on', 'reading') DEFAULT 'analytical',
            communication_preference ENUM('formal', 'casual', 'technical', 'simple') DEFAULT 'formal',
            work_hours_start TIME DEFAULT '09:00:00',
            work_hours_end TIME DEFAULT '18:00:00',
            preferred_language VARCHAR(10) DEFAULT 'en',
            timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
            skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
            learning_progress DECIMAL(5,2) DEFAULT 0.00,
            last_interaction TIMESTAMP NULL,
            total_interactions INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_user_type (user_type),
            INDEX idx_last_interaction (last_interaction)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_user_profiles table created\n";
    }

    private function createUserInteractionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_user_interactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            interaction_type ENUM('question', 'command', 'feedback', 'correction', 'approval', 'rejection') NOT NULL,
            user_input TEXT NOT NULL,
            ai_response TEXT,
            context_data JSON,
            user_satisfaction INT CHECK (user_satisfaction >= 1 AND user_satisfaction <= 5),
            response_time_ms INT DEFAULT 0,
            tokens_used INT DEFAULT 0,
            model_used VARCHAR(100),
            interaction_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success_rating ENUM('excellent', 'good', 'average', 'poor', 'failed') DEFAULT 'average',
            follow_up_required BOOLEAN DEFAULT FALSE,
            tags JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_session_id (session_id),
            INDEX idx_interaction_type (interaction_type),
            INDEX idx_timestamp (interaction_timestamp),
            FULLTEXT idx_user_input (user_input),
            FULLTEXT idx_ai_response (ai_response)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_user_interactions table created\n";
    }

    private function createWorkflowPatternsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_workflow_patterns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pattern_name VARCHAR(255) NOT NULL,
            pattern_category ENUM('development', 'deployment', 'maintenance', 'analysis', 'communication', 'planning') NOT NULL,
            trigger_conditions JSON NOT NULL,
            action_sequence JSON NOT NULL,
            success_criteria JSON,
            frequency_count INT DEFAULT 1,
            last_used TIMESTAMP NULL,
            average_completion_time INT DEFAULT 0,
            user_satisfaction_avg DECIMAL(3,2) DEFAULT 0.00,
            automation_potential ENUM('low', 'medium', 'high') DEFAULT 'low',
            complexity_level ENUM('simple', 'moderate', 'complex', 'expert') DEFAULT 'simple',
            tags JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_pattern_category (pattern_category),
            INDEX idx_frequency (frequency_count),
            INDEX idx_automation (automation_potential),
            FULLTEXT idx_pattern_name (pattern_name)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_workflow_patterns table created\n";
    }

    private function createAIKnowledgeBaseTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_knowledge_base (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic VARCHAR(255) NOT NULL,
            category ENUM('technical', 'business', 'procedural', 'reference', 'tutorial', 'faq') NOT NULL,
            content_type ENUM('text', 'code', 'command', 'explanation', 'example', 'warning') NOT NULL,
            title VARCHAR(500) NOT NULL,
            content LONGTEXT NOT NULL,
            difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
            tags JSON,
            source ENUM('user_input', 'documentation', 'code_analysis', 'web_search', 'manual_entry') DEFAULT 'manual_entry',
            verification_status ENUM('unverified', 'pending', 'verified', 'outdated') DEFAULT 'unverified',
            usage_count INT DEFAULT 0,
            last_accessed TIMESTAMP NULL,
            accuracy_rating DECIMAL(3,2) DEFAULT 0.00,
            related_topics JSON,
            code_examples JSON,
            best_practices JSON,
            common_mistakes JSON,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_topic (topic),
            INDEX idx_category (category),
            INDEX idx_content_type (content_type),
            INDEX idx_usage (usage_count),
            INDEX idx_difficulty (difficulty_level),
            FULLTEXT idx_title (title),
            FULLTEXT idx_content (content)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_knowledge_base table created\n";
    }

    private function createLearningSessionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_learning_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_type ENUM('training', 'assessment', 'practice', 'review', 'feedback') NOT NULL,
            session_name VARCHAR(255) NOT NULL,
            learning_objectives JSON,
            content_covered JSON,
            performance_metrics JSON,
            time_spent_minutes INT DEFAULT 0,
            completion_percentage DECIMAL(5,2) DEFAULT 0.00,
            skill_improvement DECIMAL(3,2) DEFAULT 0.00,
            feedback_given TEXT,
            next_session_suggestions JSON,
            session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            session_end TIMESTAMP NULL,
            status ENUM('in_progress', 'completed', 'paused', 'cancelled') DEFAULT 'in_progress',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_session_type (session_type),
            INDEX idx_status (status),
            INDEX idx_start (session_start)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_learning_sessions table created\n";
    }

    private function createAIAgentPersonalityTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_agent_personality (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_name VARCHAR(100) DEFAULT 'APS Assistant',
            personality_traits JSON NOT NULL,
            communication_style JSON NOT NULL,
            expertise_areas JSON NOT NULL,
            response_templates JSON,
            behavior_rules JSON,
            learning_preferences JSON,
            interaction_history JSON,
            adaptation_data JSON,
            performance_stats JSON,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            version VARCHAR(20) DEFAULT '1.0',
            active BOOLEAN DEFAULT TRUE,
            UNIQUE KEY unique_active_agent (active, agent_name)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_agent_personality table created\n";
    }

    private function createSystemTasksTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_system_tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            task_name VARCHAR(255) NOT NULL,
            task_type ENUM('automated', 'scheduled', 'triggered', 'manual') NOT NULL,
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            status ENUM('pending', 'in_progress', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            assigned_to ENUM('system', 'user', 'ai_agent') DEFAULT 'ai_agent',
            trigger_conditions JSON,
            execution_schedule VARCHAR(100),
            last_execution TIMESTAMP NULL,
            next_execution TIMESTAMP NULL,
            execution_count INT DEFAULT 0,
            success_rate DECIMAL(5,2) DEFAULT 0.00,
            average_execution_time INT DEFAULT 0,
            task_data JSON,
            result_data JSON,
            error_log TEXT,
            retry_count INT DEFAULT 0,
            max_retries INT DEFAULT 3,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_task_type (task_type),
            INDEX idx_priority (priority),
            INDEX idx_status (status),
            INDEX idx_next_execution (next_execution),
            FULLTEXT idx_task_name (task_name)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_system_tasks table created\n";
    }

    private function createUserPreferencesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            preference_category VARCHAR(100) NOT NULL,
            preference_key VARCHAR(100) NOT NULL,
            preference_value JSON NOT NULL,
            confidence_level DECIMAL(3,2) DEFAULT 1.00,
            source ENUM('explicit', 'inferred', 'learned') DEFAULT 'explicit',
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            usage_count INT DEFAULT 0,
            UNIQUE KEY unique_user_preference (user_id, preference_category, preference_key),
            INDEX idx_user_id (user_id),
            INDEX idx_category (preference_category),
            INDEX idx_confidence (confidence_level)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_user_preferences table created\n";
    }

    private function createContextMemoryTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_context_memory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            context_type ENUM('conversation', 'task', 'project', 'system', 'personal') NOT NULL,
            context_key VARCHAR(255) NOT NULL,
            context_value LONGTEXT NOT NULL,
            importance_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            retention_period INT DEFAULT 30,
            access_frequency INT DEFAULT 0,
            last_accessed TIMESTAMP NULL,
            expiry_date DATE,
            related_contexts JSON,
            metadata JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_context_type (context_type),
            INDEX idx_importance (importance_level),
            INDEX idx_expiry (expiry_date),
            FULLTEXT idx_context_value (context_value)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_context_memory table created\n";
    }

    private function createDecisionHistoryTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_decision_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            decision_type ENUM('task_execution', 'response_generation', 'workflow_suggestion', 'system_action', 'learning_choice') NOT NULL,
            decision_context JSON NOT NULL,
            options_considered JSON,
            chosen_option JSON NOT NULL,
            reasoning TEXT,
            confidence_score DECIMAL(3,2) DEFAULT 0.00,
            outcome ENUM('successful', 'partial', 'failed', 'pending') DEFAULT 'pending',
            feedback_received TEXT,
            learning_applied BOOLEAN DEFAULT FALSE,
            decision_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            execution_time_ms INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_decision_type (decision_type),
            INDEX idx_outcome (outcome),
            INDEX idx_timestamp (decision_timestamp),
            FULLTEXT idx_reasoning (reasoning)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_decision_history table created\n";
    }

    private function createPerformanceMetricsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_performance_metrics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            metric_date DATE NOT NULL,
            metric_type ENUM('accuracy', 'efficiency', 'learning', 'satisfaction', 'productivity') NOT NULL,
            user_id INT,
            session_id VARCHAR(255),
            metric_value DECIMAL(10,4) NOT NULL,
            metric_unit VARCHAR(50),
            baseline_value DECIMAL(10,4),
            improvement_percentage DECIMAL(5,2),
            sample_size INT DEFAULT 1,
            confidence_interval DECIMAL(5,2),
            measurement_method ENUM('automated', 'user_feedback', 'system_calculated') DEFAULT 'system_calculated',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_metric_date (metric_date),
            INDEX idx_metric_type (metric_type),
            INDEX idx_user_id (user_id),
            UNIQUE KEY unique_daily_metric (metric_date, metric_type, user_id)
        )";

        $this->pdo->exec($sql);
        echo "✅ ai_performance_metrics table created\n";
    }

    /**
     * Insert default AI agent personality data
     */
    public function insertDefaultAIPersonality() {
        echo "🎭 Setting up default AI agent personality...\n";

        $personality = [
            'communication_style' => [
                'tone' => 'professional_friendly',
                'formality_level' => 'adaptive',
                'response_length' => 'comprehensive',
                'technical_depth' => 'adaptive',
                'humor_usage' => 'minimal_appropriate'
            ],
            'personality_traits' => [
                'helpfulness' => 0.95,
                'patience' => 0.90,
                'accuracy' => 0.98,
                'creativity' => 0.85,
                'empathy' => 0.88,
                'proactivity' => 0.92
            ],
            'expertise_areas' => [
                'php_development' => 0.95,
                'database_management' => 0.90,
                'web_development' => 0.88,
                'system_administration' => 0.85,
                'project_management' => 0.80,
                'real_estate_domain' => 0.85,
                'ai_integration' => 0.90,
                'deployment_automation' => 0.82
            ],
            'behavior_rules' => [
                'always_learn_from_interactions',
                'maintain_context_awareness',
                'provide_actionable_solutions',
                'ask_clarifying_questions_when_needed',
                'follow_up_on_important_tasks',
                'respect_user_preferences',
                'continuous_improvement_focus'
            ]
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO ai_agent_personality
            (personality_traits, communication_style, expertise_areas, behavior_rules)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            personality_traits = VALUES(personality_traits),
            communication_style = VALUES(communication_style),
            expertise_areas = VALUES(expertise_areas),
            behavior_rules = VALUES(behavior_rules),
            last_updated = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            json_encode($personality['personality_traits']),
            json_encode($personality['communication_style']),
            json_encode($personality['expertise_areas']),
            json_encode($personality['behavior_rules'])
        ]);

        echo "✅ Default AI agent personality configured\n";
    }

    /**
     * Insert sample workflow patterns
     */
    public function insertSampleWorkflows() {
        echo "📋 Setting up sample workflow patterns...\n";

        $sample_workflows = [
            [
                'name' => 'Project Deep Scan Analysis',
                'category' => 'analysis',
                'triggers' => ['deep_scan_request', 'project_analysis_needed'],
                'actions' => ['scan_project_structure', 'analyze_codebase', 'identify_issues', 'generate_report'],
                'automation' => 'high'
            ],
            [
                'name' => 'Database Setup and Migration',
                'category' => 'deployment',
                'triggers' => ['database_setup_request', 'migration_needed'],
                'actions' => ['check_database_connection', 'create_schema', 'run_migrations', 'seed_data'],
                'automation' => 'high'
            ],
            [
                'name' => 'Security Audit and Hardening',
                'category' => 'maintenance',
                'triggers' => ['security_check_request', 'vulnerability_scan'],
                'actions' => ['scan_for_vulnerabilities', 'check_security_headers', 'validate_input_sanitization', 'generate_security_report'],
                'automation' => 'medium'
            ]
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO ai_workflow_patterns
            (pattern_name, pattern_category, trigger_conditions, action_sequence, automation_potential)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($sample_workflows as $workflow) {
            $stmt->execute([
                $workflow['name'],
                $workflow['category'],
                json_encode($workflow['triggers']),
                json_encode($workflow['actions']),
                $workflow['automation']
            ]);
        }

        echo "✅ Sample workflow patterns added\n";
    }

    /**
     * Insert initial knowledge base entries
     */
    public function insertInitialKnowledge() {
        echo "📚 Setting up initial knowledge base...\n";

        $knowledge_entries = [
            [
                'topic' => 'PHP Development Best Practices',
                'category' => 'technical',
                'type' => 'text',
                'title' => 'Essential PHP Development Guidelines',
                'content' => 'Always use prepared statements for database queries, implement proper error handling, follow PSR coding standards, and maintain consistent code formatting.',
                'difficulty' => 'intermediate'
            ],
            [
                'topic' => 'Real Estate CRM Management',
                'category' => 'business',
                'type' => 'text',
                'title' => 'Customer Relationship Management in Real Estate',
                'content' => 'Maintain detailed customer profiles, track interaction history, follow up regularly, personalize communications, and use data analytics for better customer insights.',
                'difficulty' => 'intermediate'
            ],
            [
                'topic' => 'AI Integration Strategies',
                'category' => 'technical',
                'type' => 'text',
                'title' => 'Implementing AI in Business Applications',
                'content' => 'Start with clear objectives, choose appropriate AI models, implement proper error handling, monitor performance, and continuously improve based on user feedback.',
                'difficulty' => 'advanced'
            ]
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO ai_knowledge_base
            (topic, category, content_type, title, content, difficulty_level)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($knowledge_entries as $entry) {
            $stmt->execute([
                $entry['topic'],
                $entry['category'],
                $entry['type'],
                $entry['title'],
                $entry['content'],
                $entry['difficulty']
            ]);
        }

        echo "✅ Initial knowledge base populated\n";
    }
}

// Auto-create AI database schema if this file is run directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $db_setup = new AIDatabaseSetup();
        $db_setup->createAISchema();
        $db_setup->insertDefaultAIPersonality();
        $db_setup->insertSampleWorkflows();
        $db_setup->insertInitialKnowledge();

        echo "\n🎉 AI Agent database setup completed successfully!\n";
        echo "Your AI assistant is now ready to learn and help you! 🤖✨\n";

    } catch (Exception $e) {
        echo "❌ Error setting up AI database: " . $e->getMessage() . "\n";
    }
}
