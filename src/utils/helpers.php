<?php

namespace App\Utils;

class Helpers {
    /**
     * Sanitize user input
     * @param string $input
     * @return string
     */
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a random string
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Format date to readable format
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($date));
    }

    /**
     * Format currency
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'INR') {
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Validate email address
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Create URL friendly slug
     * @param string $string
     * @return string
     */
    public static function createSlug($string) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
        return trim($slug, '-');
    }

    /**
     * Format file size
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if string contains specific words
     * @param string $string
     * @param array $words
     * @return bool
     */
    public static function containsWords($string, array $words) {
        foreach ($words as $word) {
            if (stripos($string, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}