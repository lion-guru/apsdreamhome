<?php

namespace App\Services\Legacy;

/**
 * Legacy db_connection class for backward compatibility
 * Proxies to the modern App\Core\Database via App\Services\Legacy\Database
 */
class db_connection extends Database {
    public function __construct() {
        parent::__construct();
    }
}
