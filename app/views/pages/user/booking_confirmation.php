<?php
$page_title = $page_title ?? __('user_booking_confirm_title', 'Booking Confirmed');
$current_page = 'bookings';
$user = $user ?? [];
$booking = $booking ?? null;

$statusLabels = [
    'token_paid' => __('user_booking_confirm_status_token_paid', 'Token Paid'),
    'agreement_signed' => __('user_booking_confirm_status_agreement_signed', 'Agreement Signed'),
    'emi_active' => __('user_booking_confirm_status_emi_active', 'EMI Active'),
    'partially_paid' => __('user_booking_confirm_status_partially_paid', 'Partially Paid'),
    'fully_paid' => __('user_booking_confirm_status_fully_paid', 'Fully Paid'),
    'cancelled' => __('user_booking_confirm_status_cancelled', 'Cancelled'),
    'transferred' => __('user_booking_confirm_status_transferred', 'Transferred'),
    'registration_done' => __('user_booking_confirm_status_registered', 'Registered'),
];
$statusColors = [
    'token_paid' => 'primary',
    'agreement_signed' => 'indigo',
    'emi_active' => 'amber',
    'partially_paid' => 'info',
    'fully_paid' => 'success',
    'cancelled' => 'danger',
    'transferred' => 'secondary',
    'registration_done' => 'success',
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-file-contract me-2"></i><?= __('user_booking_confirm_heading', 'Booking Confirmation') ?></h2>
            <p><?= __('user_booking_confirm_subtitle', 'Review your plot booking details and next steps.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_booking_confirm_my_bookings', 'My Bookings') ?>
            </a>
        </div>
    </div>
</div>

<?php if (!$booking): ?>
<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="aps-cp-empty">
            <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h5><?= __('user_booking_confirm_not_found_heading', 'Booking Not Found') ?></h5>
            <p><?= __('user_booking_confirm_not_found_desc', 'The booking you are looking for does not exist or you do not have access to it.') ?></p>
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary"><i class="fas fa-list me-2"></i><?= __('user_booking_confirm_go_to_bookings', 'Go to My Bookings') ?></a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <?php
            $bStatus = $booking['status'] ?? 'token_paid';
            $bColor = $statusColors[$bStatus] ?? 'primary';
            ?>
            <?php if (in_array($bStatus, ['token_paid', 'emi_active', 'partially_paid', 'fully_paid'])): ?>
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $bColor ?> bg-opacity-10 mb-3" style="width:80px;height:80px;">
                <i class="fas fa-check-circle fa-3x text-<?= $bColor ?>"></i>
            </div>
            <h4 class="fw-bold"><?= __('user_booking_confirm_booking', 'Booking') ?> <?= htmlspecialchars($statusLabels[$bStatus] ?? ucfirst(str_replace('_', ' ', $bStatus))) ?></h4>
            <p class="text-muted"><?= __('user_booking_confirm_your_number', 'Your booking number is') ?> <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong></p>
            <?php else: ?>
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 mb-3" style="width:80px;height:80px;">
                <i class="fas fa-info-circle fa-3x text-secondary"></i>
            </div>
            <h4 class="fw-bold"><?= __('user_booking_confirm_status_label', 'Booking Status') ?>: <?= htmlspecialchars($statusLabels[$bStatus] ?? ucfirst($bStatus)) ?></h4>
            <?php endif; ?>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt text-primary me-2"></i><?= __('user_booking_confirm_plot_details', 'Plot Details') ?></h5>
                <span class="badge bg-<?= $bColor ?>"><?= htmlspecialchars($statusLabels[$bStatus] ?? ucfirst($bStatus)) ?></span>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <h6 class="text-muted small mb-2"><?= __('user_booking_confirm_location', 'LOCATION') ?></h6>
                            <p class="mb-1 fw-bold fs-5"><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></p>
                            <p class="mb-0 text-muted">
                                <?= htmlspecialchars($booking['district_name'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <h6 class="text-muted small mb-2"><?= __('user_booking_confirm_plot_info', 'PLOT INFO') ?></h6>
                            <p class="mb-1"><strong><?= __('user_booking_confirm_plot_hash', 'Plot') ?>#<?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong> &middot; <?= __('user_booking_confirm_block', 'Block') ?> <?= htmlspecialchars($booking['block'] ?? 'N/A') ?></p>
                            <p class="mb-0 text-muted">
                                <?= number_format((float)($booking['area_sqft'] ?? 0)) ?> sqft
                                <?php if (!empty($booking['width_ft']) && !empty($booking['length_ft'])): ?>
                                    &middot; <?= number_format((float)$booking['width_ft'], 0) ?> x <?= number_format((float)$booking['length_ft'], 0) ?> ft
                                <?php endif; ?>
                                <?php if (!empty($booking['facing'])): ?>
                                    &middot; <?= htmlspecialchars($booking['facing']) ?> <?= __('user_booking_confirm_facing_suffix', 'facing') ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-receipt text-success me-2"></i><?= __('user_booking_confirm_booking_summary', 'Booking Summary') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center">
                            <div class="text-muted small"><?= __('user_booking_confirm_label_booking_number', 'Booking Number') ?></div>
                            <div class="fw-bold"><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center">
                            <div class="text-muted small"><?= __('user_booking_confirm_label_booking_date', 'Booking Date') ?></div>
                            <div class="fw-bold"><?= date('M d, Y', strtotime($booking['booking_date'] ?? 'now')) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center">
                            <div class="text-muted small"><?= __('user_booking_confirm_label_token_paid', 'Token Paid') ?></div>
                            <div class="fw-bold text-success">&#8377;<?= number_format((float)($booking['booking_amount'] ?? 0)) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center">
                            <div class="text-muted small"><?= __('user_booking_confirm_label_total_amount', 'Total Amount') ?></div>
                            <div class="fw-bold text-primary">&#8377;<?= number_format((float)($booking['total_plot_value'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-list-check text-info me-2"></i><?= __('user_booking_confirm_next_steps', 'Next Steps') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                        <div class="aps-cp-stat-icon flex-shrink-0" style="background: var(--aps-cp-primary-light); color: var(--aps-cp-primary); width:40px;height:40px;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= __('user_booking_confirm_step_pay_token', 'Pay Token Amount') ?></h6>
                            <p class="text-muted small mb-2"><?= __('user_booking_confirm_step_pay_token_desc', 'Complete your token payment of &#8377;25,000 to confirm the booking.') ?></p>
                            <button class="btn btn-primary btn-sm" onclick="showToast('<?= __('user_booking_confirm_payment_gateway_soon', 'Payment gateway coming soon') ?>', 'info')">
                                <i class="fas fa-rupee-sign me-1"></i><?= __('user_booking_confirm_pay_now', 'Pay Now') ?> (Razorpay)
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                        <div class="aps-cp-stat-icon flex-shrink-0" style="background: var(--aps-cp-success-light); color: var(--aps-cp-success); width:40px;height:40px;">
                            <i class="fas fa-file-download"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= __('user_booking_confirm_step_download_receipt', 'Download Booking Receipt') ?></h6>
                            <p class="text-muted small mb-2"><?= __('user_booking_confirm_step_download_receipt_desc', 'Save a copy of your booking confirmation for your records.') ?></p>
                            <button class="btn btn-outline-success btn-sm" onclick="showToast('<?= __('user_booking_confirm_receipt_soon', 'Receipt download coming soon') ?>', 'info')">
                                <i class="fas fa-download me-1"></i><?= __('user_booking_confirm_download_receipt', 'Download Receipt') ?>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                        <div class="aps-cp-stat-icon flex-shrink-0" style="background: var(--aps-cp-info-light); color: var(--aps-cp-info); width:40px;height:40px;">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= __('user_booking_confirm_step_sign_agreement', 'Sign Agreement') ?></h6>
                            <p class="text-muted small mb-0"><?= __('user_booking_confirm_step_sign_agreement_desc', 'Visit our office or schedule a call to complete the agreement process.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($booking['notes'])): ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-sticky-note text-warning me-2"></i><?= __('user_booking_confirm_notes', 'Notes') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($booking['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary me-2">
                <i class="fas fa-list me-2"></i><?= __('user_booking_confirm_view_all', 'View All Bookings') ?>
            </a>
            <a href="<?= BASE_URL ?>/user/bookings/new" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i><?= __('user_booking_confirm_book_another', 'Book Another Plot') ?>
            </a>
        </div>

    </div>
</div>

<?php endif; ?>
