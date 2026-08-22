<?php $pageTitle = 'IT Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">IT Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-laptop-code me-2"></i>IT Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-server"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($systemUptime ?? '99.9%') ?></h3>
                    <p class="text-muted mb-0">System Uptime</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2"><i class="fas fa-bug"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($openTickets ?? 0) ?></h3>
                    <p class="text-muted mb-0">Open Support Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($securityAlerts ?? 0) ?></h3>
                    <p class="text-muted mb-0">Security Alerts</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-database"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($dbSize ?? '0 MB') ?></h3>
                    <p class="text-muted mb-0">Database Size</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-tools me-2"></i>Recent Deployments</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($deployments)): ?>
                        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Version</th><th>Date</th><th>Deployed By</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($deployments as $dep): ?>
                                <tr>
                                    <td class="small"><code><?= htmlspecialchars($dep['version'] ?? '') ?></code></td>
                                    <td class="small"><?= htmlspecialchars($dep['deployed_at'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($dep['deployed_by'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($dep['status'] ?? '') === 'success' ? 'success' : 'danger' ?>"><?= ucfirst($dep['status'] ?? '') ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-rocket fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No deployments recorded</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-tasks me-2"></i>System Health</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($systemHealth)): ?>
                        <?php foreach ($systemHealth as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($item['component'] ?? '') ?></span>
                            <span class="badge bg-<?= ($item['status'] ?? '') === 'ok' ? 'success' : (($item['status'] ?? '') === 'warning' ? 'warning' : 'danger') ?>"><?= ucfirst($item['status'] ?? 'ok') ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-heartbeat fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No health data</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
