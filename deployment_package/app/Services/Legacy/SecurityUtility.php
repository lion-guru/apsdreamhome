<?php

namespace App\Services\Legacy;
/**
 * Comprehensive Security Utility for APS Dream Homes
 * Provides input sanitization, validation, and security functions
 */
class SecurityUtility {
    /**
     * Sanitize and validate input
     * @param mixed $input
     * @param string $type
     * @return mixed
     */
    public static function sanitizeInput($input, $type = 'string') {
        return \App\Helpers\SecurityHelper::cleanInput($input, $type);
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateCSRFToken() {
        return \App\Helpers\SecurityHelper::generateCsrfToken();
    }

    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateCSRFToken($token) {
        return \App\Helpers\SecurityHelper::validateCsrfToken($token);
    }

    /**
     * Get CSRF field HTML
     * @return string
     */
    public static function getCsrfField() {
        return \App\Helpers\SecurityHelper::csrfField();
    }

    /**
     * Generate secure random password
     * @param int $length
     * @return string
     */
    public static function generateSecurePassword($length = 12) {
        return \App\Helpers\SecurityHelper::generateRandomString($length);
    }

    /**
     * Check if IP is from a trusted source
     * @param string $ip
     * @param array $trustedIPs
     * @return bool
     */
    public static function isTrustedIP($ip, $trustedIPs = []) {
        // Default trusted IPs (localhost and private networks)
        $defaultTrusted = [
            '127.0.0.1',
            '::1',
            '192.168.0.0/16',
            '10.0.0.0/8',
            '172.16.0.0/12'
        ];

        $trustedIPs = array_merge($defaultTrusted, $trustedIPs);

        foreach ($trustedIPs as $trustedIP) {
            if (self::ipInRange($ip, $trustedIP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is within a given range
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private static function ipInRange($ip, $range) {
        if (\strpos($range, '/') !== false) {
            list($range, $netmask) = \explode('/', $range, 2);
            $rangeDecimal = \ip2long($range);
            $ipDecimal = \ip2long($ip);
            $wildcardDecimal = \pow(2, (32 - $netmask)) - 1;
            $netmaskDecimal = ~ $wildcardDecimal;
            return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
        }

        return $ip === $range;
    }
}

// Helper function for global use
if (!function_exists('sanitize')) {
    function sanitize($input, $type = 'string') {
        return SecurityUtility::sanitizeInput($input, $type);
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        return SecurityUtility::generateCSRFToken();
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return SecurityUtility::validateCSRFToken($token);
    }
}

if (!function_exists('getCsrfField')) {
    function getCsrfField() {
        return SecurityUtility::getCsrfField();
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken($token) {
        return SecurityUtility::validateCSRFToken($token);
    }
}
