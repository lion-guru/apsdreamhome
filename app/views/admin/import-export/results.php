<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Import Results') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/admin/import-export/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white"><div class="card-body text-center"><h6>Imported</h6><h2 class="mb-0"><?= (int)($result['imported'] ?? 0) ?></h2></div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-white"><div class="card-body text-center"><h6>Skipped</h6><h2 class="mb-0"><?= (int)($result['skipped'] ?? 0) ?></h2></div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white"><div class="card-body text-center"><h6>Errors</h6><h2 class="mb-0"><?= (int)($result['errors'] ?? 0) ?></h2></div></div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Error Details</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Row</th><th>Field</th><th>Value</th><th>Error</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($result['errors_list'])): ?>
                            <?php foreach ($result['errors_list'] as $err): ?>
                                <tr>
                                    <td><?= (int)($err['row'] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($err['field'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($err['value'] ?? '-') ?></td>
                                    <td class="text-danger"><?= htmlspecialchars($err['message'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-success py-3"><i class="fas fa-check-circle me-1"></i>No errors — import completed successfully.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
