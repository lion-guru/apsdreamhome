<?php
/**
 * CSRF Protection Wrapper for Admin Portal
 * Redirects to central security system
 */

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/security/csrf.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/app/services/Security/Legacy/CSRFProtection.php';

// The CSRFProtection class is now defined in includes/security/csrf.php
// which is included above. No further definitions needed here.
?>
