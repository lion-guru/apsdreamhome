<?php
/**
 * APS Dream Home - SQL Injection Detector
 */

namespace App\Security;

class SQLInjectionDetector
{
    private static $instance = null;
    private $patterns;

    private function __construct()
    {
        $this->initializePatterns();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializePatterns()
    {
        $this->patterns = [
            // SQL keywords
            '/(union|select|insert|update|delete|drop|create|alter|exec|script|truncate)/i',
            // SQL comments
            '/(\/\*|\*\/|--|#)/i',
            // SQL operators
            '/(or|and|not|like|between|in|exists|is|null)/i',
            // SQL functions
            '/(concat|substring|char|ascii|ord|length|user|database|version)/i',
            // SQL syntax
            '/(\"|\'|\\|;|xp_|sp_|0x[0-9a-f]+)/i',
            // Boolean-based injection
            '/(1=1|1=2|true|false)/i',
            // Time-based injection
            '/(sleep|benchmark|waitfor)/i'
        ];
    }

    public function detect($input)
    {
        if (is_array($input)) {
            return $this->detectArray($input);
        }

        return $this->detectString($input);
    }

    private function detectArray($input)
    {
        foreach ($input as $key => $value) {
            if ($this->detect($value)) {
                return true;
            }
        }
        return false;
    }

    private function detectString($input)
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    public function getThreatLevel($input)
    {
        $threats = 0;
        $highRiskPatterns = ['/(union|select|insert|update|delete|drop|create|alter|exec|script)/i', '/(\/\*|\*\/|--|#)/i'];
        $mediumRiskPatterns = ['/(or|and|not|like|between|in|exists|is|null)/i', '/(\"|\'|\\|;|xp_|sp_|0x[0-9a-f]+)/i'];

        foreach ($highRiskPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats += 3;
            }
        }

        foreach ($mediumRiskPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats += 2;
            }
        }

        if ($threats >= 5) {
            return 'HIGH';
        } elseif ($threats >= 2) {
            return 'MEDIUM';
        } elseif ($threats >= 1) {
            return 'LOW';
        }

        return 'NONE';
    }

    public function logAttempt($input, $threatLevel)
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'input' => $input,
            'threat_level' => $threatLevel,
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];

        $logFile = BASE_PATH . '/logs/sql_injection_attempts.log';
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
