<?php
// Role and access helper for APS Dream Homes
// session_start(); // Remove this if present

function getCurrentUserRole() {
    if (isset($_SESSION['utype'])) {
        return $_SESSION['utype'];
    } elseif (isset($_SESSION['usertype'])) {
        return $_SESSION['usertype'];
    } elseif (isset($_SESSION['aid'])) {
        return 'admin';
    }
    return null;
}

function getCurrentUserId() {
    if (isset($_SESSION['uid'])) {
        return $_SESSION['uid'];
    } elseif (isset($_SESSION['aid'])) {
        return $_SESSION['aid'];
    }
    return null;
}

function redirectToDashboardByRole() {
    $role = getCurrentUserRole();
    switch ($role) {
        case 'superadmin':
            header('Location: /march2025apssite/admin/super-admin-dashboard.php');
            exit();
        case 'admin':
            header('Location: /march2025apssite/admin/dashboard.php');
            exit();
        case 'associate':
        case 'assosiate':
            if (file_exists(__DIR__.'/../../associate_dashboard.php')) {
                header('Location: /march2025apssite/associate_dashboard.php');
            } else {
                header('Location: /march2025apssite/customer_dashboard.php');
            }
            exit();
        case 'user':
        case 'customer':
            header('Location: /march2025apssite/user_dashboard.php');
            exit();
        case 'builder':
            header('Location: /march2025apssite/builder_dashboard.php');
            exit();
        default:
            header('Location: /march2025apssite/login.php');
            exit();
    }
}

function enforceRole($roles) {
    $role = getCurrentUserRole();
    if (!in_array($role, (array)$roles)) {
        header('Location: /login.php');
        exit();
    }
}

if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        return getCurrentUserRole() !== null;
    }
}
