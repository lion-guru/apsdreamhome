<?php $page_title = $page_title ?? 'Documents'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-folder me-2"></i>Documents Management</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/documents/search" class="btn btn-outline-secondary me-2"><i class="fas fa-search me-1"></i>Search</a>
            <a href="<?= BASE_URL ?>/admin/documents/upload" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload Document</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php $_SESSION['flash_message'] = ''; $_SESSION['flash_type'] = ''; endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/documents/categories" class="text-decoration-none">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-tags fa-2x mb-2"></i>
                        <h6 class="mb-0">Categories</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/documents/types" class="text-decoration-none">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <h6 class="mb-0">Document Types</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/documents/templates" class="text-decoration-none">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-file-code fa-2x mb-2"></i>
                        <h6 class="mb-0">Templates</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= BASE_URL ?>/admin/documents/reviews" class="text-decoration-none">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-star fa-2x mb-2"></i>
                        <h6 class="mb-0">Reviews</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/business" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-building me-1"></i>Business</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/customer" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-user me-1"></i>Customer</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/user" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-users me-1"></i>User</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/property" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-home me-1"></i>Property</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/generated" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-robot me-1"></i>Generated</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/documents/ocr" class="text-decoration-none">
                <div class="card bg-secondary text-white shadow-sm">
                    <div class="card-body text-center py-2">
                        <small><i class="fas fa-eye me-1"></i>OCR</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>All Documents</span>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($documents)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>File</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td><a href="<?= BASE_URL ?>/admin/documents/show/<?= (int)$doc['id'] ?>"><?= htmlspecialchars($doc['title'] ?? 'Untitled') ?></a></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars(ucfirst($doc['type'] ?? 'other')) ?></span></td>
                                    <td><?= htmlspecialchars(basename($doc['file_path'] ?? '')) ?></td>
                                    <td><?= isset($doc['file_size']) ? number_format($doc['file_size'] / 1024, 1) . ' KB' : '-' ?></td>
                                    <td><?= htmlspecialchars($doc['uploaded_by_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($doc['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($doc['status'] ?? 'active') ?></span></td>
                                    <td><?= htmlspecialchars($doc['created_at'] ?? '-') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/documents/show/<?= (int)$doc['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/documents/download/<?= (int)$doc['id'] ?>" class="btn btn-sm btn-outline-success" title="Download"><i class="fas fa-download"></i></a>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/documents/delete/<?= (int)$doc['id'] ?>" class="style-71727" data-aps-confirm="Delete this document?">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No documents found. <a href="<?= BASE_URL ?>/admin/documents/upload">Upload your first document</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
