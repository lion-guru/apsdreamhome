

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-images"></i> Project Images: <?php echo htmlspecialchars(project['name'] ?? ''); ?></h2>
                <div>
                    <a href="/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                    <a href="/admin/projects/view/<?php echo $project['id']; ?>" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Project
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Upload New Image</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/projects/images/upload/<?php echo $project['id']; ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_title" class="form-label">Image Title</label>
                                    <input type="text" class="form-control" id="image_title" name="image_title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image_type" class="form-label">Image Type</label>
                                    <select class="form-select" id="image_type" name="image_type" required>
                                        <option value="master_plan">Master Plan</option>
                                        <option value="elevation">Elevation</option>
                                        <option value="amenity">Amenity</option>
                                        <option value="construction">Construction</option>
                                        <option value="completed">Completed</option>
                                        <option value="location">Location</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image_description" class="form-label">Description</label>
                            <textarea class="form-control" id="image_description" name="image_description" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image_file" class="form-label">Image File</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Image
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Existing Images</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($images)): ?>
                        <div class="row">
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <?php if ($image['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars(image['image_path'] ?? ''); ?>" class="card-img-top" alt="<?php echo htmlspecialchars(image['image_title'] ?? ''); ?>" style="height: 200px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars(image['image_title'] ?? ''); ?></h6>
                                            <p class="card-text small">
                                                <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars(image['image_type'] ?? '')); ?></span>
                                            </p>
                                            <?php if ($image['image_description']): ?>
                                                <p class="card-text small"><?php echo htmlspecialchars(image['image_description'] ?? ''); ?></p>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Order: <?php echo $image['display_order']; ?></small>
                                                <div>
                                                    <a href="/admin/projects/images/delete/<?php echo $image['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No images uploaded yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

