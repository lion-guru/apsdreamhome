<?php
// app/middleware/SecurityMiddleware.php

class SecurityMiddleware {
    public function handle($request, $next) {
        // Add security headers
        $this->addSecurityHeaders();

        // Validate CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken($request);
        }

        return $next($request);
    }

    private function addSecurityHeaders() {
        if (headers_sent()) {
            return;
        }

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()'
        ];

        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
    }

    private function validateCsrfToken($request) {
        $token = $_POST['_token'] ?? '';

        if (!validate_csrf_token($token)) {
            http_response_code(419); // CSRF token mismatch
            die('CSRF token validation failed');
        }
    }
}
