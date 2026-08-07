<?php

namespace App\Services;

use App\Traits\ServiceTenantTrait;

class LiveChatService
{
    use ServiceTenantTrait;

    private ?\PDO $pdo = null;

    public function __construct($db = null)
    {
        if (is_object($db) && method_exists($db, 'getPdo')) {
            $this->pdo = $db->getPdo();
        } else {
            $this->pdo = $db;
        }
    }

    protected function _tParams(): array
    {
        return $this->tenantId() > 1 ? [$this->tenantId()] : [];
    }

    public function startSession($visitorId, $userId, $visitorName, $visitorEmail, $pageUrl = '', $referrerUrl = '', $ip = '', $userAgent = '', $source = 'website')
    {
        $token = bin2hex(random_bytes(16));
        $country = '';
        try {
            $insertData = [
                'session_token' => $token,
                'visitor_id' => $visitorId,
                'user_id' => $userId,
                'visitor_name' => $visitorName,
                'visitor_email' => $visitorEmail,
                'page_url' => $pageUrl,
                'referrer_url' => $referrerUrl,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'source' => $source,
                'status' => 'open',
            ];
            $insertData = $this->tenantInsertData($insertData);
            $cols = implode(', ', array_keys($insertData));
            $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
            $stmt = $this->pdo->prepare("INSERT INTO chat_sessions ($cols) VALUES ($placeholders)");
            $stmt->execute(array_values($insertData));
            return ['id' => $this->pdo->lastInsertId(), 'token' => $token];
        } catch (\Throwable $e) {
            error_log("LiveChat startSession: " . $e->getMessage());
            return null;
        }
    }

