<?php

namespace App\Core\Log;

/**
 * Standardized Logger Class
 * 
 * Provides static methods for logging to resolve IDE warnings and 
 * standardize logging across the application.
 */
class Logger
{
    /**
     * Log an error message
     * @param string $message
     * @param array $context
     */
    public static function error($message, array $context = [])
    {
        self::log('error', $message, $context);
    }

    /**
     * Log an info message
     * @param string $message
     * @param array $context
     */
    public static function info($message, array $context = [])
    {
        self::log('info', $message, $context);
    }

    /**
     * Log a debug message
     * @param string $message
     * @param array $context
     */
    public static function debug($message, array $context = [])
    {
        self::log('debug', $message, $context);
    }

    /**
     * Log a warning message
     * @param string $message
     * @param array $context
     */
    public static function warning($message, array $context = [])
    {
        self::log('warning', $message, $context);
    }

    /**
     * Generic log method
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function log($level, $message, array $context = [])
    {
        if (function_exists('log_message')) {
            log_message($message, $level, $context);
        } else {
            // Fallback to error_log
            error_log(sprintf("[%s] %s: %s %s", date('Y-m-d H:i:s'), strtoupper($level), $message, !empty($context) ? json_encode($context) : ''));
        }
    }
}
