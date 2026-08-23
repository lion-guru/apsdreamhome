<?php
/** @var array $all_associates */
$all_associates = $all_associates ?? [];
$base           = defined('BASE_URL') ? BASE_URL : '';
$preselect      = (int)($_GET['user_id'] ?? 0);
?>
<div class="container-fluid py-4" class="style-88096">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-money-check-alt me-2"></i>Set / Revise Salary Structure</h4>
        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/agents/salaried" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= htmlspecialchars($base ?? '') ?>/admin/agents/salaried/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <!-- Agent Selection -->
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="user_id">Select Agent / Salesman <span class="text-danger">*</span></label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">— Choose Agent —</option>
                        <?php foreach ($all_associates as $assoc): ?>
                            <option value="<?= (int)$assoc['user_id'] ?>"
                                <?= $preselect === (int)$assoc['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($assoc['name'] ?? '') ?>
                                (<?= htmlspecialchars($assoc['email'] ?? '') ?>)
                                <?= $assoc['agent_type'] === 'salaried' ? 'âœ” Salaried' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">This will set the agent_type to 'salaried' automatically.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="effective_from">Effective From <span class="text-danger">*</span></label>
                    <input type="date" id="effective_from" name="effective_from" class="form-control"
                           value="<?= date('Y-m-d') ?>" required>
                    <small class="text-muted">The previous salary structure will be closed on the day before this date.</small>
                </div>

                <hr>
                <h6 class="text-muted mb-3">Monthly Fixed Salary Components (₹)</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="basic_salary">Basic Salary (₹)</label>
                        <input type="number" id="basic_salary" name="basic_salary" class="form-control"
                               min="0" step="0.01" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="hra">HRA — House Rent Allowance (₹)</label>
                        <input type="number" id="hra" name="hra" class="form-control"
                               min="0" step="0.01" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="ta_da">TA/DA — Travel & Daily Allowance (₹)</label>
                        <input type="number" id="ta_da" name="ta_da" class="form-control"
                               min="0" step="0.01" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="other_allowance">Other Allowance (₹)</label>
                        <input type="number" id="other_allowance" name="other_allowance" class="form-control"
                               min="0" step="0.01" value="0">
                    </div>
                </div>

                <hr>
                <h6 class="text-muted mb-3">Plot Sale Incentive</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="incentive_type">Incentive Type</label>
                        <select name="incentive_type" id="incentive_type" class="form-select">
                            <option value="flat_per_plot">Flat ₹ per Plot Sold</option>
                            <option value="percentage">% of Plot Sale Value</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" id="incentive_value_label" for="incentive_value">
                            Incentive Amount (₹ per plot)
                        </label>
                        <input type="number" name="incentive_value" id="incentive_value"
                               class="form-control" min="0" step="0.01" value="0">
                        <small class="text-muted" id="incentive_hint">
                            E.g. ₹2000 per plot sold this month.
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tds_applicable"
                               id="tds_applicable" value="1" checked>
                        <label class="form-check-label" for="tds_applicable">
                            Apply TDS on Salary (Section 192B — Professional Income Tax)
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="remarks">Remarks / Notes (optional)</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="2"
                              placeholder="E.g. Revised after appraisal in July 2026"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Salary Structure
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('incentive_type').addEventListener('change', function () {
    var lbl  = document.getElementById('incentive_value_label');
    var hint = document.getElementById('incentive_hint');
    if (this.value === 'percentage') {
        lbl.textContent  = 'Incentive % of Sale Value';
        hint.textContent = 'E.g. 1.5 means 1.5% of total plot sale value per booking.';
    } else {
        lbl.textContent  = 'Incentive Amount (₹ per plot)';
        hint.textContent = 'E.g. ₹2000 flat per plot sold this month.';
    }
});
</script>
