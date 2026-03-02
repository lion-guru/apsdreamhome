<?php

namespace App\Services\Legacy\Classes;
/**
 * Automated Notifier
 * Handles automated notifications based on system events and conditions
 */

use App\Services\AlertService;

class AutomatedNotifier {
    private $alertService;

    public function __construct() {
        $this->alertService = new AlertService();
    }

    /**
     * Process automated notifications
     */
    public function processAutomatedNotifications() {
        $this->alertService->processAutomatedNotifications();
    }

    /**
     * Proxy any other calls to AlertService
     */
    public function __call($name, $arguments) {
        if (method_exists($this->alertService, $name)) {
            return call_user_func_array([$this->alertService, $name], $arguments);
        }
        throw new \Exception("Method {$name} does not exist in AutomatedNotifier proxy.");
    }
}
?>
