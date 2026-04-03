<?php
/**
 * APS Dream Home - Root Entry Point (XAMPP)
 * Routes all requests through public/index.php
 * The router handles /apsdreamhome prefix stripping
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simply include the main entry point
// The router's dispatch() handles /apsdreamhome prefix stripping
require_once __DIR__ . '/public/index.php';