    public function getSession($sessionId)
    {
        try {
            $sql = "SELECT s.*, u.name as user_name, u.email as user_email FROM chat_sessions s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?" . $this->tenantSql();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_merge([$sessionId], $this->_tParams()));
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return null; }
    }

    public function getSessionByToken($token)
    {
        try {
            $sql = "SELECT s.*, u.name as user_name, u.email as user_email FROM chat_sessions s LEFT JOIN users u ON s.user_id = u.id WHERE s.session_token = ?" . $this->tenantSql();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_merge([$token], $this->_tParams()));
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return null; }
    }

    public function getSessions($status = null, $limit = 50, $offset = 0, $agentId = null)
    {
        try {
            $sql = "SELECT s.*, u.name as user_name FROM chat_sessions s LEFT JOIN users u ON s.user_id = u.id WHERE 1=1" . $this->tenantSql();
            $params = $this->_tParams();
            if ($status) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }
            if ($agentId) {
                $sql .= " AND s.assigned_agent_id = ?";
                $params[] = $agentId;
            }
            $sql .= " ORDER BY s.last_message_at DESC, s.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function sendMessage($sessionId, $senderType, $senderId, $senderName, $message, $messageType = 'text', $attachment = null, $isInternal = false)
    {
        try {
            $readVisitor = $senderType === 'visitor' ? 1 : 0;
            $readAgent = in_array($senderType, ['agent', 'system', 'bot']) ? 1 : 0;
            $insertData = [
                'session_id' => $sessionId,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'sender_name' => $senderName,
                'message' => $message,
                'message_type' => $messageType,
                'attachment_url' => $attachment['url'] ?? null,
                'is_internal_note' => $isInternal ? 1 : 0,
                'read_by_visitor' => $readVisitor,
                'read_by_agent' => $readAgent,
            ];
            $insertData = $this->tenantInsertData($insertData);
            $cols = implode(', ', array_keys($insertData));
            $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
            $stmt = $this->pdo->prepare("INSERT INTO chat_messages ($cols) VALUES ($placeholders)");
            $stmt->execute(array_values($insertData));
            $msgId = $this->pdo->lastInsertId();

            $unreadVisitor = $senderType === 'agent' ? 1 : 0;
            $unreadAdmin = $senderType === 'visitor' ? 1 : 0;
            $this->pdo->prepare("UPDATE chat_sessions SET message_count = message_count + 1, unread_visitor_count = unread_visitor_count + ?, unread_admin_count = unread_admin_count + ?, last_message_at = NOW(), last_message_by = ? WHERE id = ?" . $this->tenantSql())
                ->execute(array_merge([$unreadVisitor, $unreadAdmin, $senderType, $sessionId], $this->_tParams()));

            if ($senderType === 'agent' && $unreadVisitor >= 0) {
                $this->pdo->prepare("UPDATE chat_sessions SET first_response_at = COALESCE(first_response_at, NOW()) WHERE id = ?" . $this->tenantSql())
                    ->execute(array_merge([$sessionId], $this->_tParams()));
            }

            // WebSocket broadcast - real-time delivery to subscribers of chat_{sessionId}
            // Failures are logged inside the broadcaster and never bubble up.
            if (!$isInternal && $msgId) {
                try {
                    \App\Services\WebSocketBroadcaster::broadcastToChat((int)$sessionId, [
                        'event' => 'message',
                        'message_id' => (int)$msgId,
                        'session_id' => (int)$sessionId,
                        'sender_type' => $senderType,
                        'sender_id' => (int)$senderId,
                        'sender_name' => $senderName,
                        'message' => $message,
                        'message_type' => $messageType,
                        'attachment' => $attachment,
                        'is_internal' => (bool)$isInternal,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                } catch (\Throwable $e) {
                    error_log("LiveChat broadcast: " . $e->getMessage());
                }
            }

            return $msgId;
        } catch (\Throwable $e) {
            error_log("LiveChat sendMessage: " . $e->getMessage());
            return null;
        }
    }

    public function getMessages($sessionId, $limit = 100, $offset = 0, $includeInternal = false)
    {
        try {
            $sql = "SELECT * FROM chat_messages WHERE session_id = ?" . $this->tenantSql() . " AND deleted_at IS NULL";
            $params = array_merge([$sessionId], $this->_tParams());
            if (!$includeInternal) {
                $sql .= " AND is_internal_note = 0";
            }
            $sql .= " ORDER BY created_at ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function markRead($sessionId, $readerType)
    {
        try {
            if ($readerType === 'visitor') {
                $sql = "UPDATE chat_messages SET read_by_visitor = 1, read_at = NOW() WHERE session_id = ?" . $this->tenantSql();
                $this->pdo->prepare($sql)->execute(array_merge([$sessionId], $this->_tParams()));
                $this->pdo->prepare("UPDATE chat_sessions SET unread_visitor_count = 0 WHERE id = ?" . $this->tenantSql())->execute(array_merge([$sessionId], $this->_tParams()));
            } else {
                $sql = "UPDATE chat_messages SET read_by_agent = 1, read_at = NOW() WHERE session_id = ?" . $this->tenantSql();
                $this->pdo->prepare($sql)->execute(array_merge([$sessionId], $this->_tParams()));
                $this->pdo->prepare("UPDATE chat_sessions SET unread_admin_count = 0 WHERE id = ?" . $this->tenantSql())->execute(array_merge([$sessionId], $this->_tParams()));
            }
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function assignAgent($sessionId, $agentId, $agentName)
    {
        try {
            $sql = "UPDATE chat_sessions SET assigned_agent_id = ?, agent_name = ?, status = 'assigned', updated_at = NOW() WHERE id = ?" . $this->tenantSql();
            $this->pdo->prepare($sql)->execute(array_merge([$agentId, $agentName, $sessionId], $this->_tParams()));
            $this->sendMessage($sessionId, 'system', null, 'System', "Agent $agentName joined the chat", 'system', null, false);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function closeSession($sessionId, $closedBy, $reason = null, $rating = null, $feedback = null)
    {
        try {
            $sql = "UPDATE chat_sessions SET status = 'closed', closed_at = NOW(), closed_by = ?, close_reason = ?, rating = ?, feedback_text = ? WHERE id = ?" . $this->tenantSql();
            $this->pdo->prepare($sql)->execute(array_merge([$closedBy, $reason, $rating, $feedback, $sessionId], $this->_tParams()));
            $this->sendMessage($sessionId, 'system', null, 'System', 'Chat session closed', 'system', null, false);
            return true;
        } catch (\Throwable $e) { return false; }
    }

    public function getQuickReplies($category = null)
    {
        try {
            $sql = "SELECT * FROM chat_quick_replies WHERE is_active = 1" . $this->tenantSql();
            $params = $this->_tParams();
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            $sql .= " ORDER BY sort_order ASC, title ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    public function getWidgetSetting($key, $default = null)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM chat_widget_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? $row['setting_value'] : $default;
        } catch (\Throwable $e) { return $default; }
    }

    public function setWidgetSetting($key, $value, $updatedBy = null)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO chat_widget_settings (setting_key, setting_value, updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()");
            $stmt->execute([$key, $value, $updatedBy]);
            return true;
        } catch (\Throwable $e) { return false; }
    }

public function getStats()
    {
        $stats = [
            'total_sessions' => 0,
            'open_sessions' => 0,
            'active_sessions' => 0,
            'closed_today' => 0,
            'avg_response_seconds' => 0,
            'unread_admin' => 0,
            'avg_rating' => 0,
            'satisfaction_pct' => 0
        ];
        try {
            $tid = $this->tenantId();
            if ($tid > 1) {
                $tenantCond = " WHERE tenant_id = $tid";
            } else {
                $tenantCond = "";
            }
            $stats['total_sessions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions$tenantCond")->fetchColumn();
            $stats['open_sessions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status IN ('open','assigned')" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetchColumn();
            $stats['active_sessions'] = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetchColumn();
            $stats['closed_today'] = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions WHERE status = 'closed' AND DATE(closed_at) = CURDATE()" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetchColumn();
            $stats['unread_admin'] = (int)$this->pdo->query("SELECT COALESCE(SUM(unread_admin_count), 0) FROM chat_sessions WHERE status NOT IN ('closed')" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetchColumn();
            $rated = $this->pdo->query("SELECT COUNT(*) as total, AVG(rating) as avg_rating, SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as happy FROM chat_sessions WHERE rating IS NOT NULL" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetch(\PDO::FETCH_ASSOC);
            $stats['avg_rating'] = $rated && $rated['avg_rating'] ? round($rated['avg_rating'], 2) : 0;
            $stats['satisfaction_pct'] = $rated && $rated['total'] > 0 ? round(($rated['happy'] / $rated['total']) * 100, 1) : 0;
            $fr = $this->pdo->query("SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, first_response_at)) as avg FROM chat_sessions WHERE first_response_at IS NOT NULL" . ($tid > 1 ? " AND tenant_id = $tid" : ""))->fetch(\PDO::FETCH_ASSOC);
            $stats['avg_response_seconds'] = $fr && $fr['avg'] ? (int)$fr['avg'] : 0;
        } catch (\Throwable $e) { error_log("LiveChat getStats: " . $e->getMessage()); }
        return $stats;
    }

    public function getUnreadCount($type = 'admin')
    {
        try {
            $col = $type === 'admin' ? 'unread_admin_count' : 'unread_visitor_count';
            $sql = "SELECT COALESCE(SUM($col), 0) FROM chat_sessions WHERE status NOT IN ('closed')" . $this->tenantSql();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->_tParams());
            return (int)$stmt->fetchColumn();
        } catch (\Throwable $e) { return 0; }
    }

    public function getRecentMessages($limit = 10)
    {
        try {
            $sql = "SELECT m.*, s.visitor_name, s.visitor_email, s.status as session_status FROM chat_messages m JOIN chat_sessions s ON m.session_id = s.id WHERE m.is_internal_note = 0" . $this->tenantSql() . " ORDER BY m.created_at DESC LIMIT " . (int)$limit;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->_tParams());
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }
}
