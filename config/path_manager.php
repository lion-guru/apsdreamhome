<?php
/**
 * APS Dream Home - Universal Path Manager
 * Handles all project URLs and paths with consistency
 */

class PathManager {
    private static $baseUrl = 'http://localhost/apsdreamhome';
    private static $projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
    private static $basePath = '/apsdreamhome/';
    
    /**
     * Get project base URL
     */
    public static function getBaseUrl() {
        return self::$baseUrl;
    }
    
    /**
     * Get project root path
     */
    public static function getProjectRoot() {
        return self::$projectRoot;
    }
    
    /**
     * Get base path for URLs
     */
    public static function getBasePath() {
        return self::$basePath;
    }
    
    /**
     * Helper function for base URL generation
     */
    public static function base_url($path = '') {
        return self::buildUrl($path);
    }
    
    /**
     * Build internal URL with correct base path
     */
    public static function buildUrl($path) {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // If already has base path, return as is
        if (strpos($path, 'apsdreamhome') === 0) {
            return self::$baseUrl . '/' . $path;
        }
        
        // Add base path
        return self::$baseUrl . '/' . $path;
    }
    
    /**
     * Build API endpoint URL
     */
    public static function buildApiUrl($endpoint) {
        return self::buildUrl('config/' . $endpoint);
    }
    
    /**
     * Build file path within project
     */
    public static function buildPath($relativePath) {
        return self::$projectRoot . '\\' . str_replace('/', '\\', $relativePath);
    }
    
    /**
     * Get MCP system URLs (updated for MVC structure)
     */
    public static function getMcpUrls() {
        return [
            'dashboard' => self::buildUrl('mcp_dashboard'),
            'configuration' => self::buildUrl('mcp_configuration_gui'),
            'import' => self::buildUrl('import_mcp_config'),
            'import_handler' => self::buildUrl('import_mcp_config_handler'),
            'startup' => self::buildUrl('start_mcp_servers'),
            'server_manager' => self::buildApiUrl('mcp_server_manager'),
            'database_integration' => self::buildApiUrl('mcp_database_integration'),
            'backup' => self::buildUrl('config/restore_backup'),
            'backup_backend' => self::buildApiUrl('restore_backup_backend')
        ];
    }
    
    /**
     * Get app system URLs
     */
    public static function getAppUrls() {
        return [
            'home' => self::buildUrl('index.php'),
            'properties' => self::buildUrl('index.php/properties'),
            'projects' => self::buildUrl('index.php/projects'),
            'about' => self::buildUrl('index.php/about'),
            'contact' => self::buildUrl('index.php/contact'),
            'careers' => self::buildUrl('index.php/careers'),
            'testimonials' => self::buildUrl('index.php/testimonials'),
            'faq' => self::buildUrl('index.php/faq'),
            'admin' => self::buildUrl('index.php/admin'),
            'user_dashboard' => self::buildUrl('index.php/dashboard')
        ];
    }
    
    /**
     * Get file paths
     */
    public static function getFilePaths() {
        return [
            'mcp_config' => self::buildPath('config/mcp_servers.json'),
            'backup_manifest' => self::buildPath('backups/backup_manifest.json'),
            'logs_dir' => self::buildPath('logs'),
            'config_dir' => self::buildPath('config'),
            'app_dir' => self::buildPath('app'),
            'views_dir' => self::buildPath('app/views'),
            'controllers_dir' => self::buildPath('app/Http/Controllers'),
            'models_dir' => self::buildPath('app/Models'),
            'core_dir' => self::buildPath('app/Core')
        ];
    }
    
    /**
     * Validate if URL is internal to project
     */
    public static function isInternalUrl($url) {
        return strpos($url, self::$baseUrl) === 0 || strpos($url, self::$basePath) === 0;
    }
    
    /**
     * Convert relative URL to absolute
     */
    public static function makeAbsolute($url) {
        if (self::isInternalUrl($url)) {
            return $url;
        }
        
        return self::buildUrl($url);
    }
    
    /**
     * Get current protocol and host
     */
    public static function getCurrentHost() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * Detect if running in CLI vs web
     */
    public static function isCli() {
        return php_sapi_name() === 'cli';
    }
    
    /**
     * Get appropriate base URL for current environment
     */
    public static function getEnvironmentBaseUrl() {
        if (self::isCli()) {
            return self::$baseUrl;
        }
        
        return self::getCurrentHost() . self::$basePath;
    }
    
    /**
     * Generate JavaScript URL configuration
     */
    public static function generateJsConfig() {
        $urls = array_merge(self::getMcpUrls(), self::getAppUrls());
        
        return [
            'baseUrl' => self::getEnvironmentBaseUrl(),
            'basePath' => self::$basePath,
            'apiBase' => self::buildApiUrl(''),
            'urls' => $urls,
            'paths' => self::getFilePaths()
        ];
    }
    
    /**
     * Output JavaScript configuration
     */
    public static function outputJsConfig() {
        $config = self::generateJsConfig();
        echo '<script>';
        echo 'window.APS_CONFIG = ' . json_encode($config) . ';';
        echo '</script>';
    }
}

// Helper functions for global use
function base_url($path = '') {
    return PathManager::buildUrl($path);
}

function api_url($endpoint = '') {
    return PathManager::buildApiUrl($endpoint);
}

function project_path($relativePath = '') {
    return PathManager::buildPath($relativePath);
}

function is_internal_url($url) {
    return PathManager::isInternalUrl($url);
}
?>
