<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$users = $conn->query("SELECT id, name FROM employees WHERE status='active' ORDER BY name");
$roles = $conn->query("SELECT id, name FROM roles ORDER BY name");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $role_id = $_POST['role_id'];
    $stmt = $conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $role_id);
    if ($stmt->execute()) {
        header('Location: roles.php?msg=' . urlencode('Role assigned successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Assign Role</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Assign Role to Employee</h2><form method="POST"><div class="mb-3"><label>Employee</label><select name="user_id" class="form-control" required><?php while($u = $users->fetch_assoc()): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Role</label><select name="role_id" class="form-control" required><?php while($r = $roles->fetch_assoc()): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endwhile; ?></select></div><button type="submit" class="btn btn-success">Assign Role</button></form></div></body></html>
