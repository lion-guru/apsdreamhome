<?php

// TODO: Add proper error handling with try-catch blocks

amespace App\Core\Session;

/**
 * Simple Session Management Class
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Check if session has key
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Remove session value
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session
     */
    public function destroy()
    {
        session_destroy();
    }
    
    /**
     * Get all session data
     */
    public function all()
    {
        return $_SESSION;
    }
    
    /**
     * Flash message
     */
    public function flash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get flash message
     */
    public function getFlash($key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
