<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-folder-open me-2"></i><?= ($page_title ?? 'Document Vault') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-1"></i>Upload Document</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <?php if (!empty($documents ?? [])): ?>
            <div class="row g-3">
                <?php foreach (($documents ?? []) as $doc): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-file-<?= ($doc['type'] ?? 'file') === 'pdf' ? 'pdf' : (in_array($doc['type'] ?? '', ['doc','docx']) ? 'word' : 'file') ?> fa-3x text-muted mb-2"></i>
                            <h6 class="card-title small"><?= htmlspecialchars($doc['name'] ?? 'Untitled') ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($doc['size'] ?? '') ?></small>
                            <div class="mt-2">
                                <a href="<?= htmlspecialchars($doc['url'] ?? '#') ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-download"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p>No documents uploaded yet. Upload property documents, agreements, and more.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
