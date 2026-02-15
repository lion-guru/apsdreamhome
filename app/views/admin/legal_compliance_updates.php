<?php
/**
 * Legal Compliance Updates - Updated with Session Management
 */
require_once __DIR__ . "/core/init.php";

// Access Control
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}
?>
