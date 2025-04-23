<?php
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$conn = getDbConnection();
// Handle form submission for new lead
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_lead'])) {
    $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, address, source, status, notes, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssi',
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['source'],
        $_POST['status'],
        $_POST['notes'],
        $_POST['assigned_to'] && $_POST['assigned_to'] !== '' ? intval($_POST['assigned_to']) : null
    );
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Lead added successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error adding lead: ' . htmlspecialchars($stmt->error) . '</div>';
    }
}
// Handle lead deletion
if (isset($_GET['delete'])) {
    $lead_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM leads WHERE lead_id = ?");
    $stmt->bind_param('i', $lead_id);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Lead deleted successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error deleting lead: ' . htmlspecialchars($stmt->error) . '</div>';
    }
}
// Fetch leads
$leads = [];
$result = $conn->query("SELECT * FROM leads ORDER BY lead_id DESC");
while ($row = $result && $result->fetch_assoc()) $leads[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Leads Management</title>
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
        <h1 class="h3">Leads Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal"><i class="fas fa-plus me-1"></i> Add Lead</button>
    </div>
    <?php if (!empty($msg)) echo $msg; ?>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)): ?>
                            <tr><td colspan="6" class="text-center">No leads found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lead['lead_id']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['status']); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo htmlspecialchars($lead['lead_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lead?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Add Lead Modal -->
    <div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addLeadModalLabel">Add Lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <input type="text" class="form-control" id="source" name="source">
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="New">New</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Lost">Lost</option>
                                <option value="Converted">Converted</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assigned To (User ID)</label>
                            <input type="number" class="form-control" id="assigned_to" name="assigned_to">
                        </div>
                        <input type="hidden" name="add_lead" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>