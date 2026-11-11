<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasFlashMessages
 *
 * Provides flash message and session management for controllers.
 * Include this trait in controllers that need flash messages.
 */
trait HasFlashMessages
{
    /**
     * Set flash message (persists for one request).
     */
    protected function setFlash(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get and remove flash message.
     */
    protected function getFlash(string $key, $default = null)
    {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return $default;
    }

    /**
     * Check if flash message exists (non-destructive; getFlash consumes).
     */
    protected function hasFlash(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Get session value.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value.
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if user is logged in (any portal).
     */
    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id'])
            || isset($_SESSION['admin_id'])
            || isset($_SESSION['associate_id'])
            || isset($_SESSION['agent_id'])
            || isset($_SESSION['employee_id']);
    }

    /**
     * Check if current user is admin.
     */
    protected function isAdmin(): bool
    {
        if (!isset($_SESSION['admin_id'])) return false;
        $role = \App\Http\Middleware\RBACManager::getUserRole() ?? '';
        return in_array($role, ['admin', 'super_admin', 'manager', 'employee', 'telecaller']);
    }

    /**
     * Check if current user is associate.
     */
    protected function isAssociate(): bool
    {
        return isset($_SESSION['associate_id']);
    }

    /**
     * Require user to be logged in.
     */
    protected function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            if (!headers_sent()) {
                header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/login");
            }
            exit;
        }
    }

    /**
     * Require admin access.
     */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $_SESSION['error_message'] = 'Admin access required';
            $this->redirect('/admin/login');
        }
    }

    /**
     * Require associate access.
     */
    protected function requireAssociate(): void
    {
        if (!$this->isAssociate()) {
            $_SESSION['error_message'] = 'Associate access required';
            $this->redirect('/associate/login');
        }
    }
}