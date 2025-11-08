<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "session.save_path: " . ini_get('session.save_path') . "<br>";
session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 'hello';
}
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";
?>
