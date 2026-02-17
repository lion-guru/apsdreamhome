<?php
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

// Handle new role creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_name'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token. Action blocked.');
    }
    $role_name = trim($_POST['role_name']);
    $role_desc = trim($_POST['role_desc'] ?? '');
    if ($role_name !== '') {
        try {
            $db->execute('INSERT INTO roles (name, description) VALUES (:name, :description)', [
                'name' => $role_name,
                'description' => $role_desc
            ]);
            header('Location: manage_roles.php?msg=Role added successfully');
            exit();
        } catch (Exception $e) {
            $error = "Error adding role: " . $e->getMessage();
        }
    }
}
// Fetch all roles
try {
    $roles = $db->fetchAll('SELECT * FROM roles ORDER BY id');
} catch (Exception $e) {
    $roles = [];
    $error = "Error fetching roles: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Roles</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Role Management</h2>
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= h($_GET['msg']) ?></div>
    <?php endif; ?>
    <form method="post" class="mb-4">
        <?php echo getCsrfField(); ?>
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="role_name" class="form-label">Role Name</label>
                <input type="text" class="form-control" id="role_name" name="role_name" required>
            </div>
            <div class="col-auto">
                <label for="role_desc" class="form-label">Description</label>
                <input type="text" class="form-control" id="role_desc" name="role_desc">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Add Role</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Name</th><th>Description</th></tr></thead>
        <tbody>
        <?php foreach($roles as $role): ?>
            <tr>
                <td><?= $role['id'] ?></td>
                <td><?= h($role['name']) ?></td>
                <td><?= h($role['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

