<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Validator Proxy - APS Dream Home
 * Proxies legacy Validator class calls to the modern Core Validator.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Support\Validator as ModernValidator;

class Validator {
    private $validator;

    public function __construct($data = []) {
        $this->validator = new ModernValidator($data);
    }

    public function __call($name, $arguments) {
        if (method_exists($this->validator, $name)) {
            return call_user_func_array([$this->validator, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy Validator proxy or modern Core Validator.");
    }

    public static function __callStatic($name, $arguments) {
        return call_user_func_array([ModernValidator::class, $name], $arguments);
    }
}
