<?php
// property_approvals.php - Admin property approval workflow
session_start();
require_once '../config.php';
require_once __DIR__ . '/../includes/functions/notification_util.php';
require_once __DIR__ . '/includes/session_manager.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_permission('manage_inventory');
global $con;
$conn = $con;
$msg = $error = '';

// Handle approve/reject actions
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE properties SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    addNotification($conn, 'Property', 'Property approved.', $_SESSION['auser'] ?? null);
    $msg = 'Property approved successfully!';
}
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE properties SET status='rejected' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    addNotification($conn, 'Property', 'Property rejected.', $_SESSION['auser'] ?? null);
    $msg = 'Property rejected.';
}

// Fetch pending properties using prepared statement
$pending = [];
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE status='pending' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Property Approvals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Pending Property Approvals</h1>
    </div>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending)): ?>
                            <tr><td colspan="6" class="text-center">No pending properties.</td></tr>
                        <?php else: foreach ($pending as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['pname']) ?></td>
                                <td><?= htmlspecialchars($p['ptype']) ?></td>
                                <td><?= htmlspecialchars($p['location']) ?></td>
                                <td>₹<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <a href="?approve=<?= $p['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this property?');"><i class="fas fa-check"></i> Approve</a>
                                    <a href="?reject=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this property?');"><i class="fas fa-times"></i> Reject</a>
                                    <a href="propertyview.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
