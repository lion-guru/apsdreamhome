<?php
$brokers = $brokers ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-tie text-primary me-2"></i>Land Brokers</h4>
        <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-mountain me-1"></i>View Leads
        </a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plus-circle me-2"></i>Add New Broker</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/brokers/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Broker Name <span class="text-danger">*</span></label>
                            <input aria-label="Broker name" type="text" name="broker_name" class="form-control form-control-sm" required placeholder="e.g. Ramprasad Yadav">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Broker Type</label>
                            <select aria-label="Broker type" name="broker_type" class="form-select form-select-sm">
                                <option value="individual">Individual</option>
                                <option value="firm">Firm / Agency</option>
                                <option value="agent">Agent</option>
                                <option value="reference">Reference / Friend</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                <input aria-label="Phone" type="tel" name="phone" class="form-control form-control-sm" required placeholder="10-digit mobile">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">WhatsApp</label>
                                <input aria-label="Whatsapp" type="tel" name="whatsapp" class="form-control form-control-sm" placeholder="WhatsApp No">
                            </div>
                        </div>
                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <label class="form-label small">PAN</label>
                                <input aria-label="Pan" type="text" name="pan" class="form-control form-control-sm" placeholder="PAN Number" maxlength="10">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Aadhaar</label>
                                <input aria-label="Aadhaar" type="text" name="aadhaar" class="form-control form-control-sm" placeholder="12-digit Aadhaar" maxlength="16">
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            <label class="form-label small">Email</label>
                            <input aria-label="Email" type="email" name="email" class="form-control form-control-sm" placeholder="broker@gmail.com">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Address</label>
                            <textarea aria-label="Address" name="address" class="form-control form-control-sm" rows="2" placeholder="Full residential / office address"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Specialization / Region</label>
                            <input aria-label="Specialization" type="text" name="specialization" class="form-control form-control-sm" placeholder="e.g. Gorakhpur - AIIMS Road Belt">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small">Brokerage Model</label>
                                <select aria-label="Brokerage type" name="brokerage_type" id="brokerageTypeSelect" class="form-select form-select-sm" onchange="updateBrokerageUnit(this.value, 'brokerageRateLabel', 'brokerageRateInput', 'commissionRateHidden')">
                                    <option value="percentage" selected>Percentage (%)</option>
                                    <option value="per_bigha">Per Bigha (₹/Bigha)</option>
                                    <option value="per_kattha">Per Kattha (₹/Kattha)</option>
                                    <option value="per_decimil">Per Decimil (₹/Decimil)</option>
                                    <option value="per_sqft">Per Sq.Ft. (₹/sqft)</option>
                                    <option value="fixed">Fixed Lump-Sum (₹)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small" id="brokerageRateLabel">Rate (% / ₹)</label>
                                <input aria-label="Brokerage rate" type="number" name="brokerage_rate" id="brokerageRateInput" step="0.01" class="form-control form-control-sm" min="0" value="2.00">
                                <input type="hidden" name="commission_rate" id="commissionRateHidden" value="2.00">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small">KYC Status</label>
                                <select aria-label="Kyc verified" name="kyc_verified" class="form-select form-select-sm">
                                    <option value="0">Pending</option>
                                    <option value="1">Verified</option>
                                </select>
                            </div>
                            <div class="col-6 pt-4">
                                <div class="form-check">
                                    <input aria-label="Is active" class="form-check-input" type="checkbox" name="is_active" value="1" id="act" checked>
                                    <label class="form-check-label small" for="act">Active Broker</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Agreement / Confidential Notes</label>
                            <textarea aria-label="Notes" name="notes" class="form-control form-control-sm" rows="2" placeholder="Agreed terms or background details..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Add Broker & Generate Terms
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Broker Directory (<?= count($brokers) ?>)</span>
                    <span class="badge bg-light text-dark border">Purvanchal Region</span>
                </div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Phone / PAN</th>
                                    <th scope="col">Region</th>
                                    <th scope="col">Commission Model</th>
                                    <th scope="col">KYC</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($brokers as $b): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($b['broker_name'] ?? '—') ?></strong>
                                        <?php if (!empty($b['email'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($b['email'] ?? '') ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($b['agreement_ref_number'])): ?>
                                            <br><span class="badge bg-light text-secondary border" style="font-size: 10px;"><?= htmlspecialchars($b['agreement_ref_number']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($b['broker_type'] ?? '—')) ?></span></td>
                                    <td>
                                        <small><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($b['phone'] ?? '—') ?></small>
                                        <?php if (!empty($b['pan'])): ?>
                                            <br><small class="text-muted">PAN: <?= htmlspecialchars($b['pan']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($b['specialization'] ?? 'Purvanchal') ?></small></td>
                                    <td>
                                        <?php
                                            $bType = $b['brokerage_type'] ?? 'percentage';
                                            $bRate = (float)($b['brokerage_rate'] ?? $b['commission_rate'] ?? 0);
                                            switch ($bType) {
                                                case 'per_bigha':
                                                    echo '<span class="badge bg-info text-dark">₹' . number_format($bRate) . ' / Bigha</span>';
                                                    break;
                                                case 'per_kattha':
                                                    echo '<span class="badge bg-info text-dark">₹' . number_format($bRate) . ' / Kattha</span>';
                                                    break;
                                                case 'per_decimil':
                                                    echo '<span class="badge bg-info text-dark">₹' . number_format($bRate) . ' / Decimil</span>';
                                                    break;
                                                case 'per_sqft':
                                                    echo '<span class="badge bg-info text-dark">₹' . number_format($bRate, 2) . ' / sqft</span>';
                                                    break;
                                                case 'fixed':
                                                    echo '<span class="badge bg-primary">Fixed ₹' . number_format($bRate) . '</span>';
                                                    break;
                                                case 'percentage':
                                                default:
                                                    echo '<span class="badge bg-success">' . number_format($bRate, 2) . '%</span>';
                                                    break;
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($b['kyc_verified'])): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($b['is_active'])): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Print / View Agreement -->
                                            <a href="<?= BASE_URL ?>/admin/land-inventory/brokers/<?= (int)$b['id'] ?>/agreement" 
                                               class="btn btn-outline-info btn-sm" 
                                               title="View & Print Legal Indemnity Contract" 
                                               target="_blank">
                                                <i class="fas fa-file-contract"></i>
                                            </a>
                                            <!-- Send WhatsApp Agreement -->
                                            <?php
                                                $bPhone = preg_replace('/\D/', '', $b['phone'] ?? '');
                                                if (strlen($bPhone) === 10) $bPhone = '91' . $bPhone;
                                                $bRef = $b['agreement_ref_number'] ?? ('LBA-' . date('Y') . '-' . str_pad((string)$b['id'], 4, '0', STR_PAD_LEFT));
                                                $bWaMsg = "नमस्ते " . ($b['broker_name'] ?? '') . " जी, APS Dream Homes Gorakhpur से आपका भूमि दलाली अनुबंध पत्र (" . $bRef . ") तैयार है। अनुबंध लिंक: " . BASE_URL . "/admin/land-inventory/brokers/" . (int)$b['id'] . "/agreement";
                                                $bWaUrl = "https://api.whatsapp.com/send?phone=" . $bPhone . "&text=" . rawurlencode($bWaMsg);
                                            ?>
                                            <a href="<?= $bWaUrl ?>" target="_blank" class="btn btn-outline-success btn-sm" title="Send Agreement via WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <!-- Edit Broker Modal Trigger -->
                                            <button type="button" 
                                                    class="btn btn-outline-primary btn-sm" 
                                                    title="Edit Broker & Rate Terms" 
                                                    onclick='openEditBrokerModal(<?= json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($brokers)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No brokers added yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT BROKER & CUSTOM RATE MODAL -->
<div class="modal fade" id="editBrokerModal" tabindex="-1" aria-labelledby="editBrokerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editBrokerForm" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="editBrokerModalLabel"><i class="fas fa-user-edit text-primary me-2"></i>Edit Broker Terms & Indemnity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Broker Name <span class="text-danger">*</span></label>
                            <input aria-label="Broker name" type="text" name="broker_name" id="edit_broker_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Broker Type</label>
                            <select aria-label="Broker type" name="broker_type" id="edit_broker_type" class="form-select form-select-sm">
                                <option value="individual">Individual</option>
                                <option value="firm">Firm / Agency</option>
                                <option value="agent">Agent</option>
                                <option value="reference">Reference / Friend</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Phone <span class="text-danger">*</span></label>
                            <input aria-label="Phone" type="tel" name="phone" id="edit_phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">WhatsApp</label>
                            <input aria-label="Whatsapp" type="tel" name="whatsapp" id="edit_whatsapp" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Email</label>
                            <input aria-label="Email" type="email" name="email" id="edit_email" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">PAN Number</label>
                            <input aria-label="Pan" type="text" name="pan" id="edit_pan" class="form-control form-control-sm" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Aadhaar Number</label>
                            <input aria-label="Aadhaar" type="text" name="aadhaar" id="edit_aadhaar" class="form-control form-control-sm" maxlength="16">
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Residential / Official Address</label>
                            <textarea aria-label="Address" name="address" id="edit_address" class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small">Specialization Belt</label>
                            <input aria-label="Specialization" type="text" name="specialization" id="edit_specialization" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">KYC Verification</label>
                            <select aria-label="Kyc verified" name="kyc_verified" id="edit_kyc_verified" class="form-select form-select-sm">
                                <option value="0">Pending</option>
                                <option value="1">Verified (Docs Verified)</option>
                            </select>
                        </div>

                        <!-- COMMERCIAL RATE CUSTOMIZATION -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-hand-holding-usd text-teal me-1"></i>Negotiated Commercial Rate (तय की गई दलाली दर)</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small">Brokerage Model</label>
                                        <select aria-label="Brokerage type" name="brokerage_type" id="edit_brokerage_type" class="form-select form-select-sm" onchange="updateBrokerageUnit(this.value, 'editBrokerageRateLabel', 'edit_brokerage_rate', 'editCommissionRateHidden')">
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="per_bigha">Per Bigha (₹/Bigha)</option>
                                            <option value="per_kattha">Per Kattha (₹/Kattha)</option>
                                            <option value="per_decimil">Per Decimil (₹/Decimil)</option>
                                            <option value="per_sqft">Per Sq.Ft. (₹/sqft)</option>
                                            <option value="fixed">Fixed Lump-Sum (₹)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small" id="editBrokerageRateLabel">Rate (% / ₹)</label>
                                        <input aria-label="Brokerage rate" type="number" name="brokerage_rate" id="edit_brokerage_rate" step="0.01" class="form-control form-control-sm" min="0" required>
                                        <input type="hidden" name="commission_rate" id="editCommissionRateHidden">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check pt-2">
                                <input aria-label="Is active" class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                                <label class="form-check-label small" for="edit_is_active">Active Broker in Network</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Agreement Reference No</label>
                            <input aria-label="Agreement ref number" type="text" name="agreement_ref_number" id="edit_agreement_ref_number" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Agreement Terms / Special Instructions</label>
                            <textarea aria-label="Notes" name="notes" id="edit_notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save Changes & Update Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateBrokerageUnit(type, labelId, inputId, hiddenId) {
    const label = document.getElementById(labelId);
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    if (!label || !input) return;

    if (type === 'percentage') {
        label.textContent = 'Rate (%)';
        if (parseFloat(input.value) > 100) input.value = '2.00';
    } else if (type === 'per_bigha') {
        label.textContent = 'Rate (₹ / Bigha)';
    } else if (type === 'per_kattha') {
        label.textContent = 'Rate (₹ / Kattha)';
    } else if (type === 'per_decimil') {
        label.textContent = 'Rate (₹ / Decimil)';
    } else if (type === 'per_sqft') {
        label.textContent = 'Rate (₹ / sqft)';
    } else if (type === 'fixed') {
        label.textContent = 'Fixed Lump-Sum (₹)';
    }
    if (hidden) hidden.value = input.value;
}

document.getElementById('brokerageRateInput')?.addEventListener('input', function() {
    const hidden = document.getElementById('commissionRateHidden');
    if (hidden) hidden.value = this.value;
});
document.getElementById('edit_brokerage_rate')?.addEventListener('input', function() {
    const hidden = document.getElementById('editCommissionRateHidden');
    if (hidden) hidden.value = this.value;
});

function openEditBrokerModal(broker) {
    if (!broker || !broker.id) return;
    const form = document.getElementById('editBrokerForm');
    form.action = '<?= BASE_URL ?>/admin/land-inventory/brokers/' + broker.id + '/update';

    document.getElementById('edit_broker_name').value = broker.broker_name || broker.name || '';
    document.getElementById('edit_broker_type').value = broker.broker_type || 'individual';
    document.getElementById('edit_phone').value = broker.phone || '';
    document.getElementById('edit_whatsapp').value = broker.whatsapp || '';
    document.getElementById('edit_email').value = broker.email || '';
    document.getElementById('edit_pan').value = broker.pan || '';
    document.getElementById('edit_aadhaar').value = broker.aadhaar || '';
    document.getElementById('edit_address').value = broker.address || '';
    document.getElementById('edit_specialization').value = broker.specialization || '';
    document.getElementById('edit_kyc_verified').value = broker.kyc_verified ? '1' : '0';
    document.getElementById('edit_is_active').checked = !!parseInt(broker.is_active);
    document.getElementById('edit_agreement_ref_number').value = broker.agreement_ref_number || '';
    document.getElementById('edit_notes').value = broker.notes || '';

    const model = broker.brokerage_type || 'percentage';
    const rate = parseFloat(broker.brokerage_rate || broker.commission_rate || 0);

    document.getElementById('edit_brokerage_type').value = model;
    document.getElementById('edit_brokerage_rate').value = rate;
    document.getElementById('editCommissionRateHidden').value = rate;
    updateBrokerageUnit(model, 'editBrokerageRateLabel', 'edit_brokerage_rate', 'editCommissionRateHidden');

    const modal = new bootstrap.Modal(document.getElementById('editBrokerModal'));
    modal.show();
}
</script>
