<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class NotificationController extends BaseApiController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function list()
    {
        // List notifications
    }

    public function markRead($id)
    {
        // Mark as read
    }

    public function markAllRead()
    {
        // Mark all read
    }

    public function delete($id)
    {
        // Delete notification
    }

    public function create()
    {
        // S264: user_id is session-bound. Anonymous callers get 401; non-admins
        // can only notify themselves (kills arbitrary-inbox injection).
        $sessionUser = $_SESSION['user_id'] ?? null;
        if (!$sessionUser) {
            return $this->jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        $data = $this->inputWithJson('data') ?? $this->inputWithJson('notification') ?? [];
        $requested = $this->inputWithJson('user_id') ?? $sessionUser;
        $isAdmin = method_exists($this, 'isAdmin') ? (bool)$this->isAdmin() : false;
        $userId = (!$isAdmin && (int)$requested !== (int)$sessionUser) ? $sessionUser : $requested;
        $title = $this->inputWithJson('title') ?? '';
        $message = $this->inputWithJson('message') ?? '';
        $type = $this->inputWithJson('type') ?? 'info';
        $channel = $this->inputWithJson('channel') ?? 'global';

        if ($userId && $message !== '') {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $db->execute(
                    "INSERT INTO notifications (user_id, title, message, type, status, is_read, created_at)
                     VALUES (?, ?, ?, ?, 'unread', 0, NOW())",
                    [$userId, $title, $message, $type]
                );
                $id = $db->lastInsertId();
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Notification created',
                    'data' => ['id' => $id]
                ], 201);
            } catch (\Throwable $e) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create notification: ' . $e->getMessage()
                ], 500);
            }
        }

        return $this->jsonResponse([
            'success' => false,
            'message' => 'user_id and message are required'
        ], 422);
    }
}