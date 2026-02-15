<?php
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_permission('manage_customers');
$conn = $con;
$customers = [];
if ($conn) {
    $result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
    while ($row = $result && $result->fetch_assoc()) $customers[] = $row;
    $conn->close();
}
?>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
    </style>
</head>
<body>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Customer Management</h1>
        <a href="add_customer.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Add Customer</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr><td colspan="6" class="text-center">No customers found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td><?php echo $c['id']; ?></td>
                                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($c['city']); ?></td>
                                    <td>
                                        <a href="edit_customer.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <a href="delete_customer.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php 
require_once __DIR__ . '/../includes/functions/notification_util.php';
addNotification($conn, 'Customer', 'Customer KYC status updated or managed.', $_SESSION['auser'] ?? null);
?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
