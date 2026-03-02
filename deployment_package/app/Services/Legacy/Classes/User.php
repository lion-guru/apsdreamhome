<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy User Proxy - APS Dream Home
 * Proxies legacy User class calls to the modern Model.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User as ModernUser;

class User extends ModernUser {
    // This class now inherits from the modern Model
    // and can be used as a drop-in replacement.
}
