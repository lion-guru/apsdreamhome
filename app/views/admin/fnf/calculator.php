<?php
/**
 * F&F Settlement Calculator View
 * Variables: $employees, $selected_employee, $settlement (null until calculated)
 */

$page_title = $page_title ?? 'Full & Final Settlement Calculator';
$employees = $employees ?? [];
$selected_employee = $selected_employee ?? 0;
$settlement = $settlement ?? null;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-calculator me-2"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= BASE_URL ?>/admin/fnf/history" class="btn btn-outline-secondary">
            <i class="fas fa-history me-1"></i> Settlement History
        </a>
    </div>

    <!-- Employee + Parameters -->
    <div class="card mb-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Exit Details</h5></div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $selected_employee == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['employee_code'] ?? '') ?>) - <?= htmlspecialchars($emp['designation'] ?? '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($selected_employee): ?>
            <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="employee_id" value="<?= (int)$selected_employee ?>">
                <div class="col-md-3">
                    <label class="form-label">Last Working Day <span class="text-danger">*</span></label>
                    <input type="date" name="last_working_day" class="form-control" value="<?= htmlspecialchars($_POST['last_working_day'] ?? date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Resignation Date</label>
                    <input type="date" name="resignation_date" class="form-control" value="<?= htmlspecialchars($_POST['resignation_date'] ?? date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Notice Period (days)</label>
                    <input type="number" name="notice_period_days" class="form-control" value="<?= htmlspecialchars($_POST['notice_period_days'] ?? '30') ?>" min="0" max="90">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Notice Served (days)</label>
                    <input type="number" name="notice_served_days" class="form-control" value="<?= htmlspecialchars($_POST['notice_served_days'] ?? '0') ?>" min="0" max="90">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Exit Type</label>
                    <select name="exit_type" class="form-select">
                        <?php foreach (['resignation','termination','retirement','abandonment'] as $et): ?>
                        <option value="<?= $et ?>" <?= ($_POST['exit_type'] ?? 'resignation') === $et ? 'selected' : '' ?>><?= ucfirst($et) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="calculate" value="1" class="btn btn-primary btn-lg">
                        <i class="fas fa-calculator me-2"></i> Calculate Settlement
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-info mb-0">Select an employee above, then enter exit details to preview the settlement.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Results -->
    <?php if ($settlement): ?>
    <div class="alert alert-success">
        <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Settlement Preview Ready</h5>
        <p class="mb-0">Review the breakdown below. Click <strong>Process Settlement</strong> to finalize (creates the record + marks employee offboarded).</p>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Name:</strong> <?= htmlspecialchars($settlement['employee']['name'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Code:</strong> <?= htmlspecialchars($settlement['employee']['employee_code'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Designation:</strong> <?= htmlspecialchars($settlement['employee']['designation'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Department:</strong> <?= htmlspecialchars($settlement['employee']['department'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Joining:</strong> <?= !empty($settlement['employee']['joining_date']) ? date('d M Y', strtotime($settlement['employee']['joining_date'])) : '-' ?></div>
                <div class="col-md-3"><strong>Last Working Day:</strong> <?= !empty($settlement['employee']['last_working_day']) ? date('d M Y', strtotime($settlement['employee']['last_working_day'])) : '-' ?></div>
                <div class="col-md-3"><strong>Tenure:</strong> <?= (int)($settlement['employee']['tenure_years'] ?? 0) ?>y <?= (int)($settlement['employee']['tenure_months'] ?? 0) ?>m</div>
                <div class="col-md-3"><strong>Gross Monthly:</strong> ₹<?= number_format($settlement['salary']['gross_monthly'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Breakdown</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Component</th><th>Details</th><th class="text-end">Amount (₹)</th></tr></thead>
                    <tbody>
                        <?php foreach (($settlement['components'] ?? []) as $key => $comp): ?>
                        <?php
                            $amount = is_array($comp) ? (float)($comp['amount'] ?? 0) : (float)$comp;
                            $desc = is_array($comp) ? ($comp['description'] ?? '') : '';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)$key))) ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars((string)$desc) ?></small></td>
                            <td class="text-end fw-bold <?= $amount < 0 ? 'text-danger' : 'text-success' ?>">₹<?= number_format($amount, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary fw-bold"><td colspan="2">TOTAL EARNINGS</td><td class="text-end text-success">₹<?= number_format($settlement['totals']['earnings'] ?? 0, 2) ?></td></tr>
                        <tr class="table-danger fw-bold"><td colspan="2">TOTAL DEDUCTIONS</td><td class="text-end text-danger">₹<?= number_format($settlement['totals']['deductions'] ?? 0, 2) ?></td></tr>
                        <tr class="table-success fw-bold fs-5"><td colspan="2">NET PAYABLE</td><td class="text-end">₹<?= number_format($settlement['totals']['net_payable'] ?? 0, 2) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/admin/fnf/process" onsubmit="return confirm('Process this settlement? This creates the record and marks the employee offboarded.');">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="employee_id" value="<?= (int)$selected_employee ?>">
        <input type="hidden" name="last_working_day" value="<?= htmlspecialchars($_POST['last_working_day'] ?? date('Y-m-d')) ?>">
        <input type="hidden" name="resignation_date" value="<?= htmlspecialchars($_POST['resignation_date'] ?? date('Y-m-d')) ?>">
        <input type="hidden" name="notice_period_days" value="<?= htmlspecialchars($_POST['notice_period_days'] ?? '30') ?>">
        <input type="hidden" name="notice_served_days" value="<?= htmlspecialchars($_POST['notice_served_days'] ?? '0') ?>">
        <input type="hidden" name="exit_type" value="<?= htmlspecialchars($_POST['exit_type'] ?? 'resignation') ?>">
        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check-circle me-2"></i> Process Settlement</button>
        <a href="<?= BASE_URL ?>/admin/fnf/calculator?employee_id=<?= (int)$selected_employee ?>" class="btn btn-outline-secondary btn-lg ms-2">Recalculate</a>
    </form>
    <?php endif; ?>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
</style>
