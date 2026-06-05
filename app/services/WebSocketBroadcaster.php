<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * WebSocketBroadcaster — facade for publishing events to connected WebSocket clients.
 *
 * Two transports:
 *   1. In-process:  WebSocketServer::getInstance() is set when running inside
 *                   the WS process. Zero overhead.
 *   2. HTTP fallback: POST to http://localhost:8081/broadcast with the shared
 *                     WS_BROADCAST_KEY header. Used by Apache/PHP-FPM workers
 *                     where the WS server is a separate process.
 *
 * This class NEVER throws. All failures are logged and return false. Callers can
 * fire-and-forget without worrying about breaking the request lifecycle.
 *
 * Channel naming convention (snake_case):
 *   - 'all', 'admin'
 *   - 'user_{id}'              → specific user
 *   - 'role_{role}'            → e.g. 'role_admin', 'role_associate'
 *   - 'chat_{session_id}'      → chat session
 *   - 'analytics_global'       → real-time analytics dashboard
 *   - 'kanban_global'          → lead kanban board
 *   - any other free-form channel
 */
class WebSocketBroadcaster
{
    private static $lastError = null;

    /**
     * Broadcast a payload to a channel.
     *
     * @param string $channel       Channel name (snake_case)
     * @param mixed  $payload       JSON-serializable payload
     * @param int|null $targetUserId Optional: only this user receives it
     * @param string|null $targetRole Optional: only this role receives it
     * @return bool true if delivered, false on failure
     */
    public static function broadcast($channel, $payload, $targetUserId = null, $targetRole = null)
    {
        self::$lastError = null;
        if (!is_string($channel) || $channel === '') {
            self::$lastError = 'Invalid channel';
            return false;
        }

        // 1) In-process
        $instance = WebSocketServer::getInstance();
        if ($instance !== null) {
            try {
                $instance->broadcast($channel, $payload, $targetUserId, $targetRole);
                return true;
            } catch (\Throwable $e) {
                self::$lastError = $e->getMessage();
                error_log("WebSocketBroadcaster::broadcast in-process failed: " . $e->getMessage());
                return false;
            }
        }

        // 2) HTTP fallback
        return self::httpBroadcast($channel, $payload, $targetUserId, $targetRole);
    }

    /**
     * Convenience: broadcast to a specific user.
     */
    public static function broadcastToUser($userId, $payload, $channel = null)
    {
        $ch = $channel ?: ('user_' . (int)$userId);
        return self::broadcast($ch, $payload, (int)$userId, null);
    }

    /**
     * Convenience: broadcast to all admins.
     */
    public static function broadcastToAdmins($payload, $channel = 'admin')
    {
        return self::broadcast($channel, $payload, null, 'admin');
    }

    /**
     * Convenience: broadcast to a specific role.
     */
    public static function broadcastToRole($role, $payload, $channel = null)
    {
        $ch = $channel ?: ('role_' . $role);
        return self::broadcast($ch, $payload, null, $role);
    }

    /**
     * Convenience: broadcast to a chat session.
     */
    public static function broadcastToChat($sessionId, $payload)
    {
        return self::broadcast('chat_' . (int)$sessionId, $payload);
    }

    /**
     * Convenience: broadcast on the analytics channel.
     */
    public static function broadcastAnalytics($payload, $subchannel = 'analytics_global')
    {
        return self::broadcast($subchannel, $payload);
    }

    /**
     * Convenience: broadcast on the kanban channel.
     */
    public static function broadcastKanban($payload, $subchannel = 'kanban_global')
    {
        return self::broadcast($subchannel, $payload);
    }

    /**
     * HTTP transport to localhost:8081/broadcast.
     */
    private static function httpBroadcast($channel, $payload, $targetUserId, $targetRole)
    {
        if (!function_exists('curl_init')) {
            self::$lastError = 'curl not available';
            return false;
        }

        $body = json_encode([
            'channel' => $channel,
            'payload' => $payload,
            'userId' => $targetUserId,
            'role' => $targetRole
        ]);
        if ($body === false) {
            self::$lastError = 'json_encode failed';
            return false;
        }

        $key = $_ENV['WS_BROADCAST_KEY'] ?? getenv('WS_BROADCAST_KEY') ?: 'dev-broadcast-key';
        $port = (int)($_ENV['WS_HTTP_PORT'] ?? getenv('WS_HTTP_PORT') ?: 8081);
        $host = $_ENV['WS_HTTP_HOST'] ?? getenv('WS_HTTP_HOST') ?: '127.0.0.1';

        $ch = curl_init("http://{$host}:{$port}/broadcast");
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Broadcast-Key: ' . $key
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 800,
            CURLOPT_CONNECTTIMEOUT_MS => 400
        ]);
        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $code >= 400) {
            self::$lastError = $err ?: ("HTTP " . $code);
            // Graceful degrade - log but don't propagate
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("WebSocketBroadcaster HTTP fallback failed: " . self::$lastError);
            }
            return false;
        }
        return true;
    }

    public static function getLastError()
    {
        return self::$lastError;
    }
}
