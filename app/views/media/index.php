<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-photo-video me-2"></i>Media Gallery</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadMediaModal"><i class="fas fa-upload me-1"></i>Upload Media</button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="?category=" class="btn btn-sm <?= empty($filters ?? '') ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
                <a href="?category=images" class="btn btn-sm <?= ($filters ?? '') === 'images' ? 'btn-primary' : 'btn-outline-secondary' ?>">Images</a>
                <a href="?category=documents" class="btn btn-sm <?= ($filters ?? '') === 'documents' ? 'btn-primary' : 'btn-outline-secondary' ?>">Documents</a>
                <a href="?category=videos" class="btn btn-sm <?= ($filters ?? '') === 'videos' ? 'btn-primary' : 'btn-outline-secondary' ?>">Videos</a>
                <a href="?category=projects" class="btn btn-sm <?= ($filters ?? '') === 'projects' ? 'btn-primary' : 'btn-outline-secondary' ?>">Projects</a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <?php if (!empty($media ?? [])): ?>
        <?php foreach (($media ?? []) as $m): ?>
        <div class="col-md-3 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-img-top bg-light text-center py-4">
                    <i class="fas fa-<?= ($m['type'] ?? 'file') === 'image' ? 'image' : (($m['type'] ?? '') === 'video' ? 'video' : 'file-alt') ?> fa-3x text-muted"></i>
                </div>
                <div class="card-body p-2 text-center">
                    <small class="d-block text-truncate"><?= htmlspecialchars($m['title'] ?? $m['name'] ?? 'Untitled') ?></small>
                    <small class="text-muted"><?= htmlspecialchars($m['created_at'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-photo-video fa-3x mb-3"></i>
                <p>No media files found.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <nav><ul class="pagination pagination-sm">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#"><?= ($page ?? 1) ?></a></li>
            <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
        </ul></nav>
    </div>
</div>
