<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Session Manager Proxy
 * Standardizes session management across the application by proxying to the modern implementation.
 */

use App\Core\Session\SessionManager as CoreSessionManager;

class SessionManager extends CoreSessionManager {
    /**
     * Compatibility constructor
     */
    public function __construct() {
        parent::__construct();
        $this->start();
    }
}
