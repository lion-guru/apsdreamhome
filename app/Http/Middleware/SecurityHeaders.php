<?php

// TODO: Add proper error handling with try-catch blocks

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

        // Content Security Policy — handled centrally by BaseController::setSecurityHeaders()

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        $this->addPermissionsPolicyHeader();
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
