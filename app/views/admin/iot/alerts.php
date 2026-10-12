<?php
$alerts = $alerts ?? [];
$stats = $stats ?? [];
$view = $filterView ?? 'open';
$colonyId = (int)($colonyId ?? 0);
$sevBadge = ['critical' => 'bg-danger', 'warning' => 'bg-warning text-dark', 'info' => 'bg-info text-dark'];
$typeLabel = [
    'battery_low'   => ['Battery Low', 'fa-battery-quarter'],
    'barrier_fault' => ['Boom Barrier Fault', 'fa-dungeon'],
    'device_offline'=> ['Device Offline', 'fa-wifi'],
    'fault'         => ['Device Fault', 'fa-exclamation-triangle'],
];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-bell text-danger me-2"></i>IoT Alerts &amp; Telemetry Health
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/iot">IoT Smart Property</a></li>
                    <li class="breadcrumb-item active">Alerts</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/admin/iot" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>IoT Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/iot/devices" class="btn btn-outline-primary btn-sm"><i class="fas fa-server me-1"></i>Devices</a>
            <a href="<?= BASE_URL ?>/admin/township-security" class="btn btn-outline-secondary btn-sm"><i class="fas fa-shield-alt me-1"></i>Security Desk</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #dc3545;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Open</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['open'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #fd7e14;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Critical</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['critical'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #198754;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Resolved</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['resolved'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0d6efd;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Offline Rule</span>
                    <h6 class="fw-bold mb-0 text-dark mt-1"><?= (int)$offlineMinutes ?> min silence</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= BASE_URL ?>/admin/iot/alerts<?= $colonyId ? '?colony_id=' . $colonyId : '' ?>"
                       class="btn btn-<?= $view === 'open' ? 'danger' : 'outline-danger' ?> fw-semibold">
                        Open <span class="badge bg-white text-danger rounded-pill"><?= (int)($stats['open'] ?? 0) ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/iot/alerts?view=resolved<?= $colonyId ? '&colony_id=' . $colonyId : '' ?>"
                       class="btn btn-<?= $view === 'resolved' ? 'success' : 'outline-success' ?> fw-semibold">
                        Resolved <span class="badge bg-white text-success rounded-pill"><?= (int)($stats['resolved'] ?? 0) ?></span>
                    </a>
                </div>
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Rules: battery &lt;25% (warning) / &lt;10% (critical), fault_flag or barrier_fault = 1,
                    no telemetry for <?= (int)$offlineMinutes ?>+ minutes. Repeat triggers never spam — one open alert per device+type.
                </p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Severity</th><th>Alert</th><th>Device</th><th>Message</th><th>Age</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$alerts): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            No <?= $view ?> alerts<?= $colonyId ? " for colony #$colonyId" : '' ?>.
                        </td></tr>
                    <?php endif; ?>
                    <?php foreach ($alerts as $a):
                        $tl = $typeLabel[$a['alert_type']] ?? [$a['alert_type'], 'fa-bell'];
                        $age = strtotime($a['created_at']) ? (time() - strtotime($a['created_at'])) : 0;
                        $ageTxt = $age < 3600 ? floor($age / 60) . 'm ago' : ($age < 86400 ? floor($age / 3600) . 'h ago' : floor($age / 86400) . 'd ago');
                        ?>
                        <tr>
                            <td class="text-muted"><?= (int)$a['id'] ?></td>
                            <td><span class="badge <?= $sevBadge[$a['severity']] ?? 'bg-secondary' ?>"><?= htmlspecialchars($a['severity']) ?></span></td>
                            <td class="fw-semibold"><i class="fas <?= $tl[1] ?> me-1"></i><?= htmlspecialchars($tl[0]) ?></td>
                            <td>
                                <?= htmlspecialchars($a['device_name'] ?: 'Device #' . $a['device_id']) ?>
                                <?php if (!empty($a['device_uid'])): ?><span class="text-muted">(<?= htmlspecialchars($a['device_uid']) ?>)</span><?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($a['message']) ?></td>
                            <td class="text-muted"><?= $ageTxt ?></td>
                            <td class="text-end">
                                <?php if (!$a['is_resolved']): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/iot/alerts/<?= (int)$a['id'] ?>/resolve" class="d-inline">
                                        <?= \App\Core\Security\CSRF::generateTokenField() ?>
                                        <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm py-0">
                                            <i class="fas fa-check me-1"></i>Resolve
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">
                                        <?= htmlspecialchars($a['resolved_at'] ?? '') ?> by #<?= (int)($a['resolved_by'] ?? 0) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="alert alert-secondary small mb-0">
        <i class="fas fa-satellite-dish me-1"></i>
        <strong>Field ingest:</strong> devices POST JSON to
        <code><?= BASE_URL ?>/api/iot/ingest</code> with <code>device_uid</code> + <code>readings[]</code>
        (metric/value/unit) — no session, no CSRF; unknown uid = 401. Readings flow through the same
        pipeline as manual admin entries, so alerts fire for both.
    </div>
</div>
