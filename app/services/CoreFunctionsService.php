<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Modern Core Functions Service (Custom MVC)
 * Provides utility functions for common operations without Laravel dependencies
 */
class CoreFunctionsService
{
    private Database $db;
    private string $logPath;
    private array $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(Database $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->logPath = __DIR__ . '/../../storage/logs/admin_actions.log';
    }

    /**
     * Log admin actions with structured data
     */
    public function logAdminAction(array $data): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $this->getCurrentUserId(),
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'data' => $data
        ];

        $this->writeLog($logEntry);
    }

    /**
     * Validate input data
     */
    public function validateInput(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "Field {$field} is required";
            }

            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Field {$field} must be a valid email";
            }

            if (strpos($rule, 'numeric') !== false && !is_numeric($value)) {
                $errors[$field] = "Field {$field} must be numeric";
            }

            if (preg_match('/min:(\d+)/', $rule, $matches) && strlen($value) < $matches[1]) {
                $errors[$field] = "Field {$field} must be at least {$matches[1]} characters";
            }

            if (preg_match('/max:(\d+)/', $rule, $matches) && strlen($value) > $matches[1]) {
                $errors[$field] = "Field {$field} must not exceed {$matches[1]} characters";
            }
        }

        return ['errors' => $errors, 'valid' => empty($errors)];
    }

    /**
     * Sanitize input string
     */
    public function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate secure hash
     */
    public function generateHash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password hash
     */
    public function verifyHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Upload image file
     */
    public function uploadImage(array $file, string $destination = 'uploads'): array
    {
        $result = ['success' => false, 'message' => '', 'path' => ''];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['message'] = 'Invalid file upload';
            return $result;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedImageTypes)) {
            $result['message'] = 'Invalid file type. Allowed: ' . implode(', ', $this->allowedImageTypes);
            return $result;
        }

        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . "/../../public/{$destination}/";

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            $result['success'] = true;
            $result['path'] = $destination . '/' . $filename;
            $result['message'] = 'File uploaded successfully';
        } else {
            $result['message'] = 'Failed to upload file';
        }

        return $result;
    }

    /**
     * Calculate age from date of birth
     */
    public function calculateAge(string $dateOfBirth): int
    {
        $dob = new \DateTime($dateOfBirth);
        $today = new \DateTime();

        return $dob->diff($today)->y;
    }

    /**
     * Extract text from file
     */
    public function extractTextFromFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
                return file_get_contents($filePath);
            case 'csv':
                return $this->extractCsvText($filePath);
            default:
                return '';
        }
    }

    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Format currency
     */
    public function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'INR' => '₹'
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . number_format($amount, 2);
    }

    /**
     * Format date
     */
    public function formatDate(string $date, string $format = 'Y-m-d'): string
    {
        $dateTime = new \DateTime($date);
        return $dateTime->format($format);
    }

    /**
     * Generate slug from string
     */
    public function generateSlug(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Truncate text
     */
    public function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(string $key, int $limit = 60, int $window = 3600): bool
    {
        $cacheKey = "rate_limit_{$key}";
        $current = $this->getCache($cacheKey);

        if ($current === null) {
            $this->setCache($cacheKey, 1, $window);
            return true;
        }

        if ($current >= $limit) {
            return false;
        }

        $this->setCache($cacheKey, $current + 1, $window);
        return true;
    }

    /**
     * Get current user ID
     */
    private function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Write to log file
     */
    private function writeLog(array $data): void
    {
        $logDir = dirname($this->logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = json_encode($data) . PHP_EOL;
        file_put_contents($this->logPath, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Extract CSV text
     */
    private function extractCsvText(string $filePath): string
    {
        $text = [];
        $handle = fopen($filePath, 'r');

        if ($handle) {
            while (($row = fgetcsv($handle)) !== false) {
                $text[] = implode(' ', $row);
            }
            fclose($handle);
        }

        return implode(' ', $text);
    }

    /**
     * Simple cache implementation
     */
    private function getCache(string $key): ?int
    {
        $cacheFile = __DIR__ . "/../../storage/cache/{$key}.cache";

        if (!file_exists($cacheFile)) {
            return null;
        }

        $data = unserialize(file_get_contents($cacheFile));

        if ($data['expires'] < time()) {
            unlink($cacheFile);
            return null;
        }

        return $data['value'];
    }

    /**
     * Set cache value
     */
    private function setCache(string $key, int $value, int $ttl): void
    {
        $cacheDir = __DIR__ . "/../../storage/cache/";

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        $cacheFile = $cacheDir . "{$key}.cache";
        file_put_contents($cacheFile, serialize($data));
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get request input
     */
    public function getInput(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Create JSON response
     */
    public function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    public function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
