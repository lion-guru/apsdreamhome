<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Edit Knowledge Base Article</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo BASE_URL; ?>/admin/knowledge-base/<?php echo $article['id']; ?>/update" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">Select Category</option>
                                        <option value="Getting Started" <?php echo ($article['category'] == 'Getting Started') ? 'selected' : ''; ?>>Getting Started</option>
                                        <option value="Properties" <?php echo ($article['category'] == 'Properties') ? 'selected' : ''; ?>>Properties</option>
                                        <option value="Payments" <?php echo ($article['category'] == 'Payments') ? 'selected' : ''; ?>>Payments</option>
                                        <option value="Account" <?php echo ($article['category'] == 'Account') ? 'selected' : ''; ?>>Account</option>
                                        <option value="Legal" <?php echo ($article['category'] == 'Legal') ? 'selected' : ''; ?>>Legal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="draft" <?php echo ($article['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo ($article['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Article</button>
                        <a href="<?php echo BASE_URL; ?>/admin/knowledge-base" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
