<?php
// Role-based menu configuration
return [
    'admin' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => '/dash.php'],
        ['label' => 'Projects', 'icon' => 'fa fa-building', 'url' => '/admin/add_project.php'],
        ['label' => 'Users', 'icon' => 'fa fa-users', 'url' => '/admin/users.php'],
        ['label' => 'Logout', 'icon' => 'fa fa-sign-out', 'url' => '/logout.php'],
    ],
    'associate' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => '/associate_dashboard.php'],
        ['label' => 'My Team', 'icon' => 'fa fa-users', 'url' => '/team.php'],
        ['label' => 'Earnings', 'icon' => 'fa fa-money', 'url' => '/earnings.php'],
        ['label' => 'Profile', 'icon' => 'fa fa-user', 'url' => '/profile.php'],
        ['label' => 'Logout', 'icon' => 'fa fa-sign-out', 'url' => '/logout.php'],
    ],
    'user' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => '/user_dashboard.php'],
        ['label' => 'My Properties', 'icon' => 'fa fa-home', 'url' => '/property-listings.php'],
        ['label' => 'Profile', 'icon' => 'fa fa-user', 'url' => '/profile.php'],
        ['label' => 'Logout', 'icon' => 'fa fa-sign-out', 'url' => '/logout.php'],
    ],
    'builder' => [
        ['label' => 'Dashboard', 'icon' => 'fa fa-dashboard', 'url' => '/builder_dashboard.php'],
        ['label' => 'Projects', 'icon' => 'fa fa-building', 'url' => '/builder_projects.php'],
        ['label' => 'Profile', 'icon' => 'fa fa-user', 'url' => '/profile.php'],
        ['label' => 'Logout', 'icon' => 'fa fa-sign-out', 'url' => '/logout.php'],
    ],
];
