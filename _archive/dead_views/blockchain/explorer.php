<?php $pageTitle = $page_title ?? 'Blockchain Explorer'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-search me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
                <div class="card-body aps-cp-card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
                    <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Verification Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Status</th><td><span class="badge bg-<?= ($verification['blockchain_status'] ?? '') === 'verified' ? 'success' : 'warning' ?>"><?= strtoupper($verification['blockchain_status'] ?? 'UNKNOWN') ?></span></td></tr>
                        <tr><th>Blockchain Hash</th><td style="word-break:break-all"><code><?= htmlspecialchars($verification['blockchain_hash'] ?? '-') ?></code></td></tr>
                        <tr><th>Transaction Hash</th><td style="word-break:break-all"><code><?= htmlspecialchars($verification['transaction_hash'] ?? '-') ?></code></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-external-link-alt me-2"></i>Blockchain Explorer</h5></div>
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-3"><i class="fas fa-link"></i></div>
                    <p>View this transaction on the blockchain explorer</p>
                    <a href="<?= htmlspecialchars($explorer_url ?? '#') ?>" target="_blank" class="btn btn-primary" rel="noopener"><i class="fas fa-external-link-alt me-1"></i>Open in Explorer</a>
                    <p class="mt-3 small text-muted">You will be redirected to <?= htmlspecialchars(parse_url($explorer_url ?? '', PHP_URL_HOST) ?: 'the blockchain explorer') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
