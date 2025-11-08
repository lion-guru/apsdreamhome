<?php

namespace App\Core;

class Request {
    /**
     * Get the request method
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the request URI
     */
    public function uri() {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        return $uri ?: '/';
    }

    /**
     * Get all request data
     */
    public function all() {
        return array_merge($this->get(), $this->post(), $this->files());
    }

    /**
     * Get GET parameters
     */
    public function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get POST parameters
     */
    public function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get uploaded files
     */
    public function files($key = null) {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }

    /**
     * Get JSON input
     */
    public function json($key = null, $default = null) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }

    /**
     * Get request headers
     */
    public function headers($key = null, $default = null) {
        $headers = getallheaders();
        
        if ($key === null) {
            return $headers;
        }
        
        $key = str_replace('_', '-', strtoupper($key));
        $key = 'HTTP_' . str_replace('-', '_', $key);
        
        return $_SERVER[$key] ?? $default;
    }

    /**
     * Get the request IP address
     */
    public function ip() {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Check if the request is AJAX
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if the request is secure (HTTPS)
     */
    public function isSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Get the bearer token from the Authorization header
     */
    public function bearerToken() {
        $header = $this->headers('Authorization');
        
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        
        return null;
    }
}
