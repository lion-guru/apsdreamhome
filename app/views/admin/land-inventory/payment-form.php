<?php
$acq = $acquisition ?? [];
$payment = $payment ?? [];
$id = (int)($acq['id'] ?? 0);
$isEdit = !empty($payment['id']);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> text-primary me-2"></i>
            <?= $isEdit ? 'Edit' : 'Add' ?> Payment — Acquisition #<?= $id ?>
        </h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>/payments/<?= $isEdit ? 'update/'.$payment['id'] : 'store' ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Payment Type <span class="text-danger">*</span></label>
                                <select name="payment_type" class="form-select form-select-sm" required>
                                    <?php
                                    $types = ['earnest_money'=>'Earnest Money','advance'=>'Advance','installment'=>'Installment','final_payment'=>'Final Payment','stamp_duty'=>'Stamp Duty','registration_fee'=>'Registration Fee','broker_commission'=>'Broker Commission','mutation_fee'=>'Mutation Fee','other'=>'Other'];
                                    foreach ($types as $k=>$v):
                                        $sel = ($payment['payment_type'] ?? '') === $k ? 'selected' : '';
                                    ?>
                                        <option value="<?= $k ?>" <?= $sel ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" step="0.01" class="form-control form-control-sm" required value="<?= htmlspecialchars($payment['amount'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control form-control-sm" required value="<?= htmlspecialchars($payment['payment_date'] ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Mode</label>
                                <select name="payment_mode" class="form-select form-select-sm">
                                    <?php foreach (['cash'=>'Cash','cheque'=>'Cheque','dd'=>'Demand Draft','neft'=>'NEFT','rtgs'=>'RTGS','upi'=>'UPI','bank_transfer'=>'Bank Transfer'] as $k=>$v):
                                        $sel = ($payment['payment_mode'] ?? 'neft') === $k ? 'selected' : ''; ?>
                                        <option value="<?= $k ?>" <?= $sel ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['pending'=>'Pending','cleared'=>'Cleared','bounced'=>'Bounced','cancelled'=>'Cancelled'] as $k=>$v):
                                        $sel = ($payment['status'] ?? 'pending') === $k ? 'selected' : ''; ?>
                                        <option value="<?= $k ?>" <?= $sel ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Reference # (Cheque/DD/UTR)</label>
                                <input type="text" name="reference_number" class="form-control form-control-sm" value="<?= htmlspecialchars($payment['reference_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" value="<?= htmlspecialchars($payment['bank_name'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Remarks</label>
                                <textarea name="remarks" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($payment['remarks'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Payment</button>
                            <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= $id ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Deal Summary</div>
                <div class="aps-cp-card-body">
                    <p class="mb-1"><strong>Vendor:</strong> <?= htmlspecialchars($acq['land_owner_name'] ?? '—') ?></p>
                    <p class="mb-1"><strong>Final:</strong> ₹<?= number_format((float)($acq['final_price'] ?? 0)) ?></p>
                    <p class="mb-1"><strong>Stamp Duty:</strong> ₹<?= number_format((float)($acq['stamp_duty_amount'] ?? 0)) ?></p>
                    <p class="mb-0"><strong>Reg Fee:</strong> ₹<?= number_format((float)($acq['registration_fee'] ?? 0)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
