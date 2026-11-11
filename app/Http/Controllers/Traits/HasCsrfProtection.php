<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasCsrfProtection
 *
 * Provides CSRF token generation and validation for controllers.
 * Include this trait in controllers that need CSRF protection.
 */
trait HasCsrfProtection
{
    /**
     * Generate or retrieve CSRF token for current session.
     */
    protected function getCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        // Set/update expiry to 1 hour from now
        $_SESSION['csrf_token_expires'] = time() + 3600;
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token using timing-safe comparison.
     */
    protected function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate CSRF token or terminate with 403 Forbidden.
     * Use in POST handlers that must have CSRF protection.
     */
    protected function validateCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            } else {
                echo "403 Forbidden: Invalid CSRF token";
            }
            exit;
        }
    }

    /**
     * Alias for validateCsrfToken for backward compatibility.
     */
    protected function verifyCsrfToken(string $token): bool
    {
        return $this->validateCsrfToken($token);
    }

    /**
     * Simple CSRF validation from POST body only.
     */
    protected function validateCsrfTokenLocal(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return $token === ($_SESSION['csrf_token'] ?? '');
    }

    /**
     * Override in controller to skip CSRF protection for specific routes.
     * Returns false by default (CSRF protection enabled).
     */
    protected function skipCsrfProtection(): bool
    {
        return false;
    }
}