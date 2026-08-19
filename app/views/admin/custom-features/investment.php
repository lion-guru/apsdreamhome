<?php
/**
 * Investment Calculator Page
 * @var array $calculationResult
 * @var array $inputs
 */
$base = BASE_URL;
?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-calculator me-2"></i> Investment Calculator</h4>
        <a href="<?= $base ?>/admin/custom-features" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <!-- Calculator Form -->
        <div class="col-md-6 mb-4">
            <div class="card aps-cp-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Calculate Investment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $base ?>/admin/custom-features/investment-calculate">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Property Price (₹)</label>
                                <input type="number" name="property_price" class="form-control" step="100000" 
                                       value="<?= htmlspecialchars($inputs['property_price'] ?? '') ?>" 
                                       placeholder="e.g., 5000000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Down Payment %</label>
                                <input type="number" name="down_payment_pct" class="form-control" min="0" max="100" step="1"
                                       value="<?= htmlspecialchars($inputs['down_payment_pct'] ?? 20) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Interest Rate %</label>
                                <input type="number" name="interest_rate" class="form-control" min="0" max="30" step="0.1"
                                       value="<?= htmlspecialchars($inputs['interest_rate'] ?? 8.5) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Tenure (Years)</label>
                                <input type="number" name="loan_tenure" class="form-control" min="1" max="30"
                                       value="<?= htmlspecialchars($inputs['loan_tenure'] ?? 20) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expected Annual Appreciation %</label>
                                <input type="number" name="appreciation_rate" class="form-control" min="0" max="20" step="0.1"
                                       value="<?= htmlspecialchars($inputs['appreciation_rate'] ?? 5) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Monthly Rental Income (₹)</label>
                                <input type="number" name="monthly_rent" class="form-control" min="0" step="1000"
                                       value="<?= htmlspecialchars($inputs['monthly_rent'] ?? 15000) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Annual Maintenance %</label>
                                <input type="number" name="maintenance_pct" class="form-control" min="0" max="5" step="0.1"
                                       value="<?= htmlspecialchars($inputs['maintenance_pct'] ?? 1) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Investment Horizon (Years)</label>
                                <input type="number" name="investment_horizon" class="form-control" min="1" max="30"
                                       value="<?= htmlspecialchars($inputs['investment_horizon'] ?? 10) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax Bracket %</label>
                                <input type="number" name="tax_bracket" class="form-control" min="0" max="50" step="1"
                                       value="<?= htmlspecialchars($inputs['tax_bracket'] ?? 30) ?>" required>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-calculator me-2"></i> Calculate Investment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-md-6 mb-4">
            <div class="card aps-cp-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Investment Analysis</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($calculationResult)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-chart-line fa-4x mb-3"></i>
                            <h5>Enter details to calculate</h5>
                            <p class="text-muted">Fill in the form and click Calculate to see investment analysis</p>
                        </div>
                    <?php else: ?>
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="card aps-cp-card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total Investment</h6>
                                        <h4 class="fw-bold text-primary">₹<?= number_format($calculationResult['total_investment'] ?? 0) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card aps-cp-card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Loan Amount</h6>
                                        <h4 class="fw-bold text-info">₹<?= number_format($calculationResult['loan_amount'] ?? 0) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="card aps-cp-card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Monthly EMI</h6>
                                        <h4 class="fw-bold text-warning">₹<?= number_format($calculationResult['monthly_emi'] ?? 0) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card aps-cp-card h-100">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Total Interest</h6>
                                        <h4 class="fw-bold text-danger">₹<?= number_format($calculationResult['total_interest'] ?? 0) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Returns Analysis -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Returns After <?= $inputs['investment_horizon'] ?? 10 ?> Years</h6>
                            <div class="row">
                                <div class="col-4">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Property Value</h6>
                                            <h4 class="fw-bold text-success">₹<?= number_format($calculationResult['future_property_value'] ?? 0) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Total Rental Income</h6>
                                            <h4 class="fw-bold text-primary">₹<?= number_format($calculationResult['total_rental_income'] ?? 0) ?></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Net Profit</h6>
                                            <h4 class="fw-bold <?= ($calculationResult['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                ₹<?= number_format($calculationResult['net_profit'] ?? 0) ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ROI -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">ROI Metrics</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Total ROI</h6>
                                            <h4 class="fw-bold <?= ($calculationResult['total_roi_pct'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($calculationResult['total_roi_pct'] ?? 0, 1) ?>%
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card aps-cp-card h-100">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Annualized ROI</h6>
                                            <h4 class="fw-bold <?= ($calculationResult['annualized_roi'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($calculationResult['annualized_roi'] ?? 0, 1) ?>%
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Flow -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Monthly Cash Flow</h6>
                            <div class="card aps-cp-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <span>Rental Income</span>
                                        <span class="text-success">+₹<?= number_format($calculationResult['monthly_rent'] ?? 0) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>EMI Payment</span>
                                        <span class="text-danger">-₹<?= number_format($calculationResult['monthly_emi'] ?? 0) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Maintenance</span>
                                        <span class="text-warning">-₹<?= number_format($calculationResult['monthly_maintenance'] ?? 0) ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Net Cash Flow</span>
                                        <span class="<?= ($calculationResult['monthly_cash_flow'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($calculationResult['monthly_cash_flow'] ?? 0) >= 0 ? '+' : '' ?>₹<?= number_format($calculationResult['monthly_cash_flow'] ?? 0) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>