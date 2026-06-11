<?php
$colonies = $colonies ?? [];
$stats = $stats ?? ['available' => 0, 'booked' => 0, 'sold' => 0, 'hold' => 0, 'total' => 0];
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-eye me-2"></i>Plot Availability Dashboard</h2>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshAvailability()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <a href="/admin/plots" class="btn btn-outline-secondary">
                        <i class="fas fa-th"></i> All Plots
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3 bg-light">
                        <div class="fs-3 fw-bold text-dark"><?= $stats['total'] ?></div>
                        <div class="small text-muted">Total Plots</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3" style="background:#d4edda">
                        <div class="fs-3 fw-bold text-success"><?= $stats['available'] ?></div>
                        <div class="small text-success">Available</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3" style="background:#fff3cd">
                        <div class="fs-3 fw-bold text-warning"><?= $stats['booked'] ?></div>
                        <div class="small text-warning">Booked</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3 bg-danger bg-opacity-10">
                        <div class="fs-3 fw-bold text-danger"><?= $stats['sold'] ?></div>
                        <div class="small text-danger">Sold</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3" style="background:#ffeeba">
                        <div class="fs-3 fw-bold" style="color:#e67e22"><?= $stats['hold'] ?></div>
                        <div class="small" style="color:#e67e22">Hold</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-2">
                    <div class="card border-0 shadow-sm text-center p-3 bg-info bg-opacity-10">
                        <div class="fs-3 fw-bold text-info" id="otherCount"><?= $stats['total'] - $stats['available'] - $stats['booked'] - $stats['sold'] - $stats['hold'] ?></div>
                        <div class="small text-info">Other</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="form-label fw-bold">Colony / Site</label>
                            <select id="colonyFilter" class="form-select" onchange="filterByColony(this.value)">
                                <option value="">-- All Colonies --</option>
                                <?php foreach ($colonies as $col): ?>
                                    <option value="<?= $col['id'] ?>"><?= htmlspecialchars($col['name'] ?? $col['site_name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold">Status Filter</label>
                            <select id="statusFilter" class="form-select" onchange="filterByStatus(this.value)">
                                <option value="">-- All Status --</option>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                                <option value="sold">Sold</option>
                                <option value="hold">Hold</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label fw-bold">Search</label>
                            <input type="text" id="searchInput" class="form-control" placeholder="Plot # or block..." onkeyup="filterTable()">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showCorner" onchange="filterTable()">
                                <label class="form-check-label">Corner Plots</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mb-3">
                <span class="badge bg-success me-2 p-2">Available</span>
                <span class="badge bg-warning text-dark me-2 p-2">Booked</span>
                <span class="badge bg-danger me-2 p-2">Sold</span>
                <span class="badge" style="background:#e67e22;color:#fff;margin-right:8px;padding:6px 10px">Hold</span>
                <span class="badge bg-secondary me-2 p-2">Other</span>
            </div>

            <!-- Plots Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0" id="plotsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Plot No.</th>
                                    <th>Block</th>
                                    <th>Dimensions</th>
                                    <th>Area (sqft)</th>
                                    <th>Price / sqft</th>
                                    <th>Total Price</th>
                                    <th>Facing</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($plots)): ?>
                                    <?php foreach ($plots as $p): ?>
                                        <?php
                                        $statusClass = match($p['status'] ?? 'available') {
                                            'available' => 'success',
                                            'booked' => 'warning',
                                            'sold' => 'danger',
                                            'hold' => 'secondary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <tr class="plot-row" data-status="<?= htmlspecialchars($p['status'] ?? '') ?>" data-colony="<?= $p['colony_id'] ?? 0 ?>" data-corner="<?= !empty($p['corner_plot']) ? '1' : '0' ?>">
                                            <td><?= $p['id'] ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($p['plot_number'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($p['block'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($p['dimension_label'] ?? '') ?: number_format($p['width_ft'] ?? 0) . 'x' . number_format($p['length_ft'] ?? 0) ?></td>
                                            <td class="text-end"><?= number_format($p['area_sqft'] ?? 0) ?></td>
                                            <td class="text-end">₹<?= number_format(floatval($p['price_per_sqft'] ?? 0), 2) ?></td>
                                            <td class="text-end fw-bold">₹<?= number_format(intval($p['total_price'] ?? 0)) ?></td>
                                            <td><?= htmlspecialchars(ucfirst($p['facing'] ?? '')) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?> p-2 w-100 status-badge"
                                                      data-plot-id="<?= $p['id'] ?>"
                                                      data-status="<?= htmlspecialchars($p['status'] ?? 'available') ?>"
                                                      style="cursor:pointer"
                                                      onclick="quickStatusChange(this)">
                                                    <?= ucfirst(htmlspecialchars($p['status'] ?? 'available')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/admin/plots/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                                                <a href="/admin/plots/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <?php if (($p['status'] ?? '') === 'available'): ?>
                                                    <a href="/admin/plots/<?= $p['id'] ?>/book" class="btn btn-sm btn-outline-success" title="Book Now"><i class="fas fa-book"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="10" class="text-center text-muted py-4">No plots found. <a href="/admin/plots/create">Create a plot</a> to get started.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="statusForm">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body">
                    <input type="hidden" name="plot_id" id="statusPlotId">
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="sold">Sold</option>
                            <option value="hold">Hold</option>
                            <option value="reserved">Reserved</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let availabilityData = [];

function filterByColony(colonyId) {
    const rows = document.querySelectorAll('.plot-row');
    const selectedStatus = document.getElementById('statusFilter').value;
    rows.forEach(row => {
        const matchesColony = !colonyId || row.dataset.colony === colonyId;
        const matchesStatus = !selectedStatus || row.dataset.status === selectedStatus;
        const matchesSearch = matchesSearchFilter(row);
        const matchesCorner = matchesCornerFilter(row);
        row.style.display = matchesColony && matchesStatus && matchesSearch && matchesCorner ? '' : 'none';
    });
}

function filterByStatus(status) {
    filterByColony(document.getElementById('colonyFilter').value);
}

function filterTable() {
    filterByColony(document.getElementById('colonyFilter').value);
}

function matchesSearchFilter(row) {
    const q = document.getElementById('searchInput').value.toLowerCase();
    if (!q) return true;
    const plotNo = (row.cells[1]?.textContent || '').toLowerCase();
    const block = (row.cells[2]?.textContent || '').toLowerCase();
    return plotNo.includes(q) || block.includes(q);
}

function matchesCornerFilter(row) {
    const cb = document.getElementById('showCorner');
    if (!cb.checked) return true;
    return row.dataset.corner === '1';
}

function quickStatusChange(badge) {
    const plotId = badge.dataset.plotId;
    const currentStatus = badge.dataset.status;
    document.getElementById('statusPlotId').value = plotId;
    document.getElementById('statusForm').querySelector('select[name="status"]').value = currentStatus;
    document.getElementById('statusForm').action = '/admin/plots/' + plotId + '/update-status';
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function refreshAvailability() {
    location.reload();
}
</script>
