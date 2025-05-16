<?php
require_once(__DIR__ . '/includes/session_manager.php');
require_once(__DIR__ . '/../includes/db_config.php');
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$my_role = $_SESSION['admin_role'] ?? '';
$my_id = $_SESSION['admin_id'] ?? 0;

// Only allow edit if:
// - Super Admin can edit anyone
// - Admin can edit anyone except super_admin
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid admin ID');
}
$edit_id = (int)$_GET['id'];

$conn = getDbConnection();
$msg = $error = '';

// Fetch roles from roles table
$roles = [];
$res = $conn->query('SELECT name FROM roles');
while ($row = $res->fetch_assoc()) {
    $roles[] = $row['name'];
}

// Fetch admin details
$stmt = $conn->prepare('SELECT id, auser, aemail, apass, adob, aphone, role, status FROM admin WHERE id=?');
$stmt->bind_param('i', $edit_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die('Admin/Employee not found');
}

// Prevent admin from editing super_admin
if ($my_role === 'admin' && $user['role'] === 'super_admin') {
    die('You are not authorized to edit Super Admin');
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auser = $_POST['auser'] ?? $user['auser'];
    $aemail = $_POST['aemail'] ?? $user['aemail'];
    $adob = $_POST['adob'] ?? $user['adob'];
    $aphone = $_POST['aphone'] ?? $user['aphone'];
    $role = $_POST['role'] ?? $user['role'];
    $status = $_POST['status'] ?? $user['status'];
    $newpass = $_POST['newpass'] ?? '';
    $update_pass = false;
    if (!empty($newpass)) {
        $apass = password_hash($newpass, PASSWORD_DEFAULT);
        $update_pass = true;
    } else {
        $apass = $user['apass'];
    }
    // Prevent making anyone super_admin except by super_admin
    if ($my_role === 'admin' && $role === 'super_admin') {
        $role = $user['role']; // admin cannot promote anyone to super_admin
    }
    $stmt = $conn->prepare('UPDATE admin SET auser=?, aemail=?, apass=?, adob=?, aphone=?, role=?, status=? WHERE id=?');
    $stmt->bind_param('sssssssi', $auser, $aemail, $apass, $adob, $aphone, $role, $status, $edit_id);
    if ($stmt->execute()) {
        $msg = 'Admin/Employee updated successfully!';
        // Refresh user data
        $user = array_merge($user, compact('auser', 'aemail', 'adob', 'aphone', 'role', 'status'));
        if ($update_pass) $user['apass'] = $apass;
    } else {
        $error = 'Update failed!';
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Admin/Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Admin/Employee</h2>
    <?php if ($msg) echo '<div class="alert alert-success">' . htmlspecialchars($msg) . '</div>'; ?>
    <?php if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="auser" class="form-control" value="<?= htmlspecialchars($user['auser']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="aemail" class="form-control" value="<?= htmlspecialchars($user['aemail']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="adob" class="form-control" value="<?= htmlspecialchars($user['adob']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="aphone" class="form-control" value="<?= htmlspecialchars($user['aphone']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" <?= ($my_role === 'admin' && $user['role'] === 'super_admin' ? 'disabled' : '') ?>>
                <?php foreach ($roles as $r): ?>
                    <?php if ($my_role === 'admin' && $r === 'super_admin') continue; ?>
                    <option value="<?= htmlspecialchars($r) ?>" <?= ($user['role'] === $r ? 'selected' : '') ?>><?= htmlspecialchars($r) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($my_role === 'admin' && $user['role'] === 'super_admin'): ?>
                <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= ($user['status'] === 'active' ? 'selected' : '') ?>>Active</option>
                <option value="inactive" <?= ($user['status'] === 'inactive' ? 'selected' : '') ?>>Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password (leave blank to keep existing)</label>
            <input type="password" name="newpass" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="adminlist.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>
</body>
</html>
