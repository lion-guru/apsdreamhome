<?php
/**
 * Employee Asset Management View
*/

$page_title = $page_title ?? 'Employee Asset Management';
$assets = $assets ?? [];
$employees = $employees ?? [];
$employee_id = $employee_id ?? 0;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-laptop me-2"></i>Employee Asset Management</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignAssetModal">
            <i class="fas fa-plus me-1"></i> Assign Asset
        </button>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= count($assets) ?></h3>
                    <small>Total Assets Assigned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <?php $returned = array_filter($assets, fn($a) => $a['returned_at']); ?>
                    <h3><?= count($returned) ?></h3>
                    <small>Returned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <?php $pending = array_filter($assets, fn($a) => !$a['returned_at']); ?>
                    <h3><?= count($pending) ?></h3>
                    <small>Pending Return</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <?php $totalValue = array_sum(array_column($assets, 'current_value')); ?>
                    <h3>₹<?= number_format($totalValue, 0) ?></h3>
                    <small>Total Current Value</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $employee_id == $emp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['name']) ?> (<?= $emp['employee_code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Assets Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($assets)): ?>
            <div class="text-center py-5">
                <i class="fas fa-laptop fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Assets Assigned</h5>
                <p class="text-muted">Assign assets to employees using the button above</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset</th>
                            <th>Category</th>
                            <th>Serial No.</th>
                            <th>Employee</th>
                            <th>Assigned</th>
                            <th class="text-end">Purchase Value</th>
                            <th class="text-end">Current Value</th>
                            <th class="text-center">Status</th>
                            <th>Returned</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $a): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($a['asset_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($a['asset_category']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($a['serial_number'] ?? '-') ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['employee_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($a['employee_code']) ?></small>
                            </td>
                            <td><?= $a['assigned_date'] ? date('d M Y', strtotime($a['assigned_date'])) : '-' ?></td>
                            <td class="text-end">₹<?= number_format($a['purchase_value'] ?? 0, 2) ?></td>
                            <td class="text-end">₹<?= number_format($a['current_value'] ?? 0, 2) ?></td>
                            <td class="text-center">
                                <?php if ($a['returned_at']): ?>
                                <span class="badge bg-success">Returned</span>
                                <?php else: ?>
                                <span class="badge bg-warning text-dark">Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $a['returned_at'] ? date('d M Y', strtotime($a['returned_at'])) : '-' ?></td>
                            <td class="text-center">
                                <?php if (!$a['returned_at']): ?>
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                    onclick="openReturnModal(<?= htmlspecialchars(json_encode($a)) ?>)" title="Mark Returned">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Assign Asset Modal -->
<div class="modal fade" id="assignAssetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Assign Asset to Employee</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/fnf/assets">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="employee_id" id="modal_employee_id" value="<?= $employee_id ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= $employee_id == $emp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['name']) ?> (<?= $emp['employee_code'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Asset Category <span class="text-danger">*</span></label>
                            <select name="asset_category" class="form-select" required>
                                <option value="laptop">Laptop</option>
                                <option value="mobile">Mobile Phone</option>
                                <option value="sim">SIM Card</option>
                                <option value="vehicle">Vehicle</option>
                                <option value="id_card">ID Card</option>
                                <option value="access_card">Access Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="asset_name" class="form-control" required placeholder="e.g., MacBook Pro 16\" M2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="ABC123456">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purchase Value (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_value" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Value (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="current_value" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned Date <span class="text-danger">*</span></label>
                            <input type="date" name="assigned_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Asset Modal -->
<div class="modal fade" id="returnAssetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Mark Asset as Returned</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/fnf/assets">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="asset_id" id="return_asset_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Condition on Return <span class="text-danger">*</span></label>
                            <select name="condition" class="form-select" required>
                                <option value="good">Good</option>
                                <option value="damaged">Damaged</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Notes on condition, damages, missing accessories..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Mark Returned</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
</style>

<script>
function openReturnModal(asset) {
    document.getElementById('return_asset_id').value = asset.id;
    new bootstrap.Modal(document.getElementById('returnAssetModal')).show();
}
</script>