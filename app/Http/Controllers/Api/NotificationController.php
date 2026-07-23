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
        $data = $this->inputWithJson('data') ?? $this->inputWithJson('notification') ?? [];
        $userId = $this->inputWithJson('user_id') ?? ($_SESSION['user_id'] ?? null);
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