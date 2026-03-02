<?php
namespace App\Helpers;

class Helpers {
    /**
     * Format currency values consistently across the site
     * 
     * @param float|int $amount The amount to format
     * @param string $currency The currency symbol (default: ₹)
     * @return string Formatted currency string
     */
    public static function formatCurrency($amount, $currency = '₹') {
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Format date consistently across the site
     * 
     * @param string $date The date string to format
     * @param string $format The format to use (default: 'd M, Y')
     * @return string Formatted date
     */
    public static function formatDate($date, $format = 'd M, Y') {
        return date($format, strtotime($date));
    }

    /**
     * Truncate text to a specified length and append ellipsis
     * 
     * @param string $text The text to truncate
     * @param int $length The maximum length
     * @param string $append The string to append (default: '...')
     * @return string Truncated text
     */
    public static function truncateText($text, $length = 100, $append = '...') {
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $text = substr($text, 0, strrpos($text, ' '));
            $text .= $append;
        }
        return $text;
    }

    /**
     * Sanitize input to prevent XSS and trim whitespace
     *
     * @param string $input
     * @return string
     */
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email address format
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Generate a URL-friendly slug from a string
     *
     * @param string $str The string to convert to slug
     * @param string $separator The separator to use (default: '-')
     * @return string The slugified string
     */
    public static function str_slug($str, $separator = '-') {
        // Convert to lowercase
        $str = strtolower($str);
        
        // Replace spaces and special characters with separator
        $str = preg_replace('/[^a-z0-9\s]/', $separator, $str);
        $str = preg_replace('/\s+/', $separator, $str);
        
        // Remove multiple consecutive separators
        $str = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $str);
        
        // Trim separators from beginning and end
        $str = trim($str, $separator);
        
        return $str;
    }
}