<?php

namespace App\Services\Utilities;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;

/**
 * Utility Functions Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class UtilityService
{
    private $database;
    private $logger;
    private $config;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();
    }
    
    /**
     * Generate slug from string
     */
    public function generateSlug($string)
    {
        try {
            // Convert to lowercase and remove special characters
            $slug = strtolower($string);
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/[\s-]+/', '-', $slug);
            $slug = trim($slug, '-');
            
            return $slug ?: 'untitled';
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate slug', [
                'error' => $e->getMessage(),
                'string' => $string
            ]);
            
            return 'untitled';
        }
    }
    
    /**
     * Format currency
     */
    public function formatCurrency($amount, $currency = 'INR')
    {
        try {
            $amount = floatval($amount);
            
            switch ($currency) {
                case 'USD':
                    return '$' . number_format($amount, 2);
                case 'EUR':
                    return '€' . number_format($amount, 2);
                case 'GBP':
                    return '£' . number_format($amount, 2);
                case 'INR':
                default:
                    return '₹' . number_format($amount, 2);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to format currency', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency
            ]);
            
            return $currency . ' ' . $amount;
        }
    }
    
    /**
     * Format date
     */
    public function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        try {
            if (empty($date)) {
                return '';
            }
            
            $timestamp = is_numeric($date) ? $date : strtotime($date);
            
            if ($timestamp === false) {
                return $date;
            }
            
            return date($format, $timestamp);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to format date', [
                'error' => $e->getMessage(),
                'date' => $date,
                'format' => $format
            ]);
            
            return $date;
        }
    }
    
    /**
     * Calculate time ago
     */
    public function timeAgo($datetime)
    {
        try {
            if (empty($datetime)) {
                return '';
            }
            
            $time = strtotime($datetime);
            $now = time();
            $diff = $now - $time;
            
            if ($diff < 60) {
                return 'Just now';
            } elseif ($diff < 3600) {
                $minutes = floor($diff / 60);
                return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 86400) {
                $hours = floor($diff / 3600);
                return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 2592000) {
                $days = floor($diff / 86400);
                return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
            } elseif ($diff < 31536000) {
                $months = floor($diff / 2592000);
                return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
            } else {
                $years = floor($diff / 31536000);
                return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to calculate time ago', [
                'error' => $e->getMessage(),
                'datetime' => $datetime
            ]);
            
            return $datetime;
        }
    }
    
    /**
     * Truncate text
     */
    public function truncateText($text, $length = 100, $suffix = '...')
    {
        try {
            if (strlen($text) <= $length) {
                return $text;
            }
            
            $truncated = substr($text, 0, $length);
            $lastSpace = strrpos($truncated, ' ');
            
            if ($lastSpace !== false) {
                $truncated = substr($truncated, 0, $lastSpace);
            }
            
            return $truncated . $suffix;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to truncate text', [
                'error' => $e->getMessage(),
                'length' => $length
            ]);
            
            return substr($text, 0, $length) . $suffix;
        }
    }
    
    /**
     * Generate random string
     */
    public function generateRandomString($length = 32, $type = 'alnum')
    {
        try {
            $characters = '';
            
            switch ($type) {
                case 'alpha':
                    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                case 'numeric':
                    $characters = '0123456789';
                    break;
                case 'alnum':
                default:
                    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    break;
            }
            
            $randomString = '';
            $charactersLength = strlen($characters);
            
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            
            return $randomString;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate random string', [
                'error' => $e->getMessage(),
                'length' => $length,
                'type' => $type
            ]);
            
            return substr(md5(time()), 0, $length);
        }
    }
    
    /**
     * Validate email
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number
     */
    public function validatePhone($phone)
    {
        // Remove all non-numeric characters
        $numeric = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid phone number (10-15 digits)
        return strlen($numeric) >= 10 && strlen($numeric) <= 15;
    }
    
    /**
     * Sanitize input
     */
    public function sanitizeInput($input, $type = 'string')
    {
        try {
            switch ($type) {
                case 'email':
                    return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                case 'url':
                    return filter_var(trim($input), FILTER_SANITIZE_URL);
                case 'int':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                case 'float':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                case 'string':
                default:
                    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to sanitize input', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
            
            return $input;
        }
    }
    
    /**
     * Get client IP address
     */
    public function getClientIp()
    {
        try {
            $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
            
            foreach ($ipKeys as $key) {
                if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                    $ip = $_SERVER[$key];
                    
                    // Validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
            
            return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get client IP', [
                'error' => $e->getMessage()
            ]);
            
            return '127.0.0.1';
        }
    }
    
    /**
     * Get user agent
     */
    public function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    /**
     * Check if request is AJAX
     */
    public function isAjaxRequest()
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }
    
    /**
     * Convert bytes to human readable format
     */
    public function bytesToHuman($bytes, $precision = 2)
    {
        try {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            
            return round($bytes, $precision) . ' ' . $units[$i];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to convert bytes to human', [
                'error' => $e->getMessage(),
                'bytes' => $bytes
            ]);
            
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Create pagination array
     */
    public function createPagination($totalItems, $itemsPerPage, $currentPage = 1)
    {
        try {
            $totalPages = ceil($totalItems / $itemsPerPage);
            $currentPage = max(1, min($currentPage, $totalPages));
            $offset = ($currentPage - 1) * $itemsPerPage;
            
            return [
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage,
                'total_pages' => $totalPages,
                'current_page' => $currentPage,
                'offset' => $offset,
                'has_previous' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
                'previous_page' => $currentPage - 1,
                'next_page' => $currentPage + 1
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to create pagination', [
                'error' => $e->getMessage(),
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage
            ]);
            
            return [
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage,
                'total_pages' => 1,
                'current_page' => 1,
                'offset' => 0,
                'has_previous' => false,
                'has_next' => false,
                'previous_page' => 1,
                'next_page' => 1
            ];
        }
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken()
    {
        try {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            
            return $_SESSION['csrf_token'];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate CSRF token', [
                'error' => $e->getMessage()
            ]);
            
            return md5(time());
        }
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($token)
    {
        try {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to validate CSRF token', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Create response array
     */
    public function createResponse($success, $message = '', $data = [], $errors = [])
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Send JSON response
     */
    public function sendJsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    public function redirect($url, $statusCode = 302)
    {
        if (!headers_sent()) {
            header("Location: $url", true, $statusCode);
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
    
    /**
     * Get base URL
     */
    public function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Debug helper
     */
    public function debug($data, $label = 'DEBUG')
    {
        if ($this->config->get('app.debug', false)) {
            echo "<pre>$label:\n";
            print_r($data);
            echo "</pre>";
        }
    }
}