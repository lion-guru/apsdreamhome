<?php
session_start();
include 'config.php';
require_role('Admin');
$msg = '';
// Offboarding: deactivate employee
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $emp_id = intval($_GET['deactivate']);
    $conn->query("UPDATE employees SET status='inactive' WHERE id=$emp_id");
    // Remove all roles
    $conn->query("DELETE FROM user_roles WHERE user_id=$emp_id");
    // Log offboarding
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $details = 'Offboarding: Employee ID ' . $emp_id . ' deactivated and roles revoked.';
    $stmt2 = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Offboarding', ?, ?)");
    $stmt2->bind_param('iss', $_SESSION['auser'], $details, $ip);
    $stmt2->execute();
    // Send notification
    require_once __DIR__ . '/../includes/functions/notification_util.php';
    addNotification($conn, 'Employee', 'Your access has been revoked due to offboarding.', $emp_id);
    $msg = 'Employee offboarded and access revoked.';
}
// List all employees
$employees = $conn->query("SELECT * FROM employees ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Employees</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Employees</h2><?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?><table class="table table-bordered"><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead><tbody><?php while($e = $employees->fetch_assoc()): ?><tr><td><?= htmlspecialchars($e['name']) ?></td><td><?= htmlspecialchars($e['email']) ?></td><td><?= htmlspecialchars($e['status']) ?></td><td><?php if($e['status']==='active'): ?><a href="employees.php?deactivate=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate and offboard this employee?')">Offboard</a><?php else: ?>Offboarded<?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
