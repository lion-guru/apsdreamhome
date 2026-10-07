<?php
$page_title = $page_title ?? 'Colony Layout';
$colony = $colony ?? [];
$existing_plots = $existing_plots ?? [];
$existing_count = is_array($existing_plots) ? (int)($existing_plots['count'] ?? count($existing_plots)) : 0;
$current_layout = $current_layout ?? null;
$total_area_sqft = is_array($total_area_sqft) ? ($total_area_sqft['total'] ?? 0) : ($total_area_sqft ?? 0);
$cid = (int)($colony['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-drafting-compass me-2"></i>Layout — <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Generate Layout</div>
            <div class="card-body">
                <?php if ($current_layout): ?>
                <div class="alert alert-success">Current layout active since <?= htmlspecialchars($current_layout['created_at'] ?? '') ?></div>
                <?php endif; ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/layout/generate">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3"><label class="form-label">Total Area (sqft)</label><input type="number" step="0.01" name="total_area_sqft" class="form-control" value="<?= htmlspecialchars($total_area_sqft) ?>" required></div>
                    <div class="mb-3"><label class="form-label">Layout Name</label><input type="text" name="layout_name" class="form-control" placeholder="Phase 1"></div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-cogs me-1"></i>Generate Plots</button>
                </form>
                <?php if (!empty($existing_plots)): ?>
                <hr>
                <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/layout/save" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Layout</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/layout/delete" class="d-inline" onsubmit="return confirm('Delete generated plots?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Existing Plots (<?= $existing_count ?>)</div>
            <div class="card-body text-center py-4">
                <?php if ($existing_count > 0): ?>
                    <p class="text-muted">This colony already has <?= $existing_count ?> plots. Generating a new layout will replace them.</p>
                    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/plots" class="btn btn-outline-success"><i class="fas fa-th me-1"></i>View Plots</a>
                <?php else: ?>
                    <p class="text-muted">No plots yet — generate a layout first.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
