<?php
/**
 * Agent Registration View (proxy to core_register.php with selectedRole = agent)
 */
$selectedRole = $selectedRole ?? 'agent';
require __DIR__ . '/core_register.php';
