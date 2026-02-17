<?php
session_start();
if (isset($_SESSION['uid']) && $_SESSION['utype'] === 'employee') {
    header('Location: employee_dashboard.php');
    exit();
}
// Render employee login form
echo "<form method='POST'>Employee Login Form</form>";
