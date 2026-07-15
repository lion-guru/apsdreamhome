<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class ChatController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getConversations()
    {
        // Get conversations
    }

    public function getMessages($otherUserId)
    {
        // Get messages
    }

    public function sendMessage()
    {
        // Send message
    }

    public function markMessagesRead($otherUserId)
    {
        // Mark as read
    }

    public function getUnreadCount()
    {
        // Unread count
    }
}