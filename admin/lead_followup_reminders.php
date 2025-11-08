<?php
// lead_followup_reminders.php: List leads needing follow-up and allow admin to mark as contacted/followed-up
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$conn = getDbConnection();

// Mark lead as followed up
if (isset($_GET['followup']) && is_numeric($_GET['followup'])) {
    $lead_id = intval($_GET['followup']);
    $stmt = $conn->prepare('UPDATE leads SET status = ? WHERE lead_id = ?');
    $status = 'Contacted';
    $stmt->bind_param('si', $status, $lead_id);
    $stmt->execute();
    header('Location: lead_followup_reminders.php?msg=Lead+marked+as+Contacted.');
    exit();
}

// Find leads that are new or have not been followed up (status = 'New' or 'Qualified')
$leads = [];
$result = $conn->query("SELECT * FROM leads WHERE status IN ('New', 'Qualified') ORDER BY lead_id DESC");
while ($row = $result && $result->fetch_assoc()) $leads[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Lead Follow-up Reminders</title>
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
        <h1 class="h3">Leads Needing Follow-up</h1>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
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
                            <tr><td colspan="6" class="text-center">No leads need follow-up.</td></tr>
                        <?php else: foreach ($leads as $lead): ?>
                            <tr>
                                <td><?= htmlspecialchars($lead['lead_id']) ?></td>
                                <td><?= htmlspecialchars($lead['name']) ?></td>
                                <td><?= htmlspecialchars($lead['email']) ?></td>
                                <td><?= htmlspecialchars($lead['phone']) ?></td>
                                <td><?= htmlspecialchars($lead['status']) ?></td>
                                <td>
                                    <a href="?followup=<?= htmlspecialchars($lead['lead_id']) ?>" class="btn btn-success btn-sm" onclick="return confirm('Mark this lead as Contacted?');">
                                        <i class="fas fa-check"></i> Mark as Contacted
                                    </a>
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
