<?php
/**
 * Associate Registration View (proxy to core_register.php with selectedRole = associate)
 */
$selectedRole = $selectedRole ?? 'associate';
require __DIR__ . '/core_register.php';
