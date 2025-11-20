<?php
/**
 * Local Asset Manager
 * Manages locally downloaded CDN assets to avoid CSP issues
 */

require_once 'download_cdn_assets.php';

class LocalAssetManager {
    private $downloader;
    private $localPaths;
    
    public function __construct() {
        $this->downloader = new CDNAssetDownloader();
        $this->localPaths = $this->downloader->generateLocalPaths();
    }
    
    /**
     * Get CSS assets as HTML link tags
     */
    public function getCSSLinks() {
        $links = [];
        foreach ($this->localPaths['css'] as $name => $path) {
            $links[] = '<link rel="stylesheet" href="' . $path . '">';
        }
        return implode("\n", $links);
    }
    
    /**
     * Get JavaScript assets as HTML script tags
     */
    public function getJSScripts() {
        $scripts = [];
        foreach ($this->localPaths['js'] as $name => $path) {
            $scripts[] = '<script src="' . $path . '"></script>';
        }
        return implode("\n", $scripts);
    }
    
    /**
     * Get individual CSS path
     */
    public function getCSSPath($name) {
        return isset($this->localPaths['css'][$name]) ? $this->localPaths['css'][$name] : null;
    }
    
    /**
     * Get individual JS path
     */
    public function getJSPath($name) {
        return isset($this->localPaths['js'][$name]) ? $this->localPaths['js'][$name] : null;
    }
    
    /**
     * Get all local paths for reference
     */
    public function getAllLocalPaths() {
        return $this->localPaths;
    }
    
    /**
     * Verify all local assets exist
     */
    public function verifyAssets() {
        $missing = [];
        
        foreach ($this->localPaths as $type => $assets) {
            foreach ($assets as $name => $path) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . parse_url($path, PHP_URL_PATH);
                if (!file_exists($fullPath)) {
                    $missing[] = $fullPath;
                }
            }
        }
        
        return $missing;
    }
}

// Helper function to get asset manager instance
function get_local_asset_manager() {
    return new LocalAssetManager();
}

// Example usage:
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $manager = new LocalAssetManager();
    
    echo "Local Asset Manager Test\n";
    echo "========================\n";
    
    echo "\nCSS Links:\n";
    echo $manager->getCSSLinks();
    
    echo "\n\nJS Scripts:\n";
    echo $manager->getJSScripts();
    
    echo "\n\nVerification:\n";
    $missing = $manager->verifyAssets();
    if (empty($missing)) {
        echo "✅ All assets verified successfully!\n";
    } else {
        echo "❌ Missing assets:\n";
        foreach ($missing as $asset) {
            echo "   - $asset\n";
        }
    }
}
?>