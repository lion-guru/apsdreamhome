<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Add New Testimonial</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo BASE_URL; ?>/admin/testimonials-new/store" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Email</label>
                                    <input type="email" name="customer_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Phone</label>
                                    <input type="text" name="customer_phone" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="5">⭐⭐⭐⭐⭐ (5 Stars)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                                        <option value="2">⭐⭐ (2 Stars)</option>
                                        <option value="1">⭐ (1 Star)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Display on Website</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                        <label class="form-check-label" for="is_featured">Featured Testimonial</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Testimonial Content</label>
                            <textarea name="content" class="form-control" rows="5" required placeholder="Customer's feedback..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Testimonial</button>
                        <a href="<?php echo BASE_URL; ?>/admin/testimonials" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
