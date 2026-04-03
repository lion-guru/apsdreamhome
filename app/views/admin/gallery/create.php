<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/gallery" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Gallery
    </a>
    <h1 class="h3 mt-2 mb-1">Add Gallery Image</h1>
    <p class="text-muted">Upload a new image to the photo gallery</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/gallery" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="mb-4">
                        <label for="image" class="form-label fw-semibold">Image File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control form-control-lg" id="image" name="image" accept="image/*" required>
                        <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 5MB</div>
                    </div>

                    <div class="mb-4">
                        <label for="category" class="form-label fw-semibold">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="general">General</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="projects">Projects</option>
                            <option value="team">Team</option>
                            <option value="events">Events</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="caption" class="form-label fw-semibold">Caption</label>
                        <input type="text" class="form-control" id="caption" name="caption" placeholder="Enter image caption">
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload Image
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/gallery" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
