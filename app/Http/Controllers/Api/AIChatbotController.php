<?php
/**
 * AIChatbotController — REST API endpoints for AI Chatbot
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AIChatbotService;

class AIChatbotController extends BaseController
{
    private $chatbotService;

    public function __construct()
    {
        parent::__construct();
        $this->chatbotService = new AIChatbotService();
    }

    /**
     * Skip CSRF protection for API endpoints using Bearer token auth
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

/**
     * @OA\Post(
     *     path="/api/ai/chat",
     *     tags={"AI"},
     *     summary="AI Chat",
     *     description="Send messages to AI chatbot with multi-provider support",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"messages"},
     *             @OA\Property(property="messages", type="array", @OA\Items(type="object", @OA\Property(property="role", type="string"), @OA\Property(property="content", type="string"))),
     *             @OA\Property(property="provider", type="string", example="groq"),
     *             @OA\Property(property="model", type="string", example="llama-3.1-8b-instant"),
     *             @OA\Property(property="temperature", type="number", example=0.7),
     *             @OA\Property(property="max_tokens", type="integer", example=1024),
     *             @OA\Property(property="system_prompt", type="string"),
     *             @OA\Property(property="use_rag", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="AI response"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Chat endpoint - main chat endpoint
     * POST /api/ai/chat
     * Body: { messages: [...], provider?: string, model?: string, temperature?: float, max_tokens?: int, system_prompt?: string, use_rag?: bool }
     */
    public function chat(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
            $this->jsonError('Invalid request: messages array required', 400);
            return;
        }

        // Extract last user message from array
        $userMessage = '';
        foreach (array_reverse($input['messages']) as $msg) {
            if (isset($msg['role']) && $msg['role'] === 'user') {
                $userMessage = $msg['content'] ?? '';
                break;
            }
        }

        if (empty($userMessage)) {
            $this->jsonError('No user message found', 400);
            return;
        }

        $options = [];
        if (isset($input['provider'])) $options['provider'] = $input['provider'];
        if (isset($input['model'])) $options['model'] = $input['model'];
        if (isset($input['temperature'])) $options['temperature'] = (float)$input['temperature'];
        if (isset($input['max_tokens'])) $options['max_tokens'] = (int)$input['max_tokens'];
        if (isset($input['system_prompt'])) $options['system_prompt'] = $input['system_prompt'];

        $chatbotService = new \App\Services\AIChatbotService();

        // Multimodal: image attached
        if (!empty($input['image_base64'])) {
            $mimeType = $input['image_mime'] ?? 'image/jpeg';
            $result = $chatbotService->chatWithImage($input['messages'], $input['image_base64'], $mimeType, $options);
        } elseif (!empty($input['use_rag'])) {
            $result = $chatbotService->chatWithRag([['role' => 'user', 'content' => $userMessage]], $options);
        } else {
            $result = $chatbotService->chat($userMessage, $options);
        }

        if ($result['success']) {
            $this->jsonSuccess([
                'message' => $result['response'] ?? $result['content'] ?? '',
                'provider' => $result['provider'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown',
            ]);
        } else {
            $this->jsonError($result['error'] ?? 'Chat failed', 500);
        }
    }

    /**
     * Get available providers and their status
     * GET /api/ai/chat/providers
     */
    public function providers(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $chatbotService = new \App\Services\AIChatbotService();
        $status = $chatbotService->getProvidersStatus();
        
        $this->jsonSuccess($status);
    }

    /**
     * Set active provider
     * POST /api/ai/chat/set-provider
     * Body: { provider: string }
     */
    public function setProvider(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['provider'])) {
            $this->jsonError('Provider required', 400);
            return;
        }

        $chatbotService = new \App\Services\AIChatbotService();
        $success = $chatbotService->setProvider($input['provider']);
        
        if ($success) {
            $this->jsonSuccess(['provider' => $input['provider']]);
        } else {
            $this->jsonError('Invalid provider', 400);
        }
    }

    /**
     * Get current provider
     * GET /api/ai/chat/current-provider
     */
    public function currentProvider(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $chatbotService = new \App\Services\AIChatbotService();
        $this->jsonSuccess(['provider' => $chatbotService->getCurrentProvider()]);
    }

    /**
     * Clear conversation history
     * POST /api/ai/chat/clear
     */
    public function clearHistory(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $chatbotService = new \App\Services\AIChatbotService();
        $chatbotService->clearHistory();
        
        $this->jsonSuccess(['message' => 'History cleared']);
    }

    /**
     * Get conversation history
     * GET /api/ai/chat/history
     */
    public function history(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $chatbotService = new \App\Services\AIChatbotService();
        $this->jsonSuccess(['history' => $chatbotService->getHistory()]);
    }

    /**
     * Test provider connection
     * POST /api/ai/chat/test-provider
     * Body: { provider: string }
     */
    public function testProvider(): void
    {
        try {
            $this->requireAuth(['admin', 'super_admin']);

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['provider'])) {
                $this->jsonError('Provider required', 400);
                return;
            }

            $chatbotService = new \App\Services\AIChatbotService();
            $providers = $chatbotService->getProvidersStatus();

            $provider = $input['provider'];
            if (!isset($providers[$input['provider']])) {
                $this->jsonError('Invalid provider', 400);
                return;
            }

            $status = $providers[$provider];

            if (!$status['configured']) {
                $this->jsonError('Provider not configured (missing API key)', 400);
                return;
            }

            // Test with a simple message (unified AIService::chat wants string)
            $chatbot = new \App\Services\AIChatbotService();
            $chatbot->setProvider($provider);
            $result = $chatbot->chat('Test message - reply with "OK"', ['max_tokens' => 10]);

            if (!empty($result['success'])) {
                $this->jsonSuccess([
                    'provider' => $provider,
                    'status' => 'connected',
                    'test_response' => substr($result['response'] ?? '', 0, 50)
                ]);
            } else {
                $this->jsonError('Connection failed: ' . ($result['response'] ?? $result['error'] ?? 'Unknown error'), 500);
            }
        } catch (\Throwable $e) {
            error_log('testProvider failed: ' . $e->getMessage());
            $this->jsonError('Connection failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get provider status (admin)
     * GET /api/ai/chat/status
     */
    /**
     * Photo-based lead creation
     * POST /api/ai/lead-from-photo
     * Accepts base64 image + optional extracted info, creates lead with AI analysis
     */
    public function leadFromPhoto(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['image_base64'])) {
            $this->jsonError('Image data required', 400);
            return;
        }

        $imageBase64 = $input['image_base64'];
        $mimeType = $input['image_mime'] ?? 'image/jpeg';

        // Use AI to analyze the photo and extract property/lead info
        $chatbotService = new \App\Services\AIChatbotService();
        $analysisPrompt = "Analyze this property/lead photo and extract: property type, location hints, condition, estimated price range, and any visible contact info. Respond in JSON: {\"property_type\":\"...\",\"location\":\"...\",\"condition\":\"...\",\"estimated_price\":\"...\",\"contact_info\":\"...\",\"notes\":\"...\"}";
        $result = $chatbotService->chatWithImage([['role' => 'user', 'content' => $analysisPrompt]], $imageBase64, $mimeType);

        $analysis = [];
        if ($result['success']) {
            $text = $result['content'] ?? '';
            $start = strpos($text, '{');
            $end = strrpos($text, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $jsonText = substr($text, $start, $end - $start + 1);
                $jsonText = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', $jsonText);
                $analysis = json_decode($jsonText, true) ?? [];
            }
        }

        // Create lead with extracted info
        $db = \App\Core\Database\Database::getInstance();
        $userId = $_SESSION['user_id'] ?? $GLOBALS['api_user_id'] ?? 0;
        $tid = \App\Core\Middleware\TenantContext::getId();

        $leadData = [
            'name' => $input['name'] ?? ($analysis['contact_info'] ?? 'Photo Lead'),
            'phone' => $input['phone'] ?? '',
            'email' => $input['email'] ?? '',
            'source' => 'photo_lead',
            'status' => 'new',
            'created_by' => $userId,
            'tenant_id' => $tid,
            'notes' => json_encode($analysis),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Add property details if extracted
        if (!empty($analysis['property_type'])) {
            $leadData['notes'] = ($leadData['notes'] ?? '') . "\nProperty Type: " . $analysis['property_type'];
        }
        if (!empty($analysis['location'])) {
            $leadData['notes'] = ($leadData['notes'] ?? '') . "\nLocation: " . $analysis['location'];
        }
        if (!empty($analysis['estimated_price'])) {
            $leadData['notes'] = ($leadData['notes'] ?? '') . "\nEstimated Price: " . $analysis['estimated_price'];
        }

        $leadId = $db->insert('leads', $leadData);

        if ($leadId) {
            // Log activity
            $db->insert('lead_activities', [
                'lead_id' => $leadId,
                'activity_type' => 'photo_lead_created',
                'description' => 'Lead created from photo analysis',
                'old_value' => null,
                'new_value' => json_encode($analysis),
                'created_by' => $userId,
                'tenant_id' => $tid,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->jsonSuccess([
                'lead_id' => $leadId,
                'analysis' => $analysis,
                'message' => 'Lead created from photo analysis',
            ]);
        } else {
            $this->jsonError('Failed to create lead', 500);
        }
    }

    /**
     * Visit confirmation with photos
     * POST /api/ai/visit-confirm
     * Accepts visit_id + base64 images, confirms visit with photo evidence
     */
    public function visitConfirm(): void
    {
        $this->requireAuth(['associate', 'agent', 'employee', 'admin', 'super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['visit_id'])) {
            $this->jsonError('Visit ID required', 400);
            return;
        }

        $visitId = (int)$input['visit_id'];
        $userId = $_SESSION['user_id'] ?? $GLOBALS['api_user_id'] ?? 0;
        $tid = \App\Core\Middleware\TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance();

        // Verify visit exists and user has access (site_visits has no created_by col)
        $visit = $db->fetchOne("SELECT * FROM site_visits WHERE id = ? AND (agent_id = ? OR assigned_to = ? OR user_id = ?)", [$visitId, $userId, $userId, $userId]);
        if (!$visit) {
            $this->jsonError('Visit not found or access denied', 404);
            return;
        }

        // Process photos
        $photos = $input['photos'] ?? [];
        $photoPaths = [];
        $photoAnalysis = [];

        if (!empty($photos)) {
            $uploadDir = dirname(__DIR__, 3) . '/public/assets/uploads/visit-photos/' . $visitId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($photos as $idx => $photo) {
                $imageBase64 = $photo['image_base64'] ?? '';
                if (empty($imageBase64)) continue;

                $mimeType = $photo['mime_type'] ?? 'image/jpeg';
                $ext = $mimeType === 'image/png' ? 'png' : 'jpg';
                $filename = 'visit_' . $visitId . '_' . ($idx + 1) . '_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;

                $decoded = base64_decode($imageBase64);
                if ($decoded !== false && file_put_contents($filepath, $decoded) !== false) {
                    $relativePath = 'assets/uploads/visit-photos/' . $visitId . '/' . $filename;
                    $photoPaths[] = $relativePath;

                    // AI analysis of photo
                    $chatbotService = new \App\Services\AIChatbotService();
                    $analysisPrompt = "Analyze this site visit photo and describe: property condition, construction progress, surrounding area, and any notable observations. Be concise.";
                    $result = $chatbotService->chatWithImage([['role' => 'user', 'content' => $analysisPrompt]], $imageBase64, $mimeType);
                    if ($result['success']) {
                        $photoAnalysis[] = $result['content'];
                    }
                }
            }
        }

        // Update visit with photos and completion (site_visits has no photos col:
        // store first photo in checkin_photo, full list + analysis appended to notes)
        $feedback = trim($input['feedback'] ?? '');
        $rating = (int)($input['rating'] ?? 0);
        $ratingSql = ($rating >= 1 && $rating <= 5) ? ", rating = $rating" : "";

        $photoNotes = $feedback;
        if (!empty($photoPaths)) {
            $photoNotes .= "\n[AI Visit Photos: " . json_encode($photoPaths) . "]";
            if (!empty($photoAnalysis)) {
                $photoNotes .= "\n[AI Analysis: " . implode(' | ', $photoAnalysis) . "]";
            }
        }
        $firstPhoto = $photoPaths[0] ?? null;
        $photoSql = $firstPhoto !== null ? ", checkin_photo = ?" : "";
        $params = [$photoNotes];
        if ($firstPhoto !== null) {
            $params[] = $firstPhoto;
        }
        $params[] = $visitId;

        $db->execute(
            "UPDATE site_visits SET status = 'completed', feedback = ?, completed_at = NOW(){$photoSql}{$ratingSql} WHERE id = ?",
            $params
        );

        // Advance linked lead
        if (!empty($visit['lead_id'])) {
            $lead = $db->fetchOne("SELECT id, status FROM leads WHERE id = ? LIMIT 1", [$visit['lead_id']]);
            if ($lead && in_array($lead['status'], ['new', 'contacted', 'qualified'], true)) {
                $db->execute("UPDATE leads SET status = 'site_visit', updated_at = NOW() WHERE id = ?", [$lead['id']]);
                $db->insert('lead_activities', [
                    'lead_id' => $lead['id'],
                    'activity_type' => 'status_change',
                    'description' => 'Site visit completed with photos',
                    'old_value' => $lead['status'],
                    'new_value' => 'site_visit',
                    'created_by' => $userId,
                    'tenant_id' => $tid,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $db->execute("UPDATE leads SET first_response_at = NOW() WHERE id = ? AND first_response_at IS NULL", [$visit['lead_id']]);
        }

        $this->jsonSuccess([
            'visit_id' => $visitId,
            'status' => 'completed',
            'photos_uploaded' => count($photoPaths),
            'photo_paths' => $photoPaths,
            'photo_analysis' => $photoAnalysis,
            'message' => 'Visit confirmed with ' . count($photoPaths) . ' photo(s)',
        ]);
    }

    public function status(): void
    {
        $this->requireAdmin();
        
        $chatbotService = new \App\Services\AIChatbotService();
        $this->jsonSuccess($chatbotService->getProvidersStatus());
    }

    // ============================================================
    // Helper Methods
    // ============================================================
    protected function requireAuth(array $allowedRoles = []): int {
        // Disable output buffering to ensure clean JSON response
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        error_log("requireAuth: userId=$userId, allowedRoles=" . json_encode($allowedRoles) . ", api_user_role=" . ($GLOBALS['api_user_role'] ?? 'NOT_SET'));
        
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        
        // Check role if allowed roles specified
        if (!empty($allowedRoles)) {
            $role = $GLOBALS['api_user_role'] ?? '';
            error_log("requireAuth: checking role='$role' against allowed=" . json_encode($allowedRoles));
            if (!in_array($role, $allowedRoles, true)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
                exit;
            }
        }
        
        return $userId;
    }

    protected function requireAdmin(): void {
        $this->requireAuth(['admin', 'super_admin', 'employee']);
    }

    /**
     * Export conversation history as JSON
     * GET /api/ai/chat/export
     */
    public function exportConversation(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $chatbotService = new \App\Services\AIChatbotService();
        $exportData = $chatbotService->exportConversation();
        
        // Return as downloadable JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ai-chat-export-' . date('Y-m-d-H-i-s') . '.json"');
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import conversation from JSON
     * POST /api/ai/chat/import
     * Body: { json: "..." } or multipart with file upload
     */
    public function importConversation(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $chatbotService = new \App\Services\AIChatbotService();
        
        // Check for file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $json = file_get_contents($_FILES['file']['tmp_name']);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            $json = $input['json'] ?? '';
        }

        if (empty($json)) {
            $this->jsonError('JSON data or file required', 400);
            return;
        }

        $chatbotService = new \App\Services\AIChatbotService();
        $success = $chatbotService->importConversationJson($json);

        if ($success) {
            $this->jsonSuccess(['message' => 'Conversation imported successfully']);
        } else {
            $this->jsonError('Invalid JSON format or import failed', 400);
        }
    }

    /**
     * Get conversation history
     * GET /api/ai/chat/history
     */
    public function getHistory(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $chatbotService = new \App\Services\AIChatbotService();
        $history = $chatbotService->getHistory();
        
        $this->jsonSuccess(['history' => $history]);
    }
}