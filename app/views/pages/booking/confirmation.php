<?php
require_once __DIR__ . '/../../../Helpers/TranslationHelper.php';
$current_page = $current_page ?? 'booking-confirmation';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

$statusSteps = [
    ['label' => __('booking_conf_status_submitted', [], 'Booking Submitted'),    'icon' => 'fas fa-file-signature',   'statuses' => ['token_paid']],
    ['label' => __('booking_conf_status_admin', [], 'Admin Confirmation'),   'icon' => 'fas fa-check-circle',     'statuses' => ['token_paid', 'agreement_signed']],
    ['label' => __('booking_conf_status_agreement', [], 'Agreement Signed'),     'icon' => 'fas fa-file-signature',   'statuses' => ['agreement_signed', 'emi_active']],
    ['label' => __('booking_conf_status_payment', [], 'Payment in Progress'),  'icon' => 'fas fa-credit-card',      'statuses' => ['emi_active', 'partially_paid']],
    ['label' => __('booking_conf_status_fully_paid', [], 'Fully Paid'),           'icon' => 'fas fa-check-double',     'statuses' => ['fully_paid', 'registration_done']],
    ['label' => __('booking_conf_status_registration', [], 'Registration Done'),    'icon' => 'fas fa-home',             'statuses' => ['registration_done']],
];

$currentStatus = $booking['status'] ?? 'token_paid';
$currentStep = 0;
foreach ($statusSteps as $i => $step) {
    if (in_array($currentStatus, $step['statuses'])) {
        $currentStep = $i;
        break;
    }
}
?>

