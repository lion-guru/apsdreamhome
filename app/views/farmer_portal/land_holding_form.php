<?php
/**
 * Farmer Portal - Add Land Holding form
 */
$farmer = $farmer ?? [];
$farmer_id = $farmer_id ?? ($farmer['id'] ?? 0);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-mountain me-2 text-success"></i>Add Land Holding</h4>
        <a href="<?= BASE_URL ?>/farmers_admin/<?= (int)$farmer_id ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Farmer
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars((string)$error) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars((string)$message) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><?= htmlspecialchars((string)($farmer['name'] ?? 'Farmer')) ?></h5>
        </div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/farmers/<?= (int)$farmer_id ?>/store-land-holding">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($csrf_token ?? '')) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Khasra / Gata Number</label>
                        <input type="text" name="khasra_number" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Land Area <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="land_area" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Area Unit</label>
                        <select aria-label="Land area unit" name="land_area_unit" class="form-select">
                            <option value="acre">Acre</option>
                            <option value="bigha">Bigha</option>
                            <option value="sqft">Sq. Ft.</option>
                            <option value="hectare">Hectare</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Land Type</label>
                        <input type="text" name="land_type" class="form-control" maxlength="100" placeholder="e.g. Agricultural">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Soil Type</label>
                        <input type="text" name="soil_type" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Irrigation Source</label>
                        <input type="text" name="irrigation_source" class="form-control" maxlength="100">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Water Source</label>
                        <input type="text" name="water_source" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Road Access</label>
                        <input type="text" name="road_access" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Electricity Available</label>
                        <select aria-label="Electricity available" name="electricity_available" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Village</label>
                        <input type="text" name="village" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tehsil</label>
                        <input type="text" name="tehsil" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">District</label>
                        <input type="text" name="district" class="form-control" maxlength="100">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Land Value (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="land_value" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Current Status</label>
                        <select aria-label="Current status" name="current_status" class="form-select">
                            <option value="owned">Owned</option>
                            <option value="leased">Leased</option>
                            <option value="disputed">Disputed</option>
                            <option value="under_acquisition">Under Acquisition</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Acquisition Status</label>
                        <select aria-label="Acquisition status" name="acquisition_status" class="form-select">
                            <option value="none">None</option>
                            <option value="initiated">Initiated</option>
                            <option value="negotiating">Negotiating</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Acquisition Date</label>
                        <input type="date" name="acquisition_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acquisition Amount (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="acquisition_amount" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Status</label>
                        <select aria-label="Payment status" name="payment_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Received (&#8377;)</label>
                        <input type="number" step="0.01" min="0" name="payment_received" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ownership Document Ref</label>
                        <input type="text" name="ownership_document" class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mutation Document Ref</label>
                        <input type="text" name="mutation_document" class="form-control" maxlength="255">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea aria-label="Remarks" name="remarks" class="form-control" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Land Holding
                    </button>
                    <a href="<?= BASE_URL ?>/farmers_admin/<?= (int)$farmer_id ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
