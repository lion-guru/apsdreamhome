<?php

namespace App\Http\Controllers\Front;

use App\Services\LiveChatService;
use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class LiveChatWidgetController extends BaseController
{
    use TenantAwareTrait;
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new LiveChatService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    /**
     * Public widget — CSRF check is unsafe for unauthenticated visitors
     * (they don't have a session token yet when starting a chat).
     * Rate limiting + IP throttling should be applied at the edge if needed.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function start()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
            $name = trim($_POST['name'] ?? ($_SESSION['user_name'] ?? ''));
            $email = trim($_POST['email'] ?? ($_SESSION['user_email'] ?? ''));
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $firstMessage = trim($_POST['message'] ?? '');

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $pageUrl = $_POST['page_url'] ?? '';
            $referrer = $_POST['referrer_url'] ?? '';

            $result = $this->service->startSession(
                $userId ?: null,
                $userId ?: null,
                $name,
                $email,
                $pageUrl,
                $referrer,
                $ip,
                $ua
            );

            if ($result) {
                $this->pdo()->prepare("UPDATE chat_sessions SET visitor_phone = ?, subject = ?, category = ?, priority = ? WHERE id = ? AND tenant_id = ?")
                    ->execute([$phone, $subject, $_POST['category'] ?? null, $_POST['priority'] ?? 'normal', $result['id'], $this->tenantId()]);
                if ($firstMessage) {
                    $this->service->sendMessage($result['id'], 'visitor', $userId ?: null, $name, $firstMessage);
                }
                $welcome = $this->service->getWidgetSetting('welcome_message', 'Welcome! How can we help?');
                $this->service->sendMessage($result['id'], 'bot', null, 'APS Bot', $welcome, 'text', null, false);
            }
            header('Content-Type: application/json');
            echo json_encode($result ?: ['error' => 'Failed to start chat']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['error' => 'POST required']);
        exit;
    }

    public function send()
    {
        $token = $_POST['token'] ?? '';
        $message = trim($_POST['message'] ?? '');
        if (!$token || !$message) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'token and message required']);
            exit;
        }
        $session = $this->service->getSessionByToken($token);
        if (!$session) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid session']);
            exit;
        }
        $msgId = $this->service->sendMessage(
            $session['id'],
            'visitor',
            $session['user_id'],
            $session['visitor_name'] ?: $session['user_name'] ?: 'Visitor',
            $message
        );

        // AI auto-response when no agent is assigned
        if (empty($session['assigned_to']) && $session['status'] !== 'closed') {
            try {
                $groqKey = getenv('GROQ_API_KEY') ?: '';
                if (!empty($groqKey)) {
                    $aiReply = $this->getAutoReply($message, $session, $groqKey);
                    if (!empty($aiReply)) {
                        $this->service->sendMessage($session['id'], 'bot', null, 'APS AI Bot', $aiReply, 'text', null, false);
                    }
                }
            } catch (\Throwable $e) {
                error_log("LiveChat AI auto-reply error: " . $e->getMessage());
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $msgId]);
        exit;
    }

    public function poll()
    {
        $token = $_GET['token'] ?? '';
        $lastId = (int)($_GET['last_id'] ?? 0);
        if (!$token) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => []]);
            exit;
        }
        $session = $this->service->getSessionByToken($token);
        if (!$session) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => []]);
            exit;
        }
        try {
            $stmt = $this->pdo()->prepare("SELECT * FROM chat_messages WHERE session_id = ? AND id > ? AND sender_type IN ('agent','bot','system') AND is_internal_note = 0 ORDER BY id ASC LIMIT 100");
            $stmt->execute([$session['id'], $lastId]);
            $msgs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->service->markRead($session['id'], 'visitor');
            header('Content-Type: application/json');
            echo json_encode([
                'messages' => $msgs,
                'last_id' => empty($msgs) ? $lastId : end($msgs)['id'],
                'status' => $session['status']
            ]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function widget()
    {
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        $userName = $_SESSION['user_name'] ?? '';
        $userEmail = $_SESSION['user_email'] ?? '';

        if ($this->service === null) {
            $settings = [
                'enabled' => '1',
                'position' => 'bottom-right',
                'color' => '#007bff',
                'title' => 'APS Dream Home Support',
                'subtitle' => 'We typically reply in a few minutes',
                'auto_assign' => '1'
            ];
        } else {
            $settings = [
                'enabled' => $this->service->getWidgetSetting('widget_enabled', '1'),
                'position' => $this->service->getWidgetSetting('widget_position', 'bottom-right'),
                'color' => $this->service->getWidgetSetting('widget_color', '#007bff'),
                'title' => $this->service->getWidgetSetting('widget_title', 'APS Dream Home Support'),
                'subtitle' => $this->service->getWidgetSetting('widget_subtitle', 'We typically reply in a few minutes'),
                'auto_assign' => $this->service->getWidgetSetting('auto_assign', '1')
            ];
        }
        header('Content-Type: application/json');
        echo json_encode([
            'user' => ['id' => $userId, 'name' => $userName, 'email' => $userEmail],
            'settings' => $settings
        ]);
        exit;
    }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }

    /**
     * Generate AI auto-reply for unattended chats using Groq (Llama 3.3 70B)
     */
    private function getAutoReply(string $visitorMessage, array $session, string $groqKey): ?string
    {
        $context = "You are APS Dream Home's AI assistant. You help real estate customers in Gorakhpur, UP. " .
            "Be helpful, friendly, professional. Mix Hindi and English naturally (Hinglish). " .
            "If the question is complex or needs human help, say 'Ek hamara team member aapko jaldi contact karega.' " .
            "Never make up prices — say 'exact price ke liye hamare sales team se baat karein.' " .
            "Keep replies under 3 sentences.";

        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'user', 'content' => $context . "\n\nVisitor: " . $visitorMessage . "\n\nReply:"],
            ],
            'temperature' => 0.7,
            'max_tokens' => 200,
            'stream' => false,
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $groqKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $reply = trim($result['choices'][0]['message']['content'] ?? '');
            return !empty($reply) ? $reply : null;
        }
        return null;
    }
}
