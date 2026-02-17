<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\AI\AIManager;
use App\Services\AI\AIToolsManager;
use App\Services\AI\AIEcosystemManager;
use App\Core\Agent\Agent;
use App\Services\GeminiService;
use Exception;

class AiController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isAdmin()) {
            $this->redirect('login');
        }
    }

    public function hub()
    {
        $this->data['page_title'] = 'AI Hub';

        // Initialize Services
        // Ensure files are loaded if autoloader misses them (though composer should handle it)
        if (file_exists(APP_ROOT . '/app/Services/AI/AIManager.php')) {
            require_once APP_ROOT . '/app/Services/AI/AIManager.php';
        }
        if (file_exists(APP_ROOT . '/app/Services/AI/AIToolsManager.php')) {
            require_once APP_ROOT . '/app/Services/AI/AIToolsManager.php';
        }
        if (file_exists(APP_ROOT . '/app/Services/AI/AIEcosystemManager.php')) {
            require_once APP_ROOT . '/app/Services/AI/AIEcosystemManager.php';
        }

        $aiManager = new AIManager($this->db);
        // $toolsManager = new AIToolsManager($this->db); // Not used in view logic currently
        // $ecoManager = new AIEcosystemManager($this->db); // Not used in view logic currently

        // Handle Workflow Creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_workflow'])) {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->data['msg'] = '<div class="alert alert-danger">Security validation failed. Please try again.</div>';
            } else {
                $name = $_POST['name'] ?? '';
                $desc = $_POST['description'] ?? '';
                $trigger = $_POST['trigger_type'] ?? '';

                $actions = [
                    [
                        'agent_id' => $_POST['agent_id'] ?? '',
                        'task_type' => $_POST['task_type'] ?? '',
                        'config' => [],
                        'stop_on_failure' => true
                    ]
                ];
                $actions_json = json_encode($actions);

                $result = $this->db->execute("INSERT INTO ai_workflows (name, description, trigger_type, actions, is_active) VALUES (:name, :description, :trigger_type, :actions, 1)", [
                    'name' => $name,
                    'description' => $desc,
                    'trigger_type' => $trigger,
                    'actions' => $actions_json
                ]);

                if ($result) {
                    $this->data['msg'] = '<div class="alert alert-success">Workflow created successfully!</div>';
                    // Log activity if function exists
                    if (function_exists('log_admin_activity')) {
                        log_admin_activity($_SESSION['admin_id'] ?? 0, 'create_ai_workflow', "Created workflow: $name");
                    }
                } else {
                    $this->data['msg'] = '<div class="alert alert-danger">Error creating workflow</div>';
                }
            }
        }

        // Update mode if changed in session
        if (isset($_SESSION['ai_mode'])) {
            $aiManager->setMode($_SESSION['ai_mode']);
        }

        // Pass manager to view for mode display
        $this->data['aiManager'] = $aiManager;

        // Fetch Dashboard Stats
        $this->data['total_agents'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_agents")['count'] ?? 0;
        $this->data['active_workflows'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_workflows WHERE is_active=1")['count'] ?? 0;
        $this->data['total_executions'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs")['count'] ?? 0;
        $this->data['avg_latency'] = $this->db->fetchOne("SELECT AVG(execution_time_ms) as avg FROM ai_agent_logs")['avg'] ?: 0;
        $this->data['pending_jobs'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_jobs WHERE status='pending'")['count'] ?? 0;

        // Chart Data (Last 7 Days)
        $chart_labels = [];
        $chart_success = [];
        $chart_failed = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_labels[] = date('D', strtotime($date));

            $status_success = 'success';
            $success = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs WHERE DATE(created_at) = :date AND status = :status", [
                'date' => $date,
                'status' => $status_success
            ])['count'] ?? 0;

            $status_failed = 'failed';
            $failed = $this->db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs WHERE DATE(created_at) = :date AND status = :status", [
                'date' => $date,
                'status' => $status_failed
            ])['count'] ?? 0;

            $chart_success[] = $success;
            $chart_failed[] = $failed;
        }

        $this->data['chart_labels'] = $chart_labels;
        $this->data['chart_success'] = $chart_success;
        $this->data['chart_failed'] = $chart_failed;

        // Fetch Agents and Workflows for lists
        $this->data['agents_list'] = $this->db->fetchAll("SELECT * FROM ai_agents");
        $this->data['workflows_list'] = $this->db->fetchAll("SELECT * FROM ai_workflows LIMIT 5");

        $this->render('admin/ai/hub');
    }

    public function agent()
    {
        $this->data['page_title'] = 'AI Agent';

        // Ensure Agent class is loaded
        if (file_exists(APP_ROOT . '/vendor/autoload.php')) {
            require_once APP_ROOT . '/vendor/autoload.php';
        }

        // Initialize Agent
        // Use full namespace if needed, but 'use' statement should handle it if class exists
        // Check if class exists to avoid error
        if (class_exists('App\Core\Agent\Agent')) {
            $agent = new Agent();
        } else {
            // Fallback or error handling
            $agent = null;
            $this->data['error'] = "Agent class not found.";
        }

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $agent) {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->data['error'] = "Invalid CSRF token.";
            } else {
                $action = $_POST['action'] ?? '';
                if ($action === 'run') {
                    $res = $agent->runDailyOps();
                    $message = $res['planned'] ?? 'Operations ran.';
                }
                if ($action === 'report') {
                    $res = $agent->generateReport();
                    $message = $res['report'] ?? 'Report generated.';
                }
            }
        }

        $this->data['message'] = $message;

        $logFile = APP_ROOT . '/storage/logs/agent.log';
        $this->data['logs'] = file_exists($logFile) ? array_slice(array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 20) : [];

        $this->render('admin/ai/agent');
    }

    public function leadScoring()
    {
        $this->data['page_title'] = 'Lead Scoring';

        if (file_exists(APP_ROOT . '/app/Services/GeminiService.php')) {
            require_once APP_ROOT . '/app/Services/GeminiService.php';
        }

        $gemini = new GeminiService();
        $success_msg = '';
        $error_msg = '';

        if (isset($_POST['action']) && $_POST['action'] === 'score_leads') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error_msg = "Invalid CSRF token.";
            } else {
                try {
                    $leads_to_score = $this->db->fetchAll("SELECT id, name, status, notes, source, budget, assigned_to FROM leads WHERE ai_score IS NULL OR status != 'Converted' LIMIT 10");

                    if (empty($leads_to_score)) {
                        $success_msg = "All eligible leads are already scored.";
                    } else {
                        $scored_count = 0;
                        foreach ($leads_to_score as $lead) {
                            $prompt = "Analyze this real estate lead and provide a score from 0 to 100 based on their potential to convert.
                            Lead Details:
                            Name: {$lead['name']}
                            Status: {$lead['status']}
                            Source: {$lead['source']}
                            Notes: {$lead['notes']}

                            Respond ONLY in JSON format: {\"score\": 85, \"summary\": \"High interest shown in premium properties...\"}";

                            $ai_response = $gemini->generateText($prompt);

                            if (preg_match('/```json\s*(.*?)\s*```/s', $ai_response, $matches)) {
                                $json_content = $matches[1];
                            } else {
                                $json_content = $ai_response;
                            }

                            $data = json_decode($json_content, true);

                            if ($data && isset($data['score'])) {
                                $this->db->execute("UPDATE leads SET ai_score = :score, ai_summary = :summary WHERE id = :id", [
                                    'score' => $data['score'],
                                    'summary' => $data['summary'],
                                    'id' => $lead['id']
                                ]);

                                // Trigger alert for high score leads
                                if ($data['score'] >= 80) {
                                    if (file_exists(APP_ROOT . '/app/includes/notification_manager.php')) {
                                        require_once APP_ROOT . '/app/includes/notification_manager.php';
                                        // $nm = new NotificationManager($this->db->getConnection());
                                        // Implement notification logic if needed
                                    }
                                }

                                $scored_count++;
                            }
                        }
                        $success_msg = "Successfully scored $scored_count leads using AI.";
                    }
                } catch (Exception $e) {
                    $error_msg = "AI Scoring Error: " . $e->getMessage();
                }
            }
        }

        $this->data['success_msg'] = $success_msg;
        $this->data['error_msg'] = $error_msg;

        // Fetch scored leads
        $this->data['scored_leads'] = $this->db->fetchAll("SELECT * FROM leads WHERE ai_score IS NOT NULL ORDER BY ai_score DESC LIMIT 50");

        $this->render('admin/ai/lead_scoring');
    }

    // verifyCsrfToken is inherited from BaseController

}
