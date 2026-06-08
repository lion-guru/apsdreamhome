<?php
$page_title = $page_title ?? 'Payment Successful';
$current_page = 'bookings';
$user = $user ?? [];
$booking = $booking ?? null;
$payment = $payment ?? null;
$token_amount = $token_amount ?? 25000;
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-check-circle me-2"></i>Payment Successful</h2>
            <p>Your token payment has been processed successfully.</p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-light">
                <i class="fas fa-list me-2"></i>My Bookings
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="text-center mb-4">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;box-shadow:0 8px 24px rgba(16,185,129,0.3);">
                <i class="fas fa-check" style="font-size:36px;color:#fff;"></i>
            </div>
            <h3 class="mt-2">Thank You, <?= htmlspecialchars($user['name'] ?? '') ?>!</h3>
            <p class="text-muted">Your token payment of <strong class="text-success">₹<?= number_format($token_amount) ?></strong> has been received.</p>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-receipt text-success"></i> Payment Receipt</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Booking Number</small>
                        <strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Payment Date</small>
                        <strong><?= $payment ? date('d M Y, h:i A', strtotime($payment['created_at'])) : date('d M Y, h:i A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Amount Paid</small>
                        <strong class="text-success fs-5">₹<?= number_format((float)($payment['amount'] ?? $token_amount)) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong><i class="fas fa-credit-card me-1"></i> Razorpay (Online)</strong>
                    </div>
                    <?php if (!empty($payment['transaction_id'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Transaction ID</small>
                        <strong class="text-break"><?= htmlspecialchars($payment['transaction_id']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($payment['gateway_transaction_id'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Gateway Reference</small>
                        <strong class="text-break"><?= htmlspecialchars($payment['gateway_transaction_id']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Payment Status</small>
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> Completed</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Plot</small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong> at <?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-list-check text-primary"></i> Next Steps</h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3" style="width:32px;height:32px;border-radius:50%;background:var(--aps-cp-primary-bg,#eef2ff);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-file-signature text-primary" style="font-size:14px;"></i>
                    </div>
                    <div>
                        <strong>Sign Agreement</strong>
                        <p class="mb-0 text-muted small">Our team will contact you within 24 hours to schedule agreement signing.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3" style="width:32px;height:32px;border-radius:50%;background:var(--aps-cp-primary-bg,#eef2ff);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-calendar-check text-primary" style="font-size:14px;"></i>
                    </div>
                    <div>
                        <strong>EMI Schedule</strong>
                        <p class="mb-0 text-muted small">After agreement, your EMI plan will be generated with monthly installment dates.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div class="me-3" style="width:32px;height:32px;border-radius:50%;background:var(--aps-cp-primary-bg,#eef2ff);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-key text-primary" style="font-size:14px;"></i>
                    </div>
                    <div>
                        <strong>Allotment & Possession</strong>
                        <p class="mb-0 text-muted small">Upon full payment, you'll receive your allotment letter and possession details.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-primary w-100">
                    <i class="fas fa-eye me-2"></i>View Booking Details
                </a>
            </div>
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary w-100">
                    <i class="fas fa-list me-2"></i>All My Bookings
                </a>
            </div>
        </div>

    </div>
</div>
