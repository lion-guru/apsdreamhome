<?php
// payout_slabs.php
session_start();
require_once '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

// Handle CRUD (add/edit/delete)
// ... (Form handling logic will be added here)

$slabs = $conn->query("SELECT * FROM payout_slabs ORDER BY min_business ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payout Slabs Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'includes/admin_sidebar.php'; ?>
<?php include_once '../includes/csrf.php'; ?>
<div class="container mt-4">
    <h3>Payout Slabs Management</h3>
    <a href="#" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSlabModal">Add Payout Slab</a>
    <table class="table table-bordered table-hover">
        <thead><tr><th>Post</th><th>Business Range</th><th>Percent (%)</th><th>Reward</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($row = $slabs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['post']) ?></td>
                <td><?= number_format($row['min_business']) ?> - <?= number_format($row['max_business']) ?></td>
                <td><?= htmlspecialchars($row['percent']) ?></td>
                <td><?= htmlspecialchars($row['reward']) ?></td>
                <td>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Add/Edit Modal (to be implemented) -->
    <div class="modal fade" id="addSlabModal" tabindex="-1">
      <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRFProtection::generateToken('payout_slab')) ?>">
          <div class="modal-header"><h5 class="modal-title">Add Payout Slab</h5></div>
          <div class="modal-body">
            <div class="mb-2"><label>Post</label><input type="text" name="post" class="form-control" required></div>
            <div class="mb-2"><label>Min Business (₹)</label><input type="number" step="0.01" name="min_business" class="form-control" required></div>
            <div class="mb-2"><label>Max Business (₹)</label><input type="number" step="0.01" name="max_business" class="form-control" required></div>
            <div class="mb-2"><label>Percent (%)</label><input type="number" step="0.01" name="percent" class="form-control" required></div>
            <div class="mb-2"><label>Reward</label><input type="text" name="reward" class="form-control"></div>
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
        </form>
      </div></div>
    </div>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'payout_slab')) {
            $error = 'Invalid session. Please refresh and try again.';
        } else {
            $post = trim($_POST['post'] ?? '');
            $min_business = floatval($_POST['min_business'] ?? 0);
            $max_business = floatval($_POST['max_business'] ?? 0);
            $percent = floatval($_POST['percent'] ?? 0);
            $reward = trim($_POST['reward'] ?? '');
            if (!$post || $min_business < 0 || $max_business < $min_business || $percent <= 0) {
                $error = 'All fields are required and must be valid.';
            } else {
                // (existing payout slab add/edit logic)
            }
        }
    }
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
