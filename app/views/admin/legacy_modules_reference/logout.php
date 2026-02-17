<?php
require_once __DIR__ . '/core/init.php';

$password_changed = isset($_GET['password_changed']) && $_GET['password_changed'] == 1;

// If password was just changed, set a session message before destroying the session
// Wait, destroyAuthSession() clears everything. If we want to keep a message, 
// we should use a flash message or a GET parameter.
// The code already uses a GET parameter for redirect.

destroyAuthSession();

// Redirect to login page with message if password was changed
$redirect = 'index.php' . ($password_changed ? '?password_changed=1' : '');
header('Location: ' . $redirect);
exit();
