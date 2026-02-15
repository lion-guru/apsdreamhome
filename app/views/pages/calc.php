<?php
/**
 * EMI Calculator Page - APS Dream Homes
 * Modern Layout Integrated
 */

require_once __DIR__ . '/init.php';

$page_title = 'EMI Calculator | APS Dream Homes';
$layout = 'modern';

// Handle Calculation
$amount = isset($_REQUEST['amount']) ? (float)$_REQUEST['amount'] : 0;
$mon = isset($_REQUEST['month']) ? (int)$_REQUEST['month'] : 0;
$int = isset($_REQUEST['interest']) ? (float)$_REQUEST['interest'] : 0;

$interest = 0;
$pay = 0;
$month_pay = 0;

if ($amount > 0 && $mon > 0 && $int > 0) {
    $interest = $amount * ($int / 100);
    $pay = $amount + $interest;
    $month_pay = $pay / $mon;
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header py-5 bg-dark text-white text-center mb-0 position-relative overflow-hidden calc-header">
    <div class="container py-5 mt-4" data-aos="fade-up">
        <h1 class="display-3 fw-bold mb-3">EMI Calculator</h1>
        <p class="lead opacity-75 mb-0 mx-auto header-desc">Plan your finances easily with our simple EMI calculator.</p>
    </div>
</div>

<div class="container py-5 mt-5">
    <div class="row g-4">
        <!-- Calculator Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100" data-aos="fade-right">
                <h4 class="fw-bold text-dark mb-4">Calculate Your EMI</h4>
                <form action="" method="post">
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary">Loan Amount (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-primary">₹</span>
                            <input type="number" name="amount" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. 500000" value="<?= $amount > 0 ? $amount : '' ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary">Duration (Months)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-primary"><i class="far fa-calendar-alt"></i></span>
                            <input type="number" name="month" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. 12" value="<?= $mon > 0 ? $mon : '' ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium text-secondary">Interest Rate (%)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-primary"><i class="fas fa-percent"></i></span>
                            <input type="number" step="0.01" name="interest" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. 8.5" value="<?= $int > 0 ? $int : '' ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="calc" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold mt-2 shadow-sm">
                        <i class="fas fa-calculator me-2"></i> Calculate Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Calculation Results -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-white" data-aos="fade-left">
                <h4 class="fw-bold text-dark mb-4">Results Summary</h4>
                
                <?php if ($amount > 0): ?>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <p class="small text-muted mb-1 text-uppercase fw-bold">Monthly EMI</p>
                                <h2 class="fw-bold text-primary mb-0">₹<?= number_format($month_pay, 2) ?></h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-4 h-100">
                                <p class="small text-muted mb-1 text-uppercase fw-bold">Total Interest</p>
                                <h2 class="fw-bold text-dark mb-0">₹<?= number_format($interest, 2) ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 ps-4 border-0 rounded-start">Description</th>
                                    <th class="py-3 text-end pe-4 border-0 rounded-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-3 ps-4 text-secondary">Principal Amount</td>
                                    <td class="py-3 text-end pe-4 fw-bold">₹<?= number_format($amount, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="py-3 ps-4 text-secondary">Loan Duration</td>
                                    <td class="py-3 text-end pe-4 fw-bold"><?= $mon ?> Months</td>
                                </tr>
                                <tr>
                                    <td class="py-3 ps-4 text-secondary">Interest Rate</td>
                                    <td class="py-3 text-end pe-4 fw-bold"><?= $int ?>%</td>
                                </tr>
                                <tr>
                                    <td class="py-3 ps-4 text-secondary">Total Interest Payable</td>
                                    <td class="py-3 text-end pe-4 fw-bold">₹<?= number_format($interest, 2) ?></td>
                                </tr>
                                <tr class="table-primary border-top">
                                    <td class="py-3 ps-4 fw-bold">Total Amount Payable</td>
                                    <td class="py-3 text-end pe-4 fw-bold">₹<?= number_format($pay, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info border-0 rounded-4 p-4 mt-4 mb-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle mt-1 me-3 fs-5"></i>
                            <div>
                                <p class="small mb-0">This calculation is for illustrative purposes only. Actual interest rates and EMI may vary based on bank policies and market conditions.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 text-center">
                        <div class="bg-light rounded-circle p-4 mb-4">
                            <i class="fas fa-chart-pie fa-4x text-muted opacity-25"></i>
                        </div>
                        <h5 class="fw-bold text-secondary">No Data to Display</h5>
                        <p class="text-muted small">Fill in the form on the left to see your EMI breakdown.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .calc-header {
        background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?= get_asset_url('breadcromb.jpg', 'images') ?>') center/cover no-repeat;
    }
    .header-desc {
        max-width: 700px;
    }
    .table-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
</style>

<?php
$content = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>
