<?php
/**
 * Associate Portal - EMI Calculator
 */
$page_title = $page_title ?? 'EMI Calculator - Associate Portal';
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/tools">Tools</a></li>
                    <li class="breadcrumb-item active" aria-current="page">EMI Calculator</li>
                </ol>
            </nav>
            <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-calculator me-2 text-primary"></i> Loan EMI Calculator</h2>
        </div>
        <a href="<?= $base ?>/associate/tools" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Tools
        </a>
    </div>

    <div class="row">
        <!-- Form -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-sliders-h me-2"></i> Loan Parameters</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= $base ?>/associate/tools/emi">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Loan Amount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="amount" class="form-control" placeholder="e.g. 2500000" 
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '2500000') ?>" required min="10000" step="5000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Annual Interest Rate (%)</label>
                            <div class="input-group">
                                <input type="number" name="rate" class="form-control" placeholder="e.g. 8.5" 
                                       value="<?= htmlspecialchars($_POST['rate'] ?? '8.5') ?>" required min="1" max="30" step="0.1">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Loan Tenure (Years)</label>
                            <div class="input-group">
                                <input type="number" name="tenure" class="form-control" placeholder="e.g. 20" 
                                       value="<?= htmlspecialchars($_POST['tenure'] ?? '20') ?>" required min="1" max="30">
                                <span class="input-group-text">Years</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-calculator me-2"></i> Calculate EMI
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-success"><i class="fas fa-chart-pie me-2"></i> EMI Summary</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <?php if ($result): ?>
                        <div class="text-center mb-4">
                            <span class="text-muted d-block mb-1">Monthly Payment (EMI)</span>
                            <h2 class="display-5 fw-bold text-success">₹<?= number_format($result['emi'], 2) ?></h2>
                        </div>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Principal Amount</span>
                                <span class="fw-bold">₹<?= number_format($result['principal'], 2) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Total Interest</span>
                                <span class="fw-bold text-danger">₹<?= number_format($result['total_interest'], 2) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Total Amount Payable</span>
                                <span class="fw-bold text-primary">₹<?= number_format($result['total_payment'], 2) ?></span>
                            </li>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-coins fa-3x mb-3 text-secondary"></i>
                            <p>Enter the loan details and click Calculate to view the monthly EMI and payment schedule.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
