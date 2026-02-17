<?php
// payout_slabs.php
require_once __DIR__ . '/core/init.php';
use App\Core\Database;

if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();
$error = '';
$success = '';

// Handle CRUD (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $post_name = trim($_POST['post'] ?? '');
        $min_business = floatval($_POST['min_business'] ?? 0);
        $max_business = floatval($_POST['max_business'] ?? 0);
        $percent = floatval($_POST['percent'] ?? 0);
        $reward = trim($_POST['reward'] ?? '');

        if (!$post_name || $min_business < 0 || $max_business < $min_business || $percent <= 0) {
            $error = 'All fields are required and must be valid.';
        } else {
            try {
                $db->execute("INSERT INTO payout_slabs (post, min_business, max_business, percent, reward) VALUES (:post, :min_business, :max_business, :percent, :reward)", 
                            ['post' => $post_name, 'min_business' => $min_business, 'max_business' => $max_business, 'percent' => $percent, 'reward' => $reward]);
                $success = 'Payout slab added successfully.';
            } catch (Exception $e) {
                $error = 'Error adding payout slab: ' . $e->getMessage();
            }
        }
    }
}

$slabs = $db->fetchAll("SELECT * FROM payout_slabs ORDER BY min_business ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payout Slabs Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="container mt-4">
    <h3>Payout Slabs Management</h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= h($success) ?></div>
    <?php endif; ?>

    <a href="#" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSlabModal">Add Payout Slab</a>
    <table class="table table-bordered table-hover">
        <thead><tr><th>Post</th><th>Business Range</th><th>Percent (%)</th><th>Reward</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($slabs as $row): ?>
            <tr>
                <td><?= h($row['post']) ?></td>
                <td><?= number_format((float)$row['min_business']) ?> - <?= number_format((float)$row['max_business']) ?></td>
                <td><?= number_format((float)$row['percent'], 2) ?>%</td>
                <td><?= h($row['reward']) ?></td>
                <td>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="modal fade" id="addSlabModal" tabindex="-1">
      <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="">
          <?= getCsrfField() ?>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
