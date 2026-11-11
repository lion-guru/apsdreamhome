<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AI\ExecutiveAIService;

class ExecutiveAIController extends BaseController
{
    private $executiveAI;

    public function __construct()
    {
        parent::__construct();
        $this->executiveAI = ExecutiveAIService::getInstance();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    protected function requireAuth(array $allowedRoles = []): int {
        while (ob_get_level()) { ob_end_clean(); }
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        if (!empty($allowedRoles)) {
            $role = $GLOBALS['api_user_role'] ?? '';
            if (!in_array($role, $allowedRoles, true)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                exit;
            }
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $GLOBALS['api_user_role'] ?? '';
        $_SESSION['role'] = $_SESSION['user_role'];
        return $userId;
    }

    /**
     * Health check for CEO AI service
     * GET /api/ai/executive/health
     */
    public function health(): void
    {
        $this->setCorsHeaders();
        $this->jsonSuccess($this->executiveAI->healthCheck());
    }

    /**
     * Get complete business snapshot
     * GET /api/ai/executive/snapshot
     */
    public function snapshot(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $snapshot = $this->executiveAI->getBusinessSnapshot();
            $this->jsonSuccess($snapshot);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Generate executive summary
     * GET /api/ai/executive/summary
     */
    public function summary(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $result = $this->executiveAI->generateExecutiveSummary();
            if ($result['success']) {
                $this->jsonSuccess($result);
            } else {
                $this->jsonError($result['error'] ?? 'Failed to generate summary', 500);
            }
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Ask CEO a strategic question
     * POST /api/ai/executive/ask
     */
    public function ask(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if (empty($input['question'])) {
            $this->jsonError('Question is required', 400);
            return;
        }

        try {
            $result = $this->executiveAI->askCEO($input['question'], $input['context'] ?? []);
            if ($result['success']) {
                $this->jsonSuccess($result);
            } else {
                $this->jsonError($result['error'] ?? 'Failed to process question', 500);
            }
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Generate strategic report
     * POST /api/ai/executive/report
     */
    public function report(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if (empty($input['topic'])) {
            $this->jsonError('Topic is required', 400);
            return;
        }

        try {
            $result = $this->executiveAI->generateStrategicReport($input['topic'], $input['params'] ?? []);
            if ($result['success']) {
                $this->jsonSuccess($result);
            } else {
                $this->jsonError($result['error'] ?? 'Failed to generate report', 500);
            }
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Execute predefined CEO actions
     * POST /api/ai/executive/execute
     */
    public function execute(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if (empty($input['action'])) {
            $this->jsonError('Action is required', 400);
            return;
        }

        try {
            $result = $this->executiveAI->executeAction($input['action'], $input['params'] ?? []);
            if ($result['success']) {
                $this->jsonSuccess($result);
            } else {
                $this->jsonError($result['error'] ?? 'Action failed', 500);
            }
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get competitive analysis
     * GET /api/ai/executive/competitive
     */
    public function competitive(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $analysis = $this->executiveAI->getCompetitiveAnalysis();
            $this->jsonSuccess($analysis);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get resource allocation recommendations
     * GET /api/ai/executive/resources
     */
    public function resources(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $result = $this->executiveAI->getResourceAllocation();
            $this->jsonSuccess($result);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Daily briefing for CEO
     * GET /api/ai/executive/briefing
     */
    public function briefing(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $result = $this->executiveAI->executeAction('daily_briefing');
            $this->jsonSuccess($result);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Weekly review for CEO
     * GET /api/ai/executive/weekly-review
     */
    public function weeklyReview(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        try {
            $result = $this->executiveAI->executeAction('weekly_review');
            $this->jsonSuccess($result);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Strategic insights on specific metric
     * POST /api/ai/executive/insight
     */
    public function insight(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if (empty($input['metric'])) {
            $this->jsonError('Metric is required', 400);
            return;
        }

        try {
            $question = "Provide strategic insight on: {$input['metric']}. Current value: " . ($input['value'] ?? 'N/A') . ". Context: " . ($input['context'] ?? '');
            $result = $this->executiveAI->askCEO($question);
            
            if ($result['success']) {
                $this->jsonSuccess($result);
            } else {
                $this->jsonError($result['error'] ?? 'Failed to generate insight', 500);
            }
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get all available executive actions
     * GET /api/ai/executive/actions
     */
    public function actions(): void
    {
        $this->requireAuth(['admin', 'super_admin', 'ceo', 'cto', 'cfo', 'coo']);
        $this->setCorsHeaders();
        
        $actions = [
            'daily_briefing' => 'Get daily business briefing with key metrics',
            'weekly_review' => 'Get weekly review with trends and action items',
            'resource_allocation' => 'Get resource allocation recommendations',
            'competitive_analysis' => 'Get competitive analysis and positioning',
            'strategic_report' => 'Generate strategic report on specific topic',
            'answer_question' => 'Ask a strategic question to CEO AI',
            'executive_summary' => 'Get executive summary of business health',
        ];
        
        $this->jsonSuccess([
            'actions' => $actions,
            'usage' => 'POST /api/ai/executive/execute with {action: "action_name", params: {...}}'
        ]);
    }
}