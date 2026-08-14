<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/vendors" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i>Back to Vendors
            </a>
            <h1 class="h3 mt-2 mb-1">Add New Vendor</h1>
            <p class="text-muted mb-0">Register a new contractor, supplier, or service provider</p>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/admin/vendors/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vendor_name" class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vendor_type" class="form-label fw-semibold">Vendor Type</label>
                                <select class="form-select" id="vendor_type" name="vendor_type">
                                    <option value="contractor">Contractor</option>
                                    <option value="supplier">Supplier</option>
                                    <option value="service_provider">Service Provider</option>
                                    <option value="consultant">Consultant</option>
                                    <option value="transport">Transport</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label fw-semibold">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="blacklisted">Blacklisted</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Address</label>
                            <div class="input-group">
                                <textarea class="form-control" id="address" name="address" rows="2" data-map-picker="true"></textarea>
                                <button type="button" class="btn btn-outline-secondary" data-action="map-picker" title="Pick on Map">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-semibold">City</label>
                                <input type="text" class="form-control" id="city" name="city" data-autofill="city">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label fw-semibold">State</label>
                                <input type="text" class="form-control" id="state" name="state" data-autofill="state">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="district" class="form-label fw-semibold">District</label>
                                <input type="text" class="form-control" id="district" name="district" data-autofill="district">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label fw-semibold">Pincode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="pincode" name="pincode" data-autofill="pincode" maxlength="6" placeholder="Enter pincode">
                                    <button type="button" class="btn btn-outline-secondary" data-action="gps" title="Use My Location">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-id-card me-2 text-primary"></i>KYC & Tax Classification</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="entity_type" class="form-label fw-semibold">Entity Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="entity_type" name="entity_type" onchange="updateTdsSection()">
                                    <option value="individual">Individual (TDS 1%)</option>
                                    <option value="company">Company (TDS 2%)</option>
                                    <option value="partnership">Partnership (TDS 2%)</option>
                                    <option value="proprietorship">Proprietorship (TDS 1%)</option>
                                </select>
                                <div class="form-text">Determines 194C TDS rate automatically</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pan_number" class="form-label fw-semibold">PAN Number</label>
                                <input type="text" class="form-control" id="pan_number" name="pan_number" placeholder="AAAAA0000A" maxlength="10" class="style-36130">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gstin" class="form-label fw-semibold">GSTIN</label>
                                <input type="text" class="form-control" id="gstin" name="gstin" placeholder="22AAAAA0000A1Z5" maxlength="15" class="style-36130">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">TDS Section</label>
                                <input type="text" class="form-control" id="tds_section_display" value="194C" readonly class="style-92816">
                                <div class="form-text">Auto-detected from Entity Type</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">TDS Rate</label>
                                <input type="text" class="form-control" id="tds_rate_display" value="1%" readonly class="style-92816">
                                <div class="form-text" id="tds_rate_hint">Individual/Proprietorship = 1%, Company/Partnership = 2%</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">TDS Applicable</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_tds_applicable" name="is_tds_applicable" value="1" checked>
                                    <label class="form-check-label" for="is_tds_applicable">Deduct TDS on payments</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Bank Details</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label fw-semibold">Bank Name</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bank_account" class="form-label fw-semibold">Account Number</label>
                                <input type="text" class="form-control" id="bank_account" name="bank_account">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ifsc_code" class="form-label fw-semibold">IFSC Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="SBIN0001234" data-autofill="ifsc" maxlength="11" class="style-36130">
                                    <button type="button" class="btn btn-outline-secondary" data-action="ifsc-lookup" title="Lookup IFSC">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label fw-semibold">Payment Terms</label>
                                <select class="form-select" id="payment_terms" name="payment_terms">
                                    <option value="immediate">Immediate</option>
                                    <option value="7_days">7 Days</option>
                                    <option value="15_days">15 Days</option>
                                    <option value="30_days" selected>30 Days</option>
                                    <option value="45_days">45 Days</option>
                                    <option value="60_days">60 Days</option>
                                    <option value="90_days">90 Days</option>
                                    <option value="milestone">Milestone Based</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Contract Period</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contract_start" class="form-label fw-semibold">Contract Start</label>
                                <input type="date" class="form-control" id="contract_start" name="contract_start">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contract_end" class="form-label fw-semibold">Contract End</label>
                                <input type="date" class="form-control" id="contract_end" name="contract_end">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about this vendor..."></textarea>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Vendor</button>
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
    var tdsSectionEl = document.getElementById('tds_section_display');
    var tdsRateEl = document.getElementById('tds_rate_display');

    tdsSectionEl.value = '194C';

    if (entityType === 'individual' || entityType === 'proprietorship') {
        tdsRateEl.value = '1%';
    } else {
        tdsRateEl.value = '2%';
    }
}
</script>
