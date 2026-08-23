<?php
$lead    = $lead    ?? [];
$mode    = $mode    ?? 'create';
$brokers = $brokers ?? [];
$action  = $mode === 'edit'
    ? BASE_URL . '/admin/land-inventory/leads/' . (int)($lead['id'] ?? 0) . '/update'
    : BASE_URL . '/admin/land-inventory/leads/store';
$sources = ['broker','scout','direct','referral','web','phone'];
$statuses = ['new','screening','visit_done','dd','negotiation','legal','sale_agreement','registered','rejected','dropped'];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-<?= $mode === 'edit' ? 'edit' : 'plus-circle' ?> text-primary me-2"></i>
            <?= $mode === 'edit' ? 'Edit Lead #' . (int)($lead['id'] ?? 0) : 'New Land Lead' ?>
        </h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Leads
        </a>
    </div>

    <form method="post" action="<?= $action ?>" class="aps-cp-card">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Lead Information</div>
        <div class="aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Owner Name <span class="text-danger">*</span></label>
                    <input type="text" name="land_owner_name" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['land_owner_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Owner Phone</label>
                    <input type="text" name="owner_phone" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['owner_phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Owner Email</label>
                    <input type="email" name="owner_email" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['owner_email'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Lead Source</label>
                    <select name="lead_source" class="form-select form-select-sm">
                        <?php foreach ($sources as $s): ?>
                            <option value="<?= $s ?>" <?= ($lead['lead_source'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Broker</label>
                    <select name="broker_id" class="form-select form-select-sm">
                        <option value="">— None —</option>
                        <?php foreach ($brokers as $b): ?>
                            <option value="<?= (int)$b['id'] ?>" <?= ($lead['broker_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['broker_name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= ($lead['status'] ?? 'new') === $s ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_',' ',$s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Survey #</label>
                    <input type="text" name="survey_number" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['survey_number'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-map-marker-alt me-2"></i>Location</div>
        <div class="aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Village</label>
                    <input type="text" name="village" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['village'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Tehsil</label>
                    <input type="text" name="tehsil" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['tehsil'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">District</label>
                    <input type="text" name="district" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['district'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">State</label>
                    <input type="text" name="state" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['state'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Pincode</label>
                    <input type="text" name="pincode" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['pincode'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">GPS Lat</label>
                    <input type="number" step="0.0000001" name="gps_lat" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['gps_lat'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">GPS Lng</label>
                    <input type="number" step="0.0000001" name="gps_lng" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['gps_lng'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-ruler-combined me-2"></i>Area & Pricing</div>
        <div class="aps-cp-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Area (Acres)</label>
                    <input type="number" step="0.01" name="area_acres" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['area_acres'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Area (sqft)</label>
                    <input type="number" step="0.01" name="area_sqft" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['area_sqft'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Expected Price (₹)</label>
                    <input type="number" step="0.01" name="expected_price" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($lead['expected_price'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small">Notes</label>
                    <textarea name="notes" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($lead['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="aps-cp-card-body text-end style-60001">
            <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save me-1"></i><?= $mode === 'edit' ? 'Update Lead' : 'Create Lead' ?>
            </button>
        </div>
    </div>
    </form>
</div>
