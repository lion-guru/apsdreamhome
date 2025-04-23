<?php
session_start();
include 'config.php';
require_role('Admin');
$roles = $conn->query("SELECT id, name FROM roles ORDER BY name");
$permissions = $conn->query("SELECT * FROM permissions ORDER BY action");
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = $_POST['role_id'];
    $action = $_POST['action'];
    $desc = $_POST['description'];
    // Add permission if not exists
    $stmt = $conn->prepare("SELECT id FROM permissions WHERE action=?");
    $stmt->bind_param('s', $action);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt2 = $conn->prepare("INSERT INTO permissions (action, description) VALUES (?, ?)");
        $stmt2->bind_param('ss', $action, $desc);
        $stmt2->execute();
        $perm_id = $stmt2->insert_id;
    } else {
        $stmt->bind_result($perm_id);
        $stmt->fetch();
    }
    // Assign permission to role
    $exists = $conn->query("SELECT 1 FROM role_permissions WHERE role_id=$role_id AND permission_id=$perm_id")->num_rows;
    if (!$exists) {
        $conn->query("INSERT INTO role_permissions (role_id, permission_id) VALUES ($role_id, $perm_id)");
        $msg = 'Permission assigned to role.';
    } else {
        $msg = 'Role already has this permission.';
    }
}
// List all role-permission assignments
$assignments = $conn->query("SELECT r.name as role, p.action, p.description FROM role_permissions rp JOIN roles r ON rp.role_id=r.id JOIN permissions p ON rp.permission_id=p.id ORDER BY r.name, p.action");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Role Permissions</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Role Permissions</h2><?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?><form method="POST"><div class="mb-3"><label>Role</label><select name="role_id" class="form-control" required><?php while($r = $roles->fetch_assoc()): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Action (Permission)</label><input type="text" name="action" class="form-control" required placeholder="e.g. approve_leave"></div><div class="mb-3"><label>Description</label><input type="text" name="description" class="form-control" placeholder="Optional"></div><button type="submit" class="btn btn-success">Assign Permission</button></form><h4 class="mt-5">Current Role-Permission Assignments</h4><table class="table table-bordered"><thead><tr><th>Role</th><th>Action</th><th>Description</th></tr></thead><tbody><?php while($a = $assignments->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['role']) ?></td><td><?= htmlspecialchars($a['action']) ?></td><td><?= htmlspecialchars($a['description']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
