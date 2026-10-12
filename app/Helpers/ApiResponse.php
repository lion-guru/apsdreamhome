<?php

namespace App\Helpers;

/**
 * ApiResponse — canonical JSON envelope for all API endpoints.
 *
 * Shape (matches BaseApiController::jsonSuccess/jsonError):
 *   success: { "success": true,  "message": "...", "data": {...} }
 *   failure: { "success": false, "error":   "...", "data": {...} }
 *
 * Usage (opt-in, no behavior change to existing endpoints):
 *   return ApiResponse::ok($rows);
 *   return ApiResponse::ok($row, 'Lead created', 201);
 *   return ApiResponse::fail('Validation failed', 422, $errors);
 *
 * New API code MUST use this helper (or BaseApiController::jsonSuccess/
 * jsonError). Do NOT hand-roll `echo json_encode([...])` — the Mobile*
 * controllers carry ~600 legacy echo sites that are being migrated
 * incrementally; do not add new ones.
 */
class ApiResponse
{
    /**
     * Send a success envelope and terminate.
     */
    public static function ok($data = [], string $message = 'Success', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
        exit;
    }

    /**
     * Send an error envelope and terminate.
     */
    public static function fail(string $message = 'Error occurred', int $statusCode = 400, $data = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'data' => $data,
        ]);
        exit;
    }

    /**
     * Build (not send) a success envelope array — for controllers that
     * need to post-process before output.
     */
    public static function okArray($data = [], string $message = 'Success'): array
    {
        return ['success' => true, 'message' => $message, 'data' => $data];
    }

    /**
     * Build (not send) an error envelope array.
     */
    public static function failArray(string $message = 'Error occurred', $data = []): array
    {
        return ['success' => false, 'error' => $message, 'data' => $data];
    }
}
