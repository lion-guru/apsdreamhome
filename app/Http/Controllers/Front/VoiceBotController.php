<?php
/**
 * APS Dream Homes — Voice Bot Browser Controller
 * Option A: Customer ko link bhejo → browser mein Hindi mein baat kare
 * Uses Web Speech API (STT) + Groq/Llama (LLM) + Web Speech Synthesis (TTS)
 * 100% free, zero API keys needed
 */
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class VoiceBotController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/base';
    }

    protected function skipCsrfProtection(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/voice-bot/') !== false) return true;
        return false;
    }

    /**
     * Public voice bot page — customer ko ye link bhejo
     * /voice-bot?name=Rahul&phone=919999999999
     */
    public function index()
    {
        $name = $_GET['name'] ?? '';
        $phone = $_GET['phone'] ?? '';
        $lang = $_GET['lang'] ?? 'hi';

        $this->render('pages/voice_bot/index', [
            'page_title' => 'APS Dream Homes — Voice Assistant',
            'customer_name' => $name,
            'customer_phone' => $phone,
            'language' => $lang,
        ]);
    }

    /**
     * API: Process voice message via PropertyChatbotService (rich, live data)
     * POST /api/voice-bot/chat
     */
    public function chat()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $message = $input['message'] ?? '';
        $session = $input['session'] ?? uniqid('vb_');
        $lang = $input['lang'] ?? 'hi';
        $name = $input['name'] ?? '';
        $phone = $input['phone'] ?? '';

        if (empty($message)) {
            echo json_encode(['error' => 'No message']);
            return;
        }

        require_once __DIR__ . '/../../../Services/PropertyChatbotService.php';
        $brain = new \App\Services\PropertyChatbotService();

        $result = $brain->processMessage($message);

        echo json_encode([
            'reply' => $result['reply'],
            'intent' => $result['intent'],
            'session' => $session,
        ]);
    }

    /**
     * Admin: Voice bot analytics dashboard
     * /admin/voice-bot
     */
    public function adminDashboard()
    {
        $this->requireAdmin();
        $this->layout = 'layouts/admin';

        $stats = ['total_sessions' => 0, 'active' => 0, 'completed' => 0, 'transferred' => 0];
        $recent = [];
        $channels = [];

        try {
            $this->db = \App\Core\Database\Database::getInstance()->getConnection();

            $stats = $this->db->query("
                SELECT
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'transferred' THEN 1 ELSE 0 END) as transferred
                FROM auc_voice_bot_sessions
            ")->fetch(\PDO::FETCH_ASSOC) ?: $stats;

            $recent = $this->db->query("
                SELECT * FROM auc_voice_bot_sessions ORDER BY started_at DESC LIMIT 20
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $channels = $this->db->query("
                SELECT channel, COUNT(*) as count FROM auc_conversations GROUP BY channel ORDER BY count DESC
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('VoiceBot dashboard error: ' . $e->getMessage());
        }

        $this->render('admin/voice-bot/dashboard', [
            'activePage' => 'voice-bot',
            'stats' => $stats,
            'recent' => $recent,
            'channels' => $channels,
        ]);
    }
}
