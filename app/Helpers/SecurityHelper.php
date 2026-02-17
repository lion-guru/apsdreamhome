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
        $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://www.googletagmanager.com https://checkout.razorpay.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; ";
        $csp .= "frame-src 'self' https://www.google.com https://www.youtube.com https://checkout.razorpay.com; ";
        $csp .= "connect-src 'self' https://api.openai.com https://api.gemini.com;";

        header("Content-Security-Policy: " . $csp);

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

        if (!isset($_SESSION['csrf_token']) || (isset($_SESSION['csrf_token_expires']) && time() >= $_SESSION['csrf_token_expires'])) {
            $_SESSION['csrf_token'] = bin2hex(self::secureRandomBytes(32));
            $_SESSION['csrf_token_expires'] = time() + 3600; // 1 hour
        }

        // BACKWARD COMPATIBILITY: Set unified schema if needed
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = [
                'token' => $_SESSION['csrf_token'],
                'expires' => $_SESSION['csrf_token_expires']
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

        if (isset($_SESSION['csrf_token_expires']) && time() >= $_SESSION['csrf_token_expires']) {
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
        return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
    }

    /**
     * Secure random integer
     */
    public static function secureRandomInt($min, $max)
    {
        try {
            return \random_int($min, $max);
        } catch (\Exception $e) {
            // Log the error if logger is available, but don't fallback to insecure mt_rand for security sensitive operations
            throw new \Exception('Cryptographically secure random integer generator failed: ' . $e->getMessage());
        }
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
}
