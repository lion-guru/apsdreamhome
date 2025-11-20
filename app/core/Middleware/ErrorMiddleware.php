<?php

namespace App\Core\Middleware;

use App\Core\Middleware;

/**
 * Error Handling Middleware
 * Handles application errors and exceptions
 */
class ErrorMiddleware extends Middleware
{
    private array $errorConfig = [
        'display_errors' => true,
        'log_errors' => true,
        'error_log_file' => __DIR__ . '/../../../logs/errors.log',
        'error_levels' => [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_NOTICE => 'Notice',
            E_PARSE => 'Parse Error',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ],
    ];
    
    /**
     * Handle the error
     */
    public function handle(array $request, callable $next): mixed
    {
        try {
            // Set error reporting
            error_reporting(E_ALL);
            
            if ($this->errorConfig['display_errors']) {
                ini_set('display_errors', '1');
            } else {
                ini_set('display_errors', '0');
            }
            
            // Set custom error handlers
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
            register_shutdown_function([$this, 'handleShutdown']);
            
            // Continue with request processing
            return $next($request);
            
        } catch (\Exception $e) {
            return $this->handleException($e);
        } catch (\Error $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Handle PHP errors
     */
    public function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $errorType = $this->errorConfig['error_levels'][$errno] ?? 'Unknown Error';
        
        $errorData = [
            'type' => $errorType,
            'code' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $this->getClientIp(),
        ];
        
        // Log the error
        if ($this->errorConfig['log_errors']) {
            $this->logError($errorData);
        }
        
        // Display error if enabled
        if ($this->errorConfig['display_errors']) {
            $this->displayError($errorData);
        }
        
        return true;
    }
    
    /**
     * Handle exceptions
     */
    public function handleException($exception): mixed
    {
        $errorData = [
            'type' => 'Exception',
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $this->getClientIp(),
        ];
        
        // Log the error
        if ($this->errorConfig['log_errors']) {
            $this->logError($errorData);
        }
        
        // Display error page
        return $this->displayErrorPage($errorData);
    }
    
    /**
     * Handle fatal errors
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }
    
    /**
     * Log error to file
     */
    private function logError(array $errorData): void
    {
        try {
            $logDir = dirname($this->errorConfig['error_log_file']);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logEntry = sprintf(
                "[%s] %s: %s in %s:%d\nURL: %s\nIP: %s\nUser Agent: %s\nTrace: %s\n\n",
                $errorData['timestamp'],
                $errorData['type'],
                $errorData['message'],
                $errorData['file'],
                $errorData['line'],
                $errorData['url'],
                $errorData['ip'],
                $errorData['user_agent'],
                $errorData['trace'] ?? 'N/A'
            );
            
            error_log($logEntry, 3, $this->errorConfig['error_log_file']);
            
            // Also log to database if available
            $this->logErrorToDatabase($errorData);
            
        } catch (\Exception $e) {
            // If logging fails, try to log to PHP error log
            error_log("Failed to log error: " . $e->getMessage());
        }
    }
    
    /**
     * Log error to database
     */
    private function logErrorToDatabase(array $errorData): void
    {
        try {
            if ($this->db === null) {
                return;
            }
            
            $this->db->insert('error_logs', [
                'error_type' => $errorData['type'],
                'error_code' => $errorData['code'],
                'error_message' => $errorData['message'],
                'error_file' => $errorData['file'],
                'error_line' => $errorData['line'],
                'error_trace' => $errorData['trace'] ?? null,
                'url' => $errorData['url'],
                'method' => $errorData['method'],
                'user_agent' => $errorData['user_agent'],
                'ip_address' => $errorData['ip'],
                'user_id' => $_SESSION['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            // Database logging is optional, don't throw if it fails
        }
    }
    
    /**
     * Display error for development
     */
    private function displayError(array $errorData): void
    {
        if (headers_sent()) {
            echo "\n<!-- Error: {$errorData['message']} -->\n";
            return;
        }
        
        header('Content-Type: text/html; charset=UTF-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - <?php echo htmlspecialchars($errorData['type']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
                .error-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
                .error-header { background: #dc3545; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .error-details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .error-trace { background: #212529; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; overflow-x: auto; }
                .error-trace pre { margin: 0; white-space: pre-wrap; }
                .error-file { font-weight: bold; color: #dc3545; }
                .error-line { color: #fd7e14; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-header">
                    <h1><?php echo htmlspecialchars($errorData['type']); ?></h1>
                    <p><?php echo htmlspecialchars($errorData['message']); ?></p>
                </div>
                
                <div class="error-details">
                    <p><strong>File:</strong> <span class="error-file"><?php echo htmlspecialchars($errorData['file']); ?></span></p>
                    <p><strong>Line:</strong> <span class="error-line"><?php echo htmlspecialchars($errorData['line']); ?></span></p>
                    <p><strong>URL:</strong> <?php echo htmlspecialchars($errorData['url']); ?></p>
                    <p><strong>Method:</strong> <?php echo htmlspecialchars($errorData['method']); ?></p>
                    <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($errorData['timestamp']); ?></p>
                </div>
                
                <?php if (isset($errorData['trace']) && !empty($errorData['trace'])): ?>
                <div class="error-trace">
                    <h3>Stack Trace:</h3>
                    <pre><?php echo htmlspecialchars($errorData['trace']); ?></pre>
                </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * Display error page for production
     */
    private function displayErrorPage(array $errorData): mixed
    {
        // Determine HTTP status code
        $statusCode = $this->getHttpStatusCode($errorData['code']);
        
        // Set appropriate headers
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=UTF-8');
        
        // Check if it's an API request
        if ($this->isApiRequest()) {
            return $this->displayApiError($errorData, $statusCode);
        }
        
        // Display user-friendly error page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - Something went wrong</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    margin: 0; 
                    padding: 0; 
                    min-height: 100vh; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                }
                .error-container { 
                    background: white; 
                    border-radius: 20px; 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
                    padding: 60px; 
                    text-align: center; 
                    max-width: 500px; 
                    margin: 20px; 
                }
                .error-icon { 
                    font-size: 80px; 
                    color: #dc3545; 
                    margin-bottom: 20px; 
                }
                .error-title { 
                    font-size: 48px; 
                    font-weight: 700; 
                    color: #2c3e50; 
                    margin-bottom: 10px; 
                }
                .error-subtitle { 
                    font-size: 24px; 
                    color: #7f8c8d; 
                    margin-bottom: 30px; 
                }
                .error-message { 
                    font-size: 16px; 
                    color: #34495e; 
                    line-height: 1.6; 
                    margin-bottom: 40px; 
                }
                .error-actions { 
                    display: flex; 
                    gap: 15px; 
                    justify-content: center; 
                    flex-wrap: wrap; 
                }
                .btn { 
                    padding: 12px 24px; 
                    border: none; 
                    border-radius: 8px; 
                    text-decoration: none; 
                    font-weight: 600; 
                    transition: all 0.3s ease; 
                    cursor: pointer; 
                }
                .btn-primary { 
                    background: #3498db; 
                    color: white; 
                }
                .btn-primary:hover { 
                    background: #2980b9; 
                    transform: translateY(-2px); 
                }
                .btn-secondary { 
                    background: #95a5a6; 
                    color: white; 
                }
                .btn-secondary:hover { 
                    background: #7f8c8d; 
                    transform: translateY(-2px); 
                }
                .error-code { 
                    font-size: 14px; 
                    color: #bdc3c7; 
                    margin-top: 30px; 
                }
                @media (max-width: 600px) {
                    .error-container { padding: 40px 30px; }
                    .error-title { font-size: 36px; }
                    .error-subtitle { font-size: 18px; }
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h1 class="error-title">Oops!</h1>
                <h2 class="error-subtitle">Something went wrong</h2>
                <p class="error-message">
                    We're sorry, but something unexpected happened. 
                    Our team has been notified and is working on fixing the issue.
                </p>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">Go Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
                    <button onclick="location.reload()" class="btn btn-secondary">Try Again</button>
                </div>
                <div class="error-code">
                    Error Code: <?php echo $statusCode; ?>
                    <?php if (getenv('APP_ENV') === 'development'): ?>
                        <br>Reference: <?php echo md5($errorData['timestamp'] . $errorData['message']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        
        return null;
    }
    
    /**
     * Display API error response
     */
    private function displayApiError(array $errorData, int $statusCode): mixed
    {
        $response = [
            'error' => [
                'message' => 'An error occurred while processing your request',
                'code' => $statusCode,
                'timestamp' => $errorData['timestamp'],
            ],
            'status' => 'error',
        ];
        
        // Add debug info in development
        if (getenv('APP_ENV') === 'development') {
            $response['debug'] = [
                'message' => $errorData['message'],
                'file' => $errorData['file'],
                'line' => $errorData['line'],
                'trace' => $errorData['trace'] ?? null,
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        
        return null;
    }
    
    /**
     * Get HTTP status code from error code
     */
    private function getHttpStatusCode(int $errorCode): int
    {
        $statusCodes = [
            404 => 404,
            403 => 403,
            401 => 401,
            500 => 500,
            502 => 502,
            503 => 503,
        ];
        
        return $statusCodes[$errorCode] ?? 500;
    }
    
    /**
     * Check if request is for API
     */
    private function isApiRequest(): bool
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        return (
            strpos($acceptHeader, 'application/json') !== false ||
            strpos($contentType, 'application/json') !== false ||
            strpos($requestUri, '/api/') !== false
        );
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return 'unknown';
    }
}