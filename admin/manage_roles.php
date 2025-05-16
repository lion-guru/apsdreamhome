<?php
require_once __DIR__ . '/../includes/db_config.php';
$con = getDbConnection();

// Handle new role creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_name'])) {
    $role_name = trim($_POST['role_name']);
    $role_desc = trim($_POST['role_desc'] ?? '');
    if ($role_name !== '') {
        $stmt = $con->prepare('INSERT INTO roles (name, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $role_name, $role_desc);
        $stmt->execute();
        $stmt->close();
        header('Location: manage_roles.php?msg=Role added successfully');
        exit();
    }
}
// Fetch all roles
$roles = [];
$res = $con->query('SELECT * FROM roles ORDER BY id');
while ($row = $res && $row->fetch_assoc()) $roles[] = $row;
$con->close();
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
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <form method="post" class="mb-4">
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
                <td><?= htmlspecialchars($role['name']) ?></td>
                <td><?= htmlspecialchars($role['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
