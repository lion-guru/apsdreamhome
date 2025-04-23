<?php
session_start();
include 'config.php';
require_role('Admin');
$users = $conn->query("SELECT id, name FROM employees ORDER BY name");
$roles = $conn->query("SELECT id, name FROM roles ORDER BY name");
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $role_id = $_POST['role_id'];
    // Prevent duplicate role assignment
    $exists = $conn->query("SELECT 1 FROM user_roles WHERE user_id=$user_id AND role_id=$role_id")->num_rows;
    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $user_id, $role_id);
        $stmt->execute();
        // Log audit trail
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $action = 'Role Assignment';
        $details = "Assigned role ID $role_id to user ID $user_id";
        $stmt2 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param('isss', $_SESSION['auser'], $action, $details, $ip);
        $stmt2->execute();
        // Send notification
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Role', 'Role assigned: ' . $role_id . ' to user ' . $user_id, $_SESSION['auser']);
        $msg = 'Role assigned successfully.';
    } else {
        $msg = 'User already has this role.';
    }
}
// Remove role assignment
if (isset($_GET['remove_user']) && isset($_GET['remove_role'])) {
    $user_id = intval($_GET['remove_user']);
    $role_id = intval($_GET['remove_role']);
    $conn->query("DELETE FROM user_roles WHERE user_id=$user_id AND role_id=$role_id");
    // Log audit trail
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $action = 'Role Removal';
    $details = "Removed role ID $role_id from user ID $user_id";
    $stmt2 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param('isss', $_SESSION['auser'], $action, $details, $ip);
    $stmt2->execute();
    // Send notification
    require_once __DIR__ . '/../includes/functions/notification_util.php';
    addNotification($conn, 'Role', 'Role removed: ' . $role_id . ' from user ' . $user_id, $_SESSION['auser']);
    header('Location: manage_user_roles.php?msg=' . urlencode('Role removed successfully.'));
    exit();
}
// List all user-role assignments
$assignments = $conn->query("SELECT e.id as user_id, e.name as user, r.id as role_id, r.name as role FROM user_roles ur JOIN employees e ON ur.user_id=e.id JOIN roles r ON ur.role_id=r.id ORDER BY e.name, r.name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage User Roles</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Manage User Roles</h2><?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?><form method="POST"><div class="mb-3"><label>Employee</label><select name="user_id" class="form-control" required><?php while($u = $users->fetch_assoc()): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Role</label><select name="role_id" class="form-control" required><?php while($r = $roles->fetch_assoc()): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endwhile; ?></select></div><button type="submit" class="btn btn-success">Assign Role</button></form><h4 class="mt-5">Current Assignments</h4><table class="table table-bordered"><thead><tr><th>User</th><th>Role</th><th>Action</th></tr></thead><tbody><?php while($a = $assignments->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['user']) ?></td><td><?= htmlspecialchars($a['role']) ?></td><td><a href="manage_user_roles.php?remove_user=<?= $a['user_id'] ?>&remove_role=<?= $a['role_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this role from user?')">Remove</a></td></tr><?php endwhile; ?></tbody></table></div></body></html>
