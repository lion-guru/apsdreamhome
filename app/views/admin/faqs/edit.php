<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit FAQ</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/admin/faqs/<?php echo $faq['id']; ?>/update" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <input type="text" name="question" class="form-control" value="<?php echo htmlspecialchars($faq['question']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Answer</label>
                            <textarea name="answer" class="form-control" rows="5" required><?php echo htmlspecialchars($faq['answer']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">Select Category</option>
                                        <option value="General" <?php echo ($faq['category'] == 'General') ? 'selected' : ''; ?>>General</option>
                                        <option value="Property" <?php echo ($faq['category'] == 'Property') ? 'selected' : ''; ?>>Property</option>
                                        <option value="Payment" <?php echo ($faq['category'] == 'Payment') ? 'selected' : ''; ?>>Payment</option>
                                        <option value="Legal" <?php echo ($faq['category'] == 'Legal') ? 'selected' : ''; ?>>Legal</option>
                                        <option value="Support" <?php echo ($faq['category'] == 'Support') ? 'selected' : ''; ?>>Support</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="display_order" class="form-control" value="<?php echo $faq['display_order']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo ($faq['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($faq['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update FAQ</button>
                        <a href="<?php echo BASE_URL; ?>/admin/faqs" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
