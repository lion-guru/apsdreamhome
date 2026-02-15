<?php

/**
 * Role Permissions - Standardized Version
 */

require_once __DIR__ . '/core/init.php';

// Check if user has admin privileges
if (!isAdmin()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$db = \App\Core\App::database();

$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verifyCSRFToken($_POST["csrf_token"] ?? "")) {
        $msg = "Security validation failed. Please try again.";
        $msg_type = "danger";
    } else {
        $action_type = $_POST['action_type'] ?? 'assign';

        if ($action_type === 'assign') {
            $role_id = intval($_POST["role_id"]);
            $action = trim($_POST["action"]);
            $desc = trim($_POST["description"]);

            // Validate inputs
            if ($role_id <= 0) {
                $msg = "Invalid role selected.";
                $msg_type = "danger";
            } elseif (empty($action)) {
                $msg = "Action/Permission is required.";
                $msg_type = "danger";
            } else {
                // Check if permission already exists
                $perm = $db->fetchOne("SELECT id FROM permissions WHERE action = :action", ['action' => $action]);

                if (!$perm) {
                    // Create new permission
                    if ($db->execute("INSERT INTO permissions (action, description) VALUES (:action, :description)", [
                        'action' => $action,
                        'description' => $desc
                    ])) {
                        $perm_id = $db->lastInsertId();

                        // Log permission creation
                        logAdminActivity($db->getConnection(), "Permission Created", "Created permission: $action");
                    } else {
                        $msg = "Error creating permission.";
                        $msg_type = "danger";
                    }
                } else {
                    $perm_id = $perm['id'];
                }

                if (isset($perm_id)) {
                    // Assign permission to role
                    $exists = $db->fetchOne("SELECT 1 FROM role_permissions WHERE role_id = :role_id AND permission_id = :permission_id", [
                        'role_id' => $role_id,
                        'permission_id' => $perm_id
                    ]);

                    if (!$exists) {
                        if ($db->execute("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)", [
                            'role_id' => $role_id,
                            'permission_id' => $perm_id
                        ])) {
                            $msg = "Permission assigned to role successfully.";
                            $msg_type = "success";

                            // Log permission assignment
                            logAdminActivity($db->getConnection(), "Permission Assigned", "Assigned permission $action to role ID: $role_id");
                        } else {
                            $msg = "Error assigning permission.";
                            $msg_type = "danger";
                        }
                    } else {
                        $msg = "Role already has this permission.";
                        $msg_type = "info";
                    }
                }
            }
        } elseif ($action_type === 'delete') {
            $id = intval($_POST['id']);

            // Get details for logging before delete
            $assignment = $db->fetchOne("SELECT r.name as role_name, p.action 
                                       FROM role_permissions rp 
                                       JOIN roles r ON rp.role_id = r.id 
                                       JOIN permissions p ON rp.permission_id = p.id 
                                       WHERE rp.id = :id", ['id' => $id]);

            if ($assignment) {
                if ($db->execute("DELETE FROM role_permissions WHERE id = :id", ['id' => $id])) {
                    $msg = "Permission assignment removed successfully!";
                    $msg_type = "success";
                    logAdminActivity($db->getConnection(), "Permission Revoked", "Revoked permission '{$assignment['action']}' from role '{$assignment['role_name']}'");
                } else {
                    $msg = "Error removing permission assignment.";
                    $msg_type = "danger";
                }
            } else {
                $msg = "Assignment not found.";
                $msg_type = "warning";
            }
        }
    }
}

// Get all roles
$roles = $db->fetchAll("SELECT id, name FROM roles ORDER BY name");

// Get all permissions
$permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY action");

// List all role-permission assignments
$assignments = $db->fetchAll("SELECT rp.id, r.name as role_name, p.action, p.description 
                             FROM role_permissions rp 
                             JOIN roles r ON rp.role_id = r.id 
                             JOIN permissions p ON rp.permission_id = p.id 
                             ORDER BY r.name, p.action");

$page_title = "Role Permissions";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Role Permissions</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Permissions</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= h($msg_type) ?> alert-dismissible fade show">
                <?= h($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Assign Permission</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?= getCsrfField() ?>
                            <input type="hidden" name="action_type" value="assign">
                            <div class="mb-3">
                                <label class="form-label">Select Role</label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">Choose Role...</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= (int)$role['id'] ?>"><?= h($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permission Name (Action)</label>
                                <input type="text" name="action" class="form-control" placeholder="e.g., manage_users" required list="perm_list">
                                <datalist id="perm_list">
                                    <?php foreach ($permissions as $p): ?>
                                        <option value="<?= h($p['action']) ?>">
                                        <?php endforeach; ?>
                                </datalist>
                                <small class="text-muted">Slug format recommended (e.g., view_reports)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Assign Permission</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Active Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-nowrap custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Permission</th>
                                        <th>Description</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $a): ?>
                                        <tr>
                                            <td><span class="badge bg-info"><?= h($a['role_name']) ?></span></td>
                                            <td><code><?= h($a['action']) ?></code></td>
                                            <td><?= h($a['description'] ?: '-') ?></td>
                                            <td class="text-end">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item delete-btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" data-id="<?= (int)$a['id'] ?>" data-info="<?= h($a['role_name']) ?> - <?= h($a['action']) ?>"><i class="fas fa-trash-alt m-r-5"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($assignments)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No assignments found.</td>
                                        </tr>
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

<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="form-header">
                    <h3>Delete Permission Assignment</h3>
                    <p>Are you sure you want to remove <strong id="delete_info"></strong>?</p>
                </div>
                <div class="modal-btn delete-action">
                    <form method="POST">
                        <?= getCsrfField() ?>
                        <input type="hidden" name="action_type" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary continue-btn w-100">Delete</button>
                            </div>
                            <div class="col-6">
                                <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn w-100">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtns = document.querySelectorAll('.delete-btn');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.dataset.id;
                document.getElementById('delete_info').textContent = this.dataset.info;
            });
        });
    });
</script>

<?php include 'admin_footer.php'; ?>