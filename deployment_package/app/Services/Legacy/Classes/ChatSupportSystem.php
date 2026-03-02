<?php

namespace App\Services\Legacy\Classes;

use App\Services\ChatService;

/**
 * Proxy for ChatSupportSystem to maintain backward compatibility.
 * Redirects calls to the modern ChatService.
 */
class ChatSupportSystem {
    private $chatService;

    public function __construct($database = null) {
        // We ignore the database parameter as ChatService uses the centralized Model connection
        $this->chatService = new ChatService();
    }

    /**
     * Initiate a new chat session
     */
    public function initiateChat($userId, $propertyId = null, $department = 'general', $sessionType = 'general') {
        return $this->chatService->initiateChat($userId, $propertyId, $department, $sessionType);
    }

    /**
     * Send a message in a chat session
     */
    public function sendMessage($sessionId, $senderType, $senderId, $message, $messageType = 'text', $attachment = []) {
        return $this->chatService->sendMessage($sessionId, $senderType, $senderId, $message, $messageType, $attachment);
    }

    /**
     * Get chat session details
     */
    public function getSessionDetails($sessionId, $userId = null) {
        return $this->chatService->getSessionDetails($sessionId, $userId);
    }

    /**
     * Get user's active chat sessions
     */
    public function getUserSessions($userId) {
        return $this->chatService->getUserSessions($userId);
    }

    /**
     * End a chat session
     */
    public function endSession($sessionId, $userId, $rating = null, $feedback = '') {
        return $this->chatService->endSession($sessionId, $userId, $rating, $feedback);
    }

    /**
     * Get available agents for a department
     */
    public function getAvailableAgents($department) {
        return $this->chatService->getAvailableAgents($department);
    }

    /**
     * Transfer chat to another agent
     */
    public function transferChat($sessionId, $fromAgentId, $toAgentId, $reason, $notes = '') {
        return $this->chatService->transferChat($sessionId, $fromAgentId, $toAgentId, $reason, $notes);
    }

    /**
     * Get chat analytics for a date range
     */
    public function getChatAnalytics($startDate, $endDate, $department = null) {
        return $this->chatService->getChatAnalytics($startDate, $endDate, $department);
    }

    /**
     * Handle other method calls by redirecting to ChatService if they exist
     */
    public function __call($name, $arguments) {
        if (method_exists($this->chatService, $name)) {
            return call_user_func_array([$this->chatService, $name], $arguments);
        }
        throw new \Exception("Method {$name} does not exist in ChatSupportSystem proxy.");
    }
}

/**
 * Legacy component proxies
 */
class WebSocketServer {
    public function __call($name, $arguments) {
        // Placeholder to avoid errors if these are instantiated elsewhere
        return null;
    }
}

class MessageQueue {
    public function __construct($database = null) {}
    public function __call($name, $arguments) {
        return null;
    }
}

class AgentManager {
    public function __construct($database = null) {}
    public function __call($name, $arguments) {
        return null;
    }
}
?>
