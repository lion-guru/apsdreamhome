<?php

namespace App\Core;

/**
 * View Renderer - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Simple and efficient view rendering system
 */
class ViewRenderer
{
    private $viewPath;
    private $data = [];

    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../views/';
    }

    /**
     * Render a view with data
     */
    public function render($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->viewPath . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }

        // Extract data variables for use in view
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewFile;
        
        // Get the buffered content and clean buffer
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Set view data
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get view data
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if view exists
     */
    public function exists($view)
    {
        $viewFile = $this->viewPath . str_replace('.', '/', $view) . '.php';
        return file_exists($viewFile);
    }
}
