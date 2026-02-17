<?php

if (!function_exists('h')) {
    /**
     * Escape HTML entities
     *
     * @param string $string
     * @return string
     */
    function h($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     *
     * @param string $path
     * @return string
     */
    function url($path = '') {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('getCsrfField')) {
    /**
     * Generate CSRF field
     * 
     * @return string
     */
    function getCsrfField() {
        $token = $_SESSION['csrf_token'] ?? '';
        return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
    }
}
