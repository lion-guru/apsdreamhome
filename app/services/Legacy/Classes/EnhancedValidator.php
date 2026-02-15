<?php

namespace App\Services\Legacy\Classes;
/**
 * Legacy EnhancedValidator Proxy - APS Dream Home
 * Proxies legacy EnhancedValidator class calls to the modern Core Validator.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Support\Validator as ModernValidator;

class EnhancedValidator extends ModernValidator {
    // This class now inherits from the modern Validator
    // and can be used as a drop-in replacement.
}
