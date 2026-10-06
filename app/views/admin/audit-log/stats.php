<?php $page_title = 'Audit Statistics'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Audit Statistics</h2>
        <div>
            <?php foreach ([7, 30, 90] as $d): ?>
                <a href="<?= BASE_URL ?>/admin/audit-log/stats?days=<?= $d ?>" class="btn btn-sm <?= ((int)($days ?? 30)) === $d ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $d ?>d</a>
            <?php endforeach; ?>
            <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-sm btn-outline-secondary ms-2">All logs</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($stats['total'] ?? 0) ?></h3><small class="text-muted">Events (<?= (int)($days ?? 30) ?> days)</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= count($stats['byAction'] ?? []) ?></h3><small class="text-muted">Distinct Actions</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= count($stats['byRole'] ?? []) ?></h3><small class="text-muted">Active Roles</small></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">By Action</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php foreach (($stats['byAction'] ?? []) as $a): ?>
                            <tr><td><span class="badge bg-light text-dark"><?= htmlspecialchars($a['action'] ?? '') ?></span></td><td class="text-end"><strong><?= number_format($a['cnt'] ?? 0) ?></strong></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">By Role</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php foreach (($stats['byRole'] ?? []) as $r): ?>
                            <tr><td><?= htmlspecialchars($r['user_role'] ?? 'unknown') ?></td><td class="text-end"><strong><?= number_format($r['cnt'] ?? 0) ?></strong></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">By Status</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                        <?php foreach (($stats['byStatus'] ?? []) as $s): ?>
                            <tr><td><span class="badge bg-<?= ($s['status'] ?? '') === 'success' ? 'success' : (($s['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($s['status'] ?? '') ?></span></td><td class="text-end"><strong><?= number_format($s['cnt'] ?? 0) ?></strong></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
