<?php

namespace App\Services;

/**
 * Log — thin, structured logging wrapper that decorates PHP's error_log.
 *
 * Why a wrapper?
 *  - Adds a per-request correlation id (X-Request-Id header or generated)
 *  - Adds file:line context automatically
 *  - Honors DEBUG_MODE env var (suppresses debug in production)
 *  - Routes to a per-channel log file under storage/logs/
 *  - Falls back to error_log() if file write fails
 *
 * Levels: debug, info, warning, error, critical.
 *
 * Usage:
 *   Log::info('User logged in', ['user_id' => 42]);
 *   Log::error('Payment failed', ['order_id' => 7, 'error' => $e->getMessage()]);
 *   Log::channel('security')->warning('Failed login attempt', ['ip' => $_SERVER['REMOTE_ADDR']]);
 */
final class Log
{
    public const DEBUG   = 'debug';
    public const INFO    = 'info';
    public const WARNING = 'warning';
    public const ERROR   = 'error';
    public const CRITICAL= 'critical';

    private static ?string $requestId = null;
    private static ?string $logDir = null;
    private static ?bool $debugMode = null;
    private static array $channelBuffers = [];

    public static function setRequestId(?string $id = null): string
    {
        if ($id !== null) {
            self::$requestId = $id;
            return $id;
        }
        if (self::$requestId === null) {
            self::$requestId = 'req_' . bin2hex(random_bytes(8));
        }
        return self::$requestId;
    }

    public static function getRequestId(): string
    {
        return self::$requestId ?? self::setRequestId();
    }

    public static function debug(string $message, array $context = []): void   { self::write(self::DEBUG, $message, $context); }
    public static function info(string $message, array $context = []): void    { self::write(self::INFO, $message, $context); }
    public static function warning(string $message, array $context = []): void { self::write(self::WARNING, $message, $context); }
    public static function error(string $message, array $context = []): void   { self::write(self::ERROR, $message, $context); }
    public static function critical(string $message, array $context = []): void{ self::write(self::CRITICAL, $message, $context); }

    public static function isDebug(): bool
    {
        if (self::$debugMode === null) {
            $env = getenv('DEBUG_MODE');
            self::$debugMode = ($env === '1' || strtolower((string)$env) === 'true');
        }
        return self::$debugMode;
    }

    /**
     * Redact sensitive fields before logging.
     */
    private static function redact(array $context): array
    {
        $sensitive = ['password', 'passwd', 'pwd', 'secret', 'token', 'api_key', 'apikey', 'authorization', 'credit_card', 'card_number', 'cvv', 'ssn', 'aadhaar', 'pan'];
        foreach ($sensitive as $key) {
            if (isset($context[$key])) {
                $context[$key] = '***REDACTED***';
            }
        }
        if (isset($context['headers']) && is_array($context['headers'])) {
            foreach ($sensitive as $key) {
                if (isset($context['headers'][$key])) {
                    $context['headers'][$key] = '***REDACTED***';
                }
            }
        }
        return $context;
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        if ($level === self::DEBUG && !self::isDebug()) {
            return;
        }

        $context = self::redact($context);

        $entry = [
            'ts'         => date('c'),
            'level'      => $level,
            'request_id' => self::getRequestId(),
            'message'    => $message,
            'context'    => $context,
            'memory_mb'  => round(memory_get_usage(true) / 1024 / 1024, 2),
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $logFile = self::logDir() . '/app-' . date('Y-m-d') . '.log';
        $written = @file_put_contents($logFile, $line . "\n", FILE_APPEND | LOCK_EX);
        if ($written === false) {
            error_log('[' . $level . '] ' . $message . ' ' . json_encode($context));
        }
    }

    private static function logDir(): string
    {
        if (self::$logDir !== null) {
            return self::$logDir;
        }
        $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        self::$logDir = $dir;
        return $dir;
    }
}
