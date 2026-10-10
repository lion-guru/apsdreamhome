<?php
/** @var array $farmers */
/** @var array $filters */
/** @var array $districts */
/** @var array $summary */
$farmers  = $farmers ?? [];
$filters  = $filters ?? [];
$districts = $districts ?? [];
$summary  = $summary ?? [];
$base     = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-tractor me-2"></i>Farmer Land Bank Acquisition Ledger</h4>
        <p class="text-muted mb-0">Complete overview of all land owners, their holdings, advances & balances</p>
    </div>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Print Ledger
    </button>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-success fw-bold fs-3"><?= number_format($summary['total_farmers'] ?? 0) ?></div>
                <div class="text-muted small">Total Farmers / Land Owners</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-primary fw-bold fs-3"><?= number_format($summary['total_deals'] ?? 0) ?></div>
                <div class="text-muted small">Closed Deals</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-success fw-bold fs-3">₹<?= number_format($summary['total_advance'] ?? 0, 0) ?></div>
                <div class="text-muted small">Total Advance Paid</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="aps-cp-card text-center">
            <div class="aps-cp-card-body py-3">
                <div class="text-warning fw-bold fs-3">₹<?= number_format($summary['total_balance'] ?? 0, 0) ?></div>
                <div class="text-muted small">Outstanding Balance</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header">
        <span><i class="fas fa-filter me-2"></i>Filters</span>
    </div>
    <div class="aps-cp-card-body">
        <form method="GET" class="row g-3" id="farmerFilterForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Farmer name, phone, village, survey no..." 
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">District</label>
                <select name="district" class="form-select form-select-sm">
                    <option value="">All Districts</option>
                    <?php foreach ($districts as $d): ?>
                        <option value="<?= htmlspecialchars($d['district']) ?>" <?= ($filters['district'] ?? '') === $d['district'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['district']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Deal Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="registered" <?= ($filters['status'] ?? '') === 'registered' ? 'selected' : '' ?>>Registered</option>
                    <option value="mutated" <?= ($filters['status'] ?? '') === 'mutated' ? 'selected' : '' ?>>Mutated</option>
                    <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 btn-sm">
                    <i class="fas fa-search me-1"></i> Apply
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="<?= $base ?>/admin/land-inventory/farmer-ledger" class="btn btn-outline-secondary w-100 btn-sm">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-dark w-100 btn-sm" onclick="exportFarmerLedger()">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Farmer Ledger Table -->
<div class="aps-cp-card">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i>Farmer Acquisition Ledger</span>
        <span class="badge bg-primary"><?= count($farmers) ?> Records</span>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($farmers)): ?>
        <div class="text-center py-5">
            <i class="fas fa-tractor fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No farmer records found</h5>
            <p class="text-muted">Create land leads to start building the acquisition ledger</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="farmerLedgerTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Farmer / Land Owner</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Land Details</th>
                        <th>Leads</th>
                        <th>Deals</th>
                        <th class="text-end">Deal Value</th>
                        <th class="text-end">Advance Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Latest Milestone</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($farmers as $index => $farmer): 
                        $dealStatuses = explode(',', $farmer['deal_statuses'] ?? '');
                        $leadStatuses = explode(',', $farmer['lead_statuses'] ?? '');
                        $hasRegistered = in_array('registered', $dealStatuses) || in_array('mutated', $dealStatuses) || in_array('closed', $dealStatuses);
                        $hasInProgress = in_array('in_progress', $dealStatuses) || in_array('legal', $leadStatuses) || in_array('negotiation', $leadStatuses);
                    ?>
                    <tr class="<?= $hasRegistered ? 'table-success' : ($hasInProgress ? 'table-warning' : '') ?>">
                        <td><?= $index + 1 ?></td>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($farmer['farmer_name'] ?? '—') ?></div>
                            <?php if (!empty($farmer['survey_number'])): ?>
                                <small class="text-muted">Survey: <?= htmlspecialchars($farmer['survey_number']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($farmer['farmer_phone'])): ?>
                                <div class="small"><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($farmer['farmer_phone']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($farmer['farmer_email'])): ?>
                                <div class="small"><i class="fas fa-envelope text-muted me-1"></i><?= htmlspecialchars($farmer['farmer_email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small fw-medium"><?= htmlspecialchars($farmer['village'] ?? '—') ?></div>
                            <div class="small text-muted">
                                <?= htmlspecialchars(implode(', ', array_filter([$farmer['tehsil'] ?? '', $farmer['district'] ?? '', $farmer['state'] ?? '']))) ?>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <strong><?= number_format($farmer['total_area_acres'] ?? 0, 2) ?></strong> acres
                                <?php if (($farmer['total_area_sqft'] ?? 0) > 0): ?>
                                    <span class="text-muted"> / </span>
                                    <strong><?= number_format($farmer['total_area_sqft'] ?? 0, 0) ?></strong> sqft
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $farmer['total_leads'] ?? 0 ?></span>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?= $farmer['total_deals'] ?? 0 ?></span>
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold text-success">₹<?= number_format($farmer['total_deal_value'] ?? 0, 0) ?></div>
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold text-primary">₹<?= number_format($farmer['total_advance_paid'] ?? 0, 0) ?></div>
                        </td>
                        <td class="text-end">
                            <div class="fw-semibold text-<?= (($farmer['total_balance'] ?? 0) > 0) ? 'danger' : 'success' ?>">₹<?= number_format($farmer['total_balance'] ?? 0, 0) ?></div>
                        </td>
                        <td>
                            <?php if (!empty($farmer['latest_registration_date'])): ?>
                                <div class="small text-success">
                                    <i class="fas fa-file-signature me-1"></i>Registered: <?= date('d M Y', strtotime($farmer['latest_registration_date'])) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($farmer['latest_mutation_date'])): ?>
                                <div class="small text-primary">
                                    <i class="fas fa-landmark me-1"></i>Mutated: <?= date('d M Y', strtotime($farmer['latest_mutation_date'])) ?>
                                </div>
                            <?php elseif (!empty($farmer['latest_agreement_date'])): ?>
                                <div class="small text-warning">
                                    <i class="fas fa-file-contract me-1"></i>Agreement: <?= date('d M Y', strtotime($farmer['latest_agreement_date'])) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="<?= $base ?>/admin/land-inventory/farmer-ledger/detail/<?= urlencode($farmer['farmer_name']) ?>/<?= urlencode($farmer['village'] ?? '') ?>" 
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (($farmer['total_deals'] ?? 0) > 0): ?>
                                <a href="<?= $base ?>/admin/land-inventory/acquisitions?search=<?= urlencode($farmer['farmer_name']) ?>" 
                                   class="btn btn-sm btn-outline-success" title="View Deals" target="_blank">
                                    <i class="fas fa-handshake"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .aps-cp-card-header .badge,
    .btn,
    form,
    .table th:last-child,
    .table td:last-child {
        display: none !important;
    }
    .aps-cp-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .table { font-size: 10px; }
}
</style>

<script>
function exportFarmerLedger() {
    const table = document.getElementById('farmerLedgerTable');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = [];
        cols.forEach((col, i) => {
            // Skip last column (actions) in export
            if (i < cols.length - 1) {
                rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
            }
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'farmer-land-bank-ledger-' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}
</script>