<?php

namespace App\Services\AI;
/**
 * AI Ecosystem Manager
 * Manages Open-Source tools integration, data pipelines, and model training simulations.
 */
class AIEcosystemManager {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->ensureTablesExist();
    }

    public function seedAgents() {
        $users = [
            ['Lead Generator', 'lead_gen', ['lead_scoring', 'intent_analysis', 'automated_followup']],
            ['EMI Collector', 'emi_collector', ['billing_reminders', 'payment_processing', 'emi_tracking']],
            ['Market Researcher', 'researcher', ['web_scraping', 'competitor_analysis', 'price_tracking']],
            ['Data Analyst', 'analyst', ['property_valuation', 'market_trends', 'statistical_analysis']],
            ['Content Creator', 'content_creator', ['blog_writing', 'seo_optimization', 'property_descriptions']],
            ['Recommendation Engine', 'recommendation', ['personalized_suggestions', 'similar_properties', 'user_profiling']],
            ['Telecaller AI', 'telecalling', ['voice_synthesis', 'lead_qualification', 'appointment_scheduling']]
        ];

        foreach ($users as $agent) {
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

        try {
            $sql = "INSERT IGNORE INTO ai_workflows (name, description, nodes) VALUES (?, ?, ?)";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
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
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
                    error_log("AIEcosystemManager.php: " . $e->getMessage());
        }

        $queries = [
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS last_sentiment ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral' AFTER session_status",
            "",
            "",
            ""
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
            } catch (\Exception $e) {
                // Log but continue
                error_log("AI Ecosystem Setup Error: " . $e->getMessage() . " Query: " . $q);
            }
        }
    }

    public function populateOpenSourceTools() {
        // First seed users
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

            try {
                $sql = "INSERT IGNORE INTO ai_ecosystem_tools (name, category, source_url, capabilities) VALUES (?, ?, ?, ?)";
            } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            error_log($e->getMessage());
            }
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
