<?php

namespace App\Services\AI;
/**
 * AI Ecosystem Manager
 * Manages Open-Source tools integration, data pipelines, and model training simulations.
 */
class AIEcosystemManager {
    private $db;

    public function __construct() {
        $this->db = \App\Core\App::database();
        $this->ensureTablesExist();
    }

    public function seedAgents() {
        $agents = [
            ['Lead Generator', 'lead_gen', ['lead_scoring', 'intent_analysis', 'automated_followup']],
            ['EMI Collector', 'emi_collector', ['billing_reminders', 'payment_processing', 'emi_tracking']],
            ['Market Researcher', 'researcher', ['web_scraping', 'competitor_analysis', 'price_tracking']],
            ['Data Analyst', 'analyst', ['property_valuation', 'market_trends', 'statistical_analysis']],
            ['Content Creator', 'content_creator', ['blog_writing', 'seo_optimization', 'property_descriptions']],
            ['Recommendation Engine', 'recommendation', ['personalized_suggestions', 'similar_properties', 'user_profiling']],
            ['Telecaller AI', 'telecalling', ['voice_synthesis', 'lead_qualification', 'appointment_scheduling']]
        ];

        foreach ($agents as $agent) {
            $name = $agent[0];
            $type = $agent[1];
            $capabilities = json_encode($agent[2]);

            $sql = "INSERT IGNORE INTO ai_agents (name, type, capabilities, status) VALUES (?, ?, ?, 'idle')";
            $this->db->execute($sql, [$name, $type, $capabilities]);
        }

        $this->seedWorkflows();
    }

    public function seedWorkflows() {
        $urgentWorkflow = [
            'name' => 'Urgent Lead Nurturing',
            'description' => 'Automatically triggered for high-priority leads with immediate notification and follow-up queuing.',
            'nodes' => json_encode([
                'nodes' => [
                    [
                        'id' => 'start',
                        'type' => 'trigger',
                        'name' => 'Lead Trigger',
                        'config' => ['event' => 'lead_analysis']
                    ],
                    [
                        'id' => 'notify_admin',
                        'type' => 'notification',
                        'name' => 'Notify Admin',
                        'config' => [
                            'message' => 'URGENT: New high-value lead detected! Budget: {{prioritization.score}}. Action: {{prioritization.recommended_action}}',
                            'type' => 'critical'
                        ]
                    ],
                    [
                        'id' => 'queue_call',
                        'type' => 'telecalling',
                        'name' => 'Queue AI Callback',
                        'config' => [
                            'action' => 'schedule_call',
                            'priority' => 'high',
                            'delay_minutes' => 5
                        ]
                    ]
                ],
                'connections' => [
                    ['from' => 'start', 'to' => 'notify_admin'],
                    ['from' => 'notify_admin', 'to' => 'queue_call']
                ]
            ])
        ];

        $sql = "INSERT IGNORE INTO ai_workflows (name, description, nodes) VALUES (?, ?, ?)";
        $this->db->execute($sql, [$urgentWorkflow['name'], $urgentWorkflow['description'], $urgentWorkflow['nodes']]);
    }

    private function ensureTablesExist() {
        // Migration: Add 'type' column if it doesn't exist in ai_agents
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM ai_agents LIKE 'type'");
            if (empty($cols)) {
                // Check if table exists before trying to add column
                $tableExists = $this->db->fetchAll("SHOW TABLES LIKE 'ai_agents'");
                if (!empty($tableExists)) {
                    $this->db->execute("ALTER TABLE ai_agents ADD COLUMN type VARCHAR(50) AFTER name");
                }
            }
        } catch (Exception $e) {
            // Table might not exist yet, ignore
        }

