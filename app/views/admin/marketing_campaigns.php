<?php
/**
 * Marketing Campaigns - Updated with Session Management
 */
require_once __DIR__ . "/core/init.php";

// Access control is handled by core/init.php for non-public pages.
// But we can double check if needed.
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}
?>
