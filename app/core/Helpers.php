<?php

// TODO: Add proper error handling with try-catch blocks


/**
 * APS Dream Home - Helpers Class
 * Provides helper functions for views and controllers
 */

class Helpers
{
    /**
     * Format currency amount
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($amount, $currency = '₹')
    {
        return $currency . number_format($amount, 2);
    }
    
    /**
     * Format date
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'M d, Y')
    {
        if (empty($date)) {
            return 'N/A';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }
    
    /**
     * Truncate text
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Generate slug from text
     * @param string $text
     * @return string
     */
    public static function slugify($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * Sanitize output
     * @param string $output
     * @return string
     */
    public static function sanitize($output)
    {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate random string
     * @param int $length
     * @return string
     */
    public static function randomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Calculate time ago
     * @param string $datetime
     * @return string
     */
    public static function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            return round($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return round($diff / 3600) . ' hours ago';
        } elseif ($diff < 2592000) {
            return round($diff / 86400) . ' days ago';
        } else {
            return date('M d, Y', $time);
        }
    }
    
    /**
     * Get file size in human readable format
     * @param int $bytes
     * @return string
     */
    public static function fileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Validate email
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generate pagination links
     * @param int $current
     * @param int $total
     * @param int $per_page
     * @param string $url
     * @return array
     */
    public static function pagination($current, $total, $per_page = 10, $url = '?')
    {
        $total_pages = ceil($total / $per_page);
        $links = [];
        
        // Previous
        if ($current > 1) {
            $links[] = [
                'url' => $url . 'page=' . ($current - 1),
                'label' => 'Previous',
                'active' => false
            ];
        }
        
        // Page numbers
        for ($i = max(1, $current - 2); $i <= min($total_pages, $current + 2); $i++) {
            $links[] = [
                'url' => $url . 'page=' . $i,
                'label' => $i,
                'active' => $i == $current
            ];
        }
        
        // Next
        if ($current < $total_pages) {
            $links[] = [
                'url' => $url . 'page=' . ($current + 1),
                'label' => 'Next',
                'active' => false
            ];
        }
        
        return $links;
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Helpers\Helpers.php

function truncateText($text, $length = 100, $append = '...') {
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $text = substr($text, 0, strrpos($text, ' '));
            $text .= $append;
        }
function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
function str_slug($str, $separator = '-') {
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