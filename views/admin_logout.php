<?php
// Admin logout script
session_start();

// Destroy session
session_destroy();

// Redirect to login page
header('Location: ' . BASE_URL . 'admin/login');
exit;
?>
