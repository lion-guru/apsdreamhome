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

    <div class="row g-3">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plus-circle me-2"></i>Add New Broker</div>
                <div class="aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/brokers/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-2">
                            <label class="form-label small">Broker Name <span class="text-danger">*</span></label>
                            <input type="text" name="broker_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Broker Type</label>
                            <select name="broker_type" class="form-select form-select-sm">
                                <option value="individual">Individual</option>
                                <option value="firm">Firm / Agency</option>
                                <option value="agent">Agent</option>
                                <option value="reference">Reference / Friend</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Phone</label>
                                <input type="tel" name="phone" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label small">WhatsApp</label>
                                <input type="tel" name="whatsapp" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-2 mt-2">
                            <label class="form-label small">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Address</label>
                            <textarea name="address" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Specialization</label>
                            <input type="text" name="specialization" class="form-control form-control-sm" placeholder="e.g. Agricultural land, UP">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Commission Rate (%)</label>
                            <input type="number" name="commission_rate" step="0.01" class="form-control form-control-sm" min="0" max="100" value="2">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" checked>
                            <label class="form-check-label" for="act">Active</label>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">KYC Verified</label>
                            <select name="kyc_verified" class="form-select form-select-sm">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Add Broker
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Broker Directory (<?= count($brokers) ?>)</div>
                <div class="aps-cp-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Type</th><th>Phone</th><th>Specialization</th><th>Commission</th><th>KYC</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($brokers as $b): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($b['broker_name'] ?? '—') ?></strong>
                                        <?php if (!empty($b['email'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($b['email'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($b['broker_type'] ?? '—')) ?></span></td>
                                    <td><small><?= htmlspecialchars($b['phone'] ?? '—') ?></small></td>
                                    <td><small><?= htmlspecialchars($b['specialization'] ?? '—') ?></small></td>
                                    <td><?= number_format((float)($b['commission_rate'] ?? 0), 2) ?>%</td>
                                    <td>
                                        <?php if (!empty($b['kyc_verified'])): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($b['is_active'])): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($brokers)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No brokers added yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
