<?php
$stats = $stats ?? [];
$suggestions = $suggestions ?? [];
$models = $models ?? [];
$colonies = $colonies ?? [];
$statusFilter = $statusFilter ?? null;
$config = $config ?? [];
$statusBadge = [
    'pending'  => 'bg-warning text-dark',
    'approved' => 'bg-info text-dark',
    'applied'  => 'bg-success',
    'rejected' => 'bg-secondary',
];
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-chart-line text-primary me-2"></i>AI Dynamic Pricing & Yield Optimizer
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/plots/pricing-rules">Pricing</a></li>
                    <li class="breadcrumb-item active">Dynamic Pricing Engine</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/dynamic-pricing/experiments" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-flask me-1"></i>A/B Experiments
            </a>
            <a href="<?= BASE_URL ?>/admin/plots/pricing-rules" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-percent me-1"></i>PLC Rules
            </a>
            <a href="<?= BASE_URL ?>/admin/colonies" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-map me-1"></i>Colonies
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

    <!-- Stats ribbon -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f59e0b;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Pending</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['pending'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0ea5e9;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Approved</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['approved'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #22c55e;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Applied (30d)</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['applied_month'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #8b5cf6;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Avg Confidence</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format(((float)($stats['avg_confidence'] ?? 0)) * 100, 0) ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #64748b;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Models</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['models'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #64748b;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Colonies Seen</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['colonies_covered'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate + Train row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fas fa-wand-magic-sparkles text-primary me-2"></i>Generate Suggestions</h5>
                    <p class="text-muted small mb-3">
                        Scores live demand signals per colony (90-day sell-through, stock tightness &amp; age, tagged leads, seasonality)
                        and creates a bounded (+/–<?= number_format($config['max_change_pct'] ?? 15, 0) ?>% max) price suggestion for review.
                        Manual generation is always available; the enabled flag below gates scheduled runs only.
                    </p>
                    <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/generate" class="d-flex gap-2 flex-wrap">
                        <?= \App\Core\Security\CSRF::generateTokenField() ?>
                        <select name="colony_id" class="form-select form-select-sm" style="max-width:320px;">
                            <option value="0">All colonies with plots…</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= (int)$c['available'] ?> available)</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                            <i class="fas fa-bolt me-1"></i>Generate
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6" id="models">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fas fa-graduation-cap text-purple me-2" style="color:#8b5cf6;"></i>Train Time-Series Model</h5>
                    <p class="text-muted small mb-3">
                        Fits a trend + seasonality regression on that colony's price_history. Honest guard:
                        refuses below <strong><?= (int)($config['min_samples'] ?? 30) ?> samples</strong> (lookback <?= (int)($config['lookback_days'] ?? 180) ?>d).
                    </p>
                    <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/train" class="d-flex gap-2 flex-wrap">
                        <?= \App\Core\Security\CSRF::generateTokenField() ?>
                        <select name="colony_id" class="form-select form-select-sm" style="max-width:320px;" required>
                            <option value="">Select colony…</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="fas fa-cogs me-1"></i>Train
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Suggestions -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;" id="suggestions">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-radius:12px 12px 0 0;">
            <h5 class="fw-bold mb-0"><i class="fas fa-lightbulb text-warning me-2"></i>Price Suggestions</h5>
            <div class="btn-group btn-group-sm">
                <a href="<?= BASE_URL ?>/admin/dynamic-pricing" class="btn btn-outline-secondary<?= $statusFilter === null ? ' active' : '' ?>">All</a>
                <a href="<?= BASE_URL ?>/admin/dynamic-pricing?status=pending" class="btn btn-outline-warning<?= $statusFilter === 'pending' ? ' active' : '' ?>">Pending</a>
                <a href="<?= BASE_URL ?>/admin/dynamic-pricing?status=approved" class="btn btn-outline-info<?= $statusFilter === 'approved' ? ' active' : '' ?>">Approved</a>
                <a href="<?= BASE_URL ?>/admin/dynamic-pricing?status=applied" class="btn btn-outline-success<?= $statusFilter === 'applied' ? ' active' : '' ?>">Applied</a>
                <a href="<?= BASE_URL ?>/admin/dynamic-pricing?status=rejected" class="btn btn-outline-secondary<?= $statusFilter === 'rejected' ? ' active' : '' ?>">Rejected</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Colony</th>
                            <th class="text-end">Current Rs/sqft</th>
                            <th class="text-end">Suggested</th>
                            <th class="text-end">Δ%</th>
                            <th class="text-end">Confidence</th>
                            <th>Status</th>
                            <th>Reasoning</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$suggestions): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">
                                No suggestions yet — hit <strong>Generate</strong> above to score your colonies.
                            </td></tr>
                        <?php endif; ?>
                        <?php foreach ($suggestions as $s): ?>
                            <tr>
                                <td class="text-muted">#<?= (int)$s['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($s['colony_name'] ?? ('Colony ' . $s['colony_id'])) ?></td>
                                <td class="text-end"><?= number_format((float)$s['current_base_pps'], 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format((float)$s['suggested_base_pps'], 2) ?></td>
                                <td class="text-end fw-bold <?= (float)$s['price_change_pct'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= (float)$s['price_change_pct'] >= 0 ? '+' : '' ?><?= number_format((float)$s['price_change_pct'], 2) ?>%
                                </td>
                                <td class="text-end">
                                    <?php $conf = (float)$s['confidence_score']; ?>
                                    <span class="badge <?= $conf >= ($config['min_confidence'] ?? 0.75) ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= number_format($conf * 100, 0) ?>%
                                    </span>
                                </td>
                                <td><span class="badge <?= $statusBadge[$s['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars($s['status']) ?></span></td>
                                <td style="max-width:340px;">
                                    <small class="text-muted" title="<?= htmlspecialchars($s['reasoning'] ?? '') ?>">
                                        <?= htmlspecialchars(mb_strimwidth($s['reasoning'] ?? '', 0, 120, '…')) ?>
                                    </small>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if (in_array($s['status'], ['pending', 'approved'], true)): ?>
                                        <?php if ($s['status'] === 'pending'): ?>
                                            <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/<?= (int)$s['id'] ?>/approve" class="d-inline">
                                                <?= \App\Core\Security\CSRF::generateTokenField() ?>
                                                <button class="btn btn-outline-info btn-sm" title="Approve"><i class="fas fa-check"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/<?= (int)$s['id'] ?>/apply" class="d-inline"
                                              onsubmit="return confirm('Reprice ALL available plots of this colony to Rs.<?= number_format((float)$s['suggested_base_pps'], 2) ?>/sqft? Booked/sold plots stay untouched; every change is logged in price_history.');">
                                            <?= \App\Core\Security\CSRF::generateTokenField() ?>
                                            <button class="btn btn-success btn-sm" title="Apply to available plots"><i class="fas fa-rocket me-1"></i>Apply</button>
                                        </form>
                                        <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/<?= (int)$s['id'] ?>/reject" class="d-inline">
                                            <?= \App\Core\Security\CSRF::generateTokenField() ?>
                                            <button class="btn btn-outline-danger btn-sm" title="Reject"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= $s['status'] === 'applied' ? 'by ' . htmlspecialchars($s['approved_by_name'] ?? 'admin') : '—' ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Models -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-header bg-white py-3" style="border-radius:12px 12px 0 0;">
            <h5 class="fw-bold mb-0"><i class="fas fa-brain text-purple me-2" style="color:#8b5cf6;"></i>Trained Models</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Colony</th><th>Name</th><th>Type</th><th>Version</th>
                            <th>Samples</th><th>RMSE</th><th>R²</th><th>Trained</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$models): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">
                                No models trained yet. Use <strong>Train</strong> above (needs enough price_history samples).
                            </td></tr>
                        <?php endif; ?>
                        <?php foreach ($models as $m):
                            $metrics = json_decode($m['training_metrics'] ?? '[]', true) ?: []; ?>
                            <tr>
                                <td class="text-muted">#<?= (int)$m['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($m['colony_name'] ?? ('Colony ' . $m['colony_id'])) ?></td>
                                <td><small><?= htmlspecialchars($m['model_name'] ?? '') ?></small></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($m['model_type'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($m['model_version'] ?? '') ?></td>
                                <td><?= (int)($metrics['samples'] ?? 0) ?></td>
                                <td><?= number_format((float)($metrics['rmse'] ?? 0), 2) ?></td>
                                <td><?= number_format((float)($metrics['r2'] ?? 0), 4) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($m['trained_at'] ?? '—') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-muted small mb-0">
        <i class="fas fa-shield-alt me-1"></i>
        Apply rewrites <strong>available plots only</strong> (booked/sold/registered untouched), recalculates each plot through the live PLC engine,
        and writes one <code>price_history</code> row per plot (<code>reference_type=dynamic_pricing</code>) for audit &amp; rollback.
    </p>
</div>
