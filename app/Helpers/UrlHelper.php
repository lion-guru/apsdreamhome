<?php
/**
 * APS Dream Home - URL Helper Functions
 * Centralized URL generation and path management
 */

if (!function_exists('base_url')) {
    /**
     * Get base URL
     * @param string $path Optional path to append
     * @return string Complete URL
     */
    function base_url($path = '') {
        $baseUrl = BASE_URL;
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    /**
     * Get asset URL
     * @param string $asset Asset path
     * @return string Complete asset URL
     */
    function asset_url($asset) {
        return base_url('public/assets/' . ltrim($asset, '/'));
    }
}

if (!function_exists('route_url')) {
    /**
     * Get route URL
     * @param string $route Route name
     * @param array $params Route parameters
     * @return string Complete route URL
     */
    function route_url($route, $params = []) {
        $baseUrl = base_url();
        
        // Basic routing logic
        switch ($route) {
            case 'home':
                return $baseUrl;
            case 'properties':
                return $baseUrl . '/properties';
            case 'about':
                return $baseUrl . '/about';
            case 'contact':
                return $baseUrl . '/contact';
            case 'login':
                return $baseUrl . '/login';
            case 'register':
                return $baseUrl . '/register';
            case 'dashboard':
                return $baseUrl . '/dashboard';
            default:
                return $baseUrl . '/' . $route;
        }
    }
}

if (!function_exists('current_url')) {
    /**
     * Get current URL
     * @return string Current URL
     */
    function current_url() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . '://' . $host . $uri;
    }
}

if (!function_exists('is_current_page')) {
    /**
     * Check if current page matches given path
     * @param string $path Path to check
     * @return bool True if current page
     */
    function is_current_page($path) {
        $currentUri = $_SERVER['REQUEST_URI'];
        $targetPath = '/' . ltrim($path, '/');
        return strpos($currentUri, $targetPath) !== false;
    }
}

if (!function_exists('is_active_path')) {
    /**
     * Check if current path is active (for navigation)
     * @param string $path Path to check
     * @return string Active class if current page
     */
    function is_active_path($path) {
        return is_current_page($path) ? 'active' : '';
    }
}
?>