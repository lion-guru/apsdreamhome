<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Blog Post</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/blog/<?php echo $post['id'] ?? ''; ?>/update">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="10"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="draft" <?php echo (isset($post['status']) && $post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo (isset($post['status']) && $post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Post</button>
                        <a href="<?php echo BASE_URL; ?>/admin/blog" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
