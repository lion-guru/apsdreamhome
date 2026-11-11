<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasSecurityHeaders
 *
 * Provides defense-in-depth security HTTP headers, CSP nonce management,
 * session security, and CORS handling for controllers.
 */
trait HasSecurityHeaders
{
    /**
     * Send defense-in-depth security HTTP headers on every response.
     * Mirrors the .htaccess directives so headers are present even when
     * the request bypasses Apache (CLI, internal includes, etc.).
     */
    protected function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Dedupe: multiple controller instances are constructed per request
        // while output buffering keeps headers unsent. A second header()
        // call would stack a SECOND Content-Security-Policy with a DIFFERENT
        // nonce — browsers enforce ALL CSP headers, so every inline script
        // would be blocked. Reuse the request's nonce instead.
        if (!empty($GLOBALS['csp_nonce'])) {
            return;
        }

        // Generate CSP nonce for this request
        $cspNonce = bin2hex(random_bytes(16));
        $_SESSION['csp_nonce'] = $cspNonce;

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-XSS-Protection: 1; mode=block');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        if (class_exists('\App\Services\Log')) {
            header('X-Request-Id: ' . \App\Services\Log::getRequestId());
        }
        // HSTS only when over HTTPS
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }
        // Content Security Policy.
        // NOTE: If 'nonce-...' or 'strict-dynamic' is in the header, browsers
        // strictly IGNORE 'unsafe-inline' (W3C CSP Level 2/3 spec).
        // This codebase has 1300+ onclick/on* handlers and inline styles across
        // 450+ views, so unsafe-inline and unsafe-eval MUST be allowed without
        // a nonce in the header string.
        $base = defined('BASE_URL') ? BASE_URL : '';
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' " . ($cspNonce ? "nonce-{$cspNonce} " : "") . "https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com https://unpkg.com https://www.googletagmanager.com https://code.jquery.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com https://unpkg.com; "
            . "img-src 'self' data: blob: https:; "
            . "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; "
            . "frame-src 'self' https://www.google.com https://www.youtube.com https://www.youtube-nocookie.com; "
            . "connect-src 'self' https: ws: wss:; "
            . "report-uri {$base}/csp-report; "
            . "report-to csp-endpoint";
        header("Content-Security-Policy: " . $csp);
        header("Reporting-Endpoints: csp-endpoint=\"{$base}/csp-report\"");

        // Expose nonce to views
        $GLOBALS['csp_nonce'] = $cspNonce;
    }

    /**
     * Regenerate session ID periodically to prevent session fixation attacks.
     * Runs every 5 minutes (300s). On login, controllers should call
     * session_regenerate_id(true) immediately (see auth controllers).
     */
    protected function initSessionSecurity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $regenerateInterval = 300; // 5 minutes
        $now = time();
        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = $now;
            return;
        }
        if ($now - (int)$_SESSION['last_regenerate'] > $regenerateInterval) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = $now;
        }
    }

    /**
     * Set CORS headers for API responses.
     */
    protected function setCorsHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Get the CSP nonce for use in views.
     */
    protected function getCspNonce(): string
    {
        return $GLOBALS['csp_nonce'] ?? '';
    }
}