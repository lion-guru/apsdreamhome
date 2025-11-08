<?php
namespace App\Common\Exceptions;

use App\Common\Transformers\ResponseTransformer;

class Handler {
    public static function handle(\Throwable $e): array {
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        
        return ResponseTransformer::error(
            'An error occurred',
            'server_error',
            500,
            (getenv('APP_DEBUG') === 'true' || ($_ENV['APP_DEBUG'] ?? '') === 'true') ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : []
        );
    }
}

// Register error handlers if not already registered by the application
set_exception_handler([\App\Common\Exceptions\Handler::class, 'handle']);
set_error_handler(function($errno, $errstr, $errfile = null, $errline = null) {
    throw new \ErrorException($errstr, $errno, 1, $errfile ?? (__FILE__), $errline ?? (__LINE__));
});
