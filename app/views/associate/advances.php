<?php
/**
 * Associate Portal — Commission Advances & Loans View
 */
$page_title = $page_title ?? 'My Commission Advances & Loans';
$advances = $advances ?? [];
$active_advance = $active_advance ?? null;
$total_recovered = $total_recovered ?? 0.0;
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="mb-1 fw-bold">
                <i class="fas fa-hand-holding-usd text-primary me-2"></i>Commission Advances & Festival Loans
            </h3>
            <p class="text-muted mb-0">Apply for advances against your upcoming sales commissions with seamless automatic deduction.</p>
        </div>
        <a href="/associate/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Associate Dashboard
        </a>
    </div>

    <!-- Active Advance Banner (if any) -->
    <?php if ($active_advance): ?>
        <?php
        $appAmt = (float)$active_advance['approved_amount'];
        $recAmt = (float)$active_advance['recovered_amount'];
        $remAmt = max(0, $appAmt - $recAmt);
        $prog = $appAmt > 0 ? min(100, round(($recAmt * 100) / $appAmt)) : 0;
        ?>
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <span class="badge bg-light text-primary mb-2 text-uppercase">Active Advance in Repayment</span>
                        <h4 class="fw-bold mb-1">₹<?= number_format($remAmt, 2) ?> Outstanding</h4>
                        <div class="opacity-75 small">
                            Original Advance: ₹<?= number_format($appAmt, 2) ?> · Auto-recovery rate: <?= (float)$active_advance['deduction_pct'] ?>% per payout
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
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="fas fa-paper-plane text-primary me-2"></i>Request Commission Advance
                </div>
                <div class="card-body">
                    <?php if ($active_advance): ?>
                        <div class="alert alert-info small mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            You currently have an active advance under repayment. You can apply for a new advance once the existing balance is fully recovered.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="/associate/advances/apply">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Requested Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="500" name="requested_amount" class="form-control" placeholder="e.g. 15000" min="1000" required>
                                <small class="text-muted">Minimum request amount: ₹1,000</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Preferred Tenure (Months)</label>
                                <select name="tenure_months" class="form-select">
                                    <option value="1">1 Month (100% fast recovery)</option>
                                    <option value="2">2 Months (50% per payout)</option>
                                    <option value="3" selected>3 Months (25% per payout)</option>
                                    <option value="6">6 Months (Extended festival loan)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Purpose of Advance <span class="text-danger">*</span></label>
                                <textarea name="purpose" class="form-control" rows="2" placeholder="e.g. Festival advance, vehicle fuel expenses, client site visit hospitality" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                <i class="fas fa-paper-plane me-1"></i>Submit Advance Request
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Advance History Table -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history text-primary me-2"></i>My Advances History</span>
                    <span class="badge bg-light text-dark"><?= count($advances) ?> Records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref / Date</th>
                                <th>Requested</th>
                                <th>Approved</th>
                                <th>Recovered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($advances)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
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
