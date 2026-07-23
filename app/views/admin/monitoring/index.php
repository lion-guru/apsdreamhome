<?php $pageTitle = 'Monitoring - Errors & Alerts'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-heartbeat me-2"></i>Monitoring</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Monitoring</li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <?php
    $status = is_array($health ?? null) ? ($health['status'] ?? 'unknown') : 'unknown';
    $statusClass = $status === 'ok' ? 'success' : ($status === 'warning' ? 'warning' : 'danger');
    $checks = is_array($health ?? null) ? ($health['checks'] ?? []) : [];
    $errTotal    = (int)($error_stats['total'] ?? 0);
    $errToday    = (int)($error_stats['today'] ?? 0);
    $errCritical = (int)($error_stats['by_level']['critical'] ?? 0);
    $alertTotal  = is_array($alerts) ? count($alerts) : 0;
    ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-server fa-2x text-<?= $statusClass ?> mb-2"></i>
                    <h6 class="text-muted mb-1">Overall</h6>
                    <span class="badge bg-<?= $statusClass ?>"><?= strtoupper(htmlspecialchars((string)$status)) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bug fa-2x text-primary mb-2"></i>
                    <h6 class="text-muted mb-1">Errors (30d)</h6>
                    <strong><?= number_format($errTotal) ?></strong>
                    <div class="small text-muted">+<?= $errToday ?> today</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                    <h6 class="text-muted mb-1">Critical</h6>
                    <strong><?= number_format($errCritical) ?></strong>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bell fa-2x text-warning mb-2"></i>
                    <h6 class="text-muted mb-1">Recent Alerts</h6>
                    <strong><?= number_format($alertTotal) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($checks)): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Health Checks</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Check</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($checks as $name => $c):
                        $st  = $c['status'] ?? 'unknown';
                        $cls = $st === 'ok' ? 'success' : ($st === 'warning' ? 'warning' : 'danger');
                    ?>
                        <tr>
                            <td class="ps-4"><code><?= htmlspecialchars((string)$name) ?></code></td>
                            <td><span class="badge bg-<?= $cls ?>"><?= strtoupper(htmlspecialchars((string)$st)) ?></span></td>
                            <td><?= htmlspecialchars((string)($c['message'] ?? '')) ?></td>
                            <td class="small text-muted">
                                <?php
                                $details = $c['details'] ?? null;
                                if (is_array($details)) {
                                    echo '<code>' . htmlspecialchars(json_encode($details, JSON_UNESCAPED_SLASHES)) . '</code>';
                                } elseif (is_scalar($details)) {
                                    echo htmlspecialchars((string)$details);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bug me-2"></i>Recent Errors</h5>
                    <span class="badge bg-secondary">Last <?= count($errors ?? []) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Level</th>
                                    <th>Message</th>
                                    <th>Source</th>
                                    <th class="text-end pe-4">When</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($errors)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success me-2"></i>No errors captured
                                </td></tr>
                            <?php else: foreach ($errors as $e):
                                $lvl = $e['level'] ?? 'error';
                                $cls = $lvl === 'critical' ? 'danger' : ($lvl === 'warning' ? 'warning' : 'secondary');
                            ?>
                                <tr>
                                    <td class="ps-4"><span class="badge bg-<?= $cls ?>"><?= htmlspecialchars((string)$lvl) ?></span></td>
                                    <td>
                                        <div class="text-truncate" style="max-width:340px" title="<?= htmlspecialchars((string)($e['message'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($e['message'] ?? '')) ?>
                                        </div>
                                    </td>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars((string)($e['source'] ?? '')) ?>
                                        <?php if (!empty($e['file'])): ?>
                                            <div class="text-truncate" style="max-width:240px"><?= htmlspecialchars((string)$e['file']) ?>:<?= (int)($e['line'] ?? 0) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 small text-muted"><?= htmlspecialchars((string)($e['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Alerts</h5>
                    <span class="badge bg-secondary"><?= count($alerts ?? []) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Severity</th>
                                    <th>Source</th>
                                    <th class="text-end pe-4">When</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($alerts)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">
                                    <i class="fas fa-shield-alt text-success me-2"></i>No alerts
                                </td></tr>
                            <?php else: foreach ($alerts as $a):
                                $sev = $a['severity'] ?? 'info';
                                $cls = $sev === 'critical' ? 'danger' : ($sev === 'warning' ? 'warning' : 'info');
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars((string)$sev) ?></span>
                                        <div class="small text-muted mt-1 text-truncate" style="max-width:220px" title="<?= htmlspecialchars((string)($a['message'] ?? '')) ?>">
                                            <?= htmlspecialchars((string)($a['message'] ?? '')) ?>
                                        </div>
                                    </td>
                                    <td class="small"><?= htmlspecialchars((string)($a['source'] ?? '')) ?></td>
                                    <td class="text-end pe-4 small text-muted"><?= htmlspecialchars((string)($a['created_at'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
