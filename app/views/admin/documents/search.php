<?php $page_title = $page_title ?? 'Search Documents'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-search me-2"></i>Search Documents</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/documents/search">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="input-group">
                    <input type="text" name="q" class="form-control form-control-lg" placeholder="Search by title, name, description, or OCR text..." value="<?= htmlspecialchars($query ?? '') ?>" autofocus>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-search me-1"></i>Search</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($query)): ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Search Results for "<?= htmlspecialchars($query) ?>"</span>
                <span class="badge bg-primary"><?= count($results) ?> found</span>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (!empty($results)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr><th>Source Table</th><th>Title / Name</th><th>Type</th><th>Description</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $r): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['_source_table'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars($r['title'] ?? $r['document_name'] ?? $r['ocr_text'] ?? '-') ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($r['type'] ?? $r['document_type'] ?? '-') ?></span></td>
                                        <td><?= htmlspecialchars(mb_substr($r['description'] ?? $r['ocr_text'] ?? '', 0, 100)) ?></td>
                                        <td><?= htmlspecialchars($r['created_at'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No results found</h5>
                        <p class="text-muted">Try different keywords or check your spelling.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
