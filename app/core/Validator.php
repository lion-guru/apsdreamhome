<?php
/**
 * Validator Class
 * 
 * Provides validation functionality for forms and data
 */

namespace App\Core;

class Validator {
    
    /**
     * Validate email address
     */
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate required field
     */
    public static function required($value) {
        return !empty($value) && $value !== '';
    }
    
    /**
     * Validate minimum length
     */
    public static function min($value, $min) {
        return strlen($value) >= $min;
    }
    
    /**
     * Validate maximum length
     */
    public static function max($value, $max) {
        return strlen($value) <= $max;
    }
    
    /**
     * Validate numeric value
     */
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    /**
     * Validate integer value
     */
    public static function integer($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate URL
     */
    public static function url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate alphanumeric
     */
    public static function alphanumeric($value) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }
    
    /**
     * Validate password strength
     */
    public static function password($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
}
