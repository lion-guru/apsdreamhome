<style>
.conf-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 20px; }
.status-timeline { position: relative; padding-left: 30px; }
.status-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e0e0e0; }
.status-item { position: relative; padding-bottom: 24px; }
.status-item .dot { position: absolute; left: -24px; top: 4px; width: 16px; height: 16px; border-radius: 50%; }
.status-item .dot.active { background: #4caf50; box-shadow: 0 0 0 4px rgba(76,175,80,0.2); }
.status-item .dot.pending { background: #ffc107; }
.status-item .dot.future { background: #e0e0e0; }
</style>

<?php
$statusSteps = [
    ['label' => __('booking_status_submitted'), 'status' => ['pending'], 'icon' => 'fas fa-file-signature'],
    ['label' => __('booking_status_confirmed'), 'status' => ['confirmed'], 'icon' => 'fas fa-check-circle'],
    ['label' => __('booking_status_token'), 'status' => ['partial', 'paid'], 'icon' => 'fas fa-hand-holding-usd'],
    ['label' => __('booking_status_registration'), 'status' => ['completed'], 'icon' => 'fas fa-home'],
];
$currentStatus = $booking['status'] ?? 'pending';
$currentStep = 0;
foreach ($statusSteps as $i => $step) {
    if (in_array($currentStatus, $step['status'])) $currentStep = $i;
}
?>

<div class="container py-4">
    <!-- Success Banner -->
    <?php if ($currentStatus === 'pending'): ?>
    <div class="alert alert-success py-4 mb-4 text-center">
        <i class="fas fa-check-circle fa-3x mb-2"></i>
        <h4 class="fw-bold mb-1"><?= __('booking_submitted_successfully') ?></h4>
        <p class="mb-0"><?= __('booking_received_text') ?> <strong>Plot <?= htmlspecialchars($booking['plot_number'] ?? '') ?></strong> <?= __('booking_confirm_shortly') ?></p>
    </div>
    <?php endif; ?>

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><?= __('breadcrumb_home') ?></a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/dashboard"><?= __('breadcrumb_dashboard') ?></a></li>
            <li class="breadcrumb-item active"><?= __('booking_number') ?>#<?= $booking['id'] ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Status Timeline -->
            <div class="conf-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-clock"></i> <?= __('booking_progress') ?></h5>
                <div class="status-timeline">
                    <?php foreach ($statusSteps as $i => $step): ?>
                        <?php 
                            $stepClass = $i < $currentStep ? 'active' : ($i === $currentStep ? 'active' : 'future');
                            $stepClassDot = $i <= $currentStep ? 'active' : 'future';
                        ?>
                        <div class="status-item">
                            <div class="dot <?= $stepClassDot ?>"></div>
                            <div class="fw-semibold <?= $i <= $currentStep ? '' : 'text-muted' ?>">
                                <i class="<?= $step['icon'] ?> me-2"></i><?= $step['label'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="conf-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-file-invoice"></i> <?= __('booking_details') ?></h5>
                <table class="table">
                    <tr><th class="style-17160"><?= __('booking_number') ?></th><td><strong>#<?= $booking['id'] ?> (<?= htmlspecialchars($booking['booking_number'] ?? '') ?>)</strong></td></tr>
                    <tr><th><?= __('booking_plot') ?></th><td><?= htmlspecialchars($booking['plot_number'] ?? '') ?> — <?= htmlspecialchars($booking['colony_name'] ?? '') ?></td></tr>
                    <tr><th><?= __('booking_dimension') ?></th><td><?= htmlspecialchars($booking['dimension_label'] ?? '') ?> | <?= number_format(floatval($booking['area_sqft'] ?? 0)) ?> sqft</td></tr>
                    <tr><th><?= __('booking_total_price') ?></th><td class="fw-bold fs-5 text-primary">₹<?= number_format(intval($booking['total_amount'] ?? $booking['plot_price'] ?? 0)) ?></td></tr>
                    <tr><th><?= __('booking_status') ?></th><td><span class="badge bg-<?= $currentStatus === 'confirmed' || $currentStatus === 'completed' ? 'success' : ($currentStatus === 'cancelled' ? 'danger' : 'warning') ?> fs-6"><?= ucfirst($currentStatus) ?></span></td></tr>
                    <tr><th><?= __('booking_date') ?></th><td><?= date('d M Y', strtotime($booking['booking_date'] ?? $booking['created_at'] ?? 'now')) ?></td></tr>
                </table>

                <?php if ($plot['corner_plot'] ?? false): ?><span class="badge bg-primary me-1"><?= __('badge_corner_plot') ?></span><?php endif; ?>
                <?php if ($plot['park_facing'] ?? false): ?><span class="badge bg-success"><?= __('badge_park_facing') ?></span><?php endif; ?>
            </div>

            <!-- EMI Schedule -->
            <?php if (!empty($emis)): ?>
            <div class="conf-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-calendar-alt"></i> <?= __('payment_schedule') ?></h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>#</th><th><?= __('emi_due_date') ?></th><th><?= __('emi_amount') ?></th><th><?= __('emi_paid') ?></th><th><?= __('booking_status') ?></th></tr></thead>
                        <tbody>
                            <?php foreach ($emis as $emi): ?>
                            <tr>
                                <td><?= $emi['installment_no'] ?></td>
                                <td><?= date('d M Y', strtotime($emi['due_date'])) ?></td>
                                <td>₹<?= number_format(intval($emi['amount'])) ?></td>
                                <td>₹<?= number_format(intval($emi['paid_amount'] ?? 0)) ?></td>
                                <td><span class="badge bg-<?= $emi['status'] === 'paid' ? 'success' : ($emi['status'] === 'overdue' ? 'danger' : 'warning') ?>"><?= ucfirst($emi['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle"></i> <strong><?= __('common_note') ?>:</strong> <?= __('booking_token_note') ?> <strong>₹<?= number_format(intval($emis[0]['amount'] ?? 0)) ?></strong> <?= __('booking_token_within') ?>
                    <?php if ($currentStatus === 'pending'): ?>
                    Please complete the token payment to confirm your booking.
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <div class="conf-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-quick"></i> <?= __('booking_quick_actions') ?></h5>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/user/dashboard" class="btn btn-outline-primary">
                        <i class="fas fa-tachometer-alt"></i> <?= __('breadcrumb_dashboard') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/colony/<?= htmlspecialchars($booking['colony_slug'] ?? '') ?>/plots" class="btn btn-outline-secondary">
                        <i class="fas fa-th"></i> <?= __('booking_browse_plots') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/booking/<?= $booking['id'] ?>/pay" class="btn btn-success">
                        <i class="fas fa-credit-card"></i> <?= __('booking_pay_token') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-info">
                        <i class="fas fa-phone"></i> <?= __('booking_contact_support') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
