<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();
$userId = $_SESSION['auser_id'] ?? 0;

$success = '';
$error = '';

// Submit approval request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role_id'], $_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $user_id = intval($_POST['user_id']);
        $role_id = intval($_POST['role_id']);
        $action = $_POST['action'];
        
        $sql = "INSERT INTO role_change_approvals (user_id, role_id, action, requested_by, status, requested_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
        if ($db->execute($sql, [$user_id, $role_id, $action, $userId])) {
            logAdminActivity($userId, 'role_change_request', "Submitted role change request for user ID: $user_id to role ID: $role_id");
            $success = "Approval request submitted successfully.";
        } else {
            $error = "Error submitting request.";
        }
    }
}

// Approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval_id'], $_POST['decision'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $approval_id = intval($_POST['approval_id']);
        $decision = $_POST['decision'];
        
        $sql = "UPDATE role_change_approvals SET status=?, decided_by=?, decided_at=NOW() WHERE id=?";
        if ($db->execute($sql, [$decision, $userId, $approval_id])) {
            logAdminActivity($userId, 'role_change_decision', "Recorded $decision decision for approval ID: $approval_id");
            $success = "Decision recorded successfully.";
        } else {
            $error = "Error updating status.";
        }
    }
}

$pending = $db->fetchAll("SELECT rca.*, e.name as user, r.name as role FROM role_change_approvals rca JOIN employees e ON rca.user_id=e.id JOIN roles r ON rca.role_id=r.id WHERE rca.status='pending'");
$page_title = "Role Change Approvals";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?= h($page_title) ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Role Change Approvals</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= h($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= h($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h4 class="card-title mb-0 fw-bold">Pending Approval Requests</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Target Role</th>
                                        <th>Action Type</th>
                                        <th>Requested By</th>
                                        <th>Requested At</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pending)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No pending role change requests found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pending as $row): ?>
                                            <tr>
                                                <td class="fw-medium"><?= h($row['user']) ?></td>
                                                <td><span class="badge bg-info"><?= h($row['role']) ?></span></td>
                                                <td><span class="text-uppercase small fw-bold"><?= h($row['action']) ?></span></td>
                                                <td><?= h($row['requested_by']) ?></td>
                                                <td><?= h(date('M d, Y H:i', strtotime($row['requested_at']))) ?></td>
                                                <td><span class="badge bg-warning"><?= h($row['status']) ?></span></td>
                                                <td class="text-end">
                                                    <form method="POST" class="d-inline-block">
                                                        <?= getCsrfField() ?>
                                                        <input type="hidden" name="approval_id" value="<?= (int)$row['id'] ?>">
                                                        <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success shadow-sm me-1">
                                                            <i class="fas fa-check me-1"></i> Approve
                                                        </button>
                                                        <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger shadow-sm">
                                                            <i class="fas fa-times me-1"></i> Reject
                                                        </button>
                                                    </form>
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
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

