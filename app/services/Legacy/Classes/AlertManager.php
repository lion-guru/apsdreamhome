<?php

namespace App\Services\Legacy\Classes;
/**
 * Alert Manager Proxy Class
 * Proxies calls to the modern AlertService for backward compatibility
 */
use App\Services\AlertService;

class AlertManager {
    private $alertService;

    public function __construct() {
        $this->alertService = new AlertService();
    }

    /**
     * Check system health and send alerts if needed
     */
    public function checkSystemHealth() {
        return $this->alertService->checkSystemHealth();
    }

    /**
     * Proxy any other methods to the modern service
     */
    public function __call($name, $arguments) {
        if (method_exists($this->alertService, $name)) {
            return call_user_func_array([$this->alertService, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy AlertManager proxy or modern AlertService.");
    }
}
