<?php
/**
 * Associate Portal — Commission Advances & Loans View
 */
$page_title = $page_title ?? 'My Commission Advances & Loans';
$advances = $advances ?? [];
$active_advance = $active_advance ?? null;
$total_recovered = $total_recovered ?? 0.0;
$csrf = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$totalRequested = 0.0;
$totalApproved = 0.0;
foreach ($advances as $a) {
    $totalRequested += (float)($a['requested_amount'] ?? 0);
    $totalApproved += (float)($a['approved_amount'] ?? 0);
}
?>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/commissions">Earnings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Advances & Loans</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 text-dark fw-bold">
                <i class="fas fa-hand-holding-usd text-teal me-2" style="color: #0d9488;"></i>Commission Advances & Festival Loans
            </h2>
            <small class="text-muted">Apply for advances against your upcoming sales commissions with seamless automatic deduction</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/associate/commissions" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-rupee-sign me-1"></i> Commission Ledger
            </a>
            <a href="<?= $base ?>/associate/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Top Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark">₹<?= number_format($totalApproved, 2) ?></div>
                            <div class="text-muted small">Total Advances Approved</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark">₹<?= number_format($total_recovered, 2) ?></div>
                            <div class="text-muted small">Total Repaid via Commissions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <?php
                            $activeBal = $active_advance ? max(0, (float)$active_advance['approved_amount'] - (float)$active_advance['recovered_amount']) : 0;
                            ?>
                            <div class="fs-5 fw-bold text-dark">₹<?= number_format($activeBal, 2) ?></div>
                            <div class="text-muted small">Active Outstanding Balance</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark"><?= count($advances) ?> Applications</div>
                            <div class="text-muted small">Lifetime Request History</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Advance Banner (if any) -->
    <?php if ($active_advance): ?>
        <?php
        $appAmt = (float)$active_advance['approved_amount'];
        $recAmt = (float)$active_advance['recovered_amount'];
        $remAmt = max(0, $appAmt - $recAmt);
        $prog = $appAmt > 0 ? min(100, round(($recAmt * 100) / $appAmt)) : 0;
        ?>
        <div class="card bg-dark border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <span class="badge bg-light text-teal mb-2 text-uppercase fw-bold" style="color: #0f766e;">Active Advance in Repayment</span>
                        <h3 class="fw-bold mb-1">₹<?= number_format($remAmt, 2) ?> Outstanding</h3>
                        <div class="opacity-75 small">
                            Original Advance: ₹<?= number_format($appAmt, 2) ?> · Auto-recovery rate: <?= (float)$active_advance['deduction_pct'] ?>% per commission payout
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between small mb-1 opacity-90">
                            <span>Repaid: ₹<?= number_format($recAmt, 2) ?></span>
                            <span class="fw-bold"><?= $prog ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px; background: rgba(255,255,255,0.25);">
                            <div class="progress-bar bg-white" style="width: <?= $prog ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left: Apply Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold border-0">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-paper-plane text-primary me-2"></i> Request Commission Advance</h5>
                </div>
                <div class="card-body p-4 pt-1">
                    <?php if ($active_advance): ?>
                        <div class="alert alert-info small mb-0 rounded-3">
                            <i class="fas fa-info-circle me-1"></i>
                            You currently have an active advance under repayment. You can apply for a new advance once the existing balance is fully recovered.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?= $base ?>/associate/advances/apply">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Requested Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">₹</span>
                                    <input aria-label="Requested amount" type="number" step="500" name="requested_amount" class="form-control" placeholder="e.g. 15000" min="1000" required>
                                </div>
                                <small class="text-muted">Minimum request amount: ₹1,000</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Preferred Tenure (Months)</label>
                                <select aria-label="Tenure months" name="tenure_months" class="form-select">
                                    <option value="1">1 Month (100% fast recovery)</option>
                                    <option value="2">2 Months (50% per payout)</option>
                                    <option value="3" selected>3 Months (25% per payout)</option>
                                    <option value="6">6 Months (Extended festival loan)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Purpose of Advance <span class="text-danger">*</span></label>
                                <textarea aria-label="Purpose" name="purpose" class="form-control" rows="3" placeholder="e.g. Festival advance, vehicle fuel expenses, client site visit hospitality" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 text-white shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="fas fa-paper-plane me-1"></i> Submit Advance Request
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Advance History Table -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-history text-primary me-2"></i> My Advances History</h5>
                    <span class="badge bg-light text-dark border"><?= count($advances) ?> Records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Ref / Date</th>
                                <th scope="col">Requested</th>
                                <th scope="col">Approved</th>
                                <th scope="col">Recovered</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($advances)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-2 opacity-50 d-block"></i>
                                        No past advance requests found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($advances as $a): ?>
                                    <?php
                                    $st = $a['status'] ?? 'pending';
                                    $badge = match ($st) {
                                        'pending' => 'warning text-dark',
                                        'approved' => 'info text-white',
                                        'disbursed' => 'primary',
                                        'repaid' => 'success',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">#<?= (int)$a['id'] ?></div>
                                            <small class="text-muted"><?= date('d M Y', strtotime($a['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div>₹<?= number_format((float)$a['requested_amount'], 2) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($a['purpose'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <?php if ((float)$a['approved_amount'] > 0): ?>
                                                <div class="fw-semibold text-dark">₹<?= number_format((float)$a['approved_amount'], 2) ?></div>
                                                <small class="text-muted"><?= (float)$a['deduction_pct'] ?>% rate</small>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-success fw-bold">₹<?= number_format((float)$a['recovered_amount'], 2) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $badge ?> text-uppercase"><?= $st ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
