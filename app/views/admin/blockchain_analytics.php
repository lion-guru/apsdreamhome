<?php $pageTitle = $pageTitle ?? 'Blockchain Analytics'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Blockchain Analytics</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-exchange-alt"></i></div>
                    <h5 class="mb-1"><?= number_format($analytics['total_transactions'] ?? 0) ?></h5>
                    <small class="text-muted">Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-cube"></i></div>
                    <h5 class="mb-1"><?= number_format($analytics['total_blocks'] ?? 0) ?></h5>
                    <small class="text-muted">Blocks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-gas-pump"></i></div>
                    <h5 class="mb-1"><?= number_format($analytics['avg_gas_fee'] ?? 0, 4) ?></h5>
                    <small class="text-muted">Avg Gas Fee</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-clock"></i></div>
                    <h5 class="mb-1"><?= number_format($analytics['avg_block_time'] ?? 0, 2) ?>s</h5>
                    <small class="text-muted">Avg Block Time</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-table me-2"></i>Analytics Data</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Metric</th><th>Value</th><th>Change</th><th>Period</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics['metrics'] ?? [])): ?>
                            <?php foreach ($analytics['metrics'] as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($m['value'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= (($m['change'] ?? 0) >= 0) ? 'success' : 'danger' ?>"><?= ($m['change'] ?? 0) >= 0 ? '+' : '' ?><?= htmlspecialchars($m['change'] ?? 0) ?>%</span></td>
                                    <td><?= htmlspecialchars($m['period'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No analytics data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
