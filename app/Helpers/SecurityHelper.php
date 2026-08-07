<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Advanced input sanitization and validation
     */
    public static function cleanInput($data, $type = 'string', $options = [])
    {
        $data = trim($data);
        $data = htmlspecialchars_decode($data, ENT_QUOTES);

        switch ($type) {
            case 'email':
                $data = filter_var($data, FILTER_SANITIZE_EMAIL);
                if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                break;

            case 'int':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                $min = $options['min'] ?? PHP_INT_MIN;
                $max = $options['max'] ?? PHP_INT_MAX;
                if (!filter_var($data, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => $min, 'max_range' => $max]
                ])) {
                    return false;
                }
                break;

            case 'float':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;

            case 'url':
                $data = filter_var($data, FILTER_SANITIZE_URL);
                if (!filter_var($data, FILTER_VALIDATE_URL)) {
                    return false;
                }
                break;

            default:
                $data = strip_tags($data);
                $data = h($data);
        }

        return $data;
    }

    /**
     * Generate a secure unsubscribe token
     */
    public static function generateUnsubscribeToken($email)
    {
        $salt = getenv('APP_KEY') ?: 'aps_dream_home_default_salt';
        return hash_hmac('sha256', $email, $salt);
    }

    /**
     * Generate a secure random string
     */
    public static function generateRandomString($length = 16, $useSymbols = true)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        if ($useSymbols) {
            $chars .= '!@#$%^&*()_+=-[]{}|;:,.<>?';
        }

        $str = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[self::secureRandomInt(0, $max)];
        }
        return $str;
    }

    /**
     * Set essential security headers
     */
    public static function setSecurityHeaders()
    {
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: strict-origin-when-cross-origin");

        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com https://unpkg.com https://www.googletagmanager.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com; ";
        $csp .= "img-src 'self' data: blob: https:; ";
        $csp .= "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; ";
        $csp .= "frame-src 'self' https://www.google.com; ";
        $csp .= "connect-src 'self' https: ws: wss:;";
        $csp .= "report-uri " . (defined('BASE_URL') ? BASE_URL : '') . "/csp-report";
        $csp .= "report-to csp-endpoint";

        header("Content-Security-Policy: " . $csp);
        header("Reporting-Endpoints: csp-endpoint=\"" . (defined('BASE_URL') ? BASE_URL : '') . "/csp-report\"");

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(self::secureRandomBytes(32));
            $_SESSION['csrf_token_expires'] = time() + 3600; // 1 hour
        }

        // BACKWARD COMPATIBILITY: Set unified schema if needed
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = [
                'token' => $_SESSION['csrf_token'],
                'expires' => $_SESSION['csrf_token_expires'] ?? time() + 3600
            ];
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        if (isset($_SESSION['csrf_token_expires']) && $_SESSION['csrf_token_expires'] < time()) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF field
     */
    public static function csrfField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Secure random bytes
     */
    public static function secureRandomBytes($length = 32)
    {
        try {
            return \random_bytes($length);
        } catch (\Exception $e) {
            // We don't fallback to openssl_random_pseudo_bytes as random_bytes is preferred in PHP 7+
            // and the polyfill should handle older versions securely.
            throw new \Exception('Cryptographically secure random bytes generator failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a random key from an array using a secure random number generator.
     *
     * @param array $array
     * @return int|string|null
     */
    public static function secureArrayRand(array $array)
    {
        if (empty($array)) {
            return null;
        }

        $keys = array_keys($array);
        $count = count($keys);

        if ($count === 0) {
            return null;
        }

        $randomIndex = self::secureRandomInt(0, $count - 1);
        return $keys[$randomIndex];
    }

    /**
     * Shuffle an array using a secure random number generator.
     *
     * @param array $array
     * @return bool
     */
    public static function secureShuffle(array &$array)
    {
        $count = count($array);
        if ($count < 2) {
            return true;
        }

        for ($i = $count - 1; $i > 0; $i--) {
            $j = self::secureRandomInt(0, $i);
            $temp = $array[$i];
            $array[$i] = $array[$j];
            $array[$j] = $temp;
        }

        return true;
    }

    /**
     * Detect potential SQL injection patterns in input
     * Returns true if suspicious patterns found
     */
    public static function detectSqlInjection($input)
    {
        if (empty($input)) return false;
        $input = strtolower(trim($input));

        $patterns = [
            '/\bunion\b.*\bselect\b/',
            '/\bselect\b.*\bfrom\b/',
            '/\binsert\b.*\binto\b/',
            '/\bdelete\b.*\bfrom\b/',
            '/\bdrop\b.*\btable\b/',
            '/\bupdate\b.*\bset\b/',
            '/\bor\b\s+\d+\s*=\s*\d+/',
            '/\band\b\s+\d+\s*=\s*\d+/',
            '/;\s*(drop|delete|update|insert|select)\b/',
            "/'\s*or\s+'/",
            '/--\s*$/',
            '/\/\*.*\*\//',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect potential XSS patterns in input
     * Returns true if suspicious patterns found
     */
    public static function detectXss($input)
    {
        if (empty($input)) return false;

        $patterns = [
            '/<script[\s>]/i',
            '/javascript\s*:/i',
            '/on(error|load|click|mouse)\s*=/i',
            '/<iframe[\s>]/i',
            '/<object[\s>]/i',
            '/<embed[\s>]/i',
            '/<form[\s>]/i',
            '/expression\s*\(/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if IP is blocked in the database
     */
    public static function isIpBlocked($ip)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT 1 FROM blocked_ips WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())");
            $stmt->execute([$ip]);
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Block an IP address
     */
    public static function blockIp($ip, $reason = '', $duration = 3600)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $expiresAt = date('Y-m-d H:i:s', time() + $duration);
            $stmt = $db->prepare(
                "INSERT INTO blocked_ips (ip_address, reason, expires_at, created_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE reason = ?, expires_at = ?"
            );
            $stmt->execute([$ip, $reason, $expiresAt, $reason, $expiresAt]);
            return true;
        } catch (\Exception $e) {
            error_log('SecurityHelper::blockIp error: ' . $e->getMessage());
            return false;
        }
    }
}
