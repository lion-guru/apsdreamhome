<?php
$experiments = $experiments ?? [];
$colonies = $colonies ?? [];
$statusBadge = [
    'draft'     => 'bg-secondary',
    'running'   => 'bg-success',
    'paused'    => 'bg-warning text-dark',
    'completed' => 'bg-info text-dark',
    'archived'  => 'bg-dark',
];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-flask text-primary me-2"></i>A/B Pricing Experiments
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dynamic-pricing">Dynamic Pricing</a></li>
                    <li class="breadcrumb-item active">Experiments</li>
                </ol>
            </nav>
        </div>
        <a href="<?= BASE_URL ?>/admin/dynamic-pricing" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Optimizer
        </a>
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
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-1"><i class="fas fa-plus-circle text-primary me-2"></i>New Experiment (Draft)</h5>
                    <p class="text-muted small mb-3">
                        Control keeps the current anchor price; variants test alternate base prices. Splits are deterministic
                        (ordered plot-id buckets) so assignment is auditable. Apply prices only when you are ready —
                        drafting changes nothing.
                    </p>
                    <form method="post" action="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/store">
                        <?= \App\Core\Security\CSRF::generateTokenField() ?>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Colony</label>
                            <select name="colony_id" class="form-select form-select-sm" required>
                                <option value="">— select —</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?> (<?= (int)$c['available'] ?> available @ Rs.<?= number_format((float)$c['min_price_per_sqft'], 0) ?>/sqft)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Experiment name</label>
                            <input type="text" name="experiment_name" class="form-control form-control-sm" required maxlength="100" placeholder="e.g. Colony 5 winter premium test">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <label class="form-label small fw-semibold">Control base Rs./sqft</label>
                                <input type="number" name="control_base_pps" class="form-control form-control-sm" min="1" step="0.01" required placeholder="e.g. 1550">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold">Min sample</label>
                                <input type="number" name="min_sample_size" class="form-control form-control-sm" min="10" value="30">
                            </div>
                        </div>
                        <label class="form-label small fw-semibold">Variants</label>
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <input type="text" name="variant_label[<?= $i ?>]" class="form-control form-control-sm" placeholder="Label <?= $i + 1 ?><?= $i === 0 ? ' (required)' : '' ?>" <?= $i === 0 ? 'required' : '' ?>>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="variant_pps[<?= $i ?>]" class="form-control form-control-sm" min="1" step="0.01" placeholder="Rs./sqft" <?= $i === 0 ? 'required' : '' ?>>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="variant_split[<?= $i ?>]" class="form-control form-control-sm" min="1" max="90" step="0.1" placeholder="Split %" <?= $i === 0 ? 'required' : '' ?>>
                                </div>
                            </div>
                        <?php endfor; ?>
                        <p class="text-muted small mt-2 mb-2">Control receives the remaining traffic (100% − variant splits).</p>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Create Draft
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-0">
                    <div class="p-3 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fas fa-list text-secondary me-2"></i>Experiments</h5>
                        <span class="badge bg-primary rounded-pill"><?= count($experiments) ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>Name</th><th>Colony</th><th>Status</th><th class="text-center">Assigned</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$experiments): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No experiments yet — create a draft on the left.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($experiments as $e): ?>
                                    <tr>
                                        <td class="text-muted"><?= (int)$e['id'] ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($e['experiment_name']) ?></td>
                                        <td><?= htmlspecialchars($e['colony_name'] ?? '—') ?></td>
                                        <td><span class="badge <?= $statusBadge[$e['status']] ?? 'bg-secondary' ?>"><?= htmlspecialchars($e['status']) ?></span></td>
                                        <td class="text-center"><?= (int)($e['assigned'] ?? 0) ?></td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/admin/dynamic-pricing/experiments/<?= (int)$e['id'] ?>" class="btn btn-outline-primary btn-sm py-0">
                                                Open <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
