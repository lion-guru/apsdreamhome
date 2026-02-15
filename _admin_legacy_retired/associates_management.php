<?php
// associates_management.php
session_start();
require_once '../config.php'; // DB connection
require_once '../includes/csrf.php'; // CSRF protection
require_once __DIR__ . '/../includes/functions/notification_util.php'; // Notification utility
require_once __DIR__ . '/../includes/functions/permission_util.php'; // Permission utility
// Security: Only admin
if (!isset($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }
require_permission('manage_associates');

// Handle CRUD (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'associate_mgmt')) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');
        $post = trim($_POST['post'] ?? '');
        $parent_id = intval($_POST['parent_id'] ?? 0);
        $commission_percent = isset($_POST['commission_percent']) ? floatval($_POST['commission_percent']) : 0.0;
        $status = trim($_POST['status'] ?? 'active');
        $password = $_POST['password'] ?? '';
        if (!$name || !$email || !$phone || !$post || !$password) {
            $error = 'All fields are required.';
        } elseif (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
            $error = 'Invalid phone number.';
        } elseif ($commission_percent < 0 || $commission_percent > 20) {
            $error = 'Commission percent must be between 0 and 20.';
        } else {
            // Add/Edit logic (simplified, expand as needed)
            if (isset($_POST['edit_id']) && intval($_POST['edit_id']) > 0) {
                // Edit associate
                $stmt = $conn->prepare("UPDATE associates SET name=?, email=?, phone=?, post=?, parent_id=?, commission_percent=?, status=? WHERE associate_id=?");
                $stmt->bind_param("sssssdsi", $name, $email, $phone, $post, $parent_id, $commission_percent, $status, $_POST['edit_id']);
                $stmt->execute();
                $stmt->close();
            } else {
                // Add associate
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO associates (name, email, phone, post, parent_id, commission_percent, status, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssdss", $name, $email, $phone, $post, $parent_id, $commission_percent, $status, $hash);
                $stmt->execute();
                $stmt->close();
            }
            addNotification($conn, 'Associate', 'Associate upgraded or managed.', $_SESSION['auser'] ?? null);
            header('Location: associates_management.php');
            exit;
        }
    }
}
// Fetch associates for listing using prepared statement
$stmt = $conn->prepare("SELECT a.*, p.name AS parent_name FROM associates a LEFT JOIN associates p ON a.parent_id = p.associate_id");
$stmt->execute();
$associates = $stmt->get_result();
$stmt->close();

// Fetch all associates for parent selection using prepared statement
$stmt = $conn->prepare("SELECT associate_id, name FROM associates");
$stmt->execute();
$all = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Associates Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<div class="container mt-4">
    <h3>Associates Management</h3>
    <a href="#" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Associate</a>
    <table class="table table-bordered table-hover">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Post</th><th>Business</th><th>Parent</th><th>Commission %</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($row = $associates->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['post']); ?></td>
                <td><?php echo htmlspecialchars($row['business_volume']); ?></td>
                <td><?php echo htmlspecialchars($row['parent_name']); ?></td>
                <td><?php echo htmlspecialchars($row['commission_percent']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                    <a href="#" class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <!-- Add/Edit Modal (to be implemented) -->
    <div class="modal fade" id="addModal" tabindex="-1">
      <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRFProtection::generateToken('associate_mgmt')) ?>">
          <div class="modal-header"><h5 class="modal-title">Add Associate</h5></div>
          <div class="modal-body">
            <div class="mb-2"><label>Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control"></div>
            <div class="mb-2"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="mb-2"><label>Post</label><input type="text" name="post" class="form-control" required></div>
            <div class="mb-2"><label>Parent (Upline)</label><select name="parent_id" class="form-control">
              <option value="">--None--</option>
              <?php while($p = $all->fetch_assoc()): ?>
                <option value="<?= $p['associate_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endwhile; ?>
            </select></div>
            <div class="mb-2"><label>Commission %</label><input type="number" step="0.01" min="0" max="20" name="commission_percent" class="form-control" required></div>
            <div class="mb-2"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div>
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
        </form>
      </div></div>
    </div>
</div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
