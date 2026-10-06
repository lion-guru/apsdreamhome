<?php
/**
 * Gratuity Calculator View
*/

$page_title = $page_title ?? 'Gratuity Calculator';
$employees = $employees ?? [];
$selected_employee = $selected_employee ?? 0;
$calculation_date = $calculation_date ?? date('Y-m-d');
$result = $result ?? null;
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-calculator me-2"></i>Gratuity Calculator</h4>
                </div>
                <div class="card-body">
                    <!-- Employee Selection -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= $selected_employee == $emp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['name']) ?> (<?= $emp['employee_code'] ?>) - <?= htmlspecialchars($emp['designation']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Calculation Date</label>
                            <input type="date" name="calculation_date" class="form-control" value="<?= htmlspecialchars($calculation_date) ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Load Employee
                            </button>
                        </div>
                    </form>

                    <?php if ($selected_employee && !$result): ?>
                    <div class="alert alert-info">
                        Select calculation date and click Load Employee to calculate gratuity.
                    </div>
                    <?php endif; ?>

                    <!-- Result -->
                    <?php if (isset($result)): ?>
                    <hr>
                    <?php if (!$result['success']): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($result['message']) ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success">
                        <h5 class="alert-heading">
                            <i class="fas fa-check-circle me-2"></i>
                            Gratuity Calculation Complete
                        </h5>
                        <p class="mb-0">
                            Employee is <strong class="<?= $result['eligible'] ? 'text-success' : 'text-danger' ?>">
                                <?= $result['eligible'] ? 'ELIGIBLE' : 'NOT ELIGIBLE' ?>
                            </strong> for gratuity.
                        </p>
                    </div>

                    <!-- Employee Info -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Employee Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3"><strong>Name:</strong> <?= htmlspecialchars($result['employee']['name']) ?></div>
                                <div class="col-md-3"><strong>Code:</strong> <?= htmlspecialchars($result['employee']['employee_code']) ?></div>
                                <div class="col-md-3"><strong>Designation:</strong> <?= htmlspecialchars($result['employee']['designation']) ?></div>
                                <div class="col-md-3"><strong>Department:</strong> <?= htmlspecialchars($result['employee']['department']) ?></div>
                                <div class="col-md-3"><strong>Joining Date:</strong> <?= date('d M Y', strtotime($result['employee']['joining_date'])) ?></div>
                                <div class="col-md-3"><strong>Calculation Date:</strong> <?= date('d M Y', strtotime($result['employee']['calculation_date'])) ?></div>
                                <div class="col-md-3"><strong>Tenure:</strong> <?= $result['tenure']['years'] ?> yrs <?= $result['tenure']['months'] ?> months <?= $result['tenure']['days'] ?> days</div>
                                <div class="col-md-3"><strong>Service Years (for gratuity):</strong> <strong><?= $result['service_years_for_gratuity'] ?> years</strong></div>
                                <div class="col-md-3"><strong>Basic Salary:</strong> ₹<?= number_format($result['basic_salary'], 2) ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($result['eligible']): ?>
                    <!-- Eligible Result -->
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Eligible for Gratuity</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h2 class="mb-0">₹<?= number_format($result['gratuity_amount'], 2) ?></h2>
                                            <small>Gratuity Amount</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4 class="mb-0"><?= $result['service_years_for_gratuity'] ?></h4>
                                            <small>Years of Service</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4 class="mb-0">₹<?= number_format($result['basic_salary'], 2) ?></h4>
                                            <small>Monthly Basic</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-<?= $result['capped'] ? 'warning' : 'info' ?>">
                                <h6 class="alert-heading">
                                    <i class="fas fa-<?= $result['capped'] ? 'exclamation-triangle' : 'info-circle' ?> me-2"></i>
                                    Calculation Details
                                </h6>
                                <p class="mb-1"><strong>Formula:</strong> (15/26) × Basic × Years of Service</p>
                                <p class="mb-1"><strong>Per Day Basic:</strong> ₹<?= number_format($result['per_day_basic'], 2) ?> (Basic / 26)</p>
                                <p class="mb-1"><strong>Calculation:</strong> (<?= number_format($result['basic_salary'], 2) ?> / 26) × 15 × <?= $result['service_years_for_gratuity'] ?> = ₹<?= number_format($result['gratuity_amount'], 2) ?></p>
                                <?php if ($result['capped']): ?>
                                <p class="mb-0"><strong>Statutory Cap Applied:</strong> Maximum gratuity capped at ₹20,00,000 as per Payment of Gratuity Act, 1972</p>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= BASE_URL ?>/admin/gratuity/calculator" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> New Calculation
                                </a>
                                <a href="<?= BASE_URL ?>/admin/gratuity/report" class="btn btn-outline-primary ms-2">
                                    <i class="fas fa-file-alt me-1"></i> View Liability Report
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Not Eligible -->
                    <div class="card mb-3 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Not Eligible for Gratuity</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Not Eligible:</strong> Requires 5 years continuous service.
                            </alert>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Current Service:</strong> <?= $result['service_years_for_gratuity'] ?> years
                                </div>
                                <div class="col-md-6">
                                    <strong>Requirement:</strong> 5 years continuous service
                                </div>
                            </div>
                            <p class="text-muted mt-2">
                                <small>Note: For gratuity calculation, months ≥ 6 are rounded up to the next year. 
                                Current service: <?= $result['tenure']['years'] ?> years <?= $result['tenure']['months'] ?> months (counted as <?= $result['service_years_for_gratuity'] ?> years).</small>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
</style>