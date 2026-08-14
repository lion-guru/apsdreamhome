ï»¿<?php $pageTitle = 'Media Details'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file me-2"></i>Media Details</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/media">Media</a></li>
                    <li class="breadcrumb-item active"><?= $media['title'] ?? 'Media' ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/admin/media/edit/<?= $media['id'] ?? 0 ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                <a href="<?= BASE_URL ?>/admin/media" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>
    <?php if (empty($media)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-file fa-4x d-block mb-3"></i><h5>Media not found</h5></div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <?php if (strpos($media['mime_type'] ?? '', 'image') !== false): ?>
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="img-fluid rounded" alt="<?= $media['title'] ?>" class="style-23653">
                    <?php else: ?>
                        <i class="fas fa-file-<?= $media['type'] === 'document' ? 'alt' : 'video' ?> fa-5x text-muted mb-3"></i>
                    <?php endif; ?>
                    <p class="mt-2 mb-0"><code><?= $media['filename'] ?? '' ?></code></p>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>File Information</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Title</div><div class="col-sm-8"><strong><?= $media['title'] ?? '-' ?></strong></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Filename</div><div class="col-sm-8"><?= $media['filename'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Type</div><div class="col-sm-8"><span class="badge bg-info-subtle text-info rounded-pill px-3"><?= $media['type'] ?? 'Image' ?></span></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">MIME Type</div><div class="col-sm-8"><?= $media['mime_type'] ?? '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Size</div><div class="col-sm-8"><?= $media['size'] ? number_format($media['size'] / 1024, 1) . ' KB' : '-' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Folder</div><div class="col-sm-8"><?= $media['folder'] ?? 'General' ?></div></div>
                    <div class="row mb-3"><div class="col-sm-4 text-muted">Uploaded By</div><div class="col-sm-8"><?= $media['uploaded_by_name'] ?? 'System' ?></div></div>
                    <div class="row"><div class="col-sm-4 text-muted">Uploaded At</div><div class="col-sm-8"><?= date('d M Y H:i', strtotime($media['created_at'] ?? 'now')) ?></div></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
