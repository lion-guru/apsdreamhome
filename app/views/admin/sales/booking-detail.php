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
$co_owners = $co_owners ?? [];
$stamp_preview = $stamp_preview ?? ['show' => false];
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
$plot_gata = $plot_gata ?? null;
?>
<?php if (!empty($plot_gata) && !empty($plot_gata['gata_number'])): ?>
<div class="alert alert-light border small py-2 mb-3"><i class="fas fa-mountain me-1"></i>Plot <strong><?= htmlspecialchars($plot_gata['plot_number'] ?? '') ?></strong> · Gata <strong><?= htmlspecialchars($plot_gata['gata_number']) ?></strong> · Kisan <?= htmlspecialchars($plot_gata['owner_name'] ?? '— darj nahi —') ?><?= !empty($plot_gata['owner_phone']) ? ' (' . htmlspecialchars($plot_gata['owner_phone']) . ')' : '' ?> · <a href="<?= htmlspecialchars($base ?? '') ?>/admin/land-plot-link">desk</a></div>
<?php endif; ?>
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
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/swap" class="btn btn-sm btn-outline-primary"><i class="fas fa-right-left me-1"></i>Swap Plot</a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/legal-kit" class="btn btn-sm btn-outline-success"><i class="fas fa-file-archive me-1"></i>Legal Kit</a>
            <a href="<?= htmlspecialchars($base ?? '') ?>/admin/bookings/<?= (int)($booking['id'] ?? 0) ?>/legal-kit" class="btn btn-sm btn-outline-success" title="Legal Kit (Admin Booking Lifecycle)"><i class="fas fa-file-archive me-1"></i>Admin Legal Kit</a>
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
            <div class="progress"><div class="progress-bar bg-success"></div></div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sched"><?= __('sale_emi_schedule') ?></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rcpt"><?= __('sale_receipts') ?> (<?= count($receipts) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dl"><?= __('sale_demand_letters') ?> (<?= count($demand_letters) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#comm"><?= __('sale_commissions') ?> (<?= count($commissions) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#doc"><?= __('sale_documents') ?> (<?= count($documents) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#coown">Joint Owners (<?= count($co_owners) ?>)</a></li>
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
                <thead><tr><th><?= __('sale_receipt_num') ?></th><th><?= __('sale_date') ?></th><th><?= __('sale_mode') ?></th><th><?= __('sale_status') ?></th><th class="text-end"><?= __('sale_amount') ?></th><th><?= __('sale_collected_by') ?></th><th></th></tr></thead>
                <tbody>
                <?php if (empty($receipts)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3"><?= __('sale_no_receipts') ?></td></tr>
                <?php else: foreach ($receipts as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($r['receipt_number'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['receipt_date'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($r['payment_mode'] ?? '')) ?></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars((string)($r['status'] ?? '')) ?></span></td>
                        <td class="text-end">&#8377;<?= number_format((float)($r['amount'] ?? 0)) ?></td>
                        <td><?= htmlspecialchars((string)($r['collected_by'] ?? '—')) ?></td>
                        <td class="text-end">
                            <?php if (($r['payment_mode'] ?? '') === 'cheque' && ($r['status'] ?? '') === 'cleared'): ?>
                                <form method="POST" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/receipts/<?= (int)($r['id'] ?? 0) ?>/bounce" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
                                    <input type="hidden" name="bounce_reason" value="Cheque bounced by bank">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Mark cheque bounced (reverts installment + Rs.500 penalty)" data-aps-confirm="Mark this cheque as bounced? Installment reverts and Rs.500 penalty accrues."><i class="fas fa-undo me-1"></i>Bounce</button>
                                </form>
                            <?php endif; ?>
                            <?php if (($r['payment_mode'] ?? '') === 'cheque' && ($r['status'] ?? '') === 'bounced'): ?>
                                <a href="<?= htmlspecialchars($base ?? '') ?>/admin/sales/receipts/<?= (int)($r['id'] ?? 0) ?>/legal-notice-138" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Section 138 15-day demand notice draft"><i class="fas fa-gavel me-1"></i>138 Notice</a>
                            <?php endif; ?>
                        </td>
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
    <div class="tab-pane fade" id="coown">
        <div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
            <?php if (!empty($stamp_preview['show'])): ?>
                <div class="alert alert-<?= !empty($co_owners) && !empty($stamp_preview['has_female']) ? 'success' : 'info' ?> py-2">
                    <strong>UP Stamp Duty preview</strong> on ₹<?= number_format((float)$stamp_preview['valuation']) ?>:
                    sole-male <?= number_format((float)$stamp_preview['male_rate'], 2) ?>% = <strong>₹<?= number_format((float)$stamp_preview['duty_male']) ?></strong>
                    <?php if ($stamp_preview['duty_joint'] !== null): ?>
                        &nbsp;|&nbsp; joint <?= number_format((float)$stamp_preview['joint_rate'], 2) ?>% = <strong>₹<?= number_format((float)$stamp_preview['duty_joint']) ?></strong>
                        &nbsp;→&nbsp; <strong class="text-success">you save ₹<?= number_format((float)$stamp_preview['saving']) ?></strong>
                        <?= empty($stamp_preview['has_female']) ? '(no female co-owner yet — add one to unlock the joint rate)' : '(female co-owner on record)' ?>
                    <?php else: ?>
                        &nbsp;→&nbsp; add a female co-owner below to unlock the joint <?= number_format((float)$stamp_preview['joint_rate'], 2) ?>% rate.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="table-responsive"><table class="table table-sm m-0">
                <thead><tr><th>Name</th><th>Relation</th><th>Gender</th><th>Aadhaar</th><th>PAN</th><th class="text-end">Share %</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($co_owners)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">No joint owners — sole allotment.</td></tr>
                <?php else: foreach ($co_owners as $co): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($co['full_name'] ?? '')) ?><?= !empty($co['is_primary_contact']) ? ' <span class="badge bg-info">primary contact</span>' : '' ?></td>
                        <td><?= htmlspecialchars((string)($co['relationship'] ?? '—')) ?></td>
                        <td><span class="badge bg-<?= ($co['gender'] ?? '') === 'female' ? 'success' : 'secondary' ?>"><?= htmlspecialchars((string)($co['gender'] ?? '')) ?></span></td>
                        <td><?= htmlspecialchars((string)($co['aadhaar_no'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars((string)($co['pan_no'] ?? '—')) ?></td>
                        <td class="text-end"><?= number_format((float)($co['share_percentage'] ?? 0), 2) ?>%</td>
                        <td class="text-end">
                            <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/co-owners/delete/<?= (int)($co['id'] ?? 0) ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" data-aps-confirm="Remove this co-owner?"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
            <hr class="my-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-user-shield me-1"></i>Legal Nominee (वारिसदार)</h6>
            <?php if (!empty($booking['nominee_name'])): ?>
                <p class="small mb-2"><strong><?= htmlspecialchars((string)$booking['nominee_name']) ?></strong> (<?= htmlspecialchars((string)($booking['nominee_relation'] ?? '')) ?>)<?= !empty($booking['guardian_name']) ? ' · guardian: ' . htmlspecialchars((string)$booking['guardian_name']) : '' ?></p>
            <?php else: ?>
                <p class="small text-muted mb-2">No nominee recorded.</p>
            <?php endif; ?>
            <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/nominee" class="row g-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
                <div class="col-md-3"><input type="text" name="nominee_name" class="form-control form-control-sm" placeholder="Nominee name *" required maxlength="150" value="<?= htmlspecialchars((string)($booking['nominee_name'] ?? '')) ?>"></div>
                <div class="col-md-2"><select name="nominee_relation" class="form-select form-select-sm"><option value="">Relation…</option><?php foreach (['Spouse','Son','Daughter','Mother','Father','Brother','Sister'] as $rel): ?><option <?= ($booking['nominee_relation'] ?? '') === $rel ? 'selected' : '' ?>><?= $rel ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><input type="date" name="nominee_dob" class="form-control form-control-sm" value="<?= htmlspecialchars((string)($booking['nominee_dob'] ?? '')) ?>" title="DOB"></div>
                <div class="col-md-2"><input type="text" name="nominee_aadhaar" class="form-control form-control-sm" placeholder="Aadhaar" maxlength="20" value="<?= htmlspecialchars((string)($booking['nominee_aadhaar'] ?? '')) ?>"></div>
                <div class="col-md-2"><input type="text" name="guardian_name" class="form-control form-control-sm" placeholder="Guardian (if minor)" maxlength="150" value="<?= htmlspecialchars((string)($booking['guardian_name'] ?? '')) ?>"></div>
                <div class="col-md-1"><button type="submit" class="btn btn-sm btn-secondary w-100">Save</button></div>
            </form>
            <form method="post" action="<?= htmlspecialchars($base ?? '') ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>/co-owners/add" class="row g-2 mt-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
                <div class="col-md-3"><input type="text" name="full_name" class="form-control form-control-sm" placeholder="Full name *" required maxlength="150"></div>
                <div class="col-md-2"><select name="relationship" class="form-select form-select-sm"><option value="">Relation…</option><option>Spouse</option><option>Mother</option><option>Father</option><option>Son</option><option>Daughter</option><option>Brother</option><option>Sister</option></select></div>
                <div class="col-md-2"><select name="gender" class="form-select form-select-sm"><option value="female">Female</option><option value="male">Male</option><option value="other">Other</option></select></div>
                <div class="col-md-2"><input type="text" name="aadhaar_no" class="form-control form-control-sm" placeholder="Aadhaar" maxlength="20"></div>
                <div class="col-md-1"><input type="number" step="0.01" min="0" max="100" name="share_percentage" value="50" class="form-control form-control-sm" title="Share %"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus me-1"></i>Add Owner</button></div>
            </form>
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
