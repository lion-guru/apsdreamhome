<?php
$e = $experiment ?? null;
$results = $results ?? null;
$statusBadge = [
    'draft'     => 'bg-secondary',
    'running'   => 'bg-success',
    'paused'    => 'bg-warning text-dark',
    'completed' => 'bg-info text-dark',
    'archived'  => 'bg-dark',
];
if (!$e) {
    echo '<div class="container-fluid py-4"><div class="alert alert-danger">Experiment not found.</div></div>';
    return;
}
$controlSplit = (float)($e['traffic_allocation']['control_split_pct'] ?? 0);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-flask text-primary me-2"></i><?= htmlspecialchars($e['experiment_name']) ?>
                <span class="badge <?= $statusBadge[$e['status']] ?? 'bg-secondary' ?> fs-6 ms-2"><?= htmlspecialchars($e['status']) ?></span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dynamic-pricing">Dynamic Pricing</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dynamic-pricing/experiments">Experiments</a></li>
                    <li class="breadcrumb-item active">#<?= (int)$e['id'] ?></li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($e['status'] === 'draft'): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/<?= (int)$e['id'] ?>/start" class="d-inline">
                    <?= \App\Core\Security\CSRF::generateTokenField() ?>
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Assign every available plot of <?= htmlspecialchars($e['colony_name'] ?? '') ?> to control/variants by deterministic split?')">
                        <i class="fas fa-play me-1"></i>Start (Assign Plots)
                    </button>
                </form>
                <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/<?= (int)$e['id'] ?>/delete" class="d-inline">
                    <?= \App\Core\Security\CSRF::generateTokenField() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this draft?')">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            <?php elseif ($e['status'] === 'running'): ?>
                <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/<?= (int)$e['id'] ?>/apply" class="d-inline">
                    <?= \App\Core\Security\CSRF::generateTokenField() ?>
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Apply each assigned plot its variant price (available plots only, PlcService recalculation, price_history rollback trail)?')">
                        <i class="fas fa-tags me-1"></i>Apply Variant Prices
                    </button>
                </form>
                <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/<?= (int)$e['id'] ?>/restore" class="d-inline">
                    <?= \App\Core\Security\CSRF::generateTokenField() ?>
                    <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Restore all still-available assigned plots to their ORIGINAL pre-experiment prices and archive this experiment?')">
                        <i class="fas fa-undo me-1"></i>Restore &amp; Archive
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/dynamic-pricing/experiments" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>List
            </a>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #64748b;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Colony</span>
                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($e['colony_name'] ?? '—') ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0ea5e9;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Control Base</span>
                    <h6 class="fw-bold mb-0 text-dark">Rs.<?= number_format((float)$e['control_base_pps'], 0) ?>/sqft (<?= number_format($controlSplit, 1) ?>%)</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #8b5cf6;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Confidence / Min N</span>
                    <h6 class="fw-bold mb-0 text-dark"><?= number_format(((float)$e['confidence_level']) * 100, 0) ?>% / <?= (int)$e['min_sample_size'] ?></h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f59e0b;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Window</span>
                    <h6 class="fw-bold mb-0 text-dark"><?= $e['start_date'] ? htmlspecialchars($e['start_date']) : 'not started' ?><?= $e['end_date'] ? ' → ' . htmlspecialchars($e['end_date']) : '' ?></h6>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="fas fa-layer-group text-primary me-2"></i>Variants</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 small">
                    <thead class="table-light">
                        <tr><th>Key</th><th>Label</th><th class="text-end">Base Rs./sqft</th><th class="text-end">Traffic split</th></tr>
                    </thead>
                    <tbody>
                        <tr class="table-secondary-subtle">
                            <td class="text-muted">control</td>
                            <td class="fw-semibold">Control (current anchor)</td>
                            <td class="text-end"><?= number_format((float)$e['control_base_pps'], 2) ?></td>
                            <td class="text-end"><?= number_format($controlSplit, 1) ?>%</td>
                        </tr>
                        <?php foreach ($e['variants'] as $v): ?>
                            <tr>
                                <td class="text-muted"><?= htmlspecialchars($v['key']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($v['label']) ?></td>
                                <td class="text-end"><?= number_format((float)$v['base_pps'], 2) ?></td>
                                <td class="text-end"><?= number_format((float)$v['split_pct'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($results !== null && !isset($results['error'])): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body">
                <h5 class="fw-bold mb-1"><i class="fas fa-chart-bar text-success me-2"></i>Live Results</h5>
                <p class="text-muted small mb-3">
                    Conversion = assigned plot moved to booked/sold/registered/hold/reserved (plot status is the signal —
                    no booking-path hooks needed). Realized price is each converted plot's live PLC-inclusive price.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-2 small">
                        <thead class="table-light">
                            <tr>
                                <th>Variant</th><th class="text-end">Base</th><th class="text-end">Assigned</th>
                                <th class="text-end">Converted</th><th class="text-end">Conv. rate</th>
                                <th class="text-end">Avg realized Rs./sqft</th><th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['variants'] as $k => $s): ?>
                                <tr class="<?= $k === 'control' ? 'table-secondary-subtle' : '' ?>">
                                    <td class="fw-semibold"><?= htmlspecialchars($s['label']) ?><?= $k === 'control' ? ' <span class="badge bg-secondary">control</span>' : '' ?></td>
                                    <td class="text-end"><?= number_format((float)$s['base_pps'], 0) ?></td>
                                    <td class="text-end"><?= (int)$s['assigned'] ?></td>
                                    <td class="text-end"><?= (int)$s['converted'] ?></td>
                                    <td class="text-end fw-bold"><?= number_format((float)$s['conv_rate'], 1) ?>%</td>
                                    <td class="text-end"><?= $s['avg_realized_pps'] !== null ? number_format((float)$s['avg_realized_pps'], 2) : '—' ?></td>
                                    <td class="text-end"><?= $s['revenue'] > 0 ? 'Rs.' . number_format((float)$s['revenue'], 0) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($results['note'])): ?>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($results['note']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($e['status'] === 'draft'): ?>
        <div class="alert alert-secondary small">
            <i class="fas fa-info-circle me-1"></i>Draft — press <strong>Start (Assign Plots)</strong> to deterministically assign every available plot to control/variants. Drafting changes no prices.
        </div>
    <?php endif; ?>
</div>
