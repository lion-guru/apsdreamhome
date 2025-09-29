<?php

namespace App\Core;

class Response {
    /**
     * @var int HTTP status code
     */
    protected $statusCode = 200;

    /**
     * @var array Response headers
     */
    protected $headers = [];

    /**
     * Set the HTTP status code
     */
    public function setStatusCode(int $code): self {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set a response header
     */
    public function header(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Send a JSON response
     */
    public function json($data, int $statusCode = null) {
        if ($statusCode) {
            $this->setStatusCode($statusCode);
        }

        $this->header('Content-Type', 'application/json');
        $this->sendHeaders();
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to a different URL
     */
    public function redirect(string $url, int $statusCode = 302) {
        $this->setStatusCode($statusCode);
        $this->header('Location', $url);
        $this->sendHeaders();
        exit;
    }

    /**
     * Send the response with the given view and data
     */
    public function view(string $view, array $data = []) {
        $this->sendHeaders();
        
        // Extract data to variables for the view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = '../app/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found");
        }
        
        include $viewPath;
        
        // Get the view content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
        exit;
    }

    /**
     * Send a file download response
     */
    public function download(string $filePath, string $name = null, array $headers = []) {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            return $this;
        }

        $name = $name ?? basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $fileSize = filesize($filePath);

        // Set headers
        $this->header('Content-Type', $mimeType);
        $this->header('Content-Disposition', 'attachment; filename="' . $name . '"');
        $this->header('Content-Length', $fileSize);
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');

        // Add custom headers
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }

        $this->sendHeaders();
        
        // Output file
        readfile($filePath);
        exit;
    }

    /**
     * Send all headers
     */
    protected function sendHeaders() {
        // Set the status code
        http_response_code($this->statusCode);
        
        // Set all headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }
}
