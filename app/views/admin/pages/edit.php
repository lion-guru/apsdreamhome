<?php $page = $page ?? []; $page_title = $page_title ?? 'Edit Page'; ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i><?= htmlspecialchars($page_title ?? '') ?></h5>
                    <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/pages/update/<?= $page['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Page Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Slug</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($page['slug'] ?? '') ?>" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft" <?= ($page['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= ($page['status'] ?? '') == 'published' ? 'selected' : '' ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Page Content (HTML)</label>
                            <textarea name="content" id="pageContent" class="form-control" rows="20" class="style-14708"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                            <div class="form-text">You can use HTML tags. For rich editing, we recommend using the source view.</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Meta Description</label>
                                <textarea name="meta_description" class="form-control" rows="3"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Meta Keywords</label>
                                <textarea name="meta_keywords" class="form-control" rows="3"><?= htmlspecialchars($page['meta_keywords'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#pageContent',
    height: 500,
    menubar: true,
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
    toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | code | help',
    branding: false,
    promotion: false,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 16px; }'
});
</script>
