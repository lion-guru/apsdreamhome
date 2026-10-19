<?php
/** Admin Performance - Edit Goal */

$page_title = $page_title ?? 'Edit Goal';
$mode = 'edit';
$goal = $goal ?? [];
$employees = $employees ?? [];
$managers = $managers ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Edit performance goal/OKR</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/performance/<?= $goal['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Goal
        </a>
    </div>

    <form method="POST" id="goalForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="mode" value="edit">
        <input type="hidden" name="goal_id" value="<?= $goal['id'] ?>">

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-bullseye me-2"></i>Goal Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= ($goal['employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Goal Type <span class="text-danger">*</span></label>
                        <select name="goal_type" class="form-select" required>
                            <option value="okr" <?= ($goal['goal_type'] ?? '') === 'okr' ? 'selected' : '' ?>>OKR (Objectives & Key Results)</option>
                            <option value="kpi" <?= ($goal['goal_type'] ?? '') === 'kpi' ? 'selected' : '' ?>>KPI (Key Performance Indicator)</option>
                            <option value="personal" <?= ($goal['goal_type'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal Development</option>
                            <option value="team" <?= ($goal['goal_type'] ?? '') === 'team' ? 'selected' : '' ?>>Team Goal</option>
                            <option value="organizational" <?= ($goal['goal_type'] ?? '') === 'organizational' ? 'selected' : '' ?>>Organizational</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($goal['title'] ?? '') ?>" placeholder="e.g., Increase Q4 sales by 20%">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Assigned By</label>
                        <select name="assigned_by" class="form-select">
                            <option value="">Select Manager</option>
                            <?php foreach ($managers as $mgr): ?>
                                <option value="<?= $mgr['id'] ?>" <?= ($goal['assigned_by'] ?? '') == $mgr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mgr['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the goal, key results, success criteria..."><?= htmlspecialchars($goal['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Targets & Metrics -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2"></i>Targets & Metrics</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Target Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="target_value" class="form-control" required value="<?= htmlspecialchars($goal['target_value'] ?? '') ?>" placeholder="e.g., 1000000">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Target Unit <span class="text-danger">*</span></label>
                        <input type="text" name="target_unit" class="form-control" required value="<?= htmlspecialchars($goal['target_unit'] ?? '') ?>" placeholder="e.g., ₹, units, %, hours">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Weightage %</label>
                        <input type="number" min="0" max="100" name="weightage" class="form-control" value="<?= htmlspecialchars($goal['weightage'] ?? 100) ?>" placeholder="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Review Frequency</label>
                        <select name="review_frequency" class="form-select">
                            <option value="weekly" <?= ($goal['review_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="biweekly" <?= ($goal['review_frequency'] ?? '') === 'biweekly' ? 'selected' : '' ?>>Bi-weekly</option>
                            <option value="monthly" <?= ($goal['review_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="quarterly" <?= ($goal['review_frequency'] ?? '') === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required value="<?= htmlspecialchars($goal['start_date'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required value="<?= htmlspecialchars($goal['end_date'] ?? date('Y-m-d', strtotime('+3 months'))) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Next Review Date</label>
                        <input type="date" name="next_review_date" class="form-control" value="<?= htmlspecialchars($goal['next_review_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($goal['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= ($goal['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="on_track" <?= ($goal['status'] ?? '') === 'on_track' ? 'selected' : '' ?>>On Track</option>
                            <option value="at_risk" <?= ($goal['status'] ?? '') === 'at_risk' ? 'selected' : '' ?>>At Risk</option>
                            <option value="off_track" <?= ($goal['status'] ?? '') === 'off_track' ? 'selected' : '' ?>>Off Track</option>
                            <option value="completed" <?= ($goal['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($goal['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Parent Goal (for cascading OKRs) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-sitemap me-2"></i>Alignment</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Parent Goal (for cascading OKRs)</label>
                        <select name="parent_goal_id" class="form-select">
                            <option value="">No Parent (Top-level Goal)</option>
                            <?php
                            $parentGoals = $db->fetchAll("SELECT id, title FROM employee_goals WHERE tenant_id = ? AND status IN ('active','on_track') ORDER BY created_at DESC", [$tenantId ?? 1]);
                            foreach ($parentGoals as $pg): ?>
                                <option value="<?= $pg['id'] ?>" <?= ($goal['parent_goal_id'] ?? '') == $pg['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pg['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Review Frequency</label>
                        <select name="review_frequency" class="form-select">
                            <option value="weekly" <?= ($goal['review_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="biweekly" <?= ($goal['review_frequency'] ?? '') === 'biweekly' ? 'selected' : '' ?>>Bi-weekly</option>
                            <option value="monthly" <?= ($goal['review_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="quarterly" <?= ($goal['review_frequency'] ?? '') === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/performance/<?= $goal['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Update Goal
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('goalForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>