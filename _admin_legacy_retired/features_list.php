<?php
// List of all admin/superadmin features implemented and available for use
$features = [
    [
        'name' => 'Admin & Superadmin Login',
        'desc' => 'Secure login for admin, superadmin, and official employees with role-based access.'
    ],
    [
        'name' => 'Role Management',
        'desc' => 'Create, edit, and view all roles. Add new roles instantly usable for login and assignment.'
    ],
    [
        'name' => 'Employee Onboarding',
        'desc' => 'Add new employees, assign roles, and send onboarding notifications.'
    ],
    [
        'name' => 'Dynamic Role Assignment',
        'desc' => 'Assign any existing or newly created role to an employee during onboarding.'
    ],
    [
        'name' => 'Official Employee Login',
        'desc' => 'All employees with an official role can log in to the admin panel.'
    ],
    [
        'name' => 'Audit Log',
        'desc' => 'All onboarding and critical actions are logged for security.'
    ],
    [
        'name' => 'Notification System',
        'desc' => 'Send system notifications to employees on onboarding or other events.'
    ],
    // Add more features here as they are implemented
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel Features List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2>Implemented Features (Admin/Superadmin)</h2>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Feature</th><th>Description</th></tr></thead>
        <tbody>
        <?php foreach($features as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['name']) ?></td>
                <td><?= htmlspecialchars($f['desc']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="alert alert-info mt-4">
        <b>Note:</b> Naye features yahan add kiye ja sakte hain. Har nayi functionality implement hone par is list ko update karte rahen.
    </div>
</div>
</body>
</html>
