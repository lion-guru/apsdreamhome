<?php
/**
 * Input Sanitization Helper
 * Provides safe methods for handling user input
 */

class InputSanitizer {
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email input
     */
    public static function sanitizeEmail($input) {
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize integer input
     */
    public static function sanitizeInt($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float input
     */
    public static function sanitizeFloat($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitize URL input
     */
    public static function sanitizeURL($input) {
        return filter_var($input, FILTER_SANITIZE_URL);
    }
    
    /**
     * Get safe POST data
     */
    public static function post($key, $default = '') {
        return self::sanitizeString($_POST[$key] ?? $default);
    }
    
    /**
     * Get safe GET data
     */
    public static function get($key, $default = '') {
        return self::sanitizeString($_GET[$key] ?? $default);
    }
    
    /**
     * Get safe request data (POST or GET)
     */
    public static function request($key, $default = '') {
        return self::sanitizeString($_REQUEST[$key] ?? $default);
    }
}