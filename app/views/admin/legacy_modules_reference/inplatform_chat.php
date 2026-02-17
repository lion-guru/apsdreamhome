<?php
/**
 * In-Platform Chat - Updated with Session Management
 */
require_once __DIR__ . "/core/init.php";

// Access Control
if (!isAuthenticated()) {
    header("Location: index.php?error=access_denied");
    exit();
}
?>