        $queries = [
            "CREATE TABLE IF NOT EXISTS ai_ecosystem_tools (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) UNIQUE,
                category ENUM('data_processing', 'model_training', 'analysis', 'visualization', 'automation'),
                source_url VARCHAR(255),
                status ENUM('active', 'maintenance', 'deprecated') DEFAULT 'active',
                capabilities JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_data_pipelines (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                tool_id INT,
                config JSON,
                status ENUM('idle', 'running', 'completed', 'failed') DEFAULT 'idle',
                last_run TIMESTAMP NULL,
                FOREIGN KEY (tool_id) REFERENCES ai_ecosystem_tools(id)
            )",
            "CREATE TABLE IF NOT EXISTS ai_training_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                model_name VARCHAR(255),
                dataset_info JSON,
                accuracy FLOAT,
                status ENUM('queued', 'training', 'ready') DEFAULT 'queued',
                completed_at TIMESTAMP NULL
            )",
            "CREATE TABLE IF NOT EXISTS ai_audit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                action VARCHAR(100),
                details JSON,
                status VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_user_suggestions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                category ENUM('website', 'software', 'company', 'other') DEFAULT 'other',
                suggestion TEXT,
                sentiment ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral',
                priority ENUM('low', 'medium', 'high') DEFAULT 'low',
                status ENUM('pending', 'reviewed', 'implemented', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_agents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) UNIQUE,
                type VARCHAR(50),
                capabilities JSON,
                status ENUM('active', 'idle', 'busy', 'offline') DEFAULT 'idle',
                current_workload INT DEFAULT 0,
                last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_chat_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NULL,
                message TEXT,
                ai_response TEXT,
                sender ENUM('user', 'ai') DEFAULT 'user',
                context VARCHAR(100),
                ip_address VARCHAR(45),
                confidence_score FLOAT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS ai_learning_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                property_id INT,
                action_type VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS user_search_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                search_query TEXT,
                filters JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS last_sentiment ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral' AFTER session_status",
            "CREATE TABLE IF NOT EXISTS ai_knowledge_graph (
                id INT AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(50),
                entity_value VARCHAR(255),
                confidence FLOAT DEFAULT 1.0,
                related_to_user INT NULL,
                metadata JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY entity_user (entity_type, entity_value, related_to_user)
            )",
            "CREATE TABLE IF NOT EXISTS ai_workflows (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) UNIQUE,
                description TEXT,
                nodes JSON,
                is_active BOOLEAN DEFAULT TRUE,
                last_run TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS workflow_executions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                workflow_id INT,
                status ENUM('running', 'success', 'failed') DEFAULT 'running',
                execution_log JSON,
                context JSON,
                duration_ms INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (workflow_id) REFERENCES ai_workflows(id)
            )"
        ];
        foreach ($queries as $q) {
            try {
                // Special handling for ALTER TABLE chat_sessions to avoid failure if table doesn't exist
                if (strpos($q, 'ALTER TABLE chat_sessions') !== false) {
                    $tableExists = $this->db->fetchAll("SHOW TABLES LIKE 'chat_sessions'");
                    if (empty($tableExists)) {
                        continue;
                    }
                }
                $this->db->execute($q);
            } catch (Exception $e) {
                // Log but continue
                error_log("AI Ecosystem Setup Error: " . $e->getMessage() . " Query: " . $q);
            }
        }
    }

    public function populateOpenSourceTools() {
        // First seed agents
        $this->seedAgents();

        $tools = [
            ['TensorFlow', 'model_training', 'https://tensorflow.org', ['Deep Learning', 'Neural Networks']],
            ['PyTorch', 'model_training', 'https://pytorch.org', ['Computer Vision', 'NLP']],
            ['Pandas', 'data_processing', 'https://pandas.pydata.org', ['Data Manipulation', 'Cleaning']],
            ['Scikit-Learn', 'model_training', 'https://scikit-learn.org', ['Classification', 'Regression']],
            ['Apache Airflow', 'automation', 'https://airflow.apache.org', ['Workflow Management']],
            ['Grafana', 'visualization', 'https://grafana.com', ['Monitoring', 'Dashboards']],
            ['Hugging Face', 'model_training', 'https://huggingface.co', ['Transformers', 'Pre-trained Models']],
            ['DVC', 'data_processing', 'https://dvc.org', ['Data Version Control']],
            ['MLflow', 'analysis', 'https://mlflow.org', ['Experiment Tracking']],
            ['Plotly', 'visualization', 'https://plotly.com', ['Interactive Charts']],
            ['Keras', 'model_training', 'https://keras.io', ['High-level Neural Networks API']],
            ['NLTK', 'analysis', 'https://nltk.org', ['Natural Language Toolkit']],
            ['Spacy', 'analysis', 'https://spacy.io', ['Industrial-strength NLP']],
            ['OpenCV', 'analysis', 'https://opencv.org', ['Real-time Computer Vision']],
            ['Ray', 'automation', 'https://ray.io', ['Distributed Computing']],
            ['BentoML', 'automation', 'https://bentoml.org', ['Model Serving']],
            ['Kubeflow', 'automation', 'https://kubeflow.org', ['ML on Kubernetes']],
            ['Prefect', 'automation', 'https://prefect.io', ['Dataflow Automation']],
            ['FastAPI', 'automation', 'https://fastapi.tiangolo.com', ['High-performance API Framework']],
            ['Redis', 'data_processing', 'https://redis.io', ['In-memory Data Structure Store']],
            ['PostgreSQL', 'data_processing', 'https://postgresql.org', ['Advanced Open Source Database']],
            ['Apache Spark', 'data_processing', 'https://spark.apache.org', ['Unified Analytics Engine']],
            ['Elasticsearch', 'analysis', 'https://elastic.co', ['Distributed Search Engine']],
            ['Kibana', 'visualization', 'https://elastic.co/kibana', ['Data Visualization for ES']],
            ['Jupyter', 'analysis', 'https://jupyter.org', ['Interactive Computing']],
            ['Streamlit', 'visualization', 'https://streamlit.io', ['ML App Framework']],
            ['Dash', 'visualization', 'https://plotly.com/dash', ['Analytical Web Apps']]
        ];

        foreach ($tools as $tool) {
            $name = $tool[0];
            $category = $tool[1];
            $url = $tool[2];
            $caps = json_encode($tool[3]);

            $sql = "INSERT IGNORE INTO ai_ecosystem_tools (name, category, source_url, capabilities) VALUES (?, ?, ?, ?)";
            $this->db->execute($sql, [$name, $category, $url, $caps]);
        }
    }

    public function getEcosystemStats() {
        $stats = [];

        // Count tools by category
        $stats['tools_by_category'] = $this->db->fetchAll("SELECT category, COUNT(*) as count FROM ai_ecosystem_tools GROUP BY category");

        // Active pipelines
        $stats['pipelines_by_status'] = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM ai_data_pipelines GROUP BY status");

        // Training sessions
        $stats['training_by_status'] = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM ai_training_sessions GROUP BY status");

        return $stats;
    }

    public function createPipeline($name, $toolId, $config) {
        $sql = "INSERT INTO ai_data_pipelines (name, tool_id, config, status) VALUES (?, ?, ?, 'idle')";
        $configJson = json_encode($config);

        return $this->db->execute($sql, [$name, $toolId, $configJson]);
    }

    public function startTraining($modelName, $datasetInfo) {
        $sql = "INSERT INTO ai_training_sessions (model_name, dataset_info, status) VALUES (?, ?, 'queued')";
        $datasetJson = json_encode($datasetInfo);

        return $this->db->execute($sql, [$modelName, $datasetJson]);
    }
}
