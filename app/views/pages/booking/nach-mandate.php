<?php
$page_title = $page_title ?? 'NACH Mandate - APS Dream Home';
$current_page = 'bookings';
$booking = $booking ?? null;
$mandate = $mandate ?? null;
$user = $user ?? [];
$csrf_token = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');

$statusColors = [
    'draft'     => 'secondary',
    'submitted' => 'info',
    'approved'  => 'success',
    'rejected'  => 'danger',
    'cancelled' => 'warning',
    'expired'   => 'dark',
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-university me-2"></i>NACH Auto-Debit Mandate</h2>
            <?php if ($booking): ?>
                <p>Booking <?= htmlspecialchars($booking['booking_number'] ?? '') ?> &mdash; <?= htmlspecialchars($booking['colony_name'] ?? '') ?>, Plot <?= htmlspecialchars($booking['plot_number'] ?? '') ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <?php if ($booking): ?>
                <a href="<?= BASE_URL ?>/user/bookings/<?= (int)$booking['id'] ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Back to Booking
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Form or Status -->
    <div class="col-lg-8">
        <?php if (!empty($mandate) && !empty($mandate['success']) && !empty($mandate['data'])): ?>
            <!-- Existing mandate status -->
            <?php $m = $mandate['data']; ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <h5><i class="fas fa-info-circle text-primary"></i> Mandate Status</h5>
                    <span class="badge bg-<?= $statusColors[$m['status']] ?? 'secondary' ?>"><?= ucfirst($m['status']) ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="text-muted small">Bank Name</label>
                            <p class="fw-semibold mb-0"><?= htmlspecialchars($m['bank_name'] ?? '') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small">Account Number</label>
                            <p class="fw-semibold mb-0">****<?= htmlspecialchars(substr($m['account_number'] ?? '', -4)) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small">IFSC Code</label>
                            <p class="fw-semibold mb-0"><?= htmlspecialchars($m['ifsc_code'] ?? '') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small">Mandate Amount Cap</label>
                            <p class="fw-semibold mb-0">₹<?= number_format((float)($m['mandate_amount'] ?? 0)) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small">Frequency</label>
                            <p class="fw-semibold mb-0"><?= ucfirst($m['frequency'] ?? 'Monthly') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small">Next Debit Date</label>
                            <p class="fw-semibold mb-0"><?= $m['next_debit_date'] ? date('d M Y', strtotime($m['next_debit_date'])) : 'Pending' ?></p>
                        </div>
                    </div>

                    <?php if (($m['status'] ?? '') === 'approved'): ?>
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Your NACH mandate is active. EMI installments will be auto-debited on due dates.
                    </div>
                    <?php elseif (($m['status'] ?? '') === 'rejected'): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-times-circle me-2"></i>
                        Your mandate was rejected. <?= htmlspecialchars($m['rejection_reason'] ?? 'Please check your bank details and try again.') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Registration form -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <h5><i class="fas fa-university text-primary"></i> Register NACH Mandate</h5>
                </div>
                <div class="aps-cp-card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What is NACH?</strong> NACH (National Automated Clearing House) enables automatic EMI deduction from your bank account every month. You never miss a payment, and your credit score stays healthy.
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/user/bookings/<?= (int)($booking['id'] ?? 0) ?>/nach/register">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" class="form-control" placeholder="e.g. State Bank of India" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                <input type="text" name="account_holder_name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter account number" required pattern="[0-9]{9,18}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="e.g. SBIN0001234" required pattern="[A-Z]{4}0[A-Z0-9]{6}" maxlength="11" style="text-transform:uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mandate Type</label>
                                <select name="mandate_type" class="form-select">
                                    <option value="emandate">e-Mandate (Online - Recommended)</option>
                                    <option value="nach">NACH Physical Form</option>
                                    <option value="physical">Physical Mandate</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Monthly Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="mandate_amount" class="form-control" min="100" step="0.01"
                                       value="<?= number_format(($booking['total_plot_value'] ?? 0) / 24, 0, '.', '') ?>" required>
                                <small class="text-muted">Maximum amount that can be auto-debited per month.</small>
                            </div>
                        </div>

                        <div class="form-check mt-4 mb-3">
                            <input class="form-check-input" type="checkbox" id="nachConsent" required>
                            <label class="form-check-label" for="nachConsent">
                                I authorize APS Dream Home and my bank to process NACH auto-debit instructions for my EMI installments as per the terms and conditions.
                            </label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="nachTerms" required>
                            <label class="form-check-label" for="nachTerms">
                                I understand that my first EMI will be auto-debited on the next due date after mandate approval, and I will maintain sufficient balance in my account.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Register NACH Mandate
                        </button>
                        <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right: Info sidebar -->
    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-question-circle text-primary"></i> NACH Benefits</h5>
            </div>
            <div class="aps-cp-card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Never miss an EMI</strong><br>
                        <small class="text-muted">Auto-deduction ensures timely payments</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Better credit score</strong><br>
                        <small class="text-muted">On-time payments boost CIBIL score</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>No late fees</strong><br>
                        <small class="text-muted">Avoid 18% p.a. penalty on overdue installments</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Cancel anytime</strong><br>
                        <small class="text-muted">You can deactivate NACH from this portal</small>
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-shield-alt text-primary me-2"></i>
                        <strong>Secure & RBI-regulated</strong><br>
                        <small class="text-muted">NACH is managed by NPCI under RBI guidelines</small>
                    </li>
                </ul>
            </div>
        </div>

        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-phone text-success"></i> Need Help?</h5>
            </div>
            <div class="aps-cp-card-body">
                <p class="text-muted small mb-2">Having trouble with NACH registration?</p>
                <p class="mb-1"><i class="fas fa-phone me-2"></i>+91 92771 21112</p>
                <p class="mb-0"><i class="fas fa-envelope me-2"></i>nach@apsdreamhome.com</p>
            </div>
        </div>
    </div>
</div>
