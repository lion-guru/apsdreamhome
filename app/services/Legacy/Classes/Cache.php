<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Cache Proxy - APS Dream Home
 * Proxies legacy Cache class calls to the modern Core Cache.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Cache as ModernCache;

class Cache {
    private $cache;

    public function __construct() {
        $this->cache = new ModernCache();
    }

    public function __call($name, $arguments) {
        if (method_exists($this->cache, $name)) {
            return call_user_func_array([$this->cache, $name], $arguments);
        }
        throw new \Exception("Method {$name} not found in legacy Cache proxy or modern Core Cache.");
    }

    public static function __callStatic($name, $arguments) {
        return call_user_func_array([ModernCache::class, $name], $arguments);
    }
}
