<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-percentage me-2 text-success"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create New Rebate Rule</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="rebateForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="rule_name" class="form-control" required placeholder="e.g., Early Payment Rebate">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rule Code <span class="text-danger">*</span></label>
                        <input type="text" name="rule_code" class="form-control" required placeholder="e.g., early_payment">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rebate Type</label>
                        <select name="rebate_type" class="form-select" id="rebateType">
                            <option value="percent">Percentage</option>
                            <option value="flat_amount">Flat Amount</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rebate Value</label>
                        <input type="number" step="0.01" name="rebate_value" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Rebate Amount</label>
                        <input type="number" step="0.01" name="max_rebate_amount" class="form-control" placeholder="Optional cap">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="effective_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Applicable Tax Types</label>
                        <select name="applicable_tax_types[]" class="form-select" multiple id="taxTypesSelect">
                            <option value="property_tax">Property Tax</option>
                            <option value="water_tax">Water Tax</option>
                            <option value="sewerage_tax">Sewerage Tax</option>
                            <option value="fire_tax">Fire Tax</option>
                            <option value="education_cess">Education Cess</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Conditions (JSON)</label>
                        <textarea name="conditions_json" class="form-control font-monospace" rows="3" placeholder='{"early_payment_days": 30, "senior_citizen": false, "age_above": 60}'>{}</textarea>
                        <div class="form-text">Example: {"early_payment_days": 30} or {"senior_citizen": true, "age_above": 60}</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Create Rule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Rebate Rules</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Max Amount</th>
                            <th>Applicable To</th>
                            <th>Conditions</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rules)): ?>
                            <tr><td colspan="10" class="text-center py-4 text-muted">No rebate rules defined</td></tr>
                        <?php else: ?>
                            <?php foreach ($rules as $r): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($r['rule_code']) ?></code></td>
                                    <td><?= htmlspecialchars($r['rule_name']) ?></td>
                                    <td><span class="badge bg-<?= $r['rebate_type'] === 'percent' ? 'info' : 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $r['rebate_type'])) ?></span></td>
                                    <td><?= $r['rebate_type'] === 'percent' ? $r['rebate_value'] . '%' : '₹' . number_format($r['rebate_value'], 2) ?></td>
                                    <td><?= $r['max_rebate_amount'] ? '₹' . number_format($r['max_rebate_amount'], 2) : '-' ?></td>
                                    <td>
                                        <?php
                                        $types = json_decode($r['applicable_tax_types'], true) ?? [];
                                        echo implode(', ', array_map(fn($t) => ucwords(str_replace('_', ' ', $t)), $types)) ?: '-';
                                        ?>
                                    </td>
                                    <td>
                                        <code class="small"><?= htmlspecialchars(json_encode(json_decode($r['conditions_json'], true) ?? [])) ?></code>
                                    </td>
                                    <td class="small"><?= date('d M Y', strtotime($r['effective_from'])) ?> - <?= $r['effective_to'] ? date('d M Y', strtotime($r['effective_to'])) : 'Ongoing' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary editRule" data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['rule_name']) ?>" data-code="<?= htmlspecialchars($r['rule_code']) ?>" data-type="<?= $r['rebate_type'] ?>" data-value="<?= $r['rebate_value'] ?>" data-max="<?= $r['max_rebate_amount'] ?? '' ?>" data-types='<?= htmlspecialchars($r['applicable_tax_types']) ?>' data-conditions='<?= htmlspecialchars($r['conditions_json']) ?>' data-active="<?= $r['is_active'] ?>" data-from="<?= $r['effective_from'] ?>" data-to="<?= $r['effective_to'] ?? '' ?>"><i class="fas fa-edit"></i></button>
                                            <a href="<?= $base ?>/admin/property-tax/rebate-rule/delete/<?= $r['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this rebate rule?')"><i class="fas fa-trash"></i></a>
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

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editRebateForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="rule_id" id="editRuleId">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Rebate Rule</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                                <input type="text" name="rule_name" class="form-control" id="editRuleName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rule Code <span class="text-danger">*</span></label>
                                <input type="text" name="rule_code" class="form-control" id="editRuleCode" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rebate Type</label>
                                <select name="rebate_type" class="form-select" id="editRebateType">
                                    <option value="percent">Percentage</option>
                                    <option value="flat_amount">Flat Amount</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rebate Value</label>
                                <input type="number" step="0.01" name="rebate_value" class="form-control" id="editRebateValue" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Rebate Amount</label>
                                <input type="number" step="0.01" name="max_rebate_amount" class="form-control" id="editMaxRebate">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Effective From</label>
                                <input type="date" name="effective_from" class="form-control" id="editEffectiveFrom" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Effective To</label>
                                <input type="date" name="effective_to" class="form-control" id="editEffectiveTo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Applicable Tax Types</label>
                                <select name="applicable_tax_types[]" class="form-select" multiple id="editTaxTypes">
                                    <option value="property_tax">Property Tax</option>
                                    <option value="water_tax">Water Tax</option>
                                    <option value="sewerage_tax">Sewerage Tax</option>
                                    <option value="fire_tax">Fire Tax</option>
                                    <option value="education_cess">Education Cess</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                                    <label class="form-check-label" for="editIsActive">Active</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Conditions (JSON)</label>
                                <textarea name="conditions_json" class="form-control font-monospace" id="editConditions" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.editRule').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editRuleId').value = btn.dataset.id;
        document.getElementById('editRuleName').value = btn.dataset.name;
        document.getElementById('editRuleCode').value = btn.dataset.code;
        document.getElementById('editRebateType').value = btn.dataset.type;
        document.getElementById('editRebateValue').value = btn.dataset.value;
        document.getElementById('editMaxRebate').value = btn.dataset.max;
        document.getElementById('editEffectiveFrom').value = btn.dataset.from;
        document.getElementById('editEffectiveTo').value = btn.dataset.to;
        document.getElementById('editIsActive').checked = btn.dataset.active == '1';
        document.getElementById('editConditions').value = btn.dataset.conditions;

        const types = JSON.parse(btn.dataset.types || '[]');
        [...document.getElementById('editTaxTypes').options].forEach(opt => opt.selected = types.includes(opt.value));

        document.getElementById('editRebateForm').action = '<?= $base ?>/admin/property-tax/rebate-rule/update/' + btn.dataset.id;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});
</script>
</body>
</html>