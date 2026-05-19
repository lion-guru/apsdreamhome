<?php $pageTitle = $pageTitle ?? 'Security Dashboard'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Dashboard</h4>
        <div>
            <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-bug"></i></div>
                    <h5 class="card-title mb-1"><?= number_format($security_stats['total_threats'] ?? 0) ?></h5>
                    <small class="text-muted">Total Threats</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                    <h5 class="card-title mb-1"><?= number_format($security_stats['blocked'] ?? 0) ?></h5>
                    <small class="text-muted">Blocked</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="card-title mb-1"><?= number_format($security_stats['pending'] ?? 0) ?></h5>
                    <small class="text-muted">Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-scan"></i></div>
                    <h5 class="card-title mb-1"><?= number_format($security_stats['scans_today'] ?? 0) ?></h5>
                    <small class="text-muted">Scans Today</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Threats</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>#</th><th>Type</th><th>Source</th><th>Severity</th><th>Status</th><th>Detected</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_threats)): ?>
                            <?php $i = 1; foreach ($recent_threats as $t): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($t['type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['source'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($t['severity'] ?? 'low') === 'critical' ? 'danger' : (($t['severity'] ?? 'low') === 'high' ? 'warning' : 'secondary') ?>"><?= ucfirst($t['severity'] ?? 'low') ?></span></td>
                                    <td><span class="badge bg-<?= ($t['status'] ?? 'open') === 'resolved' ? 'success' : 'danger' ?>"><?= ucfirst($t['status'] ?? 'open') ?></span></td>
                                    <td><?= htmlspecialchars($t['detected_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No threats detected</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
