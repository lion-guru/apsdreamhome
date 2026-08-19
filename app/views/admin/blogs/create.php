<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title mb-0">Create New Blog Post</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form action="<?php echo BASE_URL; ?>/admin/blog/store" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea name="content" id="blog_content" class="form-control" rows="10" required></textarea>
                                    <button type="button" id="aiGenBlog" class="btn btn-sm mt-2" class="style-43547"><i class="fas fa-magic"></i> Generate with AI (Hindi + English)</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories ?? [] as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Featured Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Post</button>
                        <a href="<?php echo BASE_URL; ?>/admin/blogs" class="btn btn-secondary">Cancel</a>
                    </form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('aiGenBlog');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var title = document.querySelector('input[name="title"]');
        var cat = document.querySelector('select[name="category_id"] option:checked');
        var fd = new FormData();
        fd.append('csrf_token', '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>');
        fd.append('topic', title ? title.value : '');
        fd.append('category', cat ? cat.textContent : '');
        if (!title.value) { showToast('Please enter a title first.', 'info'); return; }
        var ta = document.getElementById('blog_content');
        var meta = document.querySelector('textarea[name="meta_description"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        fetch('<?php echo BASE_URL; ?>/ai/content/blog-draft', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    ta.value = (d.english || '') + "\n\n---\n\n" + (d.hindi || '');
                    if (meta && d.excerpt) meta.value = d.excerpt;
                } else {
                    showToast('AI generation failed. Please try again.', 'danger');
                }
            })
            .catch(function () { showToast('AI generation failed. Please try again.', 'danger'); })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> Generate with AI (Hindi + English)';
            });
    });
});
</script>
                </div>
            </div>
        </div>
    </div>
</div>
