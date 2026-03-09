<?php

namespace App\Services\Core;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * Core Helper Service - APS Dream Home
 * Core framework helper functions and utilities
 * Custom MVC implementation without Laravel dependencies
 */
class CoreHelperService
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new LoggingService();
    }

    /**
     * Generate secure random string
     */
    public function generateRandomString($length = 32)
    {
        try {
            return bin2hex(random_bytes($length / 2));
        } catch (Exception $e) {
            $this->logger->error("Error generating random string: " . $e->getMessage());
            return substr(md5(uniqid(mt_rand(), true)), 0, $length);
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCSRFToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->generateRandomString(32);
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
        } catch (Exception $e) {
            $this->logger->error("Error sanitizing input: " . $e->getMessage());
            return '';
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
        // Remove all non-digit characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Indian mobile number (10 digits)
        return strlen($cleanPhone) === 10 && preg_match('/^[6-9]/', $cleanPhone);
    }

    /**
     * Format currency
     */
    public function formatCurrency($amount, $currency = 'INR')
    {
        try {
            switch ($currency) {
                case 'INR':
                    return '₹' . number_format($amount, 2);
                case 'USD':
                    return '$' . number_format($amount, 2);
                case 'EUR':
                    return '€' . number_format($amount, 2);
                default:
                    return number_format($amount, 2);
            }
        } catch (Exception $e) {
            $this->logger->error("Error formatting currency: " . $e->getMessage());
            return $amount;
        }
    }

    /**
     * Format date
     */
    public function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        try {
            $timestamp = is_numeric($date) ? $date : strtotime($date);
            return date($format, $timestamp);
        } catch (Exception $e) {
            $this->logger->error("Error formatting date: " . $e->getMessage());
            return $date;
        }
    }

    /**
     * Calculate age from date of birth
     */
    public function calculateAge($dateOfBirth)
    {
        try {
            $dob = new DateTime($dateOfBirth);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            return $age;
        } catch (Exception $e) {
            $this->logger->error("Error calculating age: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate slug from string
     */
    public function generateSlug($string)
    {
        try {
            // Convert to lowercase and replace spaces with hyphens
            $slug = strtolower($string);
            
            // Replace special characters with hyphens
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            
            // Remove multiple hyphens
            $slug = preg_replace('/-+/', '-', $slug);
            
            // Remove leading and trailing hyphens
            return trim($slug, '-');
        } catch (Exception $e) {
            $this->logger->error("Error generating slug: " . $e->getMessage());
            return '';
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
            
            return substr($text, 0, $length) . $suffix;
        } catch (Exception $e) {
            $this->logger->error("Error truncating text: " . $e->getMessage());
            return $text;
        }
    }

    /**
     * Create pagination
     */
    public function createPagination($totalItems, $itemsPerPage, $currentPage = 1)
    {
        try {
            $totalPages = ceil($totalItems / $itemsPerPage);
            $offset = ($currentPage - 1) * $itemsPerPage;
            
            return [
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'offset' => $offset,
                'has_next' => $currentPage < $totalPages,
                'has_previous' => $currentPage > 1,
                'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null,
                'previous_page' => $currentPage > 1 ? $currentPage - 1 : null
            ];
        } catch (Exception $e) {
            $this->logger->error("Error creating pagination: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client IP address
     */
    public function getClientIP()
    {
        try {
            $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
            
            foreach ($ipKeys as $key) {
                if (array_key_exists($key, $_SERVER) === true) {
                    foreach (explode(',', $_SERVER[$key]) as $ip) {
                        $ip = trim($ip);
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            return $ip;
                        }
                    }
                }
            }
            
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        } catch (Exception $e) {
            $this->logger->error("Error getting client IP: " . $e->getMessage());
            return '0.0.0.0';
        }
    }

    /**
     * Log user activity
     */
    public function logActivity($userId, $action, $details = [])
    {
        try {
            $sql = "INSERT INTO user_activity 
                    (user_id, action, details, ip_address, user_agent, created_at) 
                    VALUES (:user_id, :action, :details, :ip_address, :user_agent, NOW())";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':details', json_encode($details));
            $stmt->bindParam(':ip_address', $this->getClientIP());
            $stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("Activity logged: {$action} for user {$userId}");
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email (using PHP mail function)
     */
    public function sendEmail($to, $subject, $message, $from = null)
    {
        try {
            $from = $from ?? 'noreply@apsdreamhome.com';
            $headers = [
                'From: ' . $from,
                'Reply-To: ' . $from,
                'Content-Type: text/html; charset=UTF-8',
                'MIME-Version: 1.0'
            ];
            
            $headers = implode("\r\n", $headers);
            
            if (mail($to, $subject, $message, $headers)) {
                $this->logger->info("Email sent successfully to: {$to}");
                return true;
            } else {
                $this->logger->error("Failed to send email to: {$to}");
                return false;
            }
        } catch (Exception $e) {
            $this->logger->error("Error sending email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create backup of data
     */
    public function createBackup($tables = [])
    {
        try {
            if (empty($tables)) {
                // Get all tables
                $sql = "SHOW TABLES";
                $stmt = $this->database->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $tables = array_column($result, 0);
            }

            $backup = [];
            $backup['created_at'] = date('Y-m-d H:i:s');
            $backup['tables'] = [];

            foreach ($tables as $table) {
                $sql = "SELECT * FROM {$table}";
                $stmt = $this->database->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll();
                
                $backup['tables'][$table] = $data;
            }

            $backupFile = STORAGE_PATH . '/backups/backup_' . date('Y-m-d_H-i-s') . '.json';
            $this->ensureDirectoryExists(STORAGE_PATH . '/backups');
            
            if (file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT))) {
                $this->logger->info("Backup created successfully: {$backupFile}");
                return $backupFile;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error("Error creating backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Get system information
     */
    public function getSystemInfo()
    {
        try {
            return [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'database_connection' => $this->database ? 'Connected' : 'Not Connected',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->logger->error("Error getting system info: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs($daysOld = 30)
    {
        try {
            $logDir = STORAGE_PATH . '/logs/';
            $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
            
            if (is_dir($logDir)) {
                $files = scandir($logDir);
                $deletedCount = 0;
                
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filePath = $logDir . $file;
                        if (filemtime($filePath) < $cutoffTime) {
                            if (unlink($filePath)) {
                                $deletedCount++;
                            }
                        }
                    }
                }
                
                $this->logger->info("Cleaned up {$deletedCount} old log files");
                return $deletedCount;
            }
            
            return 0;
        } catch (Exception $e) {
            $this->logger->error("Error cleaning old logs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate UUID
     */
    public function generateUUID()
    {
        try {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        } catch (Exception $e) {
            $this->logger->error("Error generating UUID: " . $e->getMessage());
            return uniqid();
        }
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get file extension
     */
    public function getFileExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Convert bytes to human readable format
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Check if string contains substring
     */
    public function contains($haystack, $needle, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return strpos($haystack, $needle) !== false;
        } else {
            return stripos($haystack, $needle) !== false;
        }
    }

    /**
     * Get array value by key with default
     */
    public function arrayGet($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Check if array is associative
     */
    public function isAssociativeArray($array)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }
        
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
