<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasJsonResponses
 *
 * Provides standardized JSON response methods for API controllers.
 * Include this trait in controllers that return JSON responses.
 */
trait HasJsonResponses
{
    /**
     * Return a JSON response and exit.
     */
    public function response($data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Return a JSON response (alias for response).
     */
    public function jsonResponse($data, int $status = 200): void
    {
        $this->response($data, $status);
    }

    /**
     * Short alias for jsonResponse.
     */
    public function json($data, int $status = 200): void
    {
        $this->response($data, $status);
    }

    /**
     * Return a JSON error response and exit.
     */
    protected function jsonError(string $message, int $status = 400): void
    {
        $this->response(['success' => false, 'error' => $message], $status);
    }

    /**
     * Return a JSON success response and exit.
     */
    protected function jsonSuccess($data = [], string $message = 'Success', int $status = 200): void
    {
        $this->response(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    /**
     * Return a 404 Not Found response.
     */
    protected function notFound(string $message = "Resource not found"): void
    {
        $this->response(['success' => false, 'error' => $message], 404);
    }

    /**
     * Return a 403 Forbidden response.
     */
    protected function forbidden(string $message = "Access denied"): void
    {
        $this->response(['success' => false, 'error' => $message], 403);
    }

    /**
     * Handle API errors consistently — log internally, return generic message to client.
     */
    protected function handleApiError(\Throwable $exception, string $context = 'API Error'): void
    {
        error_log($context . ': ' . $exception->getMessage());
        $this->response([
            'success' => false,
            'error' => 'Internal server error',
            'context' => $context,
        ], 500);
    }

    /**
     * Return a safe error response — logs the real error, returns a generic message.
     * Use this instead of $e->getMessage() to prevent information leakage.
     */
    protected function safeError(\Throwable $exception, string $context = 'API Error', int $code = 500): array
    {
        error_log($context . ': ' . $exception->getMessage());
        return ['success' => false, 'error' => 'An internal error occurred. Please try again later.', 'context' => $context];
    }

    /**
     * Return a JSON success response and exit (explicit void return).
     */
    protected function successResponse($data, string $message = 'Success'): void
    {
        $this->response([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    /**
     * Return a JSON error response and exit (explicit void return).
     */
    protected function errorResponse(string $message, int $code = 400): void
    {
        $this->response(['success' => false, 'error' => $message], $code);
    }

    /**
     * Extract Bearer token from Authorization header.
     */
    protected function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;
        if (!$header && function_exists('apache_request_headers')) {
            $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
            foreach ($headers as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) {
                    $header = $value;
                    break;
                }
            }
        }
        if (!$header) {
            return null;
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return null;
    }
}