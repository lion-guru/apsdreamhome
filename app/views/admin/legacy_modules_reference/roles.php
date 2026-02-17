<?php
/**
 * Roles Management - Standardized Version
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

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = "Security validation failed.";
        $msg_type = "danger";
    } else {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            if ($db->execute("INSERT INTO roles (name) VALUES (:name)", ['name' => $name])) {
                $msg = "Role added successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error adding role.";
                $msg_type = "danger";
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            if ($db->execute("UPDATE roles SET name = :name WHERE id = :id", [
                'name' => $name,
                'id' => $id
            ])) {
                $msg = "Role updated successfully!";
                $msg_type = "success";
            } else {
                $msg = "Error updating role.";
                $msg_type = "danger";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            // Check if role is in use
            $in_use = $db->fetchOne("SELECT COUNT(*) as count FROM admins WHERE role_id = :id", ['id' => $id]);
            if ($in_use['count'] > 0) {
                $msg = "Cannot delete role. It is currently assigned to users.";
                $msg_type = "warning";
            } else {
                if ($db->execute("DELETE FROM roles WHERE id = :id", ['id' => $id])) {
                    $msg = "Role deleted successfully!";
                    $msg_type = "success";
                } else {
                    $msg = "Error deleting role.";
                    $msg_type = "danger";
                }
            }
        }
    }
}

// Get all roles
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY id");

$page_title = "Roles Management";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Roles Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="fa fa-plus"></i> Add Role
                    </button>
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
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-nowrap custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Role Name</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td><?= (int)$role['id'] ?></td>
                                            <td><?= h($role['name']) ?></td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item edit-role" href="#" 
                                                           data-id="<?= (int)$role['id'] ?>" 
                                                           data-name="<?= h($role['name']) ?>"
                                                           data-bs-toggle="modal" data-bs-target="#editRoleModal">
                                                            <i class="fa fa-pencil m-r-5"></i> Edit
                                                        </a>
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                            <?= getCsrfField() ?>
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?= (int)$role['id'] ?>">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fa fa-trash-o m-r-5"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_role_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" id="edit_role_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editLinks = document.querySelectorAll('.edit-role');
    editLinks.forEach(link => {
        link.addEventListener('click', function() {
            document.getElementById('edit_role_id').value = this.dataset.id;
            document.getElementById('edit_role_name').value = this.dataset.name;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>

