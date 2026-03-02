<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Authentication Proxy - APS Dream Home
 * Proxies legacy Authentication class calls to the modern UnifiedAuthService.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Auth\UnifiedAuthService;

class Authentication {
    private $authService;

    public function __construct() {
        $this->authService = new UnifiedAuthService();
    }

    public function __call($name, $arguments) {
        if (method_exists($this->authService, $name)) {
            return call_user_func_array([$this->authService, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy Authentication proxy or modern UnifiedAuthService.");
    }

    public static function __callStatic($name, $arguments) {
        $instance = new self();
        return $instance->__call($name, $arguments);
    }
}
