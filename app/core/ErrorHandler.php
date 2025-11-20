<?php
/**
 * Unified Error Handler
 * Handles all error pages using the modern layout system
 */

namespace App\Core;

class ErrorHandler
{
    /**
     * Render an error page with the specified code and message
     * 
     * @param int $code HTTP error code
     * @param string $message Optional custom message
     * @param array $data Additional data for the error page
     */
    public static function render($code, $message = null, $data = [])
    {
        // Set the HTTP response code
        http_response_code($code);
        
        // Log the error
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        error_log("HTTP {$code} Error: " . $requestUri . " - IP: " . $remoteAddr . ($message ? " - Message: {$message}" : ""));
        
        // Add detailed debug logging
        error_log("ErrorHandler::render() - Starting error rendering for code: {$code}");
        error_log("ErrorHandler::render() - Message: " . ($message ?? 'null'));
        error_log("ErrorHandler::render() - Data: " . json_encode($data));
        
        // Determine the error page path
        $errorView = __DIR__ . '/../../resources/views/errors/' . $code . '.php';
        error_log("ErrorHandler::render() - Error view path: {$errorView}");
        
        // If specific error page doesn't exist, use a generic one
        if (!file_exists($errorView)) {
            error_log("ErrorHandler::render() - Error view file not found, using generic renderer");
            self::renderGeneric($code, $message, $data);
            return;
        }
        
        error_log("ErrorHandler::render() - Error view file exists, including: {$errorView}");
        
        // Include the error page
        try {
            require $errorView;
            error_log("ErrorHandler::render() - Error view included successfully");
        } catch (\Exception $e) {
            error_log("ErrorHandler::render() - Exception while including error view: " . $e->getMessage());
            error_log("ErrorHandler::render() - Exception file: " . $e->getFile() . " line: " . $e->getLine());
            throw $e;
        }
    }
    
    /**
     * Render a generic error page when specific page doesn't exist
     * 
     * @param int $code HTTP error code
     * @param string $message Optional custom message
     * @param array $data Additional data for the error page
     */
    protected static function renderGeneric($code, $message = null, $data = [])
    {
        error_log("ErrorHandler::renderGeneric() - Starting generic error rendering for code: {$code}");
        
        // Set default title and message based on code
        $titles = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable'
        ];
        
        $defaultMessages = [
            400 => 'The request could not be understood by the server.',
            401 => 'You need to be logged in to access this page.',
            403 => 'You don\'t have permission to access this page.',
            404 => 'The page you are looking for could not be found.',
            500 => 'Something went wrong on our end. We\'re working to fix it.',
            502 => 'The server received an invalid response.',
            503 => 'The server is temporarily unavailable.'
        ];
        
        $title = $titles[$code] ?? 'Error ' . $code;
        $message = $message ?? $defaultMessages[$code] ?? 'An error occurred.';
        
        // Set the page title
        $pageTitle = $code . ' - ' . $title;
        
        error_log("ErrorHandler::renderGeneric() - Page title: {$pageTitle}");
        error_log("ErrorHandler::renderGeneric() - Message: {$message}");
        
        // Capture the content for the layout
        ob_start();
        ?>
        
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card p-5">
                    <div class="error-icon mb-4">
                        <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                    </div>
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($title) ?></h1>
                    <p class="lead text-muted mb-4">
                        <?= htmlspecialchars($message) ?>
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Return to Homepage
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </a>
                    </div>
                    <small class="d-block mt-4 text-muted">
                        Error <?= $code ?> - <?= htmlspecialchars($title) ?>
                    </small>
                </div>
            </div>
        </div>
        
        <?php
        $content = ob_get_clean();
        
        error_log("ErrorHandler::renderGeneric() - Content captured, length: " . strlen($content));
        
        // Include the modern layout
        $layoutPath = __DIR__ . '/../../resources/views/layouts/modern.php';
        error_log("ErrorHandler::renderGeneric() - Layout path: {$layoutPath}");
        error_log("ErrorHandler::renderGeneric() - Layout exists: " . (file_exists($layoutPath) ? 'yes' : 'no'));
        
        try {
            require $layoutPath;
            error_log("ErrorHandler::renderGeneric() - Layout included successfully");
        } catch (\Exception $e) {
            error_log("ErrorHandler::renderGeneric() - Exception while including layout: " . $e->getMessage());
            error_log("ErrorHandler::renderGeneric() - Exception file: " . $e->getFile() . " line: " . $e->getLine());
            throw $e;
        }
    }
    
    /**
     * Handle 404 Not Found errors
     */
    public static function handle404()
    {
        self::render(404);
    }
    
    /**
     * Handle 500 Internal Server errors
     */
    public static function handle500($exception = null)
    {
        $message = $exception ? $exception->getMessage() : null;
        self::render(500, $message);
    }
    
    /**
     * Handle 403 Forbidden errors
     */
    public static function handle403($message = null)
    {
        self::render(403, $message);
    }
    
    /**
     * Handle 401 Unauthorized errors
     */
    public static function handle401($message = null)
    {
        self::render(401, $message);
    }
}