<?php
/**
 * Admin Township Infrastructure Grievance & Society AMC Helpdesk View
 */
$grievances = $grievances ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$current_colony = $current_colony ?? null;
$current_category = $current_category ?? 'all';
$current_status = $current_status ?? 'all';
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-tools text-primary me-2"></i>Township Infrastructure &amp; Society AMC Helpdesk</h3>
            <p class="text-muted small mb-0">Track resident complaints, water/drainage faults, civil road repairs &amp; AMC vendor work-orders</p>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Tickets</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= (int)($stats['total'] ?? 0) ?></div>
                <div class="text-muted small mt-1">All complaints</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Open Tickets</div>
                <div class="fs-4 fw-bold text-warning mt-1"><?= (int)($stats['open'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Awaiting dispatch</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">In Progress</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= (int)($stats['in_progress'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Contractor assigned</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Resolved / Closed</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= (int)($stats['resolved'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Completed jobs</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">SLA Breached</div>
                <div class="fs-4 fw-bold text-danger mt-1"><?= (int)($stats['sla_breached'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Past deadline</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning-subtle border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Resident Rating</div>
                <div class="fs-4 fw-bold text-dark mt-1">
                    <?= (float)($stats['avg_rating'] ?? 0) ?> <i class="fas fa-star text-warning fs-6"></i>
                </div>
                <div class="text-muted small mt-1">Satisfaction index</div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="/admin/township-grievances" class="row g-2 align-items-center">
                <div class="col-md-3">
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
                    <label for="filter_cat" class="visually-hidden">Category</label>
                    <select name="category" id="filter_cat" class="form-select form-select-sm">
                        <option value="all" <?= ($current_category === 'all') ? 'selected' : '' ?>>All Categories</option>
                        <option value="water_supply" <?= ($current_category === 'water_supply') ? 'selected' : '' ?>>Water Supply &amp; Borewell</option>
                        <option value="drainage_sanitation" <?= ($current_category === 'drainage_sanitation') ? 'selected' : '' ?>>Drainage &amp; STP</option>
                        <option value="street_lighting" <?= ($current_category === 'street_lighting') ? 'selected' : '' ?>>Street Lighting &amp; Power</option>
                        <option value="road_civil_work" <?= ($current_category === 'road_civil_work') ? 'selected' : '' ?>>Road &amp; Civil Pavers</option>
                        <option value="park_horticulture" <?= ($current_category === 'park_horticulture') ? 'selected' : '' ?>>Parks &amp; Plantation</option>
                        <option value="security_gate" <?= ($current_category === 'security_gate') ? 'selected' : '' ?>>Security &amp; Boom Barrier</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_status" class="visually-hidden">Status</label>
                    <select name="status" id="filter_status" class="form-select form-select-sm">
                        <option value="all" <?= ($current_status === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="open" <?= ($current_status === 'open') ? 'selected' : '' ?>>Open</option>
                        <option value="in_progress" <?= ($current_status === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= ($current_status === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                        <option value="closed" <?= ($current_status === 'closed') ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
                <?php if ($current_colony || $current_category !== 'all' || $current_status !== 'all'): ?>
                    <div class="col-md-1">
                        <a href="/admin/township-grievances" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Grievances Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Ticket</th>
                            <th>Colony &amp; Plot</th>
                            <th>Resident</th>
                            <th>Issue Details</th>
                            <th>Priority &amp; SLA</th>
                            <th>Contractor / Tech</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grievances)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-double fs-1 d-block mb-2 text-success opacity-50"></i>
                                    All township maintenance tickets are resolved. Zero pending grievances.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grievances as $row): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold font-monospace text-dark"><?= htmlspecialchars($row['ticket_token']) ?></div>
                                        <div class="small text-muted"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['colony_name'] ?? 'APS Township') ?></div>
                                        <?php if (!empty($row['plot_number'])): ?>
                                            <div class="small text-muted">Plot #<?= htmlspecialchars($row['plot_number']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['resident_name']) ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1 text-secondary"></i><?= htmlspecialchars($row['resident_phone']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-capitalize mb-1">
                                            <?= str_replace('_', ' ', htmlspecialchars($row['category'])) ?>
                                        </span>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['description']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['priority'] === 'emergency'): ?>
                                            <span class="badge bg-danger">Emergency</span>
                                        <?php elseif ($row['priority'] === 'high'): ?>
                                            <span class="badge bg-warning text-dark">High</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Medium</span>
                                        <?php endif; ?>
                                        <div class="small text-muted mt-1">
                                            SLA: <?= !empty($row['sla_deadline']) ? date('d M H:i', strtotime($row['sla_deadline'])) : 'N/A' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['assigned_contractor_name'])): ?>
                                            <div class="fw-semibold text-primary"><?= htmlspecialchars($row['assigned_contractor_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($row['assigned_contact']) ?></div>
                                            <div class="small text-success">Est: ₹<?= number_format((float)$row['estimated_cost'], 2) ?></div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'closed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Closed</span>
                                            <?php if (!empty($row['resident_rating'])): ?>
                                                <div class="small text-warning mt-1"><?= str_repeat('★', (int)$row['resident_rating']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($row['status'] === 'resolved'): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-clipboard-check me-1"></i>Resolved</span>
                                        <?php elseif ($row['status'] === 'in_progress'): ?>
                                            <span class="badge bg-primary"><i class="fas fa-wrench me-1"></i>In Progress</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Open</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($row['status'] === 'open' || empty($row['assigned_contractor_name'])): ?>
                                                <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#assignModal<?= $row['id'] ?>">
                                                    <i class="fas fa-user-plus me-1"></i>Assign AMC
                                                </button>
                                            <?php endif; ?>

                                            <?php if (in_array($row['status'], ['open', 'in_progress'], true)): ?>
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $row['id'] ?>">
                                                    <i class="fas fa-check me-1"></i>Resolve
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Assign Modal -->
                                        <div class="modal fade" id="assignModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/township-grievances/assign/<?= $row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Assign AMC Vendor / Tech — <?= htmlspecialchars($row['ticket_token']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="contractor_<?= $row['id'] ?>" class="form-label">Vendor / Technician Name <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="contractor_<?= $row['id'] ?>" name="assigned_contractor_name" placeholder="e.g. Ramesh Kumar Plumber" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="contact_<?= $row['id'] ?>" class="form-label">Contractor Contact Phone <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="contact_<?= $row['id'] ?>" name="assigned_contact" placeholder="e.g. 9876501234" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cost_<?= $row['id'] ?>" class="form-label">Estimated Repair Cost (₹)</label>
                                                                <input type="number" step="0.01" class="form-control" id="cost_<?= $row['id'] ?>" name="estimated_cost" value="0.00">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-info text-white">Assign &amp; Dispatch</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Resolve Modal -->
                                        <div class="modal fade" id="resolveModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="/admin/township-grievances/resolve/<?= $row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Resolve Ticket — <?= htmlspecialchars($row['ticket_token']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="notes_<?= $row['id'] ?>" class="form-label">Site Engineering Resolution Notes <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="notes_<?= $row['id'] ?>" name="resolution_notes" rows="3" placeholder="Replaced faulty valve on Sector-B main pipeline; water supply restored." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Mark as Resolved</button>
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
