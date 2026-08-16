<?php
$page_title = $page_title ?? __('user_installment_success_page_title', 'Payment Successful');
$current_page = 'bookings';
$user = $user ?? [];
$installment = $installment ?? null;
$booking = $booking ?? null;
$receipt = $receipt ?? null;
$all_installments = $all_installments ?? [];

$instStatusColors = [
    'pending' => 'warning',
    'paid'    => 'success',
    'overdue' => 'danger',
    'partial' => 'info',
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-check-circle me-2"></i><?= __('user_installment_success_hero_heading', 'Payment Successful') ?></h2>
            <p><?= __('user_installment_success_hero_subtitle', 'Your installment payment has been processed successfully.') ?></p>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($installment['booking_id'] ?? 0) ?>" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_installment_success_back_to_booking', 'Back to Booking') ?>
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
            <h3 class="mt-2"><?= __('user_installment_success_thank_you', 'Thank You,') ?> <?= htmlspecialchars($user['name'] ?? '') ?>!</h3>
            <p class="text-muted">
                <?= __('user_installment_success_received_prefix', 'Your installment #') ?><?= (int)($installment['installment_number'] ?? 0) ?> <?= __('user_installment_success_received_middle', 'payment of') ?>
                <strong class="text-success">₹<?= number_format((float)($receipt['amount'] ?? 0)) ?></strong>
                <?= __('user_installment_success_received_suffix', 'has been received.') ?>
            </p>
        </div>

        <?php if ($receipt): ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-receipt text-success"></i> <?= __('user_installment_success_receipt_header', 'Payment Receipt') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_receipt_number', 'Receipt Number') ?></small>
                        <strong><?= htmlspecialchars($receipt['receipt_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_payment_date', 'Payment Date') ?></small>
                        <strong><?= date('d M Y, h:i A', strtotime($receipt['created_at'] ?? 'now')) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_amount_paid', 'Amount Paid') ?></small>
                        <strong class="text-success fs-5">₹<?= number_format((float)($receipt['amount'] ?? 0)) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_payment_method', 'Payment Method') ?></small>
                        <strong><i class="fas fa-credit-card me-1"></i> <?= __('user_installment_success_razorpay_online', 'Razorpay (Online)') ?></strong>
                    </div>
                    <?php if (!empty($receipt['transaction_ref'])): ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_transaction_id', 'Transaction ID') ?></small>
                        <strong class="text-break"><?= htmlspecialchars($receipt['transaction_ref'] ?? '') ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_status', 'Status') ?></small>
                        <span class="badge bg-success fs-6"><i class="fas fa-check-circle me-1"></i> <?= __('user_installment_success_completed', 'Completed') ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_installment', 'Installment') ?></small>
                        <strong>#<?= (int)($installment['installment_number'] ?? 0) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_installment_success_plot', 'Plot') ?></small>
                        <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong> <?= __('user_installment_success_plot_at', 'at') ?> <?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($all_installments)): ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-list-check text-primary"></i> <?= __('user_installment_success_updated_schedule', 'Updated Payment Schedule') ?></h5>
            </div>
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= __('user_installment_success_th_due_date', 'Due Date') ?></th>
                                <th class="text-end"><?= __('user_installment_success_th_amount', 'Amount') ?></th>
                                <th class="text-end"><?= __('user_installment_success_th_paid', 'Paid') ?></th>
                                <th><?= __('user_installment_success_th_status', 'Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_installments as $ai):
                                $aiStatus = $ai['status'] ?? 'pending';
                                $aiColor = $instStatusColors[$aiStatus] ?? 'secondary';
                            ?>
                            <tr class="<?= $aiStatus === 'overdue' ? 'table-danger' : '' ?>">
                                <td><strong><?= (int)($ai['installment_number'] ?? 0) ?></strong></td>
                                <td><?= date('d M Y', strtotime($ai['due_date'] ?? 'now')) ?></td>
                                <td class="text-end">₹<?= number_format((float)($ai['emi_amount'] ?? 0)) ?></td>
                                <td class="text-end">₹<?= number_format((float)($ai['paid_amount'] ?? 0)) ?></td>
                                <td><span class="badge bg-<?= $aiColor ?>"><?= ucfirst($aiStatus) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings/<?= (int)($installment['booking_id'] ?? 0) ?>" class="btn btn-outline-primary w-100">
                    <i class="fas fa-eye me-2"></i><?= __('user_installment_success_view_booking', 'Back to Booking') ?>
                </a>
            </div>
            <div class="col-sm-6">
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary w-100">
                    <i class="fas fa-list me-2"></i><?= __('user_installment_success_all_bookings', 'All My Bookings') ?>
                </a>
            </div>
        </div>

    </div>
</div>
