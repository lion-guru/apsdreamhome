<?php

namespace App\Http\Controllers;

use App\Services\CoreFunctionsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

/**
 * Controller for Core Functions operations
 */
class CoreFunctionsController extends BaseController
{
    private CoreFunctionsService $coreFunctions;

    public function __construct(CoreFunctionsService $coreFunctions)
    {
        $this->coreFunctions = $coreFunctions;
    }

    /**
     * Validate input data
     */
    public function validateInput(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'input' => 'required',
                'type' => 'required|string|in:username,email,password,captcha,phone,url,numeric,string',
                'max_length' => 'integer|min:1|max:1000',
                'required' => 'boolean'
            ]);

            $result = $this->coreFunctions->validateInput(
                $validated['input'],
                $validated['type'],
                $validated['max_length'] ?? null,
                $validated['required'] ?? true
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => $result !== false,
                    'result' => $result,
                    'type' => $validated['type']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate multiple inputs
     */
    public function validateInputs(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'inputs' => 'required|array',
                'rules' => 'required|array'
            ]);

            $result = $this->coreFunctions->validateInputs($validated['inputs'], $validated['rules']);

            return response()->json([
                'success' => $result['valid'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format phone number
     */
    public function formatPhone(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string'
            ]);

            $formatted = $this->coreFunctions->formatPhoneNumber($validated['phone']);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['phone'],
                    'formatted' => $formatted
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format phone number',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate random string
     */
    public function generateRandomString(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'length' => 'integer|min:1|max:100'
            ]);

            $randomString = $this->coreFunctions->generateRandomString($validated['length'] ?? 16);

            return response()->json([
                'success' => true,
                'data' => [
                    'random_string' => $randomString,
                    'length' => strlen($randomString)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate random string',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate slug
     */
    public function generateSlug(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string'
            ]);

            $slug = $this->coreFunctions->generateSlug($validated['text']);

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['text'],
                    'slug' => $slug
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate slug',
                'error' => $e->getMessage()
            ], 400);
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
                'length' => 'integer|min:1|max:1000',
                'suffix' => 'string|max:50'
            ]);

            $truncated = $this->coreFunctions->truncateText(
                $validated['text'],
                $validated['length'] ?? 100,
                $validated['suffix'] ?? '...'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['text'],
                    'truncated' => $truncated,
                    'length' => strlen($truncated)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to truncate text',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format currency
     */
    public function formatCurrency(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric',
                'currency' => 'string|max:10'
            ]);

            $formatted = $this->coreFunctions->formatCurrency(
                (float) $validated['amount'],
                $validated['currency'] ?? '₹'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'amount' => $validated['amount'],
                    'formatted' => $formatted
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format currency',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format date
     */
    public function formatDate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|string',
                'format' => 'string|max:50'
            ]);

            $formatted = $this->coreFunctions->formatDate(
                $validated['date'],
                $validated['format'] ?? 'Y-m-d H:i:s'
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original' => $validated['date'],
                    'formatted' => $formatted
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to format date',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload and process image
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'image' => 'required|file|image|max:10240', // 10MB max
                'max_width' => 'integer|min:1|max:5000',
                'max_height' => 'integer|min:1|max:5000',
                'quality' => 'integer|min:1|max:100'
            ]);

            $file = $validated['image'];
            $maxWidth = $validated['max_width'] ?? 800;
            $maxHeight = $validated['max_height'] ?? 600;
            $quality = $validated['quality'] ?? 85;

            // Generate unique filename
            $filename = $this->coreFunctions->generateUniqueFilename($file->getClientOriginalName(), 'img_');
            $originalPath = $file->storeAs('temp', $filename);
            $fullOriginalPath = storage_path('app/' . $originalPath);

            // Resize image
            $resizedFilename = $this->coreFunctions->generateUniqueFilename($file->getClientOriginalName(), 'resized_');
            $resizedPath = storage_path('app/public/images/' . $resizedFilename);
            
            $this->coreFunctions->ensureDirectoryExists(dirname($resizedPath));

            $resized = $this->coreFunctions->resizeImage(
                $fullOriginalPath,
                $resizedPath,
                $maxWidth,
                $maxHeight,
                $quality
            );

            // Create thumbnail
            $thumbnailFilename = $this->coreFunctions->generateUniqueFilename($file->getClientOriginalName(), 'thumb_');
            $thumbnailPath = storage_path('app/public/thumbnails/' . $thumbnailFilename);
            
            $this->coreFunctions->ensureDirectoryExists(dirname($thumbnailPath));
            
            $thumbnail = $this->coreFunctions->createThumbnail($fullOriginalPath, $thumbnailPath);

            // Clean up temp file
            unlink($fullOriginalPath);

            if ($resized && $thumbnail) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded and processed successfully',
                    'data' => [
                        'original_name' => $file->getClientOriginalName(),
                        'resized_url' => url('storage/images/' . $resizedFilename),
                        'thumbnail_url' => url('storage/thumbnails/' . $thumbnailFilename),
                        'size' => $this->coreFunctions->formatFileSize($file->getSize()),
                        'dimensions' => [
                            'max_width' => $maxWidth,
                            'max_height' => $maxHeight
                        ]
                    ]
                ]);
            } else {
                throw new \Exception('Failed to process image');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get file information
     */
    public function getFileInfo(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filepath' => 'required|string'
            ]);

            $filepath = $validated['filepath'];
            
            if (!$this->coreFunctions->safeFileExists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            $fileInfo = [
                'exists' => true,
                'readable' => is_readable($filepath),
                'writable' => is_writable($filepath),
                'size' => $this->coreFunctions->formatFileSize(filesize($filepath)),
                'extension' => $this->coreFunctions->getFileExtension($filepath),
                'is_image' => $this->coreFunctions->isImageFile($filepath),
                'modified' => date('Y-m-d H:i:s', filemtime($filepath))
            ];

            return response()->json([
                'success' => true,
                'data' => $fileInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get file info',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Extract text from file
     */
    public function extractText(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'filepath' => 'required|string'
            ]);

            $text = $this->coreFunctions->extractTextFromFile($validated['filepath']);

            return response()->json([
                'success' => true,
                'data' => [
                    'text' => $text,
                    'length' => strlen($text)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract text',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get client information
     */
    public function getClientInfo(): JsonResponse
    {
        try {
            $info = [
                'ip' => $this->coreFunctions->getClientIp(),
                'user_agent' => request()->userAgent(),
                'is_ajax' => $this->coreFunctions->isAjaxRequest(),
                'current_url' => $this->coreFunctions->getCurrentUrl(),
                'is_authenticated' => $this->coreFunctions->isAuthenticated(),
                'user_role' => $this->coreFunctions->getUserRole()
            ];

            return response()->json([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get client info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSRF token
     */
    public function getCsrfToken(): JsonResponse
    {
        try {
            $token = $this->coreFunctions->generateCsrfToken();

            return response()->json([
                'success' => true,
                'data' => [
                    'csrf_token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSRF token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log admin action
     */
    public function logAction(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'action' => 'required|string',
                'details' => 'array'
            ]);

            $this->coreFunctions->logAdminAction([
                'action' => $validated['action'],
                'details' => $validated['details'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Action logged successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test core functions
     */
    public function test(): JsonResponse
    {
        try {
            $tests = [];

            // Test validation
            $tests['email_validation'] = $this->coreFunctions->validateInput('test@example.com', 'email') !== false;
            $tests['phone_validation'] = $this->coreFunctions->validateInput('9876543210', 'phone') !== false;
            $tests['username_validation'] = $this->coreFunctions->validateInput('testuser', 'username') !== false;

            // Test formatting
            $tests['phone_formatting'] = $this->coreFunctions->formatPhoneNumber('9876543210') === '+91 98765 43210';
            $tests['currency_formatting'] = $this->coreFunctions->formatCurrency(1234.56) === '₹1,234.56';

            // Test utilities
            $tests['slug_generation'] = $this->coreFunctions->generateSlug('Test String Here') === 'test-string-here';
            $tests['text_truncation'] = $this->coreFunctions->truncateText('This is a long text', 10) === 'This is a...';
            $tests['random_string'] = strlen($this->coreFunctions->generateRandomString(16)) === 16;

            $allPassed = array_reduce($tests, fn($carry, $result) => $carry && $result, true);

            return response()->json([
                'success' => true,
                'message' => $allPassed ? 'All tests passed' : 'Some tests failed',
                'data' => [
                    'tests' => $tests,
                    'passed' => $allPassed,
                    'total' => count($tests)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test execution failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
