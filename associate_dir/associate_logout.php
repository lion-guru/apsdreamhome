<?php
/**
 * Associate Logout System
 * APS Dream Homes
 */

session_start();

// Destroy associate session
if (isset($_SESSION['associate_logged_in'])) {
    unset($_SESSION['associate_logged_in']);
    unset($_SESSION['associate_id']);
    unset($_SESSION['associate_name']);
    unset($_SESSION['associate_mobile']);
    unset($_SESSION['associate_level']);
    unset($_SESSION['associate_status']);
}

// Destroy session completely
session_destroy();

// Redirect to login page
header("Location: associate_login.php?message=logged_out");
exit();
?>
