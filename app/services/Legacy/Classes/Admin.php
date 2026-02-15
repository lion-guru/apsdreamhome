<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Admin Proxy - APS Dream Home
 * Proxies legacy Admin class calls to the modern Model.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Admin as ModernAdmin;

class Admin extends ModernAdmin {
    // This class now inherits from the modern Model
    // and can be used as a drop-in replacement.
}
