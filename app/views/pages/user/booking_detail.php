<?php
$page_title = $page_title ?? __('user_booking_detail_title', 'Booking Detail');
$current_page = 'bookings';
$booking = $booking ?? null;
$payments = $payments ?? [];
$receipts = $receipts ?? [];
$documents = $documents ?? [];
$history = $history ?? [];
$total_paid = $total_paid ?? 0;
$user = $user ?? [];

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
$statusLabels = [
    'token_paid' => __('user_booking_detail_status_token_paid', 'Token Paid'),
    'agreement_signed' => __('user_booking_detail_status_agreement_signed', 'Agreement Signed'),
    'emi_active' => __('user_booking_detail_status_emi_active', 'EMI Active'),
    'partially_paid' => __('user_booking_detail_status_partially_paid', 'Partially Paid'),
    'fully_paid' => __('user_booking_detail_status_fully_paid', 'Fully Paid'),
    'cancelled' => __('user_booking_detail_status_cancelled', 'Cancelled'),
    'transferred' => __('user_booking_detail_status_transferred', 'Transferred'),
    'registration_done' => __('user_booking_detail_status_registered', 'Registered'),
];

$instStatusColors = [
    'pending' => 'warning',
    'paid' => 'success',
    'overdue' => 'danger',
    'partial' => 'info',
];

$docTypeLabels = [
    'application_form' => __('user_booking_detail_doctype_application_form', 'Application Form'),
    'token_receipt' => __('user_booking_detail_doctype_token_receipt', 'Token Receipt'),
    'sale_agreement' => __('user_booking_detail_doctype_sale_agreement', 'Sale Agreement'),
    'allotment_letter' => __('user_booking_detail_doctype_allotment_letter', 'Allotment Letter'),
    'demand_letter' => __('user_booking_detail_doctype_demand_letter', 'Demand Letter'),
    'noc' => __('user_booking_detail_doctype_noc', 'NOC'),
    'receipt' => __('user_booking_detail_doctype_receipt', 'Receipt'),
    'registry_deed' => __('user_booking_detail_doctype_registry_deed', 'Registry Deed'),
    'mutation_letter' => __('user_booking_detail_doctype_mutation_letter', 'Mutation Letter'),
    'other' => __('user_booking_detail_doctype_other', 'Other'),
];
?>

<div class="aps-cp-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-file-invoice-dollar me-2"></i><?= __('user_booking_detail_heading', 'Booking Detail') ?></h2>
            <?php if ($booking): ?>
                <p><?= __('user_booking_detail_hero_booking', 'Booking') ?> <?= htmlspecialchars($booking['booking_number'] ?? '') ?> &mdash; <?= htmlspecialchars($booking['colony_name'] ?? '') ?></p>
            <?php else: ?>
                <p><?= __('user_booking_detail_not_found_or_no_access', 'Booking not found or you don\'t have access.') ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 mt-3 mt-md-0 text-md-end">
            <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_booking_detail_back_to_bookings', 'Back to Bookings') ?>
            </a>
        </div>
    </div>
</div>

<?php if (!$booking): ?>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body">
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h5><?= __('user_booking_detail_not_found_heading', 'Booking Not Found') ?></h5>
                <p><?= __('user_booking_detail_not_found_desc', 'The booking you\'re looking for doesn\'t exist or you don\'t have access to it.') ?></p>
                <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-primary"><?= __('user_booking_detail_view_my_bookings', 'View My Bookings') ?></a>
            </div>
        </div>
    </div>
<?php else: ?>

