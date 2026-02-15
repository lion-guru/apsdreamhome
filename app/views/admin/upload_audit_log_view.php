<?php
// Upload Audit Log View - Updated with Session Management
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/includes/geoip_utils.php';

// Role-based access: Only superadmins can export or drilldown
$is_superadmin = isSuperAdmin();
if ((isset($_GET['export']) || isset($_GET['drilldown'])) && !$is_superadmin) {
    http_response_code(403);
    exit('Access denied: Only superadmins can export or drilldown.');
}
?>
