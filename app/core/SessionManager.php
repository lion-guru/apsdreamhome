<?php
/**
 * Session Manager
 * Handles secure session management
 */

namespace App\Core;

class SessionManager {
    public function __construct() {
        $this->initialize();
    }

    /**
     * Initialize session with security settings
     */
    public function initialize() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 7200, // 2 hours
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }

        // Set security headers
        if (!headers_sent()) {
            header('X-Session-Secure: true');
        }
    }

    /**
     * Set session value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public function remove($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public function destroy() {
        session_destroy();
    }

    /**
     * Regenerate session ID
     */
    public function regenerate() {
        session_regenerate_id(true);
    }
}

?>
