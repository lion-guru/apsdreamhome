<?php
/** @var array $booking */
/** @var array $schedule */
/** @var array $receipts */
/** @var array $demand_letters */
/** @var array $documents */
/** @var array $history */
/** @var array $commissions */
$booking = $booking ?? [];
$schedule = $schedule ?? [];
$receipts = $receipts ?? [];
$demand_letters = $demand_letters ?? [];
$documents = $documents ?? [];
$history = $history ?? [];
$commissions = $commissions ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$statusBadge = function ($s) {
    $map = [
        'token_paid'        => 'bg-info',
        'agreement_signed'  => 'bg-primary',
        'emi_active'        => 'bg-warning text-dark',
        'partially_paid'    => 'bg-warning text-dark',
        'fully_paid'        => 'bg-success',
        'cancelled'         => 'bg-danger',
        'transferred'       => 'bg-secondary',
        'registration_done' => 'bg-success',
    ];
    return $map[$s] ?? 'bg-secondary';
};
$totalDue   = 0.0;
$totalPaid  = 0.0;
foreach ($schedule as $s) {
    $totalDue  += (float)($s['amount_due']   ?? 0);
    $totalPaid += (float)($s['amount_paid']  ?? 0);
}
$progressPct = $totalDue > 0 ? min(100, round($totalPaid / $totalDue * 100)) : 0;
?>
<div class="aps-cp-card mb-3">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0">
            <i class="fas fa-bookmark me-2"></i><?= __('sale_booking_num') ?> — <?= htmlspecialchars((string)($booking['booking_number'] ?? '')) ?>
            <span class="badge ms-2 <?= $statusBadge($booking['status'] ?? '') ?>"><?= htmlspecialchars((string)($booking['status'] ?? '')) ?></span>
        </h5>
        <div class="d-flex gap-1 flex-wrap">
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i><?= __('sale_edit') ?></a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/schedule" class="btn btn-sm btn-outline-info"><i class="fas fa-calendar me-1"></i><?= __('sale_schedule') ?></a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/finance/agreement/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-pdf me-1"></i><?= __('sale_agreement') ?></a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/finance/allotment/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-pdf me-1"></i><?= __('sale_allotment') ?></a>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="fas fa-ban me-1"></i><?= __('sale_cancel') ?></button>
            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="fas fa-exchange-alt me-1"></i><?= __('sale_transfer') ?></button>
        </div>
    </div>
    <div class="aps-cp-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_customer') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['customer_name'] ?? '—')) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_plot') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['plot_code'] ?? '—')) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_channel') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['channel'] ?? '—')) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_booking_date') ?></div>
                <div class="fw-bold"><?= htmlspecialchars((string)($booking['booking_date'] ?? '—')) ?></div>
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_plot_value') ?></div>
                <div>&#8377;<?= number_format((float)($booking['total_plot_value'] ?? 0)) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_agreement_value') ?></div>
                <div class="text-primary">&#8377;<?= number_format((float)($booking['agreement_value'] ?? 0)) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_token') ?></div>
                <div>&#8377;<?= number_format((float)($booking['booking_amount'] ?? 0)) ?></div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small"><?= __('sale_total_paid') ?></div>
                <div class="text-success fw-bold">&#8377;<?= number_format($totalPaid) ?></div>
            </div>
        </div>
        <div class="mt-3">
            <div class="d-flex justify-content-between small text-muted"><span><?= __('sale_collection_progress') ?></span><span><?= $progressPct ?>%</span></div>
            <div class="progress" class="style-32124"><div class="progress-bar bg-success" class="style-39132"></div></div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sched"><?= __('sale_emi_schedule') ?></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rcpt"><?= __('sale_receipts') ?> (<?= count($receipts) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dl"><?= __('sale_demand_letters') ?> (<?= count($demand_letters) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#comm"><?= __('sale_commissions') ?> (<?= count($commissions) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#doc"><?= __('sale_documents') ?> (<?= count($documents) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#hist"><?= __('sale_history') ?> (<?= count($history) ?>)</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="sched">
        <div class="aps-cp-card">
            <div class="aps-cp-card-body p-0">
                <div class="table-responsive"><table class="table table-sm m-0">
                    <thead><tr><th>#</th><th><?= __('sale_due_date') ?></th><th><?= __('sale_type') ?></th><th class="text-end"><?= __('sale_amount') ?></th><th class="text-end"><?= __('sale_paid') ?></th><th><?= __('sale_status') ?></th><th><?= __('sale_actions') ?></th></tr></thead>
                    <tbody>
                        <?php if (empty($schedule)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3"><?= __('sale_no_schedule') ?></td></tr>
                        <?php else: foreach ($schedule as $i => $s):
                            $rowClass = !empty($s['is_overdue']) ? 'table-danger' : '';
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= (int)($s['installment_number'] ?? ($i + 1)) ?></td>
                                <td><?= htmlspecialchars((string)($s['due_date'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($s['installment_type'] ?? '')) ?></td>
                                <td class="text-end">&#8377;<?= number_format((float)($s['amount_due'] ?? 0)) ?></td>
                                <td class="text-end text-success">&#8377;<?= number_format((float)($s['amount_paid'] ?? 0)) ?></td>
                                <td>
                                    <?php
                                    $st = $s['status'] ?? 'pending';
                                    $cls = ['paid' => 'success', 'overdue' => 'danger', 'partial' => 'warning', 'cleared' => 'success'][$st] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($st ?? '') ?></span>
                                </td>
                                <td class="text-nowrap">
                                    <?php if ($st !== 'paid'): ?>
                                        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/installments/<?= (int)($s['id'] ?? 0) ?>/pay" class="btn btn-sm btn-success"><i class="fas fa-indian-rupee-sign"></i> <?= __('sale_pay') ?></a>
                                        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/installments/<?= (int)($s['id'] ?? 0) ?>/demand-letter" class="btn btn-sm btn-outline-warning"><i class="fas fa-envelope"></i></a>
                                        <a href="<?= htmlspecialchars($base ?? '') ?>/admin/finance/demand-letter/<?= (int)($s['id'] ?? 0) ?>" class="btn btn-sm btn-outline-danger" title="Download Demand Letter PDF"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="rcpt">
        <div class="aps-cp-card"><div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th><?= __('sale_receipt_num') ?></th><th><?= __('sale_date') ?></th><th><?= __('sale_mode') ?></th><th><?= __('sale_status') ?></th><th class="text-end"><?= __('sale_amount') ?></th><th><?= __('sale_collected_by') ?></th></tr></thead>
                <tbody>
                <?php if (empty($receipts)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3"><?= __('sale_no_receipts') ?></td></tr>
                <?php else: foreach ($receipts as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($r['receipt_number'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['receipt_date'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['payment_mode'] ?? '')) ?></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string)($r['status'] ?? '')) ?></span></td>
                        <td class="text-end">&#8377;<?= number_format((float)($r['amount'] ?? 0)) ?></td>
                        <td><?= htmlspecialchars((string)($r['collected_by'] ?? '—')) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="tab-pane fade" id="dl">
        <div class="aps-cp-card"><div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th><?= __('sale_letter_num') ?></th><th><?= __('sale_generated') ?></th><th><?= __('sale_sent') ?></th><th><?= __('sale_type') ?></th></tr></thead>
                <tbody>
                <?php if (empty($demand_letters)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3"><?= __('sale_none') ?></td></tr>
                <?php else: foreach ($demand_letters as $dl): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($dl['letter_number'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($dl['generated_date'] ?? '')) ?></td>
                        <td><?= !empty($dl['sent_at']) ? '<i class="fas fa-check text-success"></i> ' . htmlspecialchars((string)$dl['sent_at']) : '<span class="text-muted">' . __('sale_draft') . '</span>' ?></td>
                        <td><?= htmlspecialchars((string)($dl['letter_type'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="tab-pane fade" id="comm">
        <div class="aps-cp-card"><div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th><?= __('sale_level') ?></th><th><?= __('sale_beneficiary') ?></th><th><?= __('sale_type') ?></th><th class="text-end"><?= __('sale_pct') ?></th><th class="text-end"><?= __('sale_amount') ?></th><th><?= __('sale_status') ?></th></tr></thead>
                <tbody>
                <?php if (empty($commissions)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3"><?= __('sale_no_commissions') ?></td></tr>
                <?php else: foreach ($commissions as $c): ?>
                    <tr>
                        <td>L<?= (int)($c['level'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($c['beneficiary_name'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string)($c['commission_type'] ?? '')) ?></td>
                        <td class="text-end"><?= number_format((float)($c['percentage'] ?? 0), 2) ?>%</td>
                        <td class="text-end">&#8377;<?= number_format((float)($c['amount'] ?? 0)) ?></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string)($c['status'] ?? '')) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="tab-pane fade" id="doc">
        <div class="aps-cp-card"><div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th><?= __('sale_document') ?></th><th><?= __('sale_type') ?></th><th><?= __('sale_uploaded') ?></th><th><?= __('sale_verified') ?></th></tr></thead>
                <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3"><?= __('sale_no_documents') ?></td></tr>
                <?php else: foreach ($documents as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($d['document_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($d['document_type'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($d['uploaded_at'] ?? '')) ?></td>
                        <td><?= !empty($d['verified_at']) ? '<i class="fas fa-check text-success"></i>' : '<span class="text-muted">' . __('sale_pending') . '</span>' ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="tab-pane fade" id="hist">
        <div class="aps-cp-card"><div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th><?= __('sale_date') ?></th><th><?= __('sale_from') ?></th><th><?= __('sale_to') ?></th><th><?= __('sale_by') ?></th><th><?= __('sale_note') ?></th></tr></thead>
                <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3"><?= __('sale_no_history') ?></td></tr>
                <?php else: foreach ($history as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($h['changed_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($h['from_status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($h['to_status'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($h['changed_by_name'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string)($h['notes'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>

<!-- Cancel modal -->
<div class="modal fade" id="cancelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/cancel">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
        <div class="modal-header"><h5 class="modal-title"><?= __('sale_cancel_booking') ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label"><?= __('sale_reason') ?></label><textarea name="reason" class="form-control" required></textarea></div>
            <div class="mb-2"><label class="form-label"><?= __('sale_cancellation_charge') ?></label><input type="number" step="0.01" name="cancellation_charge" value="0" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button"><?= __('sale_close') ?></button><button class="btn btn-danger" type="submit"><?= __('sale_cancel_booking') ?></button></div>
    </form>
</div></div></div>

<!-- Transfer modal -->
<div class="modal fade" id="transferModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/transfer">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
        <div class="modal-header"><h5 class="modal-title"><?= __('sale_transfer_booking') ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-2"><label class="form-label"><?= __('sale_new_customer_id') ?></label><input type="number" name="new_customer_id" class="form-control" required></div>
            <div class="mb-2"><label class="form-label"><?= __('sale_reason') ?></label><textarea name="reason" class="form-control" required></textarea></div>
            <div class="mb-2"><label class="form-label"><?= __('sale_transfer_charge') ?></label><input type="number" step="0.01" name="transfer_charge" value="0" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button"><?= __('sale_close') ?></button><button class="btn btn-warning" type="submit"><?= __('sale_initiate_transfer') ?></button></div>
    </form>
</div></div></div>
