<?php
// Data Tables - Updated with Session Management
require_once __DIR__ . "/core/init.php";

// Authentication check (handled by core/init.php)
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}
