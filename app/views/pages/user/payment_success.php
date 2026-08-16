<?php
$page_title = $page_title ?? __('user_payment_success_page_title', 'Payment Successful');
$current_page = 'bookings';
$user = $user ?? [];
$booking = $booking ?? null;
$payment = $payment ?? null;
$token_amount = $token_amount ?? 25000;
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-check-circle me-2"></i><?= __('user_payment_success_hero_heading', 'Payment Successful') ?></h2>
            <p><?= __('user_payment_success_hero_subtitle', 'Your token payment has been processed successfully.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-light">
                <i class="fas fa-list me-2"></i><?= __('user_payment_success_my_bookings', 'My Bookings') ?>
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="text-center mb-4">
            <div class="style-75714">
                <i class="fas fa-check" class="style-3728"></i>
            </div>
            <h3 class="mt-2"><?= __('user_payment_success_thank_you', 'Thank You,') ?> <?= htmlspecialchars($user['name'] ?? '') ?>!</h3>
            <p class="text-muted"><?= __('user_payment_success_received_prefix', 'Your token payment of') ?> <strong class="text-success">₹<?= number_format($token_amount) ?></strong> <?= __('user_payment_success_received_suffix', 'has been received.') ?></p>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-receipt text-success"></i> <?= __('user_payment_success_receipt_header', 'Payment Receipt') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_booking_number', 'Booking Number') ?></small>
                        <strong><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_payment_date', 'Payment Date') ?></small>
                        <strong><?= $payment ? date('d M Y, h:i A', strtotime($payment['created_at'])) : date('d M Y, h:i A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_amount_paid', 'Amount Paid') ?></small>
                        <strong class="text-success fs-5">₹<?= number_format((float)($payment['amount'] ?? $token_amount)) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_payment_method', 'Payment Method') ?></small>
                        <strong><i class="fas fa-credit-card me-1"></i> <?= __('user_payment_success_razorpay_online', 'Razorpay (Online)') ?></strong>
                    </div>
                    <?php if (!empty($payment['transaction_id'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_transaction_id', 'Transaction ID') ?></small>
                        <strong class="text-break"><?= htmlspecialchars($payment['transaction_id']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($payment['gateway_transaction_id'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_gateway_reference', 'Gateway Reference') ?></small>
                        <strong class="text-break"><?= htmlspecialchars($payment['gateway_transaction_id']) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_payment_status', 'Payment Status') ?></small>
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> <?= __('user_payment_success_completed', 'Completed') ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_payment_success_plot', 'Plot') ?></small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong> <?= __('user_payment_success_plot_at', 'at') ?> <?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-list-check text-primary"></i> <?= __('user_payment_success_next_steps_header', 'Next Steps') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3" class="style-89166">
                        <i class="fas fa-file-signature text-primary" class="style-10933"></i>
                    </div>
                    <div>
                        <strong><?= __('user_payment_success_step1_title', 'Sign Agreement') ?></strong>
                        <p class="mb-0 text-muted small"><?= __('user_payment_success_step1_desc', 'Our team will contact you within 24 hours to schedule agreement signing.') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="me-3" class="style-89166">
                        <i class="fas fa-calendar-check text-primary" class="style-10933"></i>
                    </div>
                    <div>
                        <strong><?= __('user_payment_success_step2_title', 'EMI Schedule') ?></strong>
                        <p class="mb-0 text-muted small"><?= __('user_payment_success_step2_desc', 'After agreement, your EMI plan will be generated with monthly installment dates.') ?></p>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div class="me-3" class="style-89166">
                        <i class="fas fa-key text-primary" class="style-10933"></i>
                    </div>
                    <div>
                        <strong><?= __('user_payment_success_step3_title', 'Allotment & Possession') ?></strong>
                        <p class="mb-0 text-muted small"><?= __('user_payment_success_step3_desc', "Upon full payment, you'll receive your allotment letter and possession details.") ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-primary w-100">
                    <i class="fas fa-eye me-2"></i><?= __('user_payment_success_view_booking', 'View Booking Details') ?>
                </a>
            </div>
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary w-100">
                    <i class="fas fa-list me-2"></i><?= __('user_payment_success_all_bookings', 'All My Bookings') ?>
                </a>
            </div>
        </div>

    </div>
</div>
