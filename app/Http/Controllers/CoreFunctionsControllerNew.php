<?php

namespace App\Http\Controllers;

use App\Services\CoreFunctionsServiceNew;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for Core Functions operations
 */
class CoreFunctionsControllerNew extends BaseController
{
    /**
     * Log admin action
     */
    public function logAdminAction(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|string|max:255',
                'details' => 'nullable|array',
                'ip_address' => 'nullable|ip',
                'user_agent' => 'nullable|string|max:500'
            ]);

            $result = CoreFunctionsServiceNew::logAdminAction($validated);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Admin action logged successfully' : 'Failed to log admin action'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log admin action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate input
     */
    public function validateInput(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'input' => 'required',
                'type' => 'required|in:username,email,password,phone,captcha,number,url,string',
                'max_length' => 'nullable|integer|min:1|max:1000',
                'required' => 'nullable|boolean'
            ]);

            $result = CoreFunctionsServiceNew::validateInput(
                $validated['input'],
                $validated['type'],
                $validated['max_length'] ?? null,
                $validated['required'] ?? true
            );

            $isValid = $result !== false;

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['input'],
                    'validated' => $result,
                    'is_valid' => $isValid,
                    'type' => $validated['type']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Input validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate request headers
     */
    public function validateRequestHeaders(Request $request): JsonResponse
    {
        try {
            $result = CoreFunctionsServiceNew::validateRequestHeaders();

            return response()->json([
                'success' => true,
                'data' => [
                    'headers_valid' => $result,
                    'request_info' => [
                        'method' => $request->method(),
                        'content_type' => $request->header('Content-Type'),
                        'user_agent' => $request->header('User-Agent'),
                        'ip_address' => $request->ip()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Header validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send security response
     */
    public function sendSecurityResponse(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status_code' => 'required|integer|min:100|max:599',
                'message' => 'required|string|max:500',
                'data' => 'nullable|array'
            ]);

            return CoreFunctionsServiceNew::sendSecurityResponse(
                $validated['status_code'],
                $validated['message'],
                $validated['data'] ?? null
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send security response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize admin session
     */
    public function initAdminSession(Request $request): JsonResponse
    {
        try {
            CoreFunctionsServiceNew::initAdminSession();

            return response()->json([
                'success' => true,
                'message' => 'Admin session initialized successfully',
                'data' => [
                    'session_id' => session()->getId(),
                    'csrf_token' => session()->get('csrf_token'),
                    'session_configured' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize admin session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current URL
     */
    public function getCurrentUrl(Request $request): JsonResponse
    {
        try {
            $url = CoreFunctionsServiceNew::getCurrentUrl();

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'parsed' => parse_url($url)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get current URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check file exists
     */
    public function checkFileExists(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filepath' => 'required|string|max:500'
            ]);

            $result = CoreFunctionsServiceNew::safeFileExists($validated['filepath']);

            return response()->json([
                'success' => true,
                'data' => [
                    'filepath' => $validated['filepath'],
                    'exists' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check file existence',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Safe redirect
     */
    public function safeRedirect(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url|max:500',
                'permanent' => 'nullable|boolean'
            ]);

            $redirectResponse = CoreFunctionsServiceNew::safeRedirect(
                $validated['url'],
                $validated['permanent'] ?? false
            );

            return response()->json([
                'success' => true,
                'message' => 'Redirect response prepared',
                'data' => [
                    'url' => $validated['url'],
                    'permanent' => $validated['permanent'] ?? false,
                    'status_code' => $validated['permanent'] ? 301 : 302
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare redirect',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format phone number
     */
    public function formatPhoneNumber(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string|max:20'
            ]);

            $formatted = CoreFunctionsServiceNew::formatPhoneNumber($validated['phone']);
            $isValid = CoreFunctionsServiceNew::isValidPhoneNumber($validated['phone']);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['phone'],
                    'formatted' => $formatted,
                    'is_valid' => $isValid
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format phone number',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate random string
     */
    public function generateRandomString(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'length' => 'nullable|integer|min:1|max:255'
            ]);

            $length = $validated['length'] ?? 16;
            $randomString = CoreFunctionsServiceNew::generateRandomString($length);

            return response()->json([
                'success' => true,
                'data' => [
                    'random_string' => $randomString,
                    'length' => $length,
                    'generated_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate random string',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check authentication status
     */
    public function checkAuthentication(Request $request): JsonResponse
    {
        try {
            $isAuthenticated = CoreFunctionsServiceNew::isAuthenticated();
            $userRole = CoreFunctionsServiceNew::getUserRole();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_authenticated' => $isAuthenticated,
                    'user_role' => $userRole,
                    'user_id' => auth()->id(),
                    'session_id' => session()->getId()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check authentication status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check user permission
     */
    public function checkPermission(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'permission' => 'required|string|max:100'
            ]);

            $hasPermission = CoreFunctionsServiceNew::hasPermission($validated['permission']);

            return response()->json([
                'success' => true,
                'data' => [
                    'permission' => $validated['permission'],
                    'has_permission' => $hasPermission,
                    'user_role' => CoreFunctionsServiceNew::getUserRole()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format currency
     */
    public function formatCurrency(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'currency' => 'nullable|string|max:10'
            ]);

            $formatted = CoreFunctionsServiceNew::formatCurrency(
                (float) $validated['amount'],
                $validated['currency'] ?? '₹'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original_amount' => $validated['amount'],
                    'formatted' => $formatted,
                    'currency' => $validated['currency'] ?? '₹'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format date
     */
    public function formatDate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'format' => 'nullable|string|max:50'
            ]);

            $formatted = CoreFunctionsServiceNew::formatDate(
                $validated['date'],
                $validated['format'] ?? 'Y-m-d H:i:s'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original_date' => $validated['date'],
                    'formatted' => $formatted,
                    'format' => $validated['format'] ?? 'Y-m-d H:i:s'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format date',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sanitize filename
     */
    public function sanitizeFilename(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string|max:255'
            ]);

            $sanitized = CoreFunctionsServiceNew::sanitizeFilename($validated['filename']);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['filename'],
                    'sanitized' => $sanitized
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sanitize filename',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ensure directory exists
     */
    public function ensureDirectoryExists(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'directory' => 'required|string|max:500'
            ]);

            $result = CoreFunctionsServiceNew::ensureDirectoryExists($validated['directory']);

            return response()->json([
                'success' => true,
                'data' => [
                    'directory' => $validated['directory'],
                    'created' => $result,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to ensure directory exists',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file extension
     */
    public function getFileExtension(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string|max:255'
            ]);

            $extension = CoreFunctionsServiceNew::getFileExtension($validated['filename']);
            $isImage = CoreFunctionsServiceNew::isImageFile($validated['filename']);

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $validated['filename'],
                    'extension' => $extension,
                    'is_image' => $isImage
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get file extension',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resize image
     */
    public function resizeImage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'source_path' => 'required|string|max:500',
                'destination_path' => 'required|string|max:500',
                'max_width' => 'nullable|integer|min:1|max:5000',
                'max_height' => 'nullable|integer|min:1|max:5000',
                'quality' => 'nullable|integer|min:1|max:100'
            ]);

            $result = CoreFunctionsServiceNew::resizeImage(
                $validated['source_path'],
                $validated['destination_path'],
                $validated['max_width'] ?? 800,
                $validated['max_height'] ?? 600,
                $validated['quality'] ?? 85
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Image resized successfully' : 'Failed to resize image',
                'data' => [
                    'source_path' => $validated['source_path'],
                    'destination_path' => $validated['destination_path'],
                    'max_width' => $validated['max_width'] ?? 800,
                    'max_height' => $validated['max_height'] ?? 600,
                    'quality' => $validated['quality'] ?? 85
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resize image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate slug
     */
    public function generateSlug(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'string' => 'required|string|max:255'
            ]);

            $slug = CoreFunctionsServiceNew::generateSlug($validated['string']);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['string'],
                    'slug' => $slug
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate slug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Truncate text
     */
    public function truncateText(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string',
                'length' => 'nullable|integer|min:1|max:1000',
                'suffix' => 'nullable|string|max:50'
            ]);

            $truncated = CoreFunctionsServiceNew::truncateText(
                $validated['text'],
                $validated['length'] ?? 100,
                $validated['suffix'] ?? '...'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original_length' => strlen($validated['text']),
                    'truncated' => $truncated,
                    'length' => $validated['length'] ?? 100,
                    'suffix' => $validated['suffix'] ?? '...'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to truncate text',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client IP
     */
    public function getClientIp(Request $request): JsonResponse
    {
        try {
            $ip = CoreFunctionsServiceNew::getClientIp();

            return response()->json([
                'success' => true,
                'data' => [
                    'ip' => $ip,
                    'request_headers' => [
                        'CF-Connecting-IP' => $request->header('CF-Connecting-IP'),
                        'Client-IP' => $request->header('Client-IP'),
                        'X-Forwarded-For' => $request->header('X-Forwarded-For'),
                        'REMOTE_ADDR' => $request->ip()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get client IP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key' => 'required|string|max:255',
                'max_attempts' => 'nullable|integer|min:1|max:1000',
                'time_window' => 'nullable|integer|min:60|max:86400'
            ]);

            $result = CoreFunctionsServiceNew::checkRateLimit(
                $validated['key'],
                $validated['max_attempts'] ?? 5,
                $validated['time_window'] ?? 300
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $validated['key'],
                    'max_attempts' => $validated['max_attempts'] ?? 5,
                    'time_window' => $validated['time_window'] ?? 300,
                    'allowed' => $result,
                    'message' => $result ? 'Rate limit not exceeded' : 'Rate limit exceeded'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check rate limit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send JSON response
     */
    public function sendJsonResponse(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'data' => 'required',
                'status_code' => 'nullable|integer|min:100|max:599'
            ]);

            $statusCode = $validated['status_code'] ?? 200;
            $response = CoreFunctionsServiceNew::sendJsonResponse($validated['data'], $statusCode);

            return response()->json([
                'success' => true,
                'message' => 'JSON response prepared',
                'data' => [
                    'status_code' => $statusCode,
                    'response_data' => $validated['data']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare JSON response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if request is AJAX
     */
    public function checkAjaxRequest(Request $request): JsonResponse
    {
        try {
            $isAjax = CoreFunctionsServiceNew::isAjaxRequest();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_ajax' => $isAjax,
                    'request_headers' => [
                        'X-Requested-With' => $request->header('X-Requested-With'),
                        'Content-Type' => $request->header('Content-Type')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check AJAX request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get WhatsApp templates
     */
    public function getWhatsAppTemplates(Request $request): JsonResponse
    {
        try {
            $templates = CoreFunctionsServiceNew::getWhatsAppTemplates();

            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'count' => count($templates),
                    'loaded_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get WhatsApp templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hash password
     */
    public function hashPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string|min:6|max:255'
            ]);

            $hashedPassword = CoreFunctionsServiceNew::hashPassword($validated['password']);

            return response()->json([
                'success' => true,
                'message' => 'Password hashed successfully',
                'data' => [
                    'hashed_password' => $hashedPassword,
                    'algorithm' => 'bcrypt',
                    'hashed_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hash password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify password hash
     */
    public function verifyPasswordHash(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string',
                'hash' => 'required|string'
            ]);

            $isValid = CoreFunctionsServiceNew::verifyPasswordHash(
                $validated['password'],
                $validated['hash']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid,
                    'verified_at' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify password hash',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
