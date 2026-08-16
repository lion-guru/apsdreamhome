<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/vendors" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>Back to Vendors
            </a>
            <h1 class="h3 mt-2 mb-1">Edit Vendor</h1>
            <p class="text-muted mb-0">Update vendor information</p>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/admin/vendors/update/<?= $vendor['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vendor_name" class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="<?= htmlspecialchars($vendor['vendor_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vendor_type" class="form-label fw-semibold">Vendor Type</label>
                                <select class="form-select" id="vendor_type" name="vendor_type">
                                    <option value="contractor" <?= ($vendor['vendor_type'] ?? '') === 'contractor' ? 'selected' : '' ?>>Contractor</option>
                                    <option value="supplier" <?= ($vendor['vendor_type'] ?? '') === 'supplier' ? 'selected' : '' ?>>Supplier</option>
                                    <option value="service_provider" <?= ($vendor['vendor_type'] ?? '') === 'service_provider' ? 'selected' : '' ?>>Service Provider</option>
                                    <option value="consultant" <?= ($vendor['vendor_type'] ?? '') === 'consultant' ? 'selected' : '' ?>>Consultant</option>
                                    <option value="transport" <?= ($vendor['vendor_type'] ?? '') === 'transport' ? 'selected' : '' ?>>Transport</option>
                                    <option value="other" <?= ($vendor['vendor_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label fw-semibold">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?= htmlspecialchars($vendor['contact_person'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($vendor['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($vendor['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($vendor['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($vendor['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="blacklisted" <?= ($vendor['status'] ?? '') === 'blacklisted' ? 'selected' : '' ?>>Blacklisted</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="rating" class="form-label fw-semibold">Rating (1-5)</label>
                                <select class="form-select" id="rating" name="rating">
                                    <?php $curRating = floatval($vendor['rating'] ?? 0); ?>
                                    <?php for ($r = 0; $r <= 5; $r++): ?>
                                        <option value="<?= $r ?>" <?= $curRating == $r ? 'selected' : '' ?>><?= $r ?> <?= $r === 1 ? 'Star' : ($r > 1 ? 'Stars' : '') ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Address</label>
                            <div class="input-group">
                                <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($vendor['address'] ?? '') ?></textarea>
                                <button type="button" class="btn btn-outline-secondary" data-action="map-picker" data-target="address" title="Pick on Map">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-semibold">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($vendor['city'] ?? '') ?>" data-autofill="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label fw-semibold">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?= htmlspecialchars($vendor['state'] ?? '') ?>" data-autofill="state">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="pincode" class="form-label fw-semibold">Pincode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="pincode" name="pincode" value="<?= htmlspecialchars($vendor['pincode'] ?? '') ?>" data-autofill="pincode" maxlength="6" placeholder="Enter pincode">
                                    <button type="button" class="btn btn-outline-secondary" data-action="gps" title="Use My Location">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </button>
                                </div>
                            </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-id-card me-2 text-primary"></i>KYC & Tax Classification</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="entity_type" class="form-label fw-semibold">Entity Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="entity_type" name="entity_type" onchange="updateTdsSection()">
                                    <option value="individual" <?= ($vendor['entity_type'] ?? '') === 'individual' ? 'selected' : '' ?>>Individual (TDS 1%)</option>
                                    <option value="company" <?= ($vendor['entity_type'] ?? '') === 'company' ? 'selected' : '' ?>>Company (TDS 2%)</option>
                                    <option value="partnership" <?= ($vendor['entity_type'] ?? '') === 'partnership' ? 'selected' : '' ?>>Partnership (TDS 2%)</option>
                                    <option value="proprietorship" <?= ($vendor['entity_type'] ?? '') === 'proprietorship' ? 'selected' : '' ?>>Proprietorship (TDS 1%)</option>
                                </select>
                                <div class="form-text">Determines 194C TDS rate automatically</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pan_number" class="form-label fw-semibold">PAN Number</label>
                                <input type="text" class="form-control" id="pan_number" name="pan_number" value="<?= htmlspecialchars($vendor['pan_number'] ?? '') ?>" maxlength="10" class="style-36130">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gstin" class="form-label fw-semibold">GSTIN</label>
                                <input type="text" class="form-control" id="gstin" name="gstin" value="<?= htmlspecialchars($vendor['gstin'] ?? $vendor['gst_number'] ?? '') ?>" maxlength="15" class="style-36130">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">TDS Section</label>
                                <input type="text" class="form-control" id="tds_section_display" value="<?= htmlspecialchars($vendor['tds_section'] ?? '194C') ?>" readonly class="style-92816">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">TDS Rate</label>
                                <?php
                                $et = $vendor['entity_type'] ?? 'individual';
                                $tdsRate = in_array($et, ['individual', 'proprietorship']) ? '1%' : '2%';
                                ?>
                                <input type="text" class="form-control" id="tds_rate_display" value="<?= $tdsRate ?>" readonly class="style-92816">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">TDS Applicable</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_tds_applicable" name="is_tds_applicable" value="1" <?= ($vendor['is_tds_applicable'] ?? 1) == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_tds_applicable">Deduct TDS</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">KYC Status</label>
                                <?php
                                $kycColors = ['verified' => 'success', 'pending' => 'warning', 'rejected' => 'danger'];
                                $kycColor = $kycColors[$vendor['kyc_status'] ?? 'pending'] ?? 'secondary';
                                ?>
                                <div class="mt-2">
                                    <span class="badge bg-<?= $kycColor ?> fs-6"><?= ucfirst($vendor['kyc_status'] ?? 'pending') ?></span>
                                    <?php if (!empty($vendor['kyc_verified_at'])): ?>
                                        <br><small class="text-muted">Verified: <?= htmlspecialchars($vendor['kyc_verified_at'] ?? '') ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Bank Details</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label fw-semibold">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= htmlspecialchars($vendor['bank_name'] ?? '') ?>" data-autofill="bank_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bank_account" class="form-label fw-semibold">Account Number</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account" value="<?= htmlspecialchars($vendor['bank_account'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ifsc_code" class="form-label fw-semibold">IFSC Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-uppercase" id="ifsc_code" name="ifsc_code" value="<?= htmlspecialchars($vendor['ifsc_code'] ?? '') ?>" data-autofill="ifsc" maxlength="11" placeholder="SBIN0001234">
                                    <button type="button" class="btn btn-outline-secondary" data-action="ifsc-lookup" title="Auto-fill from IFSC">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label fw-semibold">Payment Terms</label>
                                <select class="form-select" id="payment_terms" name="payment_terms">
                                    <option value="immediate" <?= ($vendor['payment_terms'] ?? '') === 'immediate' ? 'selected' : '' ?>>Immediate</option>
                                    <option value="7_days" <?= ($vendor['payment_terms'] ?? '') === '7_days' ? 'selected' : '' ?>>7 Days</option>
                                    <option value="15_days" <?= ($vendor['payment_terms'] ?? '') === '15_days' ? 'selected' : '' ?>>15 Days</option>
                                    <option value="30_days" <?= ($vendor['payment_terms'] ?? '') === '30_days' ? 'selected' : '' ?>>30 Days</option>
                                    <option value="45_days" <?= ($vendor['payment_terms'] ?? '') === '45_days' ? 'selected' : '' ?>>45 Days</option>
                                    <option value="60_days" <?= ($vendor['payment_terms'] ?? '') === '60_days' ? 'selected' : '' ?>>60 Days</option>
                                    <option value="90_days" <?= ($vendor['payment_terms'] ?? '') === '90_days' ? 'selected' : '' ?>>90 Days</option>
                                    <option value="milestone" <?= ($vendor['payment_terms'] ?? '') === 'milestone' ? 'selected' : '' ?>>Milestone Based</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Contract Period</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contract_start" class="form-label fw-semibold">Contract Start</label>
                                <input type="date" class="form-control" id="contract_start" name="contract_start" value="<?= htmlspecialchars($vendor['contract_start'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contract_end" class="form-label fw-semibold">Contract End</label>
                                <input type="date" class="form-control" id="contract_end" name="contract_end" value="<?= htmlspecialchars($vendor['contract_end'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($vendor['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Vendor</button>
                            <a href="<?= BASE_URL ?>/admin/vendors" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateTdsSection() {
    var entityType = document.getElementById('entity_type').value;
    var tdsRateEl = document.getElementById('tds_rate_display');

    if (entityType === 'individual' || entityType === 'proprietorship') {
        tdsRateEl.value = '1%';
    } else {
        tdsRateEl.value = '2%';
    }
}
</script>