<?php
$bStatus = $booking['status'] ?? 'token_paid';
$colorClass = $statusColors[$bStatus] ?? 'secondary';
$statusLabel = $statusLabels[$bStatus] ?? ucfirst($bStatus);
$totalVal = (float)($booking['total_plot_value'] ?? 0);
$paidAmt = (float)($booking['booking_amount'] ?? 0);
$pendingAmt = max(0, $totalVal - $total_paid);
$payPct = $totalVal > 0 ? round(($total_paid / $totalVal) * 100) : 0;
?>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-map-marker-alt text-primary"></i> <?= __('user_booking_detail_plot_details', 'Plot Details') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="mb-3">
                    <span class="badge bg-<?= $colorClass ?> fs-6"><?= $statusLabel ?></span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_colony', 'Colony') ?></small>
                    <strong><?= htmlspecialchars($booking['colony_name'] ?? 'N/A') ?></strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_plot_number', 'Plot Number') ?></small>
                    <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong>
                </div>
                <?php if (!empty($booking['block'])): ?>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_block', 'Block') ?></small>
                    <strong><?= htmlspecialchars($booking['block'] ?? '') ?></strong>
                </div>
                <?php endif; ?>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_area', 'Area') ?></small>
                    <strong><?= number_format((float)($booking['area_sqft'] ?? 0)) ?> sq ft</strong>
                </div>
                <?php if (!empty($booking['width_ft']) && !empty($booking['length_ft'])): ?>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_dimensions', 'Dimensions') ?></small>
                    <strong><?= htmlspecialchars($booking['width_ft'] ?? '') ?> x <?= htmlspecialchars($booking['length_ft'] ?? '') ?> ft</strong>
                    <?php if (!empty($booking['dimension_label'])): ?>
                        <br><small class="text-muted">(<?= htmlspecialchars($booking['dimension_label'] ?? '') ?>)</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($booking['facing'])): ?>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_facing', 'Facing') ?></small>
                    <strong><?= htmlspecialchars($booking['facing'] ?? '') ?></strong>
                </div>
                <?php endif; ?>
                <?php if (!empty($booking['corner_plot'])): ?>
                <div class="mb-2">
                    <span class="badge bg-info"><i class="fas fa-star me-1"></i> <?= __('user_booking_detail_corner_plot', 'Corner Plot') ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($booking['road_width_ft']) && $booking['road_width_ft'] > 0): ?>
                <div class="mb-2">
                    <small class="text-muted d-block"><?= __('user_booking_detail_label_road_width', 'Road Width') ?></small>
                    <strong><?= htmlspecialchars($booking['road_width_ft'] ?? '') ?> ft</strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-info-circle text-primary"></i> <?= __('user_booking_detail_booking_info', 'Booking Info') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_booking_detail_label_booking_number', 'Booking Number') ?></small>
                        <strong class="fs-6"><?= htmlspecialchars($booking['booking_number'] ?? 'N/A') ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_booking_detail_label_booking_date', 'Booking Date') ?></small>
                        <strong class="fs-6"><?= date('d M Y', strtotime($booking['booking_date'] ?? 'now')) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_booking_detail_label_total_value', 'Total Plot Value') ?></small>
                        <strong class="fs-6 text-primary">₹<?= number_format($totalVal) ?></strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block"><?= __('user_booking_detail_label_channel', 'Channel') ?></small>
                        <strong class="fs-6"><?= ucfirst(str_replace('_', ' ', $booking['channel'] ?? 'Direct')) ?></strong>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-semibold"><?= __('user_booking_detail_payment_progress', 'Payment Progress') ?></span>
                        <span class="fw-bold text-<?= $payPct >= 100 ? 'success' : 'primary' ?>"><?= $payPct ?>%</span>
                    </div>
                    <div class="aps-cp-progress" class="style-51045">
                        <div class="aps-cp-progress-bar" class="style-13409"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-success"><?= __('user_booking_detail_paid', 'Paid') ?>: ₹<?= number_format($total_paid) ?></small>
                        <?php if ($pendingAmt > 0): ?>
                            <small class="text-danger"><?= __('user_booking_detail_pending', 'Pending') ?>: ₹<?= number_format($pendingAmt) ?></small>
                        <?php else: ?>
                            <small class="text-success"><i class="fas fa-check-circle"></i> <?= __('user_booking_detail_fully_paid', 'Fully Paid') ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($history)): ?>
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header">
                <h5><i class="fas fa-history text-info"></i> <?= __('user_booking_detail_status_timeline', 'Status Timeline') ?></h5>
            </div>
            <div class="aps-cp-card-body">
                <div class="position-relative" class="style-11366">
                    <div class="position-absolute" class="style-1238"></div>
                    <?php foreach ($history as $i => $h):
                        $hColor = $statusColors[$h['to_status']] ?? 'secondary';
                    ?>
                    <div class="position-relative mb-3">
                        <div class="position-absolute" class="style-41260"></div>
                        <div>
                            <strong class="text-<?= $hColor ?>"><?= $statusLabels[$h['to_status']] ?? ucfirst($h['to_status']) ?></strong>
                            <small class="text-muted ms-2"><?= date('d M Y, h:i A', strtotime($h['created_at'] ?? 'now')) ?></small>
                            <?php if (!empty($h['changed_by_name'])): ?>
                                <small class="text-muted ms-2"><?= __('user_booking_detail_by', 'by') ?> <?= htmlspecialchars($h['changed_by_name'] ?? '') ?></small>
                            <?php endif; ?>
                            <?php if (!empty($h['reason'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($h['reason'] ?? '') ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($payments)): ?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-calendar-alt text-warning"></i> <?= __('user_booking_detail_payment_schedule', 'Payment Schedule') ?> (<?= count($payments) ?> <?= __('user_booking_detail_installments', 'installments') ?>)</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="aps-cp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= __('user_booking_detail_th_due_date', 'Due Date') ?></th>
                        <th class="text-end"><?= __('user_booking_detail_th_amount', 'Amount') ?></th>
                        <th class="text-end"><?= __('user_booking_detail_th_principal', 'Principal') ?></th>
                        <th class="text-end"><?= __('user_booking_detail_th_interest', 'Interest') ?></th>
                        <th><?= __('user_booking_detail_th_status', 'Status') ?></th>
                        <th><?= __('user_booking_detail_th_paid_date', 'Paid Date') ?></th>
                        <th class="text-end"><?= __('user_booking_detail_th_paid_amount', 'Paid Amount') ?></th>
                        <th class="text-end"><?= __('user_booking_detail_th_action', 'Action') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p):
                        $pStatus = $p['status'] ?? 'pending';
                        $pColor = $instStatusColors[$pStatus] ?? 'secondary';
                        $isOverdue = $pStatus === 'overdue';
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td><strong><?= (int)$p['installment_no'] ?></strong></td>
                        <td>
                            <?= date('d M Y', strtotime($p['due_date'] ?? 'now')) ?>
                            <?php if ($isOverdue): ?>
                                <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> <?= round((strtotime('now') - strtotime($p['due_date'])) / 86400) ?> <?= __('user_booking_detail_days_overdue', 'days overdue') ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">₹<?= number_format((float)$p['amount']) ?></td>
                        <td class="text-end">₹<?= number_format((float)($p['principal'] ?? 0)) ?></td>
                        <td class="text-end">₹<?= number_format((float)($p['interest'] ?? 0)) ?></td>
                        <td><span class="badge bg-<?= $pColor ?>"><?= ucfirst($pStatus) ?></span></td>
                        <td><?= $p['paid_date'] ? date('d M Y', strtotime($p['paid_date'])) : '-' ?></td>
                        <td class="text-end">
                            <?php if ((float)($p['paid_amount'] ?? 0) > 0): ?>
                                ₹<?= number_format((float)$p['paid_amount']) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if (in_array($pStatus, ['pending', 'overdue', 'partial'], true)): ?>
                                <a href="<?= BASE_URL ?>/user/installments/<?= (int)$p['id'] ?>/pay" class="btn btn-sm btn-success me-1" title="<?= __('user_booking_detail_pay_now', 'Pay Now') ?>">
                                    <i class="fas fa-credit-card me-1"></i><?= __('user_booking_detail_pay', 'Pay') ?>
                                </a>
                                <a href="<?= BASE_URL ?>/user/installments/<?= (int)$p['id'] ?>/demand-letter" class="aps-cp-icon-btn" title="<?= __('user_booking_detail_view_demand_letter', 'View Demand Letter') ?>" target="_blank">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($receipts)): ?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-receipt text-success"></i> <?= __('user_booking_detail_payment_receipts', 'Payment Receipts') ?> (<?= count($receipts) ?>)</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="aps-cp-table">
                <thead>
                    <tr>
                        <th><?= __('user_booking_detail_th_receipt_number', 'Receipt #') ?></th>
                        <th><?= __('user_booking_detail_th_date', 'Date') ?></th>
                        <th><?= __('user_booking_detail_th_amount', 'Amount') ?></th>
                        <th><?= __('user_booking_detail_th_payment_mode', 'Payment Mode') ?></th>
                        <th><?= __('user_booking_detail_th_reference', 'Reference') ?></th>
                        <th><?= __('user_booking_detail_th_status', 'Status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receipts as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['receipt_number'] ?? 'N/A') ?></strong></td>
                        <td><?= date('d M Y', strtotime($r['receipt_date'] ?? 'now')) ?></td>
                        <td class="text-success fw-bold">₹<?= number_format((float)($r['amount'] ?? 0)) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $r['payment_mode'] ?? 'cash')) ?></td>
                        <td><?= htmlspecialchars($r['transaction_ref'] ?? $r['cheque_number'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= ($r['status'] ?? '') === 'cleared' ? 'success' : (($r['status'] ?? '') === 'bounced' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($r['status'] ?? 'pending') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($documents)): ?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-folder-open text-info"></i> <?= __('user_booking_detail_documents', 'Documents') ?> (<?= count($documents) ?>)</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <div class="table-responsive">
            <table class="aps-cp-table">
                <thead>
                    <tr>
                        <th><?= __('user_booking_detail_th_document', 'Document') ?></th>
                        <th><?= __('user_booking_detail_th_type', 'Type') ?></th>
                        <th><?= __('user_booking_detail_th_status', 'Status') ?></th>
                        <th><?= __('user_booking_detail_th_uploaded', 'Uploaded') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td><strong><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($doc['document_name'] ?? __('user_booking_detail_document_default', 'Document')) ?></strong></td>
                        <td><span class="badge bg-light text-dark"><?= $docTypeLabels[$doc['document_type']] ?? ucfirst($doc['document_type']) ?></span></td>
                        <td>
                            <span class="badge bg-<?= ($doc['status'] ?? '') === 'verified' ? 'success' : (($doc['status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($doc['status'] ?? 'pending') ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($doc['created_at'] ?? 'now')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <?php if ($bStatus === 'token_paid'): ?>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>/user/bookings/<?= (int)$booking['id'] ?>/pay-token" class="btn btn-success w-100 py-3" class="style-12699">
            <i class="fas fa-credit-card me-2"></i><?= __('user_booking_detail_pay_token', 'Pay Token Amount') ?>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary w-100">
            <i class="fas fa-arrow-left me-2"></i><?= __('user_booking_detail_back_to_my_bookings', 'Back to My Bookings') ?>
        </a>
    </div>
    <div class="col-md-4">
        <button class="btn btn-outline-danger w-100" onclick="if(confirm('<?= __('user_booking_detail_cancel_confirm', 'Are you sure you want to cancel this booking? This action cannot be undone.') ?>')) { /* Cancel logic */ }">
            <i class="fas fa-times-circle me-2"></i><?= __('user_booking_detail_cancel_booking', 'Cancel Booking') ?>
        </button>
    </div>
    <?php elseif ($bStatus === 'agreement_signed'): ?>
    <div class="col-md-4">
        <span class="btn btn-success w-100 py-3 disabled" class="style-69721">
            <i class="fas fa-check-circle me-2"></i><?= __('user_booking_detail_payment_complete', 'Payment Complete') ?>
        </span>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary w-100">
            <i class="fas fa-arrow-left me-2"></i><?= __('user_booking_detail_back_to_my_bookings', 'Back to My Bookings') ?>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= BASE_URL ?>/user/bookings/<?= (int)$booking['id'] ?>" class="btn btn-outline-info w-100">
            <i class="fas fa-eye me-2"></i><?= __('user_booking_detail_view_details', 'View Details') ?>
        </a>
    </div>
    <?php else: ?>
    <div class="col-md-6">
        <a href="<?= BASE_URL ?>/user/bookings" class="btn btn-outline-primary w-100">
            <i class="fas fa-arrow-left me-2"></i><?= __('user_booking_detail_back_to_my_bookings', 'Back to My Bookings') ?>
        </a>
    </div>
    <div class="col-md-6">
        <?php if ($bStatus !== 'cancelled' && $bStatus !== 'fully_paid'): ?>
        <button class="btn btn-outline-danger w-100" onclick="if(confirm('<?= __('user_booking_detail_cancel_confirm', 'Are you sure you want to cancel this booking? This action cannot be undone.') ?>')) { /* Cancel logic */ }">
            <i class="fas fa-times-circle me-2"></i><?= __('user_booking_detail_cancel_booking', 'Cancel Booking') ?>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>
