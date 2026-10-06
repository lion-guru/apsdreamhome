<?php
/**
 * Arrears Preview View
 * Shows calculated arrears before processing
*/

$page_title = $page_title ?? 'Arrears Preview';
$employee = $employee ?? null;
$current_structure = $current_structure ?? null;
$arrears = $arrears ?? null;
$preview_month = $preview_month ?? date('n');
$preview_year = $preview_year ?? date('Y');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= BASE_URL ?>/admin/salary/structures" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Structures
        </a>
    </div>

    <!-- Employee Info Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Employee: <?= htmlspecialchars($employee['name'] ?? 'Unknown') ?> (ID: <?= $employee['id'] ?? 0 ?>)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Current Salary Structure</h6>
                    <?php if ($current_structure): ?>
                    <table class="table table-sm table-bordered">
                        <tr><th>Basic Salary</th><td>₹<?= number_format($current_structure['basic_salary'] ?? 0, 2) ?></td></tr>
                        <tr><th>HRA</th><td>₹<?= number_format($current_structure['hra'] ?? 0, 2) ?></td></tr>
                        <tr><th>Conveyance</th><td>₹<?= number_format($current_structure['conveyance'] ?? 0, 2) ?></td></tr>
                        <tr><th>Medical Allowance</th><td>₹<?= number_format($current_structure['medical_allowance'] ?? 0, 2) ?></td></tr>
                        <tr><th>Special Allowance</th><td>₹<?= number_format($current_structure['special_allowance'] ?? 0, 2) ?></td></tr>
                        <tr><th>Other Allowances</th><td>₹<?= number_format($current_structure['other_allowances'] ?? 0, 2) ?></td></tr>
                        <tr><th>PF (Employee)</th><td>₹<?= number_format($current_structure['pf_employee'] ?? 0, 2) ?></td></tr>
                        <tr><th>TDS</th><td>₹<?= number_format($current_structure['tds'] ?? 0, 2) ?></td></tr>
                        <tr class="table-primary"><th>Gross Salary</th><td><strong>₹<?= number_format(($current_structure['basic_salary']??0)+($current_structure['hra']??0)+($current_structure['conveyance']??0)+($current_structure['medical_allowance']??0)+($current_structure['special_allowance']??0)+($current_structure['other_allowances']??0), 2) ?></strong></td></tr>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-warning">No active salary structure found</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h6>Proposed New Structure</h6>
                    <form method="POST" action="<?= BASE_URL ?>/admin/salary/arrears/preview">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="employee_id" value="<?= $employee['id'] ?? 0 ?>">
                        <input type="hidden" name="month" value="<?= $preview_month ?>">
                        <input type="hidden" name="year" value="<?= $preview_year ?>">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Basic Salary *</label>
                                <input type="number" step="0.01" name="basic_salary" class="form-control" value="<?= $current_structure['basic_salary'] ?? 0 ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">HRA</label>
                                <input type="number" step="0.01" name="hra" class="form-control" value="<?= $current_structure['hra'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Conveyance</label>
                                <input type="number" step="0.01" name="conveyance" class="form-control" value="<?= $current_structure['conveyance'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Medical Allowance</label>
                                <input type="number" step="0.01" name="medical_allowance" class="form-control" value="<?= $current_structure['medical_allowance'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Special Allowance</label>
                                <input type="number" step="0.01" name="special_allowance" class="form-control" value="<?= $current_structure['special_allowance'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other Allowances</label>
                                <input type="number" step="0.01" name="other_allowances" class="form-control" value="<?= $current_structure['other_allowances'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PF (Employee)</label>
                                <input type="number" step="0.01" name="pf_employee" class="form-control" value="<?= $current_structure['pf_employee'] ?? 0 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TDS</label>
                                <input type="number" step="0.01" name="tds" class="form-control" value="<?= $current_structure['tds'] ?? 0 ?>">
                            </div>
                        </div>
                        <hr>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Revision Month/Year</label>
                                <div class="input-group">
                                    <select name="month" class="form-select" required>
                                        <?php for ($m=1;$m<=12;$m++): ?>
                                        <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="year" class="form-select" style="max-width:100px" required>
                                        <?php for ($y=date('Y');$y<=date('Y')+2;$y++): ?>
                                        <option value="<?= $y ?>" <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" name="preview_arrears">
                                <i class="fas fa-calculator me-1"></i> Calculate Arrears
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears Results -->
    <?php if ($arrears && isset($arrears['success'])): ?>
    <div class="card mb-4">
        <div class="card-header <?= $arrears['success'] ? 'bg-success' : 'bg-danger' ?> text-white">
            <h5 class="mb-0">
                <?= $arrears['success'] ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-exclamation-triangle me-1"></i>' ?>
                Arrears Calculation Result
            </h5>
        </div>
        <div class="card-body">
            <?php if (!$arrears['success']): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($arrears['message'] ?? 'Calculation failed') ?></div>
            <?php else: ?>
                <div class="alert alert-info">
                    <strong>Arrears Period:</strong> <?= htmlspecialchars($arrears['arrears_period'] ?? '') ?>
                    | <strong>Total Months:</strong> <?= $arrears['summary']['total_months'] ?? 0 ?>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>₹<?= number_format($arrears['summary']['total_arrears_net'] ?? 0, 2) ?></h4>
                                <small>Total Arrears (Net)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>₹<?= number_format($arrears['summary']['total_arrears_basic'] ?? 0, 2) ?></h4>
                                <small>Basic Arrears</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h4>₹<?= number_format($arrears['summary']['total_arrears_gross'] ?? 0, 2) ?></h4>
                                <small>Gross Arrears</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h4>₹<?= number_format($arrears['summary']['total_arrears_deductions'] ?? 0, 2) ?></h4>
                                <small>Additional Deductions</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown -->
                <h6>Monthly Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Month</th>
                                <th>Old Basic</th>
                                <th>New Basic</th>
                                <th>Basic Diff</th>
                                <th>Old Gross</th>
                                <th>New Gross</th>
                                <th>Gross Diff</th>
                                <th>Old Net</th>
                                <th>New Net</th>
                                <th>Net Arrears</th>
                                <th>Already Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($arrears['arrears_months'] as $am): ?>
                            <tr>
                                <td><?= htmlspecialchars($am['month_name'] . ' ' . $am['year']) ?></td>
                                <td>₹<?= number_format($am['old_basic'], 2) ?></td>
                                <td>₹<?= number_format($am['new_basic'], 2) ?></td>
                                <td class="<?= $am['arrears_basic'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    ₹<?= number_format($am['arrears_basic'], 2) ?>
                                </td>
                                <td>₹<?= number_format($am['old_gross'], 2) ?></td>
                                <td>₹<?= number_format($am['new_gross'], 2) ?></td>
                                <td class="<?= $am['arrears_gross'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    ₹<?= number_format($am['arrears_gross'], 2) ?>
                                </td>
                                <td>₹<?= number_format($am['old_net'], 2) ?></td>
                                <td>₹<?= number_format($am['new_net'], 2) ?></td>
                                <td class="<?= $am['arrears_net'] >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                                    ₹<?= number_format($am['arrears_net'], 2) ?>
                                </td>
                                <td>₹<?= number_format($am['already_paid_net'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary fw-bold">
                                <td>TOTAL</td>
                                <td></td><td></td>
                                <td>₹<?= number_format($arrears['summary']['total_arrears_basic'] ?? 0, 2) ?></td>
                                <td></td><td></td>
                                <td>₹<?= number_format($arrears['summary']['total_arrears_gross'] ?? 0, 2) ?></td>
                                <td></td><td></td>
                                <td>₹<?= number_format($arrears['summary']['total_arrears_net'] ?? 0, 2) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Process Button -->
                <?php if (($arrears['summary']['total_arrears_net'] ?? 0) > 0): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/salary/arrears/process" class="mt-3" onsubmit="return confirm('Process arrears payment? This will create payment records for all months.');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="employee_id" value="<?= $employee['id'] ?? 0 ?>">
                    <input type="hidden" name="month" value="<?= $preview_month ?>">
                    <input type="hidden" name="year" value="<?= $preview_year ?>">
                    <!-- Re-post the structure values -->
                    <?php foreach (['basic_salary','hra','conveyance','medical_allowance','special_allowance','other_allowances','pf_employee','tds'] as $field): ?>
                    <input type="hidden" name="<?= $field ?>" value="<?= $_POST[$field] ?? $current_structure[$field] ?? 0 ?>">
                    <?php endforeach; ?>
                    <input type="hidden" name="month" value="<?= $preview_month ?>">
                    <input type="hidden" name="year" value="<?= $preview_year ?>">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check-circle me-1"></i> Process Arrears (₹<?= number_format($arrears['summary']['total_arrears_net'] ?? 0, 2) ?>)
                    </button>
                </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>