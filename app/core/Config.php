<?php
/**
 * Legacy Config/Env Loader - Shim for backward compatibility
 * 
 * This file is deprecated. Please use App\Core\App to bootstrap the application.
 */

use Dotenv\Dotenv;

// Ensure environment variables are loaded if not already
if (!isset($_ENV['APP_ENV']) && file_exists(dirname(__DIR__, 2) . '/.env') && class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();
    } catch (\Exception $e) {
        // Ignore errors if .env cannot be loaded here
    }
}

// Load the modern helper file if functions are not defined
if (!function_exists('env')) {
    require_once __DIR__ . '/../Helpers/env.php';
}

// Legacy Env class for backward compatibility
if (!class_exists('Env')) {
    class Env
    {
        public static function get($key, $default = null)
        {
            return $_ENV[$key] ?? getenv($key) ?: $default;
        }

        public static function load() 
        { 
            // No-op, handled by Dotenv
        }
        
        public static function set($key, $value) 
        { 
            $_ENV[$key] = $value; 
        }
        
        public static function has($key) 
        { 
            return isset($_ENV[$key]); 
        }
        
        public static function all() 
        { 
            return $_ENV; 
        }
    }
}

// Legacy config functions (wrapped to prevent redefinition)

if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        // If App class is available and initialized, use it
        if (class_exists('App\Core\App')) {
            try {
                $app = \App\Core\App::getInstance();
                if ($app) {
                    if (is_null($key)) {
                        return $app;
                    }
                    return $app->config($key) ?? $default;
                }
            } catch (\Exception $e) {
                // App instance might not be ready
            }
        }
        
        // Fallback to Env
        if (class_exists('Env')) {
            return Env::get($key, $default);
        }
        
        return $default;
    }
}

if (!function_exists('email_config')) {
    function email_config()
    {
        return [
            'smtp_host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'smtp_port' => env('MAIL_PORT', 587),
            'smtp_username' => env('MAIL_USERNAME', ''),
            'smtp_password' => env('MAIL_PASSWORD', ''),
            'smtp_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@apsdreamhome.com'),
            'from_name' => env('MAIL_FROM_NAME', 'APS Dream Home'),
            'admin_email' => env('ADMIN_EMAIL', 'admin@apsdreamhome.com')
        ];
    }
}

if (!function_exists('db_config')) {
    function db_config()
    {
        return [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'apsdreamhome'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }
}
