<?php
/**
 * Township Commercial Shop & Office Lease Rent Roll Desk
 */
$leases = $leases ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$current_colony = $current_colony ?? null;
$current_status = $current_status ?? 'all';
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-store text-primary me-2"></i>Commercial Shop &amp; Plaza Rent Roll Desk</h3>
            <p class="text-muted small mb-0">Manage township commercial retail shops, shopping plazas, recurring rent roll &amp; utility billing</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLeaseModal">
                <i class="fas fa-plus me-1"></i>New Commercial Lease
            </button>
        </div>
    </div>

    <!-- Rent Roll KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Monthly Rent Run-Rate</div>
                <div class="fs-3 fw-bold text-primary mt-1">₹<?= number_format((float)($stats['monthly_run_rate'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1"><?= (int)($stats['active_leases'] ?? 0) ?> active leased commercial units</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Rent Collected</div>
                <div class="fs-3 fw-bold text-success mt-1">₹<?= number_format((float)($stats['total_collected'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1">Total revenue collected to date</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Outstanding Rent Dues</div>
                <div class="fs-3 fw-bold text-danger mt-1">₹<?= number_format((float)($stats['total_outstanding'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1">Pending rent demand collection</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Security Deposits Held</div>
                <div class="fs-3 fw-bold text-info mt-1">₹<?= number_format((float)($stats['deposits_held'] ?? 0), 2) ?></div>
                <div class="text-muted small mt-1">Held in escrow account</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="/admin/commercial-leases" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label for="filter_colony" class="visually-hidden">Township Colony</label>
                    <select name="colony_id" id="filter_colony" class="form-select form-select-sm">
                        <option value="">All Township Colonies</option>
                        <?php foreach ($colonies as $col): ?>
                            <option value="<?= (int)$col['id'] ?>" <?= ($current_colony == $col['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_status" class="visually-hidden">Status</label>
                    <select name="status" id="filter_status" class="form-select form-select-sm">
                        <option value="all" <?= ($current_status === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= ($current_status === 'active') ? 'selected' : '' ?>>Active Lease</option>
                        <option value="notice_period" <?= ($current_status === 'notice_period') ? 'selected' : '' ?>>Notice Period</option>
                        <option value="terminated" <?= ($current_status === 'terminated') ? 'selected' : '' ?>>Terminated</option>
                        <option value="expired" <?= ($current_status === 'expired') ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
                <?php if ($current_colony || $current_status !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="/admin/commercial-leases" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Commercial Leases Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Unit / Plaza</th>
                            <th>Township Colony</th>
                            <th>Tenant &amp; Trade</th>
                            <th>Monthly Rent</th>
                            <th>Security Deposit</th>
                            <th>Tenure / Lock-in</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leases)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-store-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No commercial leases registered yet.
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newLeaseModal">
                                            Register First Commercial Lease
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leases as $lease): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($lease['unit_number']) ?></div>
                                        <span class="badge bg-light text-dark border text-capitalize small">
                                            <?= str_replace('_', ' ', htmlspecialchars($lease['unit_type'])) ?>
                                        </span>
                                        <div class="small text-muted font-monospace"><?= htmlspecialchars($lease['lease_token']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($lease['colony_name'] ?? 'APS Commercial Complex') ?></div>
                                        <div class="small text-muted"><?= number_format((float)$lease['carpet_area_sqft'], 0) ?> sq.ft carpet</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($lease['tenant_name']) ?></div>
                                        <?php if (!empty($lease['tenant_business_name'])): ?>
                                            <div class="small text-primary fw-medium"><?= htmlspecialchars($lease['tenant_business_name']) ?></div>
                                        <?php endif; ?>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($lease['tenant_phone']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">₹<?= number_format((float)$lease['monthly_rent'], 2) ?></div>
                                        <div class="small text-muted">+<?= (float)$lease['annual_escalation_pct'] ?>% yrly escalation</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-secondary">₹<?= number_format((float)$lease['security_deposit'], 2) ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d M Y', strtotime($lease['lease_start_date'])) ?> to <?= date('d M Y', strtotime($lease['lease_end_date'])) ?></div>
                                        <div class="small text-muted"><?= (int)$lease['lock_in_months'] ?> mo. lock-in</div>
                                    </td>
                                    <td>
                                        <?php if ($lease['status'] === 'active'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>
                                        <?php elseif ($lease['status'] === 'notice_period'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Notice</span>
                                        <?php elseif ($lease['status'] === 'terminated'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Terminated</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="/admin/commercial-leases/<?= $lease['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-invoice-dollar me-1"></i>Rent Ledger
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- New Commercial Lease Modal -->
<div class="modal fade" id="newLeaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/commercial-leases">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Register New Commercial Shop / Plaza Lease</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modal_colony" class="form-label fw-semibold">Township Colony / Complex <span class="text-danger">*</span></label>
                            <select name="colony_id" id="modal_colony" class="form-select" required>
                                <option value="">Select Colony...</option>
                                <?php foreach ($colonies as $col): ?>
                                    <option value="<?= (int)$col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_unit" class="form-label fw-semibold">Unit / Shop No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_unit" name="unit_number" placeholder="e.g. SHOP-G-04" required>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_type" class="form-label fw-semibold">Unit Type</label>
                            <select name="unit_type" id="modal_type" class="form-select">
                                <option value="retail_shop">Retail Shop</option>
                                <option value="office_space">Office Suite</option>
                                <option value="atm_kiosk">ATM / Kiosk</option>
                                <option value="commercial_plot">Commercial Plot</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="modal_carpet" class="form-label">Carpet Area (sq.ft)</label>
                            <input type="number" step="0.01" class="form-control" id="modal_carpet" name="carpet_area_sqft" placeholder="e.g. 350">
                        </div>
                        <div class="col-md-4">
                            <label for="modal_rent" class="form-label fw-semibold">Monthly Rent (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="modal_rent" name="monthly_rent" placeholder="e.g. 25000" required>
                        </div>
                        <div class="col-md-4">
                            <label for="modal_deposit" class="form-label fw-semibold">Security Deposit (₹)</label>
                            <input type="number" step="0.01" class="form-control" id="modal_deposit" name="security_deposit" placeholder="e.g. 75000">
                        </div>
                    </div>

                    <hr class="text-muted">
                    <h6 class="fw-bold text-primary mb-3">Tenant &amp; Trade Details</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="modal_tenant_name" class="form-label fw-semibold">Tenant Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_tenant_name" name="tenant_name" placeholder="e.g. Vivek Agrawal" required>
                        </div>
                        <div class="col-md-4">
                            <label for="modal_tenant_biz" class="form-label">Business / Trade Name</label>
                            <input type="text" class="form-control" id="modal_tenant_biz" name="tenant_business_name" placeholder="e.g. Agrawal Pharmacy">
                        </div>
                        <div class="col-md-4">
                            <label for="modal_tenant_phone" class="form-label fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_tenant_phone" name="tenant_phone" placeholder="e.g. 9876543210" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modal_tenant_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="modal_tenant_email" name="tenant_email" placeholder="e.g. tenant@example.com">
                        </div>
                        <div class="col-md-6">
                            <label for="modal_tenant_gstin" class="form-label">GSTIN (if applicable)</label>
                            <input type="text" class="form-control" id="modal_tenant_gstin" name="tenant_gstin" placeholder="e.g. 09ABCDE1234F1Z5">
                        </div>
                    </div>

                    <hr class="text-muted">
                    <h6 class="fw-bold text-primary mb-3">Lease Tenure &amp; Submeter Initial Reading</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="modal_start" class="form-label">Lease Start Date</label>
                            <input type="date" class="form-control" id="modal_start" name="lease_start_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="modal_end" class="form-label">Lease End Date</label>
                            <input type="date" class="form-control" id="modal_end" name="lease_end_date" value="<?= date('Y-m-d', strtotime('+11 months')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="modal_meter" class="form-label">Submeter Reading (Units)</label>
                            <input type="number" step="0.01" class="form-control" id="modal_meter" name="electricity_submeter_reading" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Commercial Lease</button>
                </div>
            </div>
        </form>
    </div>
</div>
