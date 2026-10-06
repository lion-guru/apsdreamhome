<?php $page_title = 'System Health'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h2>
        <a href="<?= BASE_URL ?>/admin/health" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt me-1"></i>Refresh</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= (int)($summary['pass'] ?? 0) ?></h3><small class="text-muted">Passing</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning"><?= (int)($summary['warn'] ?? 0) ?></h3><small class="text-muted">Warnings</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-danger"><?= (int)($summary['fail'] ?? 0) ?></h3><small class="text-muted">Failing</small></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead><tr><th style="width:60px">State</th><th>Service</th><th>Detail</th></tr></thead>
                <tbody>
                <?php foreach (($checks ?? []) as $c): ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($c['state'] === 'pass'): ?><span class="badge bg-success">PASS</span>
                            <?php elseif ($c['state'] === 'warn'): ?><span class="badge bg-warning text-dark">WARN</span>
                            <?php else: ?><span class="badge bg-danger">FAIL</span><?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($c['label']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($c['detail']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/cron-health" class="btn btn-sm btn-outline-secondary">Cron detail</a>
        <a href="<?= BASE_URL ?>/admin/database" class="btn btn-sm btn-outline-secondary">Database detail</a>
        <a href="<?= BASE_URL ?>/admin/cache" class="btn btn-sm btn-outline-secondary">Cache admin</a>
        <a href="<?= BASE_URL ?>/admin/backup" class="btn btn-sm btn-outline-secondary">Backups</a>
        <a href="<?= BASE_URL ?>/admin/it-support" class="btn btn-sm btn-outline-secondary">IT toolbox</a>
    </div>
</div>
