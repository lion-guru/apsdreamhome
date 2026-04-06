<?php
/**
 * Admin View Testimonial Page
 * Review and approve/reject individual testimonials
 */

$page_title = $page_title ?? 'Review Testimonial';
$t = $testimonial ?? [];
$statuses = $statuses ?? ['pending', 'approved', 'rejected'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-comment-alt me-2"></i>Review Testimonial</h1>
            <p class="text-muted mb-0">Review and manage customer feedback</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/testimonials" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>Name:</strong></td>
                                    <td><?php echo htmlspecialchars($t['name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($t['email'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td><?php echo htmlspecialchars($t['phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td><?php echo htmlspecialchars($t['location'] ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>Property:</strong></td>
                                    <td><?php echo htmlspecialchars($t['property_type'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Rating:</strong></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= ($t['rating'] ?? 0) ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                        (<?php echo $t['rating'] ?? 0; ?>/5)
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Submitted:</strong></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($t['submitted_at'] ?? 'now')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($t['status'] ?? 'pending') === 'approved' ? 'success' : (($t['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($t['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo htmlspecialchars($t['title'] ?? 'Testimonial'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="lead"><?php echo nl2br(htmlspecialchars($t['testimonial'] ?? 'No content')); ?></p>
                </div>
            </div>

            <?php if ($t['photo_path'] ?? false): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Photo</h5>
                </div>
                <div class="card-body">
                    <img src="<?php echo BASE_URL . '/' . $t['photo_path']; ?>" alt="Customer" class="img-fluid rounded" style="max-height: 300px;">
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <form id="testimonialActionForm">
                        <div class="mb-3">
                            <label class="form-label">Update Status</label>
                            <select name="status" class="form-select" id="statusSelect">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo ($t['status'] ?? '') === $status ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($status); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" <?php echo ($t['featured'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="featuredCheck">
                                    <i class="fas fa-star text-warning me-1"></i>Feature on Homepage
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Review Notes (Internal)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Add your notes here..."><?php echo htmlspecialchars($t['notes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-1"></i>Update Testimonial
                        </button>

                        <button type="button" class="btn btn-outline-danger w-100" onclick="deleteTestimonial(<?php echo $t['id'] ?? 0; ?>)">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($t['reviewed_by_name'] ?? false): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Review History</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i>Reviewed by: <?php echo htmlspecialchars($t['reviewed_by_name']); ?><br>
                        <i class="fas fa-clock me-1"></i>Reviewed at: <?php echo date('M d, Y H:i', strtotime($t['reviewed_at'] ?? 'now')); ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('testimonialActionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/admin/testimonials/update-status/<?php echo $t['id'] ?? 0; ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
});

function deleteTestimonial(id) {
    if (confirm('Are you sure you want to delete this testimonial?')) {
        fetch('<?php echo BASE_URL; ?>/admin/testimonials/delete/' + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo BASE_URL; ?>/admin/testimonials';
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>
