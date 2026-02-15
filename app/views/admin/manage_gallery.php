require_once __DIR__ . '/core/init.php';

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();

$msg = '';
$error = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO gallery (image_path, caption, status) VALUES (?, ?, ?)";
            if ($db->execute($sql, [$_POST['image_path'], $_POST['caption'], $_POST['status']])) {
                $msg = "Gallery image added successfully!";
            } else {
                $error = "Error adding image.";
            }
        } elseif ($_POST['action'] === 'edit') {
            $sql = "UPDATE gallery SET image_path=?, caption=?, status=? WHERE id=?";
            if ($db->execute($sql, [$_POST['image_path'], $_POST['caption'], $_POST['status'], $_POST['id']])) {
                $msg = "Gallery image updated successfully!";
            } else {
                $error = "Error updating image.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM gallery WHERE id=?";
            if ($db->execute($sql, [$_POST['id']])) {
                $msg = "Gallery image deleted successfully!";
            } else {
                $error = "Error deleting image.";
            }
        }
    }
}

// Fetch all gallery images
$gallery = $db->fetchAll("SELECT * FROM gallery ORDER BY id DESC");

$page_title = "Manage Gallery";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Manage Gallery</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                        <i class="fas fa-plus"></i> Add New Image
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= h($msg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= h($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php foreach($gallery as $img): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="position-relative">
                                        <img src="<?= h($img['image_path']) ?>" class="card-img-top" alt="<?= h($img['caption']) ?>" style="height: 200px; object-fit: cover;" onerror="this.src='../assets/images/placeholder.jpg'">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-<?= $img['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst(h($img['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text text-truncate" title="<?= h($img['caption']) ?>">
                                            <?= $img['caption'] ? h($img['caption']) : '<em class="text-muted">No caption</em>' ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <button class="btn btn-sm btn-outline-info edit-image" 
                                                    data-id="<?= (int)$img['id'] ?>" 
                                                    data-image_path="<?= h($img['image_path']) ?>" 
                                                    data-caption="<?= h($img['caption']) ?>" 
                                                    data-status="<?= h($img['status']) ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                <?= getCsrfField() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$img['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($gallery)): ?>
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No images found in the gallery.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addImageModalLabel">Add New Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Image Path</label>
                        <input type="text" name="image_path" class="form-control" placeholder="/assets/images/gallery/example.jpg" required>
                        <small class="text-muted">Enter the relative path to the image file.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Caption</label>
                        <input type="text" name="caption" class="form-control" placeholder="Brief description">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Image Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1" aria-labelledby="editImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editImageModalLabel">Edit Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img id="edit_preview" src="" class="img-thumbnail mb-3" style="max-height: 150px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image Path</label>
                        <input type="text" name="image_path" id="edit_image_path" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Caption</label>
                        <input type="text" name="caption" id="edit_caption" class="form-control">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-image');
    const editModal = new bootstrap.Modal(document.getElementById('editImageModal'));
    
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_image_path').value = this.dataset.image_path;
            document.getElementById('edit_caption').value = this.dataset.caption;
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_preview').src = this.dataset.image_path;
            editModal.show();
        });
    });

    // Update preview when path changes
    document.getElementById('edit_image_path').addEventListener('change', function() {
        document.getElementById('edit_preview').src = this.value;
    });
});
</script>

<?php include 'admin_footer.php'; ?>


