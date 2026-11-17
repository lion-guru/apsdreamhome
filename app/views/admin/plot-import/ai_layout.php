<?php
$pageTitle = $page_title ?? 'AI Layout-to-Plot Creator';
$colonies = $colonies ?? [];
$preview = $preview ?? null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-brain text-primary me-2"></i>AI Layout-to-Plot Creator</h1>
            <div class="text-muted small mt-1">Upload a colony layout map image — Gemini Vision reads every numbered plot and drafts them for review. Nothing is written until you press "Create Plots".</div>
        </div>
        <a href="<?= BASE_URL ?>/admin/plots/import" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-upload me-1"></i> CSV Import
        </a>
    </div>

    <?php if ($this->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($this->getFlash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($this->getFlash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?= htmlspecialchars($this->getFlash('warning')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-map me-2"></i>Step 1 — Upload Layout Map</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/plots/import/ai-layout/extract" enctype="multipart/form-data">
                        <?= \App\Helpers\SecurityHelper::csrfField() ?>
                        <div class="mb-3">
                            <label for="colony_id" class="form-label">Colony *</label>
                            <select name="colony_id" id="colony_id" class="form-select" required>
                                <option value="">-- Select Colony --</option>
                                <?php foreach ($colonies as $colony): ?>
                                    <option value="<?= (int) $colony['id'] ?>" <?= (isset($preview) && (int) $preview['colony_id'] === (int) $colony['id']) ? 'selected' : '' ?>><?= htmlspecialchars($colony['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="layout_file" class="form-label">Layout Map Image *</label>
                            <input type="file" name="layout_file" id="layout_file" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                            <div class="form-text">JPG/PNG/WEBP, max 5 MB. Higher resolution = better plot-number detection.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-wand-magic-sparkles me-1"></i> Extract Plots with AI
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <?php if ($preview): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list-check me-2"></i>Step 2 — Review Extracted Plots</h5>
                        <span class="badge bg-primary"><?= htmlspecialchars($preview['engine'] ?? 'ai') ?></span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-1">Colony: <strong><?= htmlspecialchars($preview['colony_name'] ?? '') ?></strong> · extracted <?= htmlspecialchars($preview['extracted_at'] ?? '') ?></p>
                        <?php if (!empty($preview['notes'])): ?>
                            <p class="text-muted small mb-2">AI notes: <?= htmlspecialchars($preview['notes']) ?></p>
                        <?php endif; ?>
                        <form method="POST" action="<?= BASE_URL ?>/admin/plots/import/ai-layout/commit">
                            <?= \App\Helpers\SecurityHelper::csrfField() ?>
                            <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px"><input type="checkbox" id="selectAll" checked></th>
                                            <th>Plot No</th>
                                            <th>Block</th>
                                            <th>W × L (ft)</th>
                                            <th>Area (sqft)</th>
                                            <th>Facing</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($preview['plots'] as $i => $p): ?>
                                        <tr>
                                            <td><input type="checkbox" name="selected[]" value="<?= (int) $i ?>" checked></td>
                                            <td class="fw-bold"><?= htmlspecialchars($p['plot_number']) ?></td>
                                            <td><?= htmlspecialchars($p['block'] ?? '—') ?></td>
                                            <td><?= ($p['width_ft'] !== null && $p['length_ft'] !== null) ? (htmlspecialchars((string) $p['width_ft']) . ' × ' . htmlspecialchars((string) $p['length_ft'])) : '—' ?></td>
                                            <td><?= $p['sqft'] !== null ? htmlspecialchars((string) round((float) $p['sqft'])) : '—' ?></td>
                                            <td><?= htmlspecialchars($p['facing'] ?? '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-muted small"><?= count($preview['plots']) ?> plots ready. Duplicates (same plot number) are skipped automatically.</span>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Create Plots
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="fas fa-image fa-3x mb-3 text-secondary"></i>
                        <h4>No extraction yet</h4>
                        <p class="mb-0">Select a colony, upload the layout map, and press "Extract Plots with AI".<br>A preview table will appear here before anything is saved.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script nonce="<?= htmlspecialchars($GLOBALS['csp_nonce'] ?? '') ?>">
(function () {
    var all = document.getElementById('selectAll');
    if (!all) return;
    all.addEventListener('change', function () {
        document.querySelectorAll('input[name="selected[]"]').forEach(function (cb) { cb.checked = all.checked; });
    });
})();
</script>
