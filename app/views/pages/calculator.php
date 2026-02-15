<?php
/**
 * EMI Calculator View - APS Dream Home
 */
?>

<!-- Page Header -->
<section class="calculator-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">EMI Calculator</h1>
        <p class="lead mb-0">Plan your property investment with our easy-to-use EMI calculator.</p>
    </div>
</section>

<style>
    .calculator-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
</style>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2 mb-4">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<!-- Calculator Section -->
<section class="calculator-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <!-- Input Form -->
                        <div class="col-md-5 bg-white p-4 p-lg-5">
                            <h4 class="fw-bold mb-4">Calculate EMI</h4>
                            <form action="<?= BASE_URL ?>calc" method="POST">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Loan Amount (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                        <input type="number" name="amount" class="form-control bg-light border-0" placeholder="e.g. 500000" value="<?= $_REQUEST['amount'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Interest Rate (%)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-percent text-primary"></i></span>
                                        <input type="number" step="0.01" name="interest" class="form-control bg-light border-0" placeholder="e.g. 8.5" value="<?= $_REQUEST['interest'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Duration (Months)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                                        <input type="number" name="month" class="form-control bg-light border-0" placeholder="e.g. 12" value="<?= $_REQUEST['month'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <button type="submit" name="calc" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                                    Calculate Now
                                </button>
                            </form>
                        </div>

                        <!-- Results Display -->
                        <div class="col-md-7 bg-primary text-white p-4 p-lg-5 d-flex flex-column justify-content-center">
                            <?php if (isset($calc_result)): ?>
                                <h4 class="fw-bold mb-4 text-white">Calculation Summary</h4>
                                <div class="row g-4">
                                    <div class="col-sm-6">
                                        <div class="result-item p-3 bg-white bg-opacity-10 rounded-3 h-100">
                                            <p class="small mb-1 opacity-75">Monthly EMI</p>
                                            <h3 class="fw-bold mb-0">₹<?= number_format($calc_result['monthly_installment'], 2) ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="result-item p-3 bg-white bg-opacity-10 rounded-3 h-100">
                                            <p class="small mb-1 opacity-75">Total Interest</p>
                                            <h3 class="fw-bold mb-0">₹<?= number_format($calc_result['total_interest'], 2) ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="result-item p-3 bg-white bg-opacity-10 rounded-3">
                                            <p class="small mb-1 opacity-75">Total Payable Amount</p>
                                            <h3 class="fw-bold mb-0">₹<?= number_format($calc_result['total_payable'], 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 border border-white border-opacity-25 rounded-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small opacity-75">Principal Amount</span>
                                        <span class="fw-bold">₹<?= number_format($calc_result['amount'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="small opacity-75">Loan Tenure</span>
                                        <span class="fw-bold"><?= $calc_result['months'] ?> Months</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calculator fa-4x mb-4 opacity-25"></i>
                                    <h4 class="fw-bold">Ready to Calculate?</h4>
                                    <p class="opacity-75">Enter your loan details on the left to see your monthly installments and total interest.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
