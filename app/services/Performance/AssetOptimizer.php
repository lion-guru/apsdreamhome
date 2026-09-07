<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - Asset Optimizer
 * Combines and minifies CSS/JS files for better performance
 */

class AssetOptimizer {
    private $assets_dir;
    private $cache_dir;
    
    public function __construct() {
        $this->assets_dir = __DIR__ . '/../assets';
        $this->cache_dir = __DIR__ . '/../cache/optimized';
        $this->ensureCacheDir();
    }
    
    private function ensureCacheDir() {
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    public function optimizeCSS($files, $output_name = 'combined.css') {
        $combined = '';
        $last_modified = 0;
        
        foreach ($files as $file) {
            $filepath = $this->assets_dir . '/css/' . $file;
            if (file_exists($filepath)) {
                $content = file_get_contents($filepath);
                // Remove comments and extra whitespace
                $content = $this->minifyCSS($content);
                $combined .= "/* === $file === */\n" . $content . "\n";
                $last_modified = max($last_modified, filemtime($filepath));
            }
        }
        
        $output_file = $this->cache_dir . '/' . $output_name;
        
        // Check if we need to regenerate
        if (file_exists($output_file) && filemtime($output_file) >= $last_modified) {
            return '/apsdreamhome/cache/optimized/' . $output_name;
        }
        
        file_put_contents($output_file, $combined);
        return '/apsdreamhome/cache/optimized/' . $output_name;
    }
    
    public function optimizeJS($files, $output_name = 'combined.js') {
        $combined = '';
        $last_modified = 0;
        
        foreach ($files as $file) {
            $filepath = $this->assets_dir . '/js/' . $file;
            if (file_exists($filepath)) {
                $content = file_get_contents($filepath);
                // Simple minification
                $content = $this->minifyJS($content);
                $combined .= "/* === $file === */\n" . $content . ";\n";
                $last_modified = max($last_modified, filemtime($filepath));
            }
        }
        
        $output_file = $this->cache_dir . '/' . $output_name;
        
        // Check if we need to regenerate
        if (file_exists($output_file) && filemtime($output_file) >= $last_modified) {
            return '/apsdreamhome/cache/optimized/' . $output_name;
        }
        
        file_put_contents($output_file, $combined);
        return '/apsdreamhome/cache/optimized/' . $output_name;
    }
    
    private function minifyCSS($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    '], '', $css);
        return $css;
    }
    
    private function minifyJS($js) {
        // Remove comments
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        // Remove whitespace (simple)
        $js = preg_replace('/\s+/', ' ', $js);
        $js = str_replace(["\r\n", "\r", "\n", "\t"], '', $js);
        return trim($js);
    }
    
    public function getOptimizedAssets() {
        return [
            'css' => $this->optimizeCSS([
                'bootstrap.min.css',
                'style.css',
                'custom.css'
            ], 'main.css'),
            'js' => $this->optimizeJS([
                'jquery.min.js',
                'bootstrap.bundle.min.js',
                'main.js'
            ], 'main.js')
        ];
    }
}

// Global optimizer instance
$optimizer = new AssetOptimizer();
?>
