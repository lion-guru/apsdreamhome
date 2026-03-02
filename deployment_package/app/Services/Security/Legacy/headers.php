<?php

namespace App\Services\Security\Legacy;
/**
 * Security Headers for APS Dream Home
 */

class SecurityHeaders {
    public static function setAll() {
        // Content Security Policy
        $csp = "default-src 'self'; " .
                "script-src 'self' 'nonce-" . self::getNonce() . "' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'nonce-" . self::getNonce() . "' https://fonts.googleapis.com; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' https://fonts.gstatic.com; " .
                "connect-src 'self'; " .
                "frame-ancestors 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'";

        header("Content-Security-Policy: $csp");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // HSTS for HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    private static function getNonce() {
        if (!isset($_SESSION['csp_nonce'])) {
            $_SESSION['csp_nonce'] = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(16));
        }
        return $_SESSION['csp_nonce'];
    }

    public static function getNonceAttribute() {
        return 'nonce="' . self::getNonce() . '"';
    }
}
?>
