<div class="mb-4">
    <a href="<?= BASE_URL ?>/admin/gallery" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Gallery</a>
    <h1 class="h3 mt-2 mb-1">Add Gallery Image</h1>
    <p class="text-muted">Upload a new image to the photo gallery</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/admin/gallery" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control form-control-lg" name="image" accept="image/*" required>
                        <div class="form-text">JPG, PNG, GIF, WebP. Max 5MB</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" class="form-control" name="title" placeholder="Image title" maxlength="255">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" name="category">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Caption</label>
                        <input type="text" class="form-control" name="caption" placeholder="Short caption" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Detailed description"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="0" min="0" class="style-86527">
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload Image</button>
                        <a href="<?= BASE_URL ?>/admin/gallery" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
