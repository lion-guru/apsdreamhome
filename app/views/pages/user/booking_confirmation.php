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
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $bColor ?> bg-opacity-10 mb-3">
                <i class="fas fa-check-circle fa-3x text-<?= $bColor ?>"></i>
            </div>
            <h4 class="fw-bold"><?= __('user_booking_confirm_booking', 'Booking') ?> <?= htmlspecialchars($statusLabels[$bStatus] ?? ucfirst(str_replace('_', ' ', $bStatus))) ?></h4>
            <p class="text-muted"><?= __('user_booking_confirm_your_number', 'Your booking number is') ?> <strong><?= htmlspecialchars($booking['booking_number'] ?? '') ?></strong></p>
            <?php else: ?>
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 mb-3">
                <i class="fas fa-info-circle fa-3x text-secondary"></i>
            </div>
            <h4 class="fw-bold"><?= __('user_booking_confirm_status_label', 'Booking Status') ?>: <?= htmlspecialchars($statusLabels[$bStatus] ?? ucfirst($bStatus)) ?></h4>
            <?php endif; ?>
        </div>

        <div class="alert alert-danger py-2 px-3 mb-4 small border-start border-4 border-danger text-start" style="background-color: #fff8f8;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-danger me-2 flex-shrink-0"></i>
                <div>
                    <strong>वैधानिक सूचना (Master Deed धारा 2.1 व 2.9):</strong> टोकन बुकिंग राशि <strong>₹<?= number_format((float)($booking['booking_amount'] ?? 51000)) ?></strong> पूर्णतः <strong>नॉन-रिफंडेबल (गैर-वापसी योग्य / वापस नहीं होगी)</strong> है। आवंटन पक्का करने हेतु 15 दिनों में 25% राशि जमा करना अनिवार्य है।
                </div>
            </div>
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
                            <p class="mb-1 fw-bold fs-5">
                                <?= __('user_booking_confirm_plot', 'Plot') ?> <?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?>
                                <?php if (!empty($booking['block'])): ?>
                                    (<?= __('user_booking_confirm_block', 'Block') ?> <?= htmlspecialchars($booking['block']) ?>)
                                <?php endif; ?>
                            </p>
                            <p class="mb-0 text-muted">
                                <?= number_format((float)($booking['area_sqft'] ?? 0), 0) ?> sq ft
                                <?php if (!empty($booking['width_ft']) && !empty($booking['length_ft'])): ?>
                                    &middot; <?= number_format((float)$booking['width_ft'], 0) ?> x <?= number_format((float)$booking['length_ft'], 0) ?> ft
                                <?php endif; ?>
                                <?php if (!empty($booking['facing'])): ?>
                                    &middot; <?= htmlspecialchars($booking['facing'] ?? '') ?> <?= __('user_booking_confirm_facing_suffix', 'facing') ?>
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
                <?php
                $totalVal = (float)($booking['total_plot_value'] ?? 0);
                $tokenVal = (float)($booking['booking_amount'] ?? 51000);
                $mand25 = round($totalVal * 0.25);
                $bal15 = max(0, $mand25 - $tokenVal);
                $rem75 = max(0, $totalVal - $tokenVal - $bal15);
                $emi36 = round($rem75 / 36);
                ?>
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="text-muted small"><?= __('user_booking_confirm_label_booking_number', 'Booking Number') ?></div>
                            <div class="fw-bold"><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></div>
                            <div class="text-muted small mt-1"><?= date('M d, Y', strtotime($booking['booking_date'] ?? 'now')) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                            <div class="text-danger small fw-bold">Stage 1: Token Paid/Due</div>
                            <div class="fw-bold text-danger fs-5">&#8377;<?= number_format($tokenVal) ?></div>
                            <span class="badge bg-danger text-wrap" style="font-size: 0.68rem;">Non-Refundable (गैर-वापसी)</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-2 bg-warning bg-opacity-10 rounded">
                            <div class="text-dark small fw-bold">Stage 2: 15-Day 25% Balance</div>
                            <div class="fw-bold text-dark fs-5">&#8377;<?= number_format($bal15) ?></div>
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Due in 15 Calendar Days</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                            <div class="text-primary small fw-bold">Stage 3: Total / 36-Mo EMI</div>
                            <div class="fw-bold text-primary fs-5">&#8377;<?= number_format($totalVal) ?></div>
                            <span class="text-primary small d-block" style="font-size: 0.72rem;">Approx &#8377;<?= number_format($emi36) ?>/mo</span>
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
                        <div class="aps-cp-stat-icon flex-shrink-0 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-success fw-bold">1. ₹51,000 Non-Refundable Token Recorded</h6>
                            <p class="text-muted small mb-0">Your initial ₹<?= number_format($tokenVal) ?> token allotment is recorded under Master Deed Section 2.1 &amp; 2.9 (Non-refundable / गैर-वापसी योग्य).</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 border-start border-4 border-warning">
                        <div class="aps-cp-stat-icon flex-shrink-0 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-dark fw-bold">2. Pay Mandatory 25% Balance within 15 Days</h6>
                            <p class="text-muted small mb-2">Pay balance ₹<?= number_format($bal15) ?> within 15 calendar days from booking date to confirm allotment and avoid statutory cancellation.</p>
                            <button class="btn btn-warning btn-sm text-dark fw-bold" onclick="window.location.href='<?= BASE_URL ?>/payments'">
                                <i class="fas fa-rupee-sign me-1"></i>Pay 25% Balance (&#8377;<?= number_format($bal15) ?>)
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                        <div class="aps-cp-stat-icon flex-shrink-0 text-primary">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">3. Sign Tripartite Master Deed &amp; 4 Corner Pillars Demarcation</h6>
                            <p class="text-muted small mb-0">Visit our corporate office to execute the registered agreement and inspect the 4 concrete corner pillars on your plot site.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                        <div class="aps-cp-stat-icon flex-shrink-0 text-secondary">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">4. Sub-Registrar Registry &amp; Possession Handover</h6>
                            <p class="text-muted small mb-0">Registry completion at the Sub-Registrar office with immediate physical possession upon payment completion or EMI activation.</p>
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
                <p class="mb-0"><?= nl2br(htmlspecialchars($booking['notes'] ?? '')) ?></p>
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
