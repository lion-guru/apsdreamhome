<?php
$current_page = $current_page ?? 'booking-confirmation';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

$statusSteps = [
    ['label' => 'Booking Submitted',    'icon' => 'fas fa-file-signature',   'statuses' => ['token_paid']],
    ['label' => 'Admin Confirmation',   'icon' => 'fas fa-check-circle',     'statuses' => ['token_paid', 'agreement_signed']],
    ['label' => 'Agreement Signed',     'icon' => 'fas fa-file-signature',   'statuses' => ['agreement_signed', 'emi_active']],
    ['label' => 'Payment in Progress',  'icon' => 'fas fa-credit-card',      'statuses' => ['emi_active', 'partially_paid']],
    ['label' => 'Fully Paid',           'icon' => 'fas fa-check-double',     'statuses' => ['fully_paid', 'registration_done']],
    ['label' => 'Registration Done',    'icon' => 'fas fa-home',             'statuses' => ['registration_done']],
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
    <div class="alert alert-success py-4 mb-4 text-center" style="border-radius:14px;">
        <i class="fas fa-check-circle fa-3x mb-2"></i>
        <h3 class="fw-bold mb-1">Booking Confirmed!</h3>
        <p class="mb-0">
            Your booking request for <strong>Plot <?= htmlspecialchars($booking['plot_number'] ?? '') ?></strong>
            has been submitted successfully.
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
                    <span><i class="fas fa-clock me-2"></i>Booking Progress</span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="position-relative" style="padding-left: 24px;">
                        <div class="position-absolute" style="left: 10px; top: 0; bottom: 0; width: 2px; background: #e0e0e0;"></div>
                        <?php foreach ($statusSteps as $i => $step): ?>
                        <div class="position-relative pb-3">
                            <div class="position-absolute rounded-circle d-flex align-items-center justify-content-center"
                                 style="left: -18px; top: 2px; width: 16px; height: 16px; z-index:1;
                                        background: <?= $i <= $currentStep ? '#10b981' : '#e0e0e0' ?>;">
                            </div>
                            <div class="<?= $i <= $currentStep ? '' : 'opacity-50' ?>">
                                <i class="<?= $step['icon'] ?> me-1"></i>
                                <strong><?= $step['label'] ?></strong>
                                <?php if ($i === $currentStep): ?>
                                    <span class="badge bg-primary ms-2">Current</span>
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
                    <span><i class="fas fa-info-circle me-2"></i>Booking Details</span>
                </div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Booking Number</small>
                            <strong><?= htmlspecialchars($booking['booking_number'] ?? '—') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Booking Date</small>
                            <strong><?= htmlspecialchars($booking['booking_date'] ?? '—') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-primary"><?= ucfirst(str_replace('_', ' ', $booking['status'] ?? '')) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Plot</small>
                            <strong><?= htmlspecialchars($booking['plot_number'] ?? '') ?> — <?= htmlspecialchars($booking['colony_name'] ?? '') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Area</small>
                            <strong><?= number_format($booking['area_sqft'] ?? 0) ?> sqft</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Dimensions</small>
                            <strong><?= htmlspecialchars($booking['dimension_label'] ?? '—') ?></strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong class="fs-5 text-primary">₹<?= number_format($booking['total_plot_value'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMI Schedule (if applicable) -->
            <?php if (!empty($schedule)): ?>
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-header">
                    <span><i class="fas fa-calendar-alt me-2"></i>Payment Schedule</span>
                </div>
                <div class="aps-cp-card-body" style="overflow-x:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
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
                                <th colspan="2">Totals</th>
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
                    <span><i class="fas fa-list-check me-2"></i>What's Next</span>
                </div>
                <div class="aps-cp-card-body">
                    <ol class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" style="width:24px;height:24px;line-height:24px;">1</span>
                            <div>
                                <strong>Admin Confirmation</strong>
                                <small class="d-block text-muted">Our team will review and confirm your booking within 24 hours.</small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" style="width:24px;height:24px;line-height:24px;">2</span>
                            <div>
                                <strong>Pay Token Amount</strong>
                                <small class="d-block text-muted">Pay 25% token (₹<?= number_format((float)($booking['total_plot_value'] ?? 0) * 0.25) ?>) to confirm your spot.</small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" style="width:24px;height:24px;line-height:24px;">3</span>
                            <div>
                                <strong>Sign Agreement</strong>
                                <small class="d-block text-muted">Visit our office or complete online agreement signing.</small>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary rounded-circle me-2 flex-shrink-0" style="width:24px;height:24px;line-height:24px;">4</span>
                            <div>
                                <strong>Registration</strong>
                                <small class="d-block text-muted">Complete registration at the Sub-Registrar office.</small>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body d-grid gap-2">
                    <a href="<?= $baseUrl ?>/user/dashboard" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-1"></i>My Dashboard
                    </a>
                    <a href="<?= $baseUrl ?>/plots/browse" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Browse More Plots
                    </a>
                    <?php if (!empty($booking['plot_number'])): ?>
                    <a href="<?= $baseUrl ?>/plots/<?= $booking['plot_id'] ?? 0 ?>/detail" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>View Plot Detail
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact -->
            <div class="aps-cp-card">
                <div class="aps-cp-card-body text-center">
                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold">Need Help?</h6>
                    <p class="small text-muted mb-2">Our team is here to assist you.</p>
                    <a href="tel:+919277121112" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-phone me-1"></i>+91 92771 21112
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
