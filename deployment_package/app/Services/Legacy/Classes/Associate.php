<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy Associate Proxy - APS Dream Home
 * Proxies legacy Associate class calls to the modern Model.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Associate as ModernAssociate;

class Associate extends ModernAssociate {
    // This class now inherits from the modern Model
    // and can be used as a drop-in replacement.
}
