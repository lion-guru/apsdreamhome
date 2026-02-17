<?php

namespace App\Core;

class View {
    /**
     * @var array Data to be passed to the view
     */
    protected $data = [];
    
    /**
     * @var string Layout file name
     */
    protected $layout = 'layouts/app';
    
    /**
     * @var array Sections content
     */
    protected $sections = [];
    
    /**
     * @var string Current section name
     */
    protected $currentSection = '';
    
    /**
     * Set the layout file
     */
    public function layout(string $layout): self {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Share data across all views
     */
    public function share($key, $value = null): self {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Render a view file
     */
    public function render(string $view, array $data = []) {
        // Merge shared data with view-specific data
        $data = array_merge($this->data, $data);
        
        // Start output buffering
        ob_start();
        
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found at path: {$viewPath}");
        }
        
        include $viewPath;
        
        // Get the view content
        $content = ob_get_clean();
        
        // If no layout is set, return the content directly
        if ($this->layout === false) {
            return $content;
        }
        
        // Render the layout with the content
        return $this->renderLayout($content);
    }
    
    /**
     * Render the layout with the given content
     */
    protected function renderLayout(string $content) {
        // Store the content in a variable accessible in the layout
        $view = $this;
        
        // Start output buffering
        ob_start();
        
        // Include the layout file
        $layoutPath = $this->getViewPath($this->layout);
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout [{$this->layout}] not found at path: {$layoutPath}");
        }
        
        include $layoutPath;
        
        // Return the final output
        return ob_get_clean();
    }
    
    /**
     * Start a section
     */
    public function section(string $name) {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End a section
     */
    public function endSection() {
        if (empty($this->currentSection)) {
            throw new \Exception('No section started');
        }
        
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = '';
    }
    
    /**
     * Get the content of a section
     */
    public function yield(string $section) {
        return $this->sections[$section] ?? '';
    }
    
    /**
     * Include a partial
     */
    public function include(string $view, array $data = []) {
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View [{$view}] not found at path: {$viewPath}");
        }
        
        // Extract data to variables
        extract(array_merge($this->data, $data));
        
        // Include the view file
        include $viewPath;
    }
    
    /**
     * Get the full path to a view file
     */
    protected function getViewPath(string $view): string {
        $view = str_replace('.', '/', $view);
        
        // Primary: Check in resources/views (modern system)
        $modernPath = '../resources/views/' . $view . '.php';
        if (file_exists($modernPath)) {
            return $modernPath;
        }
        
        // Secondary: Check for .blade.php extension
        $bladePath = '../resources/views/' . $view . '.blade.php';
        if (file_exists($bladePath)) {
            return $bladePath;
        }
        
        // Fallback: Check in src/Views (legacy system) during migration
        $legacyPath = '../src/Views/' . $view . '.php';
        if (file_exists($legacyPath)) {
            return $legacyPath;
        }
        
        // Final fallback: Check old app/views location
        $oldPath = '../app/views/' . $view . '.php';
        return $oldPath;
    }
    
    /**
     * Escape HTML special characters
     */
    public function e($value, $doubleEncode = true): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
    
    /**
     * Get the current URL
     */
    public function currentUrl(): string {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
               '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get the asset URL
     */
    public function asset(string $path): string {
        return asset($path);
    }
}
