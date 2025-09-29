<?php
namespace App\Common\Transformers;

class ResponseTransformer {
    /**
     * Generate a success response
     * 
     * @param mixed $data The response data
     * @param string|null $message Optional success message
     * @param int $status HTTP status code (default: 200)
     * @param array $metadata Additional metadata to include in the response
     * @return array The formatted response array
     */
    public static function success($data = null, string $message = null, int $status = 200, array $metadata = []): array {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'status' => $status
        ];
        
        // Add metadata if provided
        if (!empty($metadata)) {
            $response['meta'] = $metadata;
        }
        
        return array_filter($response, function($value) {
            return $value !== null && $value !== '' && $value !== [];
        });
    }
    
    /**
     * Generate an error response
     * 
     * @param string $message Error message
     * @param string $code Error code (default: 'error')
     * @param int $status HTTP status code (default: 400)
     * @param array $errors Additional error details
     * @param array $metadata Additional metadata
     * @return array The formatted error response array
     */
    public static function error(string $message, string $code = 'error', int $status = 400, array $errors = [], array $metadata = []): array {
        $response = [
            'success' => false,
            'error' => array_filter([
                'code' => $code,
                'message' => $message,
                'status' => $status,
                'errors' => empty($errors) ? null : $errors
            ])
        ];
        
        // Add metadata if provided
        if (!empty($metadata)) {
            $response['meta'] = $metadata;
        }
        
        return $response;
    }
}
