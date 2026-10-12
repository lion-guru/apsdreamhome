<?php
/**
 * Admin Civil Infrastructure Work Orders Dashboard
 */
$page_title = $page_title ?? 'Civil Infrastructure Work Orders';
$orders = $orders ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$current_colony = $current_colony ?? null;
$current_status = $current_status ?? 'all';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-hard-hat text-warning me-2"></i>Civil Infrastructure Work Orders
            </h3>
            <p class="text-muted small mb-0">Township construction contracts, contractor RA milestone billing & retention fund desk.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                <i class="fas fa-plus-circle me-1"></i>Issue New Work Order
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                        <i class="fas fa-file-contract fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Active Contracts</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['active_orders'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                        <i class="fas fa-rupee-sign fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Contract Sum</span>
                        <h4 class="fw-bold mb-0 text-dark">₹<?= number_format($stats['total_contract_value'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                        <i class="fas fa-check-double fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Disbursed</span>
                        <h4 class="fw-bold mb-0 text-dark">₹<?= number_format($stats['total_paid'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                        <i class="fas fa-shield-alt fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Retention Held (5%)</span>
                        <h4 class="fw-bold mb-0 text-dark">₹<?= number_format($stats['total_retention_held'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" action="/admin/work-orders" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label for="filterColony" class="form-label small fw-semibold text-muted mb-0">Colony / Township</label>
                    <select name="colony_id" id="filterColony" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Colonies</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($current_colony == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterStatus" class="form-label small fw-semibold text-muted mb-0">Contract Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= ($current_status === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="active" <?= ($current_status === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= ($current_status === 'completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($current_status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4 text-end pt-3">
                    <a href="/admin/work-orders" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Colony</th>
                        <th>Work Scope</th>
                        <th>Contractor</th>
                        <th>Contract Sum</th>
                        <th>Billed / Progress</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block text-secondary"></i>
                                No civil work orders found matching your filter.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <?php
                            $amt = (float)$o['contract_amount'];
                            $billed = (float)$o['total_billed'];
                            $pct = ($amt > 0) ? min(100, round(($billed / $amt) * 100)) : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($o['order_number']) ?></span>
                                    <div class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($o['colony_name'] ?? 'Colony #' . $o['colony_id']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($o['work_title']) ?></div>
                                    <span class="badge bg-light text-dark border small text-uppercase"><?= str_replace('_', ' ', htmlspecialchars($o['work_category'])) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($o['contractor_name']) ?></div>
                                    <small class="text-muted"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($o['contractor_phone']) ?></small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">₹<?= number_format($amt, 2) ?></span>
                                    <div class="text-muted small">Retention: <?= $o['retention_pct'] ?>% | TDS: <?= $o['tds_pct'] ?>%</div>
                                </td>
                                <td style="min-width: 150px;">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>₹<?= number_format($billed, 2) ?></span>
                                        <span class="fw-bold"><?= $pct ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($o['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif ($o['status'] === 'completed'): ?>
                                        <span class="badge bg-primary">Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($o['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/work-orders/<?= $o['id'] ?>" class="btn btn-outline-primary" title="View & Manage RA Bills">
                                            <i class="fas fa-folder-open me-1"></i>Manage Bills
                                        </a>
                                        <a href="/admin/work-orders/<?= $o['id'] ?>/print" target="_blank" class="btn btn-outline-secondary" title="Print Work Order Letterhead">
                                            <i class="fas fa-print"></i>
                                        </a>
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

<!-- Modal: New Work Order -->
<div class="modal fade" id="newOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/work-orders/store" class="modal-content">
            <?= \App\Core\Security::csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-contract text-primary me-2"></i>Issue Civil Construction Work Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Colony / Township <span class="text-danger">*</span></label>
                    <select name="colony_id" class="form-select" required>
                        <option value="">Select Colony</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Work Category <span class="text-danger">*</span></label>
                    <select name="work_category" class="form-select" required>
                        <option value="boundary_wall">Boundary Wall & Demarcation</option>
                        <option value="road_tarring">CC Road & Tarring</option>
                        <option value="drainage">Underground Drainage & Sewerage</option>
                        <option value="electrification">Electrification & Transformers</option>
                        <option value="leveling">Earth Leveling & Filling</option>
                        <option value="gate_arch">Grand Entry Gate Arch</option>
                        <option value="landscaping">Parks & Green Belt Landscaping</option>
                        <option value="general">General Civil Works</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Work Title / Specification <span class="text-danger">*</span></label>
                    <input type="text" name="work_title" class="form-control" placeholder="e.g. 1500 Rft RCC Boundary Wall with 6ft Height" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contractor / Firm Name <span class="text-danger">*</span></label>
                    <input type="text" name="contractor_name" class="form-control" placeholder="e.g. Shri Ram Construction Co." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contractor Phone <span class="text-danger">*</span></label>
                    <input type="text" name="contractor_phone" class="form-control" placeholder="e.g. 9876543210" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contractor PAN (for TDS)</label>
                    <input type="text" name="contractor_pan" class="form-control" placeholder="ABCDE1234F">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Contractor GSTIN</label>
                    <input type="text" name="contractor_gstin" class="form-control" placeholder="09AAAAA0000A1Z5">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Total Contract Sum (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="contract_amount" class="form-control" placeholder="1500000" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Retention Holdback (%)</label>
                    <input type="number" step="0.5" name="retention_pct" class="form-control" value="5.0" title="Held back until defect liability period expires">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">TDS Deduction (%)</label>
                    <input type="number" step="0.5" name="tds_pct" class="form-control" value="1.0" title="Sec 194C (1% Indiv / 2% Co)">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Commencement Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Target Completion Deadline</label>
                    <input type="date" name="completion_deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+3 months')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Scope of Work & Milestone Terms</label>
                    <textarea name="scope_description" rows="3" class="form-control" placeholder="Detailed technical specifications, milestone payment stages, material grade (e.g. M20 concrete, Fe550 steel)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Issue Work Order</button>
            </div>
        </form>
    </div>
</div>
