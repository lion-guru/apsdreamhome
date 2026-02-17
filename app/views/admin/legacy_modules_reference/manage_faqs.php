<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// Create table if not exists
$db->execute("CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$msg = '';
$error = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token. Please try again.";
    } else {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO faqs (question, answer, status) VALUES (?, ?, ?)";
            if ($db->execute($sql, [$_POST['question'], $_POST['answer'], $_POST['status']])) {
                $msg = "FAQ added successfully!";
            } else {
                $error = "Error adding FAQ.";
            }
        } elseif ($_POST['action'] === 'edit') {
            $sql = "UPDATE faqs SET question=?, answer=?, status=? WHERE id=?";
            if ($db->execute($sql, [$_POST['question'], $_POST['answer'], $_POST['status'], $_POST['id']])) {
                $msg = "FAQ updated successfully!";
            } else {
                $error = "Error updating FAQ.";
            }
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM faqs WHERE id=?";
            if ($db->execute($sql, [$_POST['id']])) {
                $msg = "FAQ deleted successfully!";
            } else {
                $error = "Error deleting FAQ.";
            }
        }
    }
}

// Fetch all faqs
$faqs = $db->fetchAll("SELECT * FROM faqs ORDER BY id DESC");

$page_title = "Manage FAQs";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Manage Frequently Asked Questions (FAQs)</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                        <i class="fas fa-plus"></i> Add New FAQ
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="faqTable" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Question</th>
                                    <th>Answer</th>
                                    <th>Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($faqs as $f): ?>
                                    <tr>
                                        <td><?= h($f['question']) ?></td>
                                        <td><?= nl2br(h($f['answer'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $f['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= h(ucfirst($f['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-faq"
                                                    data-id="<?= (int)$f['id'] ?>"
                                                    data-question="<?= h($f['question']) ?>"
                                                    data-answer="<?= h($f['answer']) ?>"
                                                    data-status="<?= h($f['status']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                                <?php echo getCsrfField(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($faqs)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No FAQs found.</td>
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

<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFaqModalLabel">Add New FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer</label>
                        <textarea name="answer" class="form-control" rows="4" required></textarea>
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
                    <button type="submit" class="btn btn-primary">Add FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <?php echo getCsrfField(); ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFaqModalLabel">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" name="question" id="edit_question" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer</label>
                        <textarea name="answer" id="edit_answer" class="form-control" rows="4" required></textarea>
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
                    <button type="submit" class="btn btn-primary">Update FAQ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-faq');
    const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_question').value = this.dataset.question;
            document.getElementById('edit_answer').value = this.dataset.answer;
            document.getElementById('edit_status').value = this.dataset.status;
            editModal.show();
        });
    });
});
</script>

<?php include 'admin_footer.php'; ?>
