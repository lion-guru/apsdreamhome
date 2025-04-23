<?php
session_start();
if (!isset($_SESSION['access_token']) || !$_SESSION['access_token']) {
    // Not logged in, redirect to login page
    header('Location: google_login.php');
    exit;
}
?>
