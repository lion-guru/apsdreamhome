<?php
/**
 * Security Headers Middleware
 */

namespace App\Http\Middleware;

class SecurityHeaders
{
    /**
     * Handle an incoming request
     */
    public function handle($request, $next)
    {
        // Add security headers
        $this->addSecurityHeaders();

        return $next($request);
    }

    /**
     * Add security headers
     */
    protected function addSecurityHeaders()
    {
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');

        // Content Type Options
        header('X-Content-Type-Options: nosniff');

        // Frame Options
        header('X-Frame-Options: SAMEORIGIN');

        // Strict Transport Security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // Content Security Policy
        $this->addCSPHeader();

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        $this->addPermissionsPolicyHeader();
    }

    /**
     * Add Content Security Policy header
     */
    protected function addCSPHeader()
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: http:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self' https://api.example.com",
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "upgrade-insecure-requests"
        ];

        header('Content-Security-Policy: ' . implode('; ', $csp));
    }

    /**
     * Add Permissions Policy header
     */
    protected function addPermissionsPolicyHeader()
    {
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()'
        ];

        header('Permissions-Policy: ' . implode(', ', $permissions));
    }
}
