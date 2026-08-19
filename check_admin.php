<?php
require_once 'c:/xampp/htdocs/apsdreamhome/vendor/autoload.php';

$user = \App\Models\User\User::where('username', 'admin')->first();
if ($user) {
    echo "Admin user found: " . $user->username . " - " . $user->email . "\n";
    echo "Password hash: " . $user->password . "\n";
} else {
    echo "Admin user not found\n";
}

// Also check for any admin users
$admins = \App\Models\User\User::where('role', 'admin')->get();
foreach ($admins as $admin) {
    echo "Admin: " . $admin->username . " - " . $admin->email . "\n";
}