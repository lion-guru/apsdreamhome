<?php
/** @var array $rules */
/** @var array $colonies */
/** @var int $selected_colony */
$rules = $rules ?? [];
$colonies = $colonies ?? [];
$selected_colony = $selected_colony ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-tags me-2"></i>PLC Rules Management</h4>
        <p class="text-muted mb-0">Configure Preferential Location Charges for plots</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPlcModal">
        <i class="fas fa-plus me-1"></i> Add PLC Rule
    </button>
</div>

<!-- Filters -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label small">Filter by Colony</label>
        <select class="form-select form-select-sm" id="colonyFilter" onchange="filterByColony(this.value)">
            <option value="0">All Colonies</option>
            <?php foreach ($colonies as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $selected_colony == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small">Status</label>
        <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus(this.value)">
            <option value="">All Status</option>
            <option value="1" <?= ($statusFilter ?? '') === '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= ($statusFilter ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
</div>

<!-- PLC Rules Table -->
<div class="aps-cp-card">
    <div class="aps-cp-card-body p-0">
        <?php if (empty($rules)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No PLC rules configured</h6>
                <p class="text-muted small">Click "Add PLC Rule" to create your first pricing rule</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Colony</th>
                        <th>Attribute</th>
                        <th>Condition</th>
                        <th>Charge Type</th>
                        <th>Value</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td><?= (int)$rule['id'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($rule['name']) ?></td>
                            <td><?= htmlspecialchars($rule['colony_name'] ?? 'Global') ?></td>
                            <td><code class="small"><?= htmlspecialchars($rule['attribute_key']) ?></code></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($rule['condition_type']) ?>
                                    <?= htmlspecialchars($rule['condition_value']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($rule['charge_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($rule['charge_type'] === 'percentage'): ?>
                                    <span class="text-primary"><?= number_format((float)$rule['charge_value'], 2) ?>%</span>
                                <?php elseif ($rule['charge_type'] === 'fixed_per_sqft'): ?>
                                    <span class="text-success">₹<?= number_format((float)$rule['charge_value'], 2) ?>/sqft</span>
                                <?php else: ?>
                                    <span class="text-warning">₹<?= number_format((float)$rule['charge_value'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int)$rule['priority'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $rule['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $rule['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-sm" title="Edit"
                                            onclick="editRule(<?= (int)$rule['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmDelete(<?= (int)$rule['id'] ?>, '<?= htmlspecialchars($rule['name'], ENT_QUOTES) ?>')"
                                            title="Deactivate">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Create Modal -->
<div class="modal fade" id="createPlcModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/plots/pricing-rules/create">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create PLC Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Colony</label>
                            <select name="colony_id" class="form-select form-select-sm">
                                <option value="0">Global (All Colonies)</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required 
                                   placeholder="e.g., Corner Plot Premium">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Attribute Key <span class="text-danger">*</span></label>
                            <select name="attribute_key" class="form-select form-select-sm" required>
                                <option value="corner_plot">Corner Plot (plots.corner_plot)</option>
                                <option value="park_facing">Park Facing (plots.park_facing)</option>
                                <option value="road_width_ft">Road Width (road_width_ft)</option>
                                <option value="facing">Facing Direction</option>
                                <option value="is_corner">Corner Plot (legacy alias)</option>
                                <option value="is_park_facing">Park Facing (legacy alias)</option>
                                <option value="is_commercial_front">Commercial Front (needs plot attribute)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Condition Type <span class="text-danger">*</span></label>
                            <select name="condition_type" class="form-select form-select-sm" required>
                                <option value="equals_true">Equals True (Boolean)</option>
                                <option value="equals">Equals Value</option>
                                <option value="greater_than">Greater Than (Number)</option>
                                <option value="contains">Contains Text</option>
                                <option value="in">In List (Comma Separated)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Condition Value <span class="text-danger">*</span></label>
                            <input type="text" name="condition_value" class="form-control form-control-sm" required 
                                   placeholder="e.g., 1, 40, park, east">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Charge Type <span class="text-danger">*</span></label>
                            <select name="charge_type" class="form-select form-select-sm" required>
                                <option value="percentage">Percentage of Base Rate</option>
                                <option value="fixed_per_sqft">Fixed ₹ per Sq Ft</option>
                                <option value="fixed_total">Fixed Total Amount</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Charge Value <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="charge_value" class="form-control form-control-sm" required 
                                   placeholder="e.g., 10 for 10% or 150 for ₹150/sqft">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Priority</label>
                            <input type="number" name="priority" class="form-control form-control-sm" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editPlcModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" id="editPlcForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit PLC Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Colony</label>
                            <select name="colony_id" class="form-select form-select-sm" id="editColony">
                                <option value="0">Global</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" required id="editName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Attribute Key <span class="text-danger">*</span></label>
                            <select name="attribute_key" class="form-select form-select-sm" required id="editAttributeKey">
                                <option value="corner_plot">Corner Plot</option>
                                <option value="park_facing">Park Facing</option>
                                <option value="road_width_ft">Road Width</option>
                                <option value="facing">Facing Direction</option>
                                <option value="is_corner">Corner (legacy alias)</option>
                                <option value="is_park_facing">Park Facing (legacy alias)</option>
                                <option value="is_commercial_front">Commercial Front</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Condition Type</label>
                            <select name="condition_type" class="form-select form-select-sm" required id="editConditionType">
                                <option value="equals_true">Equals True</option>
                                <option value="equals">Equals Value</option>
                                <option value="greater_than">Greater Than</option>
                                <option value="contains">Contains</option>
                                <option value="in">In List</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Condition Value</label>
                            <input type="text" name="condition_value" class="form-control form-control-sm" id="editConditionValue">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Charge Type</label>
                            <select name="charge_type" class="form-select form-select-sm" required id="editChargeType">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_per_sqft">Fixed ₹/sqft</option>
                                <option value="fixed_total">Fixed Total</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Charge Value</label>
                            <input type="number" step="0.01" name="charge_value" class="form-control form-control-sm" required id="editChargeValue">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Priority</label>
                            <input type="number" name="priority" class="form-control form-control-sm" value="0" min="0" id="editPriority">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">Active</label>
                            </div>
                        </div>
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

<script>
const base = <?= json_encode(BASE_URL) ?>;
let currentEditId = null;

function filterByColony(colonyId) {
    const url = new URL(window.location);
    url.searchParams.set('colony_id', colonyId);
    window.location.href = url.toString();
}

function filterByStatus(status) {
    const url = new URL(window.location);
    if (status === '') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    window.location.href = url.toString();
}

function confirmDelete(id, name) {
    if (confirm(`Deactivate "${name}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $base ?>/admin/plots/pricing-rules/' + id + '/delete';
        form.innerHTML = '<input type="hidden" name="csrf_token" value="' + '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>' + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function editRule(id) {
    currentEditId = id;
    fetch('<?= $base ?>/admin/plots/pricing-rules/' + id + '/edit', {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editPlcForm').action = '<?= $base ?>/admin/plots/pricing-rules/' + id + '/update';
                document.getElementById('editId').value = data.id;
                document.getElementById('editName').value = data.name || '';
                document.getElementById('editColony').value = data.colony_id || '0';
                document.getElementById('editAttributeKey').value = data.attribute_key || '';
                document.getElementById('editConditionType').value = data.condition_type || '';
                document.getElementById('editConditionValue').value = data.condition_value || '';
                document.getElementById('editChargeType').value = data.charge_type || '';
                document.getElementById('editChargeValue').value = data.charge_value || '';
                document.getElementById('editPriority').value = data.priority || 0;
                document.getElementById('editIsActive').checked = data.is_active == 1;

                const modal = new bootstrap.Modal(document.getElementById('editPlcModal'));
                modal.show();
            } else {
                alert(data.error || 'Failed to load rule');
            }
        })
        .catch(() => alert('Failed to load rule'));
}

document.getElementById('createPlcModal')?.addEventListener('hidden.bs.modal', function() {
    this.querySelector('form').reset();
});
</script>