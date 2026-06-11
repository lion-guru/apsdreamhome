<?php
$page_title = $page_title ?? 'Salary Structures';
$editMode = ($mode ?? '') === 'edit';
$es = $edit_structure ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><?= $editMode ? 'Edit Salary Structure' : 'Salary Structures' ?></h4>
    <?php if (!$editMode): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Structure</button>
    <?php endif; ?>
</div>

<?php if ($editMode && $es): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body aps-cp-card-body">
            <h6 class="fw-bold mb-3">Editing: <?= htmlspecialchars($es['employee_name'] ?? '') ?></h6>
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/salary-structure/update/<?= $es['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Basic Salary (₹)</label><input type="number" name="basic_salary" class="form-control" step="0.01" value="<?= htmlspecialchars($es['basic_salary'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">HRA (%)</label><input type="number" name="hra_percent" class="form-control" step="0.01" value="<?= $es['basic_salary'] > 0 ? round(($es['hra'] ?? 0) / $es['basic_salary'] * 100, 2) : 0 ?>"></div>
                    <div class="col-md-4"><label class="form-label">Travel Allowance</label><input type="number" name="travel_allowance" class="form-control" step="0.01" value="<?= htmlspecialchars($es['ta'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Medical Allowance</label><input type="number" name="medical_allowance" class="form-control" step="0.01" value="<?= htmlspecialchars($es['medical_allowance'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Special Allowance</label><input type="number" name="special_allowance" class="form-control" step="0.01" value="<?= htmlspecialchars($es['special_allowance'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">PF (%)</label><input type="number" name="pf_percent" class="form-control" step="0.01" value="<?= $es['basic_salary'] > 0 ? round(($es['pf_deduction'] ?? 0) / $es['basic_salary'] * 100, 2) : 0 ?>"></div>
                    <div class="col-md-4"><label class="form-label">TDS Deduction</label><input type="number" name="tds_deduction" class="form-control" step="0.01" value="<?= htmlspecialchars($es['tds_deduction'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Effective From</label><input type="date" name="effective_from" class="form-control" value="<?= htmlspecialchars($es['effective_from'] ?? date('Y-m-d')) ?>"></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button> <a href="<?= BASE_URL ?>/admin/hr/salary-structure" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Basic</th><th>Gross</th><th>Net</th><th>PF</th><th>Effective</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($structures ?? [])): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No salary structures</td></tr>
                    <?php else: ?>
                        <?php foreach ($structures as $s): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($s['employee_name'] ?? '') ?></td>
                                <td>₹<?= number_format($s['basic_salary'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['gross_salary'] ?? 0, 2) ?></td>
                                <td class="fw-bold text-success">₹<?= number_format($s['net_salary'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['pf_deduction'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($s['effective_from'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= ($s['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                        <?= ($s['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/hr/salary-structure/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/salary-structure/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Salary Structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Basic Salary (₹) <span class="text-danger">*</span></label><input type="number" name="basic_salary" class="form-control" step="0.01" required></div>
                        <div class="col-md-4"><label class="form-label">HRA (%)</label><input type="number" name="hra_percent" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">DA (%)</label><input type="number" name="da_percent" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Travel Allowance</label><input type="number" name="travel_allowance" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Medical Allowance</label><input type="number" name="medical_allowance" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Special Allowance</label><input type="number" name="special_allowance" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">PF (%)</label><input type="number" name="pf_percent" class="form-control" step="0.01" value="12"></div>
                        <div class="col-md-4"><label class="form-label">TDS Deduction</label><input type="number" name="tds_deduction" class="form-control" step="0.01" value="0"></div>
                        <div class="col-md-4"><label class="form-label">Effective From</label><input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
