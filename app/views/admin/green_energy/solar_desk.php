<?php
/**
 * Admin PM Surya Ghar Green Solar Township Desk View
 */
$page_title = $page_title ?? 'PM Surya Ghar Solar Township Desk';
$requests = $requests ?? [];
$stats = $stats ?? [];
$current_status = $current_status ?? null;
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-solar-panel text-warning me-2"></i>PM Surya Ghar Green Solar Township Desk
            </h3>
            <p class="text-muted small mb-0">Rooftop solar applications, central (MNRE) & UP state subsidies, PVVNL net-metering sanctions & vendor commissioning.</p>
        </div>
        <div>
            <a href="/customer/solar-calculator" target="_blank" class="btn btn-outline-warning text-dark">
                <i class="fas fa-calculator me-1"></i>Launch Solar Calculator
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">Total Requests</span>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_requests'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">Feasibility Pending</span>
                <h4 class="fw-bold mb-0 text-warning"><?= number_format($stats['pending_feasibility'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">DISCOM Sanctioned</span>
                <h4 class="fw-bold mb-0 text-info"><?= number_format($stats['discom_sanctioned'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">Commissioned</span>
                <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['installed_count'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">Total Capacity</span>
                <h4 class="fw-bold mb-0 text-primary"><?= number_format($stats['total_kw_capacity'] ?? 0, 1) ?> kW</h4>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <span class="text-muted small d-block">Subsidy Claimed</span>
                <h5 class="fw-bold mb-0 text-success">₹<?= number_format($stats['total_subsidy_claimed'] ?? 0) ?></h5>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="fw-bold small text-muted me-2">Filter Status:</span>
                <a href="/admin/green-energy/solar" class="btn btn-sm <?= empty($current_status) ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
                <a href="/admin/green-energy/solar?status=inquiry" class="btn btn-sm <?= $current_status === 'inquiry' ? 'btn-primary' : 'btn-outline-secondary' ?>">Inquiries</a>
                <a href="/admin/green-energy/solar?status=site_feasibility" class="btn btn-sm <?= $current_status === 'site_feasibility' ? 'btn-warning' : 'btn-outline-secondary' ?>">Site Feasibility</a>
                <a href="/admin/green-energy/solar?status=subsidy_approved" class="btn btn-sm <?= $current_status === 'subsidy_approved' ? 'btn-info' : 'btn-outline-secondary' ?>">Subsidy Approved</a>
                <a href="/admin/green-energy/solar?status=discom_sanctioned" class="btn btn-sm <?= $current_status === 'discom_sanctioned' ? 'btn-primary' : 'btn-outline-secondary' ?>">DISCOM Sanctioned</a>
                <a href="/admin/green-energy/solar?status=installed" class="btn btn-sm <?= $current_status === 'installed' ? 'btn-success' : 'btn-outline-secondary' ?>">Commissioned</a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Token & Date</th>
                            <th>Customer & Plot</th>
                            <th>Capacity</th>
                            <th>Cost & Subsidies</th>
                            <th>Net Payable</th>
                            <th>DISCOM / Consumer</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-sun fa-3x mb-3 text-warning opacity-50"></i>
                                    <p class="mb-0">No solar applications found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark font-monospace"><?= htmlspecialchars($req['request_token']) ?></div>
                                        <small class="text-muted"><?= date('d M Y', strtotime($req['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($req['customer_name'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($req['customer_phone'] ?? 'N/A') ?></small>
                                        <?php if (!empty($req['plot_number'])): ?>
                                            <div class="badge bg-light text-dark border mt-1">Plot <?= htmlspecialchars($req['plot_number']) ?> (<?= htmlspecialchars($req['colony_name']) ?>)</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark px-2 py-1 fs-6">
                                            <?= number_format($req['system_capacity_kw'], 1) ?> kW
                                        </span>
                                        <div class="small text-muted mt-1">~<?= number_format($req['estimated_monthly_units']) ?> Units/mo</div>
                                    </td>
                                    <td>
                                        <div><span class="text-muted small">Cost:</span> ₹<?= number_format($req['estimated_project_cost']) ?></div>
                                        <div class="small text-success">
                                            <i class="fas fa-arrow-down me-1"></i>Central: ₹<?= number_format($req['central_subsidy_amount']) ?>
                                        </div>
                                        <div class="small text-success">
                                            <i class="fas fa-arrow-down me-1"></i>UP: ₹<?= number_format($req['state_subsidy_amount']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="fw-bold text-primary mb-0">₹<?= number_format($req['net_customer_payable']) ?></h6>
                                        <div class="small text-muted">Savings: ₹<?= number_format($req['estimated_annual_savings']) ?>/yr</div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= htmlspecialchars($req['discom_provider']) ?></div>
                                        <div class="small text-muted font-monospace">CA: <?= htmlspecialchars($req['consumer_number'] ?? 'Pending') ?></div>
                                        <?php if (!empty($req['sanction_ref'])): ?>
                                            <div class="small text-success"><i class="fas fa-file-signature me-1"></i><?= htmlspecialchars($req['sanction_ref']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($req['status']) {
                                            'installed' => 'bg-success',
                                            'discom_sanctioned' => 'bg-primary',
                                            'subsidy_approved' => 'bg-info',
                                            'site_feasibility' => 'bg-warning text-dark',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal<?= $req['id'] ?>">
                                            <i class="fas fa-edit me-1"></i>Update
                                        </button>

                                        <!-- Update Modal -->
                                        <div class="modal fade" id="updateModal<?= $req['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-start">
                                                    <form method="POST" action="/admin/green-energy/solar/<?= $req['id'] ?>/update">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Solar Application #<?= htmlspecialchars($req['request_token']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Application Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="inquiry" <?= $req['status'] === 'inquiry' ? 'selected' : '' ?>>Inquiry (Initial Lead)</option>
                                                                    <option value="site_feasibility" <?= $req['status'] === 'site_feasibility' ? 'selected' : '' ?>>Site Feasibility Survey</option>
                                                                    <option value="subsidy_approved" <?= $req['status'] === 'subsidy_approved' ? 'selected' : '' ?>>PM Surya Ghar Subsidy Pre-Approved</option>
                                                                    <option value="discom_sanctioned" <?= $req['status'] === 'discom_sanctioned' ? 'selected' : '' ?>>PVVNL Net-Metering Sanctioned</option>
                                                                    <option value="installed" <?= $req['status'] === 'installed' ? 'selected' : '' ?>>System Installed & Grid Synchronized</option>
                                                                    <option value="rejected" <?= $req['status'] === 'rejected' ? 'selected' : '' ?>>Rejected / Infeasible</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">PVVNL Net-Metering Sanction Ref</label>
                                                                <input type="text" name="sanction_ref" class="form-control" value="<?= htmlspecialchars($req['sanction_ref'] ?? '') ?>" placeholder="e.g. PVVNL/SOLAR/2026/8941">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Solar EPC Partner Name</label>
                                                                <input type="text" name="installer_partner" class="form-control" value="<?= htmlspecialchars($req['installer_partner'] ?? '') ?>" placeholder="e.g. Tata Power Solar / Waree Energies">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Installer Contact Number</label>
                                                                <input type="text" name="installer_contact" class="form-control" value="<?= htmlspecialchars($req['installer_contact'] ?? '') ?>" placeholder="10-digit mobile number">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Admin Notes</label>
                                                                <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($req['notes'] ?? '') ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
