<?php
/**
 * Admin Township Construction Permits & Architectural Map NOC Desk View
 */
$approvals = $approvals ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$current_status = $current_status ?? 'all';
$current_colony = $current_colony ?? null;
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-drafting-compass text-primary me-2"></i>Construction Permits & Architectural Map NOC Desk</h3>
            <p class="text-muted small mb-0">Township building bylaws, setback verification & foundation excavation clearances (Gorakhpur Town Planning)</p>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Applications</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= (int)($stats['total'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Township plot construction requests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Pending Review</div>
                <div class="fs-3 fw-bold text-warning mt-1"><?= (int)($stats['submitted'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Awaiting architectural setback audit</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Site Inspected</div>
                <div class="fs-3 fw-bold text-info mt-1"><?= (int)($stats['inspected'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Physical boundary & road pegs checked</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Sanctioned Area</div>
                <div class="fs-3 fw-bold text-success mt-1"><?= number_format((float)($stats['total_sqft'] ?? 0), 0) ?> <span class="fs-6 text-muted">sq.ft</span></div>
                <div class="text-muted small mt-1"><?= (int)($stats['approved'] ?? 0) ?> building permits granted</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="/admin/construction-approvals" class="row g-2 align-items-center">
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
                        <option value="submitted" <?= ($current_status === 'submitted') ? 'selected' : '' ?>>Submitted (New)</option>
                        <option value="site_inspected" <?= ($current_status === 'site_inspected') ? 'selected' : '' ?>>Site Inspected</option>
                        <option value="approved" <?= ($current_status === 'approved') ? 'selected' : '' ?>>Approved (NOC Issued)</option>
                        <option value="rejected" <?= ($current_status === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
                <?php if ($current_colony || $current_status !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="/admin/construction-approvals" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Permit Token</th>
                            <th>Plot & Colony</th>
                            <th>Buyer Details</th>
                            <th>Building Specs</th>
                            <th>Setbacks (F / R / L / R)</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approvals)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-check fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    No construction permit applications found matching criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approvals as $row): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark font-monospace"><?= htmlspecialchars($row['permit_token']) ?></div>
                                        <div class="small text-muted"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Plot #<?= htmlspecialchars($row['plot_number']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['colony_name'] ?? 'APS Township') ?> (<?= number_format((float)$row['area_sqft'], 0) ?> sq.ft)</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['customer_name']) ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($row['customer_phone']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-capitalize mb-1">
                                            <?= str_replace('_', ' ', htmlspecialchars($row['building_type'])) ?>
                                        </span>
                                        <div class="small text-muted">Built-up: <?= number_format((float)$row['total_builtup_sqft'], 0) ?> sq.ft</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border">F: <?= (float)$row['front_setback_ft'] ?>'</span>
                                        <span class="badge bg-secondary-subtle text-secondary border">R: <?= (float)$row['rear_setback_ft'] ?>'</span>
                                        <div class="small text-muted mt-1">
                                            Sides: <?= (float)$row['side_setback_left_ft'] ?>' / <?= (float)$row['side_setback_right_ft'] ?>'
                                            <?= (!empty($row['rainwater_harvesting_pit'])) ? '<i class="fas fa-tint text-info ms-1" title="RWH Pit Sanctioned"></i>' : '' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'approved'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Approved</span>
                                        <?php elseif ($row['status'] === 'site_inspected'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-user-check me-1"></i>Inspected</span>
                                        <?php elseif ($row['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($row['status'] === 'submitted'): ?>
                                                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#inspectModal<?= $row['id'] ?>">
                                                    <i class="fas fa-ruler-combined me-1"></i>Inspect
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($row['status'] !== 'approved'): ?>
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $row['id'] ?>">
                                                    <i class="fas fa-stamp me-1"></i>Approve
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $row['id'] ?>">
                                                    <i class="fas fa-ban me-1"></i>Reject
                                                </button>
                                            <?php else: ?>
                                                <a href="/admin/construction-approvals/permit/<?= $row['id'] ?>" target="_blank" class="btn btn-outline-primary">
                                                    <i class="fas fa-print me-1"></i>Permit NOC
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Inspect Modal -->
                                        <div class="modal fade" id="inspectModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/construction-approvals/inspect/<?= $row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Site Setback Inspection — Plot #<?= htmlspecialchars($row['plot_number']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="engineer_<?= $row['id'] ?>" class="form-label">Inspecting Site Engineer</label>
                                                                <input type="text" class="form-control" id="engineer_<?= $row['id'] ?>" name="site_engineer_name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'APS Town Planning Engineer') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="insp_date_<?= $row['id'] ?>" class="form-label">Inspection Date</label>
                                                                <input type="date" class="form-control" id="insp_date_<?= $row['id'] ?>" name="inspection_date" value="<?= date('Y-m-d') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="notes_<?= $row['id'] ?>" class="form-label">Physical Inspection Notes & Boundary Peg Status</label>
                                                                <textarea class="form-control" id="notes_<?= $row['id'] ?>" name="inspection_notes" rows="3" placeholder="Checked front 10ft setback line against road tar edge; clear drainage slope."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-info text-white">Save Site Inspection</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/construction-approvals/approve/<?= $row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Grant Construction Permit NOC — Plot #<?= htmlspecialchars($row['plot_number']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="text-muted small">Sanctioning foundation excavation and building construction for 1 year under APS Dream Home Township bylaws.</p>
                                                            <div class="mb-3">
                                                                <label for="app_eng_<?= $row['id'] ?>" class="form-label">Sanctioning Officer / Chief Planner</label>
                                                                <input type="text" class="form-control" id="app_eng_<?= $row['id'] ?>" name="site_engineer_name" value="<?= htmlspecialchars($row['site_engineer_name'] ?? 'APS Chief Town Planner') ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="app_notes_<?= $row['id'] ?>" class="form-label">Sanction Conditions / Notes</label>
                                                                <textarea class="form-control" id="app_notes_<?= $row['id'] ?>" name="notes" rows="2" placeholder="Strict compliance with approved architectural drawings and RWH pit installation."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Issue Official Permit NOC</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/construction-approvals/reject/<?= $row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Reject Application — Plot #<?= htmlspecialchars($row['plot_number']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="rej_reason_<?= $row['id'] ?>" class="form-label">Rejection Reason</label>
                                                                <textarea class="form-control" id="rej_reason_<?= $row['id'] ?>" name="rejection_reason" rows="3" required placeholder="Front setback inadequate or unauthorized projection over drainage line."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                        </div>
                                                    </div>
                                                </form>
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
