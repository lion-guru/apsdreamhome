<?php
/**
 * Admin Logout
 */
session_start();
session_destroy();
header('Location: admin_panel.php');
exit;
?>
