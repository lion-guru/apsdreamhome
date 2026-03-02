<?php

namespace App\Services\Legacy\Classes;
/**
 * Alert Escalation Proxy Class
 * Proxies calls to the modern AlertService for backward compatibility
 */
use App\Services\AlertService;

class AlertEscalation {
    private $alertService;

    public function __construct() {
        $this->alertService = new AlertService();
    }

    /**
     * Process alert escalations
     */
    public function processEscalations() {
        return $this->alertService->processEscalations();
    }

    /**
     * Proxy any other methods to the modern service
     */
    public function __call($name, $arguments) {
        if (method_exists($this->alertService, $name)) {
            return call_user_func_array([$this->alertService, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy AlertEscalation proxy or modern AlertService.");
    }
}
