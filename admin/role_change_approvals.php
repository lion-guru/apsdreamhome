<?php
session_start();
include 'config.php';
require_role('Admin');
// Submit approval request
if (isset($_POST['user_id'], $_POST['role_id'], $_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $role_id = intval($_POST['role_id']);
    $action = $_POST['action'];
    $conn->query("INSERT INTO role_change_approvals (user_id, role_id, action, requested_by, status, requested_at) VALUES ($user_id, $role_id, '$action', {$_SESSION['auser']}, 'pending', NOW())");
}
// Approve/reject
if (isset($_POST['approval_id'], $_POST['decision'])) {
    $approval_id = intval($_POST['approval_id']);
    $decision = $_POST['decision'];
    $conn->query("UPDATE role_change_approvals SET status='$decision', decided_by={$_SESSION['auser']}, decided_at=NOW() WHERE id=$approval_id");
}
$pending = $conn->query("SELECT rca.*, e.name as user, r.name as role FROM role_change_approvals rca JOIN employees e ON rca.user_id=e.id JOIN roles r ON rca.role_id=r.id WHERE rca.status='pending'");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Role Change Approvals</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Role Change Approvals</h2><table class='table table-bordered'><thead><tr><th>User</th><th>Role</th><th>Action</th><th>Requested By</th><th>Status</th><th>Action</th></tr></thead><tbody><?php while($row = $pending->fetch_assoc()): ?><tr><td><?= htmlspecialchars($row['user']) ?></td><td><?= htmlspecialchars($row['role']) ?></td><td><?= htmlspecialchars($row['action']) ?></td><td><?= htmlspecialchars($row['requested_by']) ?></td><td><?= htmlspecialchars($row['status']) ?></td><td><form method='post'><input type='hidden' name='approval_id' value='<?= $row['id'] ?>'><button name='decision' value='approved' class='btn btn-success btn-sm'>Approve</button> <button name='decision' value='rejected' class='btn btn-danger btn-sm'>Reject</button></form></td></tr><?php endwhile; ?></tbody></table></div></body></html>
