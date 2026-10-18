<?php
/**
 * Work Distribution Config
*/

$config = $config ?? [];
$department = $department ?? '';
$algorithms = $algorithms ?? ['hybrid', 'least_loaded', 'skill_based', 'performance_based', 'round_robin'];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.config-section { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; }
.config-section h6 { color: #7c2d12; margin-bottom: 1rem; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Work Distribution Config - <?= htmlspecialchars($department) ?></h4>
            <p class="text-muted mb-0 small">Configure assignment algorithms, weights, and thresholds</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" id="configForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <div class="config-section">
            <h6><i class="fas fa-toggle-on me-2"></i>Auto Assignment</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_assign_enabled" id="auto_assign_enabled" <?= !empty($config['auto_assign_enabled']) ? 'checked' : '' ?> value="1">
                        <label class="form-check-label fw-semibold" for="auto_assign_enabled">Enable Auto Assignment</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_on_assignment" id="notify_on_assignment" <?= !empty($config['notify_on_assignment']) ? 'checked' : '' ?> value="1">
                        <label class="form-check-label fw-semibold" for="notify_on_assignment">Notify on Assignment</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_on_rebalance" id="notify_on_rebalance" <?= !empty($config['notify_on_rebalance']) ? 'checked' : '' ?> value="1">
                        <label class="form-check-label fw-semibold" for="notify_on_rebalance">Notify on Rebalance</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-section">
            <h6><i class="fas fa-magic me-2"></i>Assignment Algorithm</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Algorithm <span class="text-danger">*</span></label>
                    <select name="assignment_algorithm" class="form-select" required>
                        <?php foreach ($algorithms as $algo): ?>
                            <option value="<?= $algo ?>" <?= ($config['assignment_algorithm'] ?? 'hybrid') === $algo ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $algo)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="config-section">
            <h6><i class="fas fa-balance-scale me-2"></i>Scoring Weights (must sum to 1.0)</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Skill Weight</label>
                    <input type="number" step="0.05" min="0" max="1" name="skill_weight" class="form-control" value="<?= $config['skill_weight'] ?? 0.40 ?>" required>
                    <div class="form-text">Skill match importance (0-1)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Workload Weight</label>
                    <input type="number" step="0.05" min="0" max="1" name="workload_weight" class="form-control" value="<?= $config['workload_weight'] ?? 0.40 ?>" required>
                    <div class="form-text">Workload balance importance (0-1)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Performance Weight</label>
                    <input type="number" step="0.05" min="0" max="1" name="performance_weight" class="form-control" value="<?= $config['performance_weight'] ?? 0.20 ?>" required>
                    <div class="form-text">Performance importance (0-1)</div>
                </div>
            </div>
            <div class="mt-2 text-muted small">
                <span id="weightSum">Current sum: <?= round(($config['skill_weight'] ?? 0.4) + ($config['workload_weight'] ?? 0.4) + ($config['performance_weight'] ?? 0.2), 2) ?></span> (should equal 1.0)
            </div>
        </div>

        <div class="config-section">
            <h6><i class="fas fa-sliders-h me-2"></i>Thresholds & Limits</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Max Tasks per Employee</label>
                    <input type="number" min="1" max="50" name="max_tasks_per_employee" class="form-control" value="<?= $config['max_tasks_per_employee'] ?? 10 ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rebalance Threshold (%)</label>
                    <input type="number" step="0.5" min="50" max="100" name="rebalance_threshold" class="form-control" value="<?= $config['rebalance_threshold'] ?? 80 ?>" required>
                    <div class="form-text">Workload % that triggers rebalance</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rebalance Interval (hours)</label>
                    <input type="number" min="1" max="168" name="rebalance_interval_hours" class="form-control" value="<?= $config['rebalance_interval_hours'] ?? 4 ?>" required>
                </div>
            </div>
        </div>

        <div class="config-section">
            <h6><i class="fas fa-robot me-2"></i>AI Assignment (Experimental)</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_assignment_enabled" id="ai_assignment_enabled" <?= !empty($config['ai_assignment_enabled']) ? 'checked' : '' ?> value="1">
                        <label class="form-check-label fw-semibold" for="ai_assignment_enabled">Enable AI Assignment</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">AI Model</label>
                    <select name="ai_model" class="form-select">
                        <option value="hybrid_algorithm" <?= ($config['ai_model'] ?? 'hybrid_algorithm') === 'hybrid_algorithm' ? 'selected' : '' ?>>Hybrid Algorithm</option>
                        <option value="gpt-4" <?= ($config['ai_model'] ?? '') === 'gpt-4' ? 'selected' : '' ?>>GPT-4</option>
                        <option value="claude" <?= ($config['ai_model'] ?? '') === 'claude' ? 'selected' : '' ?>>Claude</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/work-distribution/<?= urlencode($department) ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.querySelectorAll('input[name="skill_weight"], input[name="workload_weight"], input[name="performance_weight"]').forEach(input => {
    input.addEventListener('input', function() {
        const skill = parseFloat(document.querySelector('input[name="skill_weight"]').value) || 0;
        const workload = parseFloat(document.querySelector('input[name="workload_weight"]').value) || 0;
        const performance = parseFloat(document.querySelector('input[name="performance_weight"]').value) || 0;
        const sum = (skill + workload + performance).toFixed(2);
        document.getElementById('weightSum').textContent = 'Current sum: ' + sum + (sum == 1.0 ? ' ✓' : ' ✗ (should equal 1.0)');
        document.getElementById('weightSum').className = sum == 1.0 ? 'text-success small' : 'text-danger small';
    });
});

// Trigger initial calculation
document.querySelectorAll('input[name="skill_weight"], input[name="workload_weight"], input[name="performance_weight"]').forEach(input => input.dispatchEvent(new Event('input')));
</script>