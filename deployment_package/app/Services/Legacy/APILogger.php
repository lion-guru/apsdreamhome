<?php

namespace App\Services\Legacy;
/**
 * APILogger Class
 * Provides centralized logging for API and system activities
 */
class APILogger {
    private $logDir;

    public function __construct() {
        $this->logDir = __DIR__ . '/../logs/';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * General log method
     */
    public function log($message, $level = 'info', $category = 'system') {
        $logFile = $this->logDir . $category . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Log error message
     */
    public function error($message) {
        $this->log($message, 'error', 'error');
    }

    /**
     * Log info message with optional context
     */
    public function info($message, $context = []) {
        $msg = $message;
        if (!empty($context)) {
            $msg .= ' ' . json_encode($context);
        }
        $this->log($msg, 'info', 'info');
    }

    /**
     * Log warning message
     */
    public function warning($message, $category = 'system') {
        $this->log($message, 'warning', $category);
    }

    /**
     * Log foreclosure attempt specifically
     */
    public function logForeclosureAttempt($emiPlanId, $status, $message, $amount = 0.0, $details = []) {
        $logData = [
            'emi_plan_id' => $emiPlanId,
            'status' => $status,
            'message' => $message,
            'amount' => $amount,
            'details' => $details
        ];
        $this->log("Foreclosure Attempt: " . json_encode($logData), $status === 'success' ? 'info' : 'error', 'foreclosure');
    }
}
