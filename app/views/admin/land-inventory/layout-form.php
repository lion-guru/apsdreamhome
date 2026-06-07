<?php
$colony = $colony ?? [];
$colonyId = (int)($colony['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>New Layout — <?= htmlspecialchars($colony['name'] ?? 'Colony #'.$colonyId) ?></h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/layouts" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/layouts/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Layout Name <span class="text-danger">*</span></label>
                        <input type="text" name="layout_name" class="form-control form-control-sm" required placeholder="e.g. Phase 1 - North Block">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Version</label>
                        <input type="number" name="version" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Layout Type</label>
                        <select name="layout_type" class="form-select form-select-sm">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="mixed">Mixed Use</option>
                            <option value="industrial">Industrial</option>
                            <option value="agricultural">Agricultural</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Total Plots</label>
                        <input type="number" name="total_plots" class="form-control form-control-sm" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Total Area (sqft)</label>
                        <input type="number" name="total_area_sqft" step="0.01" class="form-control form-control-sm" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Road Area %</label>
                        <input type="number" name="road_area_pct" step="0.01" class="form-control form-control-sm" min="0" max="100" value="15">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Common Area %</label>
                        <input type="number" name="common_area_pct" step="0.01" class="form-control form-control-sm" min="0" max="100" value="5">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Approval Date</label>
                        <input type="date" name="approval_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Approval # / Auth #</label>
                        <input type="text" name="approval_number" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_current" value="1" id="isCur" checked>
                            <label class="form-check-label" for="isCur">Set as Current Layout</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small">Layout File URL (PDF / Image)</label>
                        <input type="text" name="layout_file_url" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Plot Map JSON (optional)</label>
                        <input type="text" name="plot_map_json" class="form-control form-control-sm" placeholder='{"plots": [...]}'>
                    </div>

                    <div class="col-12">
                        <label class="form-label small">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Layout</button>
                    <a href="<?= BASE_URL ?>/admin/land-inventory/colonies/<?= $colonyId ?>/layouts" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
