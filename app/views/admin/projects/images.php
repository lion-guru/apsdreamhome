

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-images"></i> Project Images: <?php echo htmlspecialchars($project['name'] ?? ''); ?></h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Projects
                    </a>
                    <a href="<?= BASE_URL ?>/admin/projects/view/<?php echo $project['id'] ?? ''; ?>" class="btn btn-info">
                        <i class="fas fa-eye"></i> View Project
                    </a>
                </div>
            </div>

            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/projects/images/upload/<?php echo $project['id'] ?? ''; ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">Upload</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header aps-cp-card-header">
                    <h5 class="mb-0">Project Images</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($images)): ?>
                        <div class="row">
                            <?php foreach ($images as $image): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="position-relative">
                                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded" alt="Project Image" />
                                        <a href="<?= BASE_URL ?>/admin/projects/images/delete/<?php echo $image['id'] ?? 0; ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="return confirm('Delete this image?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No images uploaded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

