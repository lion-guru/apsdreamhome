<?php
// Verify Archive - Updated with Session Management
require_once __DIR__ . '/core/init.php';

if (!isSuperAdmin()) { http_response_code(403); exit('Access denied.'); }
?>
