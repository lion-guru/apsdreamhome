<?php
$calculations = $calculations ?? [];
$properties = $properties ?? [];
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Property ROI Calculator</h1>

    <div class="row">
        <!-- Input Form -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Investment Details</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="/admin/reports/roi-calculator">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Property Price (₹)</label>
                            <input type="number" name="property_price" class="form-control" 
                                   value="<?= $_POST['property_price'] ?? '5000000' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Down Payment (₹)</label>
                            <input type="number" name="down_payment" class="form-control" 
                                   value="<?= $_POST['down_payment'] ?? '1000000' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Loan Amount (₹)</label>
                            <input type="number" name="loan_amount" class="form-control" 
                                   value="<?= $_POST['loan_amount'] ?? '4000000' ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interest Rate (%)</label>
                                <input type="number" step="0.1" name="interest_rate" class="form-control" 
                                       value="<?= $_POST['interest_rate'] ?? '7.5' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loan Tenure (Years)</label>
                                <input type="number" name="loan_tenure" class="form-control" 
                                       value="<?= $_POST['loan_tenure'] ?? '20' ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expected Monthly Rent (₹)</label>
                            <input type="number" name="expected_rent" class="form-control" 
                                   value="<?= $_POST['expected_rent'] ?? '25000' ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Annual Appreciation (%)</label>
                                <input type="number" step="0.1" name="annual_appreciation" class="form-control" 
                                       value="<?= $_POST['annual_appreciation'] ?? '5' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Annual Expenses (₹)</label>
                                <input type="number" name="annual_expenses" class="form-control" 
                                       value="<?= $_POST['annual_expenses'] ?? '50000' ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-calculator"></i> Calculate ROI
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Property Select -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Quick Select Property</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <select id="quickProperty" class="form-select" onchange="fillPropertyDetails()">
                        <option value="">-- Select a Property --</option>
                        <?php foreach ($properties as $property): ?>
                        <option value="<?= $property['price'] ?>"><?= htmlspecialchars($property['title']) ?> - ₹<?= number_format($property['price']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Auto-fills property price</small>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-8">
            <?php if (!empty($calculations)): ?>
            <!-- Recommendation Banner -->
            <div class="alert alert-<?= strpos($calculations['recommendation'], 'EXCELLENT') !== false || strpos($calculations['recommendation'], 'GOOD') !== false ? 'success' : (strpos($calculations['recommendation'], 'MODERATE') !== false ? 'warning' : 'danger') ?> mb-4">
                <h5 class="alert-heading"><i class="fas fa-chart-line"></i> Investment Analysis</h5>
                <p class="mb-0"><?= $calculations['recommendation'] ?></p>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Monthly EMI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($calculations['emi'], 2) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Net Rental Yield</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $calculations['net_rental_yield'] ?>%</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Annualized ROI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $calculations['roi_annualized'] ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Break-Even (Years)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $calculations['break_even_years'] ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Future Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?= number_format($calculations['future_property_value'], 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-secondary shadow h-100 py-2">
                        <div class="card-body aps-cp-card-body">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total ROI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $calculations['roi'] ?>%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Metrics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detailed Investment Metrics</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td width="50%"><strong>Annual EMI Payment</strong></td>
                                    <td>₹<?= number_format($calculations['annual_emi'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Gross Rental Yield</strong></td>
                                    <td><?= $calculations['gross_rental_yield'] ?>%</td>
                                </tr>
                                <tr>
                                    <td><strong>Capital Appreciation</strong></td>
                                    <td>₹<?= number_format($calculations['capital_appreciation'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Return</strong></td>
                                    <td>₹<?= number_format($calculations['total_return'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Investment</strong></td>
                                    <td>₹<?= number_format($calculations['inputs']['down_payment'] + ($calculations['annual_emi'] * $calculations['inputs']['loan_tenure']), 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cash Flow Projection -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">10-Year Cash Flow Projection</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="cashFlowTable">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Rental Income</th>
                                    <th>EMI Paid</th>
                                    <th>Expenses</th>
                                    <th>Net Cash Flow</th>
                                    <th>Property Value</th>
                                    <th>Equity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calculations['cash_flow'] as $year): ?>
                                <tr>
                                    <td><?= $year['year'] ?></td>
                                    <td>₹<?= number_format($year['rental_income'], 0) ?></td>
                                    <td>₹<?= number_format($year['emi_paid'], 0) ?></td>
                                    <td>₹<?= number_format($year['expenses'], 0) ?></td>
                                    <td class="<?= $year['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        ₹<?= number_format($year['net_cash_flow'], 0) ?>
                                    </td>
                                    <td>₹<?= number_format($year['property_value'], 0) ?></td>
                                    <td>₹<?= number_format($year['equity'], 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calculator fa-4x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Enter investment details to calculate ROI</h5>
                    <p class="text-muted">Fill in the form on the left to see detailed ROI analysis</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function fillPropertyDetails() {
    const select = document.getElementById('quickProperty');
    const price = select.value;
    if (price) {
        document.querySelector('input[name="property_price"]').value = price;
        document.querySelector('input[name="down_payment"]').value = Math.round(price * 0.2);
        document.querySelector('input[name="loan_amount"]').value = Math.round(price * 0.8);
    }
}
</script>

<?php  ?>