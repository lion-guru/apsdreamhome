<?php
$lead  = $lead ?? [];
$deal  = $deal ?? [];
$id = (int)($lead['id'] ?? 0);
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-registered text-success me-2"></i>Register Property — Lead #<?= $id ?></h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Owner & Property</div>
        <div class="aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/register" id="regForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <h6 class="text-muted mt-2 mb-3">Negotiated Terms</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Negotiated Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="negotiated_price" step="0.01" class="form-control form-control-sm" required value="<?= htmlspecialchars($deal['negotiated_price'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Final Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="final_price" step="0.01" class="form-control form-control-sm" required value="<?= htmlspecialchars($deal['final_price'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Broker Commission (₹)</label>
                        <input type="number" name="broker_commission" step="0.01" class="form-control form-control-sm" value="<?= htmlspecialchars($deal['broker_commission'] ?? 0) ?>">
                    </div>
                </div>

                <h6 class="text-muted mt-4 mb-3">Sale Deed & Registration</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Sale Deed Number <span class="text-danger">*</span></label>
                        <input type="text" name="sale_deed_number" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Registration Date <span class="text-danger">*</span></label>
                        <input type="date" name="registration_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Reg Office / SRO <span class="text-danger">*</span></label>
                        <input type="text" name="registration_office" class="form-control form-control-sm" required placeholder="e.g. SRO Gorakhpur">
                    </div>
                </div>

                <h6 class="text-muted mt-4 mb-3">Government Duties (auto-creates pending payments)</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Stamp Duty (₹)</label>
                        <input type="number" name="stamp_duty_amount" step="0.01" class="form-control form-control-sm" placeholder="Auto-calculated if blank">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Registration Fee (₹)</label>
                        <input type="number" name="registration_fee" step="0.01" class="form-control form-control-sm" placeholder="Auto-calculated if blank">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Mutation Filed Date</label>
                        <input type="date" name="mutation_filed_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Mutation Number</label>
                        <input type="text" name="mutation_number" class="form-control form-control-sm">
                    </div>
                </div>

                <h6 class="text-muted mt-4 mb-3">Compliance</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="property_tax_upto_date" value="1" id="taxUpto">
                            <label class="form-check-label" for="taxUpto">Property Tax Up-to-date</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">RERA Registration #</label>
                        <input type="text" name="rera_registration" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">DD Reference</label>
                        <input type="text" name="dd_reference" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-registered me-1"></i>Register Property & Create Payment Plan
                    </button>
                    <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
