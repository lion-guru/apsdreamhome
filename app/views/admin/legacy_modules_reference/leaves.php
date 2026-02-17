<?php
/**
 * Leaves Management - Updated with Session Management
 */
require_once __DIR__ . "/core/init.php";

// Access Control
if (!hasPermission("manage_leaves")) {
    header("Location: login.php?error=access_denied");
    exit();
}
?>
