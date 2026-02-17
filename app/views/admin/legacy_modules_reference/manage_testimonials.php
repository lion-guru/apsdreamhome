<?php
define('IN_ADMIN', true);
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = 'Manage Testimonials';
$error = '';
$success = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO testimonials (client_name, testimonial, client_photo, status) VALUES (?, ?, ?, ?)";
            if ($db->execute($sql, [$_POST['client_name'], $_POST['testimonial'], $_POST['client_photo'], $_POST['status']])) {
                $success = "Testimonial added successfully!";
            } else {
                $error = "Failed to add testimonial.";
            }
        } elseif ($_POST['action'] === 'edit') {
            $sql = "UPDATE testimonials SET client_name=?, testimonial=?, client_photo=?, status=? WHERE id=?";
            if ($db->execute($sql, [$_POST['client_name'], $_POST['testimonial'], $_POST['client_photo'], $_POST['status'], $_POST['id']])) {
                $success = "Testimonial updated successfully!";
            } else {
                $error = "Failed to update testimonial.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM testimonials WHERE id=?";
            if ($db->execute($sql, [$_POST['id']])) {
                $success = "Testimonial deleted successfully!";
            } else {
                $error = "Failed to delete testimonial.";
            }
        }
    }
}

// Fetch all testimonials
$testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY id DESC");

include 'admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="h3 mb-0 text-gray-800">Manage Testimonials</h2>
        </div>
        <div class="col text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                <i class="fas fa-plus"></i> Add New Testimonial
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
            <h6 class="m-0 font-weight-bold text-primary">Testimonials List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Client Name</th>
                            <th>Testimonial</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No testimonials found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($testimonials as $t): ?>
                                <tr>
                                    <td class="text-center">
                                        <img src="<?= h($t['client_photo']) ?>" alt="<?= h($t['client_name']) ?>" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='../assets/images/placeholder.jpg'">
                                    </td>
                                    <td><?= h($t['client_name']) ?></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?= h($t['testimonial']) ?>">
                                            <?= h($t['testimonial']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $t['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= h(ucfirst($t['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info edit-btn"
                                                data-id="<?= (int)$t['id'] ?>"
                                                data-client_name="<?= h($t['client_name']) ?>"
                                                data-testimonial="<?= h($t['testimonial']) ?>"
                                                data-client_photo="<?= h($t['client_photo']) ?>"
                                                data-status="<?= h($t['status']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#editTestimonialModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this testimonial?');">
                                            <?= getCsrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
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

<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1" aria-labelledby="addTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTestimonialModalLabel">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Testimonial</label>
                        <textarea name="testimonial" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Photo Path/URL</label>
                        <input type="text" name="client_photo" class="form-control" placeholder="/assets/images/testimonials/client.jpg">
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
                    <button type="submit" class="btn btn-primary">Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Testimonial Modal -->
<div class="modal fade" id="editTestimonialModal" tabindex="-1" aria-labelledby="editTestimonialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTestimonialModalLabel">Edit Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" id="edit_client_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Testimonial</label>
                        <textarea name="testimonial" id="edit_testimonial" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Photo Path/URL</label>
                        <input type="text" name="client_photo" id="edit_client_photo" class="form-control">
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
                    <button type="submit" class="btn btn-primary">Update Testimonial</button>
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
            document.getElementById('edit_client_name').value = this.dataset.client_name;
            document.getElementById('edit_testimonial').value = this.dataset.testimonial;
            document.getElementById('edit_client_photo').value = this.dataset.client_photo;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>
