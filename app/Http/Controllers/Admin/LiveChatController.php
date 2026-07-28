<?php

namespace App\Http\Controllers\Admin;

use App\Services\LiveChatService;

class LiveChatController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new LiveChatService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        $status = $_GET['status'] ?? 'open';
        $sessions = $this->service ? $this->service->getSessions($status, 100) : [];
        $stats = $this->service ? $this->service->getStats() : [];
        $quickReplies = $this->service ? $this->service->getQuickReplies() : [];
        return $this->render('admin.live_chat.index', [
            'page_title' => 'Live Chat Support',
            'page_heading' => 'Live Chat Support',
            'sessions' => $sessions,
            'stats' => $stats,
            'quick_replies' => $quickReplies,
            'current_status' => $status
        ]);
    }

    public function open($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : (int)($_GET['id'] ?? 0);
        if (!$this->service || !$id) {
            return $this->redirect(BASE_URL . '/admin/live-chat');
        }
        $session = $this->service->getSession($id);
        if (!$session) {
            $this->setFlash('error', 'Session not found');
            return $this->redirect(BASE_URL . '/admin/live-chat');
        }
        $this->service->markRead($id, 'agent');
        $messages = $this->service->getMessages($id, 500, 0, true);
        $quickReplies = $this->service->getQuickReplies();
        return $this->render('admin.live_chat.view', [
            'page_title' => 'Chat #' . $id,
            'page_heading' => 'Chat with ' . ($session['visitor_name'] ?: $session['user_name'] ?: 'Visitor'),
            'session' => $session,
            'messages' => $messages,
            'quick_replies' => $quickReplies
        ]);
    }

    public function send()
    {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $isInternal = !empty($_POST['is_internal']);
        if (!$this->service || !$sessionId || !$message) {
            return $this->redirect(BASE_URL . '/admin/live-chat');
        }
        $this->service->sendMessage($sessionId, 'agent', $this->getUserId(), $this->getUserName(), $message, 'text', null, $isInternal);
        list($tSql, $tParams) = $this->tenantWhere();
        $this->pdo()->prepare("UPDATE chat_sessions SET status = 'active' WHERE id = ? AND status IN ('open','assigned') $tSql")
            ->execute(array_merge([$sessionId], $tParams));
        if ($isInternal) {
            return $this->redirect(BASE_URL . '/admin/live-chat/open/' . $sessionId);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function poll()
    {
        $sessionId = (int)($_GET['session_id'] ?? 0);
        $lastId = (int)($_GET['last_id'] ?? 0);
        if (!$this->service || !$sessionId) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => []]);
            exit;
        }
        try {
            $stmt = $this->pdo()->prepare("SELECT * FROM chat_messages WHERE session_id = ? AND id > ? AND is_internal_note = 0 ORDER BY id ASC LIMIT 100");
            $stmt->execute([$sessionId, $lastId]);
            $newMessages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->service->markRead($sessionId, 'agent');
            header('Content-Type: application/json');
            echo json_encode(['messages' => $newMessages, 'last_id' => empty($newMessages) ? $lastId : end($newMessages)['id']]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['messages' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function assign()
    {
        $sessionId = (int)($_GET['id'] ?? 0);
        if ($this->service && $sessionId) {
            $this->service->assignAgent($sessionId, $this->getUserId(), $this->getUserName());
        }
        return $this->redirect(BASE_URL . '/admin/live-chat/open/' . $sessionId);
    }

    public function close()
    {
        $sessionId = (int)($_GET['id'] ?? 0);
        $reason = $_GET['reason'] ?? null;
        if ($this->service && $sessionId) {
            $this->service->closeSession($sessionId, $this->getUserId(), $reason);
        }
        return $this->redirect(BASE_URL . '/admin/live-chat');
    }

    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $allowed = ['widget_enabled','widget_position','widget_color','widget_title','widget_subtitle','business_hours_only','business_hours_start','business_hours_end','auto_assign','welcome_message','offline_message'];
            foreach ($allowed as $k) {
                if (isset($_POST[$k])) {
                    $this->service->setWidgetSetting($k, $_POST[$k], $this->getUserId());
                }
            }
            $this->setFlash('success', 'Widget settings updated');
            return $this->redirect(BASE_URL . '/admin/live-chat/settings');
        }
        $settings = [];
        $keys = ['widget_enabled','widget_position','widget_color','widget_title','widget_subtitle','business_hours_only','business_hours_start','business_hours_end','auto_assign','welcome_message','offline_message'];
        foreach ($keys as $k) {
            $settings[$k] = $this->service->getWidgetSetting($k, '');
        }
        return $this->render('admin.live_chat.settings', [
            'page_title' => 'Chat Widget Settings',
            'page_heading' => 'Live Chat Widget Settings',
            'settings' => $settings
        ]);
    }

    public function quickReplies()
    {
        $replies = $this->service ? $this->service->getQuickReplies() : [];
        return $this->render('admin.live_chat.quick_replies', [
            'page_title' => 'Quick Replies',
            'page_heading' => 'Quick Reply Templates',
            'replies' => $replies
        ]);
    }

    public function api()
    {
        header('Content-Type: application/json');
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'unread':
                echo json_encode(['unread' => $this->service ? $this->service->getUnreadCount('admin') : 0]);
                break;
            case 'stats':
                echo json_encode($this->service ? $this->service->getStats() : []);
                break;
            default:
                echo json_encode(['error' => 'Unknown action']);
        }
        exit;
    }

    private function getUserId()
    {
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    private function getUserName()
    {
        return $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin';
    }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
