<?php $page_title = $page_title ?? 'Document Details'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-alt me-2"></i>Document Details</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
            <a href="<?= !empty($document['file_path']) ? BASE_URL . '/admin/documents/download/' . (int)($document['id'] ?? 0) : '#' ?>" class="btn btn-success <?= empty($document['file_path']) ? 'disabled' : '' ?>"><i class="fas fa-download me-1"></i>Download</a>
            <form method="POST" action="<?= BASE_URL ?>/admin/documents/delete/<?= (int)($document['id'] ?? 0) ?>" class="style-71727" data-aps-confirm="Delete this document permanently?">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>
            </form>
        </div>
    </div>

    <?php if (!empty($document)): ?>
        <div class="card shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?= htmlspecialchars($document['title'] ?? 'Untitled') ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($document['description'] ?? 'No description') ?></p>

                        <div class="table-responsive"><table class="table table-bordered mt-3">
                            <tr><th class="style-58160">Document Type</th><td><span class="badge bg-info"><?= htmlspecialchars(ucfirst($document['type'] ?? 'N/A')) ?></span></td></tr>
                            <tr><th>File Name</th><td><?= htmlspecialchars(basename($document['file_path'] ?? 'N/A')) ?></td></tr>
                            <tr><th>File Size</th><td><?= isset($document['file_size']) ? number_format($document['file_size'] / 1024, 1) . ' KB' : 'N/A' ?></td></tr>
                            <tr><th>Related Entity</th><td><?= htmlspecialchars(ucfirst($document['related_type'] ?? 'N/A')) ?> <?= !empty($document['related_id']) ? '(ID: ' . (int)$document['related_id'] . ')' : '' ?></td></tr>
                            <tr><th>Uploaded By</th><td><?= htmlspecialchars($document['uploaded_by_name'] ?? 'N/A') ?></td></tr>
                            <tr><th>Uploaded At</th><td><?= htmlspecialchars($document['created_at'] ?? 'N/A') ?></td></tr>
                            <tr><th>Status</th><td><span class="badge bg-<?= ($document['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($document['status'] ?? 'active') ?></span></td></tr>
                        </table></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                                <p class="mb-0"><?= htmlspecialchars(strtoupper(pathinfo($document['file_path'] ?? '', PATHINFO_EXTENSION) ?: 'FILE')) ?></p>
                                <hr>
                                <a href="<?= BASE_URL ?>/admin/documents/download/<?= (int)$document['id'] ?>" class="btn btn-success w-100"><i class="fas fa-download me-1"></i>Download File</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Document not found</h5>
            <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-primary mt-3">Back to Documents</a>
        </div>
    <?php endif; ?>
</div>
