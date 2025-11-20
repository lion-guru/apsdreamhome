<?php
/**
 * Asset Optimization Tool for APS Dream Home
 * Combines and minifies CSS/JS files for better performance
 */

class AssetOptimizer {
    private $css_files = [];
    private $js_files = [];
    private $output_dir = '/assets/optimized/';
    
    public function __construct() {
        $this->output_dir = __DIR__ . '/assets/optimized/';
        if (!is_dir($this->output_dir)) {
            mkdir($this->output_dir, 0755, true);
        }
    }
    
    /**
     * Add CSS file to optimization queue
     */
    public function addCSS($file_path) {
        if (file_exists($file_path)) {
            $this->css_files[] = $file_path;
        }
    }
    
    /**
     * Add JS file to optimization queue
     */
    public function addJS($file_path) {
        if (file_exists($file_path)) {
            $this->js_files[] = $file_path;
        }
    }
    
    /**
     * Minify CSS content
     */
    private function minifyCSS($content) {
        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        // Remove unnecessary whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        // Remove whitespace around specific characters
        $content = preg_replace('/\s*([{}:;,])\s*/', '$1', $content);
        // Remove trailing whitespace
        $content = trim($content);
        return $content;
    }
    
    /**
     * Minify JS content
     */
    private function minifyJS($content) {
        // Basic minification - remove comments and extra whitespace
        $content = preg_replace('/\/\*.*?\*\//s', '', $content); // Remove block comments
        $content = preg_replace('/\/\/.*$/m', '', $content); // Remove line comments
        $content = preg_replace('/\s+/', ' ', $content); // Collapse whitespace
        $content = trim($content);
        return $content;
    }
    
    /**
     * Combine and optimize CSS files
     */
    public function optimizeCSS($output_filename = 'optimized.css') {
        if (empty($this->css_files)) {
            return false;
        }
        
        $combined_content = "/* APS Dream Home - Optimized CSS Bundle\n * Generated: " . date('Y-m-d H:i:s') . "\n */\n\n";
        
        foreach ($this->css_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace(__DIR__, '', $file);
            $combined_content .= "/* Source: $relative_path */\n";
            $combined_content .= $this->minifyCSS($content) . "\n\n";
        }
        
        $output_path = $this->output_dir . $output_filename;
        file_put_contents($output_path, $combined_content);
        
        return $output_path;
    }
    
    /**
     * Combine and optimize JS files
     */
    public function optimizeJS($output_filename = 'optimized.js') {
        if (empty($this->js_files)) {
            return false;
        }
        
        $combined_content = "/* APS Dream Home - Optimized JS Bundle\n * Generated: " . date('Y-m-d H:i:s') . "\n */\n\n";
        
        foreach ($this->js_files as $file) {
            $content = file_get_contents($file);
            $relative_path = str_replace(__DIR__, '', $file);
            $combined_content .= "/* Source: $relative_path */\n";
            $combined_content .= $this->minifyJS($content) . "\n\n";
        }
        
        $output_path = $this->output_dir . $output_filename;
        file_put_contents($output_path, $combined_content);
        
        return $output_path;
    }
    
    /**
     * Generate optimized CSS bundle for homepage
     */
    public function generateHomepageCSSBundle() {
        $this->css_files = []; // Reset
        
        // Add local CSS files used on homepage
        $css_files = [
            '/assets/css/modern-design-system.css',
            '/assets/css/home.css',
            '/assets/css/modern-style.css',
            '/assets/css/custom-styles.css'
        ];
        
        foreach ($css_files as $file) {
            $this->addCSS(__DIR__ . $file);
        }
        
        return $this->optimizeCSS('homepage-bundle.min.css');
    }
    
    /**
     * Generate optimized JS bundle for homepage
     */
    public function generateHomepageJSBundle() {
        $this->js_files = []; // Reset
        
        // Add local JS files used on homepage
        $js_files = [
            '/assets/js/main.js',
            '/assets/js/custom.js'
        ];
        
        foreach ($js_files as $file) {
            $this->addJS(__DIR__ . $file);
        }
        
        return $this->optimizeJS('homepage-bundle.min.js');
    }
    
    /**
     * Get file size in KB
     */
    public function getFileSize($file_path) {
        if (file_exists($file_path)) {
            return round(filesize($file_path) / 1024, 2);
        }
        return 0;
    }
    
    /**
     * Compare original vs optimized sizes
     */
    public function getOptimizationReport() {
        $report = [
            'original_css_size' => 0,
            'optimized_css_size' => 0,
            'original_js_size' => 0,
            'optimized_js_size' => 0,
            'css_savings' => 0,
            'js_savings' => 0
        ];
        
        // Calculate original CSS size
        foreach ($this->css_files as $file) {
            $report['original_css_size'] += $this->getFileSize($file);
        }
        
        // Calculate original JS size
        foreach ($this->js_files as $file) {
            $report['original_js_size'] += $this->getFileSize($file);
        }
        
        // Get optimized sizes
        $optimized_css = $this->output_dir . 'homepage-bundle.min.css';
        $optimized_js = $this->output_dir . 'homepage-bundle.min.js';
        
        if (file_exists($optimized_css)) {
            $report['optimized_css_size'] = $this->getFileSize($optimized_css);
            $report['css_savings'] = round((($report['original_css_size'] - $report['optimized_css_size']) / $report['original_css_size']) * 100, 2);
        }
        
        if (file_exists($optimized_js)) {
            $report['optimized_js_size'] = $this->getFileSize($optimized_js);
            $report['js_savings'] = round((($report['original_js_size'] - $report['optimized_js_size']) / $report['original_js_size']) * 100, 2);
        }
        
        return $report;
    }
}

// Usage example
if (php_sapi_name() === 'cli') {
    echo "APS Dream Home Asset Optimizer\n";
    echo "================================\n\n";
    
    $optimizer = new AssetOptimizer();
    
    echo "Generating homepage CSS bundle...\n";
    $css_bundle = $optimizer->generateHomepageCSSBundle();
    if ($css_bundle) {
        echo "✓ CSS bundle created: " . basename($css_bundle) . "\n";
    }
    
    echo "\nGenerating homepage JS bundle...\n";
    $js_bundle = $optimizer->generateHomepageJSBundle();
    if ($js_bundle) {
        echo "✓ JS bundle created: " . basename($js_bundle) . "\n";
    }
    
    echo "\nOptimization Report:\n";
    $report = $optimizer->getOptimizationReport();
    echo "Original CSS: {$report['original_css_size']} KB\n";
    echo "Optimized CSS: {$report['optimized_css_size']} KB\n";
    echo "CSS Savings: {$report['css_savings']}%\n\n";
    
    echo "Original JS: {$report['original_js_size']} KB\n";
    echo "Optimized JS: {$report['optimized_js_size']} KB\n";
    echo "JS Savings: {$report['js_savings']}%\n";
}
?>