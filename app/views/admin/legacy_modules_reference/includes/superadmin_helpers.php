<?php
// Super Admin check helper
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'superadmin';
    }
}
