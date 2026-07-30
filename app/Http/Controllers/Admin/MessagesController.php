<?php
namespace App\Http\Controllers\Admin;

use \App\Traits\TenantAwareTrait;

class MessagesController extends \App\Http\Controllers\Admin\AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function inbox()
    {
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        $conversations = $this->db->fetchAll("
            SELECT
                CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS other_user_id,
                MAX(m.sent_at) AS last_message_time,
                (SELECT content FROM messages
                 WHERE (sender_id = ? AND receiver_id = m.receiver_id)
                    OR (sender_id = m.receiver_id AND receiver_id = ?)
                 ORDER BY sent_at DESC LIMIT 1) AS last_message,
                (SELECT COUNT(*) FROM messages
                 WHERE receiver_id = ? AND sender_id = (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
                   AND read_at IS NULL) AS unread_count,
                u.name AS other_user_name,
                u.email AS other_user_email,
                u.role AS other_user_role
            FROM messages m
            JOIN users u ON u.id = (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
            WHERE m.sender_id = ? OR m.receiver_id = ?
            GROUP BY other_user_id
            ORDER BY last_message_time DESC
        ", [$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);

        $data = [
            'page_title' => 'Messages - Inbox',
            'conversations' => $conversations ?: [],
            'total_unread' => $this->db->fetchColumn("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND read_at IS NULL", [$userId]) ?: 0,
        ];
        $this->render('admin/messages/inbox', $data);
    }

    public function conversation($otherUserId)
    {
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        $otherUserId = (int)$otherUserId;

        $messages = $this->db->fetchAll("
            SELECT m.*, u.name AS sender_name, u.role AS sender_role
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ", [$userId, $otherUserId, $otherUserId, $userId]);

        $otherUser = $this->db->fetchOne("SELECT id, name, email, role, phone FROM users WHERE id = ?", [$otherUserId]);

        if (!$otherUser) {
            header('Location: ' . (BASE_URL) . '/admin/messages');
            exit;
        }

        $this->db->exec("UPDATE messages SET read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL", [$otherUserId, $userId]);

        $data = [
            'page_title' => 'Messages - ' . ($otherUser['name'] ?? 'Unknown'),
            'messages' => $messages ?: [],
            'other_user' => $otherUser,
        ];
        $this->render('admin/messages/conversation', $data);
    }

    public function sendMessage()
    {
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (!$receiverId || !$message) {
            $_SESSION['error_message'] = 'Receiver and message are required.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL) . '/admin/messages'));
            exit;
        }

        $this->db->exec(
            "INSERT INTO messages (sender_id, receiver_id, content, message_type, sender_type, sent_at) VALUES (?, ?, ?, 'text', 'admin', NOW())",
            [$userId, $receiverId, $message]
        );

        $_SESSION['success_message'] = 'Message sent successfully.';
        header('Location: ' . (BASE_URL) . '/admin/messages/conversation/' . $receiverId);
        exit;
    }

    public function compose()
    {
        $search = trim($_GET['search'] ?? '');
        [$tidSql, $tidParams] = $this->tenantWhere();

        $users = [];
        if (strlen($search) >= 2) {
            $users = $this->db->fetchAll(
                "SELECT id, name, email, role, phone FROM users
                 WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?){$tidSql}
                 ORDER BY name ASC LIMIT 20",
                array_merge(["%$search%", "%$search%", "%$search%"], $tidParams)
            );
        } else {
            $tidWhere = $tidSql ? str_replace(' AND ', ' WHERE ', $tidSql) : '';
            $users = $this->db->fetchAll(
                "SELECT id, name, email, role, phone FROM users{$tidWhere} ORDER BY name ASC LIMIT 50",
                $tidParams
            );
        }

        $data = [
            'page_title' => 'Compose Message',
            'users' => $users ?: [],
            'search' => $search,
        ];
        $this->render('admin/messages/compose', $data);
    }

    public function ajaxSearchUsers()
    {
        $search = trim($_GET['q'] ?? '');
        if (strlen($search) < 1) {
            echo json_encode([]);
            exit;
        }
        [$tidSql, $tidParams] = $this->tenantWhere();
        $users = $this->db->fetchAll(
            "SELECT id, name, email, role, phone FROM users
             WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?){$tidSql}
             ORDER BY name ASC LIMIT 10",
            array_merge(["%$search%", "%$search%", "%$search%"], $tidParams)
        );
        header('Content-Type: application/json');
        echo json_encode($users ?: []);
        exit;
    }
}
