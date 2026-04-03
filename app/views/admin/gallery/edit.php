<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/gallery" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Gallery
    </a>
    <h1 class="h3 mt-2 mb-1">Edit Gallery Image</h1>
    <p class="text-muted">Update image details</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/gallery/<?php echo $image['id']; ?>/update" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <!-- Current Image Preview -->
                    <?php if (!empty($image['image_path'])): ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Image</label>
                        <div class="border rounded p-3">
                            <img src="<?php echo BASE_URL . '/' . $image['image_path']; ?>" class="img-thumbnail" style="max-height: 200px;" alt="Current image">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="image" class="form-label fw-semibold">New Image (optional)</label>
                        <input type="file" class="form-control form-control-lg" id="image" name="image" accept="image/*">
                        <div class="form-text">Leave empty to keep current image. Supported: JPG, PNG, GIF</div>
                    </div>

                    <div class="mb-4">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="general" <?php echo ($image['category'] ?? '') === 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="residential" <?php echo ($image['category'] ?? '') === 'residential' ? 'selected' : ''; ?>>Residential</option>
                            <option value="commercial" <?php echo ($image['category'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                            <option value="projects" <?php echo ($image['category'] ?? '') === 'projects' ? 'selected' : ''; ?>>Projects</option>
                            <option value="team" <?php echo ($image['category'] ?? '') === 'team' ? 'selected' : ''; ?>>Team</option>
                            <option value="events" <?php echo ($image['category'] ?? '') === 'events' ? 'selected' : ''; ?>>Events</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="caption" class="form-label fw-semibold">Caption</label>
                        <input type="text" class="form-control" id="caption" name="caption" value="<?php echo htmlspecialchars($image['caption'] ?? ''); ?>" placeholder="Enter image caption">
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active" <?php echo ($image['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($image['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Image
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/gallery" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
