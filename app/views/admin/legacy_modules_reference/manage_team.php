<?php
define('IN_ADMIN', true);
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Manage Team Members';
$error = '';
$success = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO team (name, designation, bio, photo, status) VALUES (?, ?, ?, ?, ?)";
            if ($db->execute($sql, [$_POST['name'], $_POST['designation'], $_POST['bio'], $_POST['photo'], $_POST['status']])) {
                $success = "Team member added successfully!";
            } else {
                $error = "Failed to add team member.";
            }
        } elseif ($_POST['action'] === 'edit') {
            $sql = "UPDATE team SET name=?, designation=?, bio=?, photo=?, status=? WHERE id=?";
            if ($db->execute($sql, [$_POST['name'], $_POST['designation'], $_POST['bio'], $_POST['photo'], $_POST['status'], $_POST['id']])) {
                $success = "Team member updated successfully!";
            } else {
                $error = "Failed to update team member.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM team WHERE id=?";
            if ($db->execute($sql, [$_POST['id']])) {
                $success = "Team member deleted successfully!";
            } else {
                $error = "Failed to delete team member.";
            }
        }
    }
}

// Fetch all team members
$team = $db->fetchAll("SELECT * FROM team ORDER BY id DESC");

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="h3 mb-0 text-gray-800">Manage Team Members</h2>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                <i class="fas fa-plus"></i> Add New Member
            </button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= h($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= h($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Team Members List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Bio</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($team)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No team members found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($team as $m): ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="<?= h($m['photo']) ?>" alt="<?= h($m['name']) ?>" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../assets/images/placeholder.jpg'">
                                    </td>
                                    <td><?= h($m['name']) ?></td>
                                    <td><?= h($m['designation']) ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="<?= h($m['bio']) ?>">
                                            <?= h($m['bio']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $m['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= h(ucfirst($m['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info edit-btn"
                                                data-id="<?= (int)$m['id'] ?>"
                                                data-name="<?= h($m['name']) ?>"
                                                data-designation="<?= h($m['designation']) ?>"
                                                data-bio="<?= h($m['bio']) ?>"
                                                data-photo="<?= h($m['photo']) ?>"
                                                data-status="<?= h($m['status']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#editTeamModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            <?= getCsrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
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

<!-- Add Team Member Modal -->
<div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTeamModalLabel">Add New Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo Path/URL</label>
                        <input type="text" name="photo" class="form-control" placeholder="/assets/images/team/member.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Member Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="editTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeamModalLabel">Edit Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" id="edit_designation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" id="edit_bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo Path/URL</label>
                        <input type="text" name="photo" id="edit_photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_designation').value = this.dataset.designation;
            document.getElementById('edit_bio').value = this.dataset.bio;
            document.getElementById('edit_photo').value = this.dataset.photo;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>
