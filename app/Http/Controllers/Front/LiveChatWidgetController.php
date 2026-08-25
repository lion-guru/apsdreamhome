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
        try {
            $this->service = new LiveChatService($this->db);
        } catch (\Throwable $e) {
            error_log('LiveChatWidgetController service init error: ' . $e->getMessage());
            $this->service = null;
        }
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
            $json = json_decode(file_get_contents('php://input'), true) ?: [];
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
            $name = trim($json['name'] ?? ($_POST['name'] ?? ($_SESSION['user_name'] ?? '')));
            $email = trim($json['email'] ?? ($_POST['email'] ?? ($_SESSION['user_email'] ?? '')));
            $phone = trim($json['phone'] ?? ($_POST['phone'] ?? ''));
            $subject = trim($json['subject'] ?? ($_POST['subject'] ?? ''));
            $firstMessage = trim($json['message'] ?? ($_POST['message'] ?? ''));

            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $pageUrl = $json['page_url'] ?? ($_POST['page_url'] ?? '');
            $referrer = $json['referrer_url'] ?? ($_POST['referrer_url'] ?? '');

            $result = null;
            if ($this->service) {
                try {
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
                } catch (\Throwable $e) {
                    error_log('LiveChat startSession exception: ' . $e->getMessage());
                    $result = null;
                }
            } else {
                error_log('LiveChat start: service is null');
            }

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
            echo json_encode($result ? [
                'id' => $result['id'],
                'session_token' => $result['token'],
                'status' => 'open',
            ] : ['error' => 'Failed to start chat']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['error' => 'POST required']);
        exit;
    }

    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'POST required']);
            exit;
        }

        // Debug: log what we're receiving
        error_log('LiveChatWidgetController::send - Content-Type: ' . ($this->request->headers->get('content_type') ?? 'NOT SET'));
        error_log('LiveChatWidgetController::send - Raw content: ' . $this->request->getContentAsString());
        
        // Use request object which handles JSON body parsing
        $input = $this->request->getContentAsJson();
        error_log('LiveChatWidgetController::send - Parsed input: ' . json_encode($input));
        
        $token = $input['token'] ?? ($_POST['token'] ?? '');
        $message = trim($input['message'] ?? ($_POST['message'] ?? ''));
        if (!$token || !$message) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'token and message required']);
            exit;
        }
        $session = $this->service ? $this->service->getSessionByToken($token) : null;
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

        // Persist user message to chat_history
        try {
            $tid = (int)$this->tenantId();
            $uid = (int)($session['user_id'] ?? 0);
            $sid = $session['id'];
            $this->pdo()->prepare("INSERT INTO chat_history (user_id, session_id, role, message, tenant_id) VALUES (?, ?, 'user', ?, ?)")
                ->execute([$uid, $sid, $message, $tid]);
        } catch (\Throwable $e) {
            error_log("chat_history insert user msg error: " . $e->getMessage());
        }

        // AI auto-response when no agent is assigned
        if (empty($session['assigned_to']) && $session['status'] !== 'closed') {
            try {
                $ai = \App\Services\AI\FreeAIEngines::getInstance()->generate(
                    $message,
                    ['max_tokens' => 200, 'temperature' => 0.7],
                    'chat'
                );
                $aiReply = trim($ai['text'] ?? '');
                if ($aiReply !== '') {
                    $this->service->sendMessage($session['id'], 'bot', null, 'APS AI Bot', $aiReply, 'text', null, false);
                    // Persist bot response to chat_history
                    try {
                        $this->pdo()->prepare("INSERT INTO chat_history (user_id, session_id, role, message, tenant_id) VALUES (?, ?, 'bot', ?, ?)")
                            ->execute([$uid, $sid, $aiReply, $tid]);
                    } catch (\Throwable $e2) {
                        error_log("chat_history insert bot msg error: " . $e2->getMessage());
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
        $session = $this->service ? $this->service->getSessionByToken($token) : null;
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

    public function history()
    {
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0);
        $sessionId = $_GET['session_id'] ?? '';
        if (!$userId || !$sessionId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Missing params']);
            exit;
        }
        try {
            $stmt = $this->pdo()->prepare(
                "SELECT role, message, created_at FROM chat_history WHERE user_id = ? AND session_id = ? ORDER BY created_at ASC LIMIT 100"
            );
            $stmt->execute([$userId, $sessionId]);
            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
