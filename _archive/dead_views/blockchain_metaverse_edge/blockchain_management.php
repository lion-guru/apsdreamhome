<?php $pageTitle = $pageTitle ?? 'Blockchain Management'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-link me-2"></i>Blockchain Network</h4>
        <span class="badge bg-<?= ($chain_status ?? 'inactive') === 'active' ? 'success' : 'secondary' ?> fs-6"><?= ucfirst($chain_status ?? 'inactive') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-cubes"></i></div>
                    <h5 class="mb-1"><?= count($blocks ?? []) ?></h5>
                    <small class="text-muted">Total Blocks</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-check-double"></i></div>
                    <h5 class="mb-1"><?= count(array_filter($blocks ?? [], fn($b) => ($b['verified'] ?? false))) ?></h5>
                    <small class="text-muted">Verified Blocks</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-clock"></i></div>
                    <h5 class="mb-1"><?= ($chain_status === 'active' ? 'Running' : 'Stopped') ?></h5>
                    <small class="text-muted">Chain Status</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-cube me-2"></i>Blocks</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>Index</th><th>Hash</th><th>Previous Hash</th><th>Timestamp</th><th>Transactions</th><th>Verified</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($blocks)): ?>
                            <?php foreach ($blocks as $b): ?>
                                <tr>
                                    <td><?= $b['index'] ?? '-' ?></td>
                                    <td><code><?= htmlspecialchars(substr($b['hash'] ?? '', 0, 16)) ?>...</code></td>
                                    <td><code><?= htmlspecialchars(substr($b['previous_hash'] ?? '', 0, 16)) ?>...</code></td>
                                    <td><?= htmlspecialchars($b['timestamp'] ?? '-') ?></td>
                                    <td><?= count($b['transactions'] ?? []) ?></td>
                                    <td><i class="fas fa-<?= ($b['verified'] ?? false) ? 'check-circle text-success' : 'times-circle text-danger' ?>"></i></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No blocks in chain</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
