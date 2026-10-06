<?php $page_title = 'Database Monitor'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-database me-2"></i>Database Monitor</h2>
        <span class="badge bg-success"><i class="fas fa-eye me-1"></i>read-only</span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h6><?= htmlspecialchars($overview['version'] ?? '?') ?></h6><small class="text-muted">MySQL</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= number_format($overview['table_count'] ?? 0) ?></h4><small class="text-muted">Tables</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= number_format($overview['db_size_mb'] ?? 0, 1) ?> MB</h4><small class="text-muted">Top-100 Size</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= $overview['threads'] !== null ? number_format($overview['threads']) : '—' ?></h4><small class="text-muted">Connections</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= $overview['slow_queries'] !== null ? number_format($overview['slow_queries']) : '—' ?></h4><small class="text-muted">Slow Queries</small></div></div></div>
        <div class="col-md-2"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h4><?= $overview['uptime'] !== null ? gmdate('H:i', (int)$overview['uptime'] % 86400) . '+' . floor((int)$overview['uptime'] / 86400) . 'd' : '—' ?></h4><small class="text-muted">Uptime</small></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-table me-2"></i>Largest Tables</h6>
            <form method="GET" action="<?= BASE_URL ?>/admin/database" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="table name..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
            </form>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tables)): ?>
                <p class="text-muted text-center py-4">No tables found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead><tr><th>Table</th><th class="text-end">Rows (est)</th><th class="text-end">Data MB</th><th class="text-end">Index MB</th><th class="text-end">Total MB</th></tr></thead>
                        <tbody>
                        <?php foreach ($tables as $t): ?>
                            <tr>
                                <td><code class="small"><?= htmlspecialchars($t['table_name']) ?></code></td>
                                <td class="text-end"><?= number_format((int)$t['table_rows']) ?></td>
                                <td class="text-end"><?= number_format((float)$t['data_mb'], 2) ?></td>
                                <td class="text-end"><?= number_format((float)$t['idx_mb'], 2) ?></td>
                                <td class="text-end"><strong><?= number_format((float)$t['size_mb'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