<div class="container py-4">

    <!-- Success Banner -->
    <div class="alert alert-success py-4 mb-4 text-center" class="style-84037">
        <i class="fas fa-check-circle fa-3x mb-2"></i>
        <h3 class="fw-bold mb-1"><?= __('booking_conf_heading', [], 'Booking Confirmed!') ?></h3>
        <p class="mb-0">
            <?= __('booking_conf_success_prefix', [], 'Your booking request for') ?> <strong><?= __('booking_conf_success_plot', [], 'Plot') ?> <?= htmlspecialchars($booking['plot_number'] ?? '') ?></strong>
            <?= __('booking_conf_success_suffix', [], 'has been submitted successfully.') ?>
        </p>
        <div class="mt-3">
            <span class="badge bg-dark fs-6">
                <i class="fas fa-hashtag me-1"></i><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?>
            </span>
        </div>
    </div>

    <div class="row g-4">

        <!-- Left: Booking Progress + Details -->
        <div class="col-lg-8">

            <!-- Status Timeline -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-clock me-2"></i><?= __('booking_conf_progress', [], 'Booking Progress') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="position-relative" class="style-66736">
                        <div class="position-absolute" class="style-985"></div>
                        <?php foreach ($statusSteps as $i => $step): ?>
                        <div class="position-relative pb-3">
                            <div class="position-absolute rounded-circle d-flex align-items-center justify-content-center"
                                 class="style-36086">
                            </div>
                            <div class="<?= $i <= $currentStep ? '' : 'opacity-50' ?>">
                                <i class="<?= $step['icon'] ?> me-1"></i>
                                <strong><?= $step['label'] ?></strong>
                                <?php if ($i === $currentStep): ?>
                                    <span class="badge bg-primary ms-2"><?= __('booking_conf_current', [], 'Current') ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-info-circle me-2"></i><?= __('booking_conf_details', [], 'Booking Details') ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_number', [], 'Booking Number') ?></small>
                            <strong><?= htmlspecialchars($booking['booking_number'] ?? '—') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_date', [], 'Booking Date') ?></small>
                            <strong><?= htmlspecialchars($booking['booking_date'] ?? '—') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_status', [], 'Status') ?></small>
                            <span class="badge bg-primary"><?= ucfirst(str_replace('_', ' ', $booking['status'] ?? '')) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_plot', [], 'Plot') ?></small>
                            <strong><?= htmlspecialchars($booking['plot_number'] ?? '') ?> — <?= htmlspecialchars($booking['colony_name'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_area', [], 'Area') ?></small>
                            <strong><?= number_format($booking['area_sqft'] ?? 0) ?> sqft</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block"><?= __('booking_conf_dimensions', [], 'Dimensions') ?></small>
                            <strong><?= htmlspecialchars($booking['dimension_label'] ?? '—') ?></strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block"><?= __('booking_conf_total_amount', [], 'Total Amount') ?></small>
                            <strong class="fs-5 text-primary">₹<?= number_format($booking['total_plot_value'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMI Schedule (if applicable) -->
            <?php if (!empty($schedule)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-calendar-alt me-2"></i><?= __('booking_conf_payment_schedule', [], 'Payment Schedule') ?></span>
                </div>
                <div class="aps-cp-card-body" class="style-10754">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th><?= __('booking_conf_th_due_date', [], 'Due Date') ?></th>
                                <th class="text-end"><?= __('booking_conf_th_amount', [], 'Amount') ?></th>
                                <th class="text-center"><?= __('booking_conf_th_status', [], 'Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule as $inst): ?>
                            <tr>
                                <td><?= (int)$inst['installment_no'] ?></td>
                                <td><?= htmlspecialchars($inst['due_date']) ?></td>
                                <td class="text-end">₹<?= number_format((float)$inst['amount']) ?></td>
                                <td class="text-center">
                                    <?php
                                    $statusClass = match($inst['status'] ?? 'pending') {
                                        'paid'    => 'success',
                                        'overdue' => 'danger',
                                        default   => 'warning',
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($inst['status'] ?? 'pending') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2"><?= __('booking_conf_totals', [], 'Totals') ?></th>
                                <th class="text-end">₹<?= number_format($totalPaid + $totalPending) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: What's Next + Actions -->
        <div class="col-lg-4">

            <!-- What's Next -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-list-check me-2"></i><?= __('booking_conf_whats_next', [], "What's Next") ?></span>
                </div>
                <div class="aps-cp-card-body">
                    <ol class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" class="style-96907">1</span>
                            <div>
                                <strong><?= __('booking_conf_step1_title', [], 'Admin Confirmation') ?></strong>
                                <small class="d-block text-muted"><?= __('booking_conf_step1_desc', [], 'Our team will review and confirm your booking within 24 hours.') ?></small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" class="style-96907">2</span>
                            <div>
                                <strong><?= __('booking_conf_step2_title', [], 'Pay Token Amount') ?></strong>
                                <small class="d-block text-muted"><?= __('booking_conf_step2_prefix', [], 'Pay 25% token') ?> (₹<?= number_format((float)($booking['total_plot_value'] ?? 0) * 0.25) ?>) <?= __('booking_conf_step2_suffix', [], 'to confirm your spot.') ?></small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" class="style-96907">3</span>
                            <div>
                                <strong><?= __('booking_conf_step3_title', [], 'Sign Agreement') ?></strong>
                                <small class="d-block text-muted"><?= __('booking_conf_step3_desc', [], 'Visit our office or complete online agreement signing.') ?></small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" class="style-96907">4</span>
                            <div>
                                <strong><?= __('booking_conf_step4_title', [], 'Registration') ?></strong>
                                <small class="d-block text-muted"><?= __('booking_conf_step4_desc', [], 'Complete registration at the Sub-Registrar office.') ?></small>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body d-grid gap-2">
                    <a href="<?= $baseUrl ?>/user/dashboard" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-1"></i><?= __('booking_conf_my_dashboard', [], 'My Dashboard') ?>
                    </a>
                    <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i><?= __('booking_conf_browse', [], 'Browse More Plots') ?>
                    </a>
                    <?php if (!empty($booking['plot_number'])): ?>
                    <a href="<?= $baseUrl ?>/plots/<?= $booking['plot_id'] ?? 0 ?>/detail" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i><?= __('booking_conf_view_detail', [], 'View Plot Detail') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact -->
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold"><?= __('booking_conf_need_help', [], 'Need Help?') ?></h6>
                    <p class="small text-muted mb-2"><?= __('booking_conf_help_desc', [], 'Our team is here to assist you.') ?></p>
                    <a href="tel:+919277121112" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-phone me-1"></i>+91 92771 21112
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
