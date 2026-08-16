<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Edit Testimonial</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo BASE_URL; ?>/admin/testimonials/<?php echo $testimonial['id']; ?>/update" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($testimonial['customer_name'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Email</label>
                                    <input type="email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($testimonial['customer_email'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Phone</label>
                                    <input type="text" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($testimonial['customer_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating" class="form-select" required>
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($testimonial['rating'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo str_repeat('⭐', $i); ?> (<?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?>)
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo ($testimonial['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo ($testimonial['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo ($testimonial['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Display on Website</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" value="1" <?php echo !empty($testimonial['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Testimonial Content</label>
                            <textarea name="content" class="form-control" rows="5" required placeholder="Customer's feedback..."><?php echo htmlspecialchars($testimonial['content'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Testimonial</button>
                        <a href="<?php echo BASE_URL; ?>/admin/testimonials" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
