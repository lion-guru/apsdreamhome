<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/gallery" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Gallery</a>
    <h1 class="h3 mt-2 mb-1">Edit Gallery Image</h1>
    <p class="text-muted">Update image details</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/admin/gallery/<?= $image['id'] ?>/update" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <?php if (!empty($image['image_path'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Image</label>
                        <div class="border rounded p-2">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($image['image_path'] ?? '') ?>" class="img-thumbnail style-73275" alt="Current">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Image (optional)</label>
                        <input type="file" class="form-control form-control-lg" name="image" accept="image/*">
                        <div class="form-text">Leave empty to keep current image.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($image['title'] ?? '') ?>" maxlength="255">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" name="category">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($image['category'] ?? '') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($image['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($image['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" value="<?= (int)($image['sort_order'] ?? 0) ?>" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Caption</label>
                        <input type="text" class="form-control" name="caption" value="<?= htmlspecialchars($image['caption'] ?? '') ?>" maxlength="255">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($image['description'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Image</button>
                        <a href="<?= BASE_URL ?>/admin/gallery" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
