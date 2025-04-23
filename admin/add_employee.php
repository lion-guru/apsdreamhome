<?php
session_start();
include 'config.php';
require_role('Admin');
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : null;
    $stmt = $conn->prepare("INSERT INTO employees (name, email, status) VALUES (?, ?, 'active')");
    $stmt->bind_param('ss', $name, $email);
    if ($stmt->execute()) {
        $emp_id = $stmt->insert_id;
        // Assign default role if provided
        if ($role_id) {
            $conn->query("INSERT INTO user_roles (user_id, role_id) VALUES ($emp_id, $role_id)");
        }
        // Log onboarding
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $details = 'Onboarding: Employee ' . $name . ' (ID: ' . $emp_id . ')';
        $stmt2 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Onboarding', ?, ?)");
        $stmt2->bind_param('iss', $_SESSION['auser'], $details, $ip);
        $stmt2->execute();
        // Send notification
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Employee', 'Welcome to the system! Your access has been set up.', $emp_id);
        $msg = 'Employee onboarded successfully.';
    } else {
        $msg = 'Error: ' . htmlspecialchars($stmt->error);
    }
}
// Get all roles
$roles = $conn->query("SELECT id, name FROM roles ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Employee</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Add New Employee</h2><?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?><form method="POST"><div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div><div class="mb-3"><label>Default Role</label><select name="role_id" class="form-control"><option value="">-- Select Role --</option><?php while($r = $roles->fetch_assoc()): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option><?php endwhile; ?></select></div><button type="submit" class="btn btn-success">Add Employee</button></form></div></body></html>
