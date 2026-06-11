<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-layer-group me-2"></i>Salary Structures</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>New Structure</button>
    </div>
    <?php if (isset($edit_structure)): ?>
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning text-white"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Structure #<?= $edit_structure['id'] ?> - <?= htmlspecialchars($edit_structure['employee_name'] ?? '') ?></h5></div>
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/structures/update/<?= $edit_structure['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Basic Salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="<?= $edit_structure['basic_salary'] ?? 0 ?>" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">HRA</label><input type="number" step="0.01" name="hra" class="form-control" value="<?= $edit_structure['hra'] ?? 0 ?>"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">DA</label><input type="number" step="0.01" name="da" class="form-control" value="<?= $edit_structure['da'] ?? 0 ?>"></div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Travel Allowance</label><input type="number" step="0.01" name="travel_allowance" class="form-control" value="<?= $edit_structure['travel_allowance'] ?? 0 ?>"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Medical Allowance</label><input type="number" step="0.01" name="medical_allowance" class="form-control" value="<?= $edit_structure['medical_allowance'] ?? 0 ?>"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Special Allowance</label><input type="number" step="0.01" name="special_allowance" class="form-control" value="<?= $edit_structure['special_allowance'] ?? 0 ?>"></div>
                    <div class="col-md-3 mb-3"><label class="form-label">PF %</label><input type="number" step="0.01" name="pf_percent" class="form-control" value="<?= $edit_structure['pf_percent'] ?? 12 ?>"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Tax Deduction</label><input type="number" step="0.01" name="tax_deduction" class="form-control" value="<?= $edit_structure['tax_deduction'] ?? 0 ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Effective From</label><input type="date" name="effective_from" class="form-control" value="<?= $edit_structure['effective_from'] ?? date('Y-m-d') ?>"></div>
                </div>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Update</button>
                <a href="<?= BASE_URL ?>/admin/salary/structures" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Basic</th><th>HRA</th><th>DA</th><th>TA</th><th>Medical</th><th>Special</th><th>PF%</th><th>Tax</th><th>Effective</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($structures ?? [])): ?>
                            <tr><td colspan="13" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No salary structures defined</td></tr>
                        <?php else: ?>
                            <?php foreach ($structures as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><strong><?= htmlspecialchars($s['employee_name'] ?? '') ?></strong></td>
                                <td>₹<?= number_format($s['basic_salary'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['hra'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['da'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['travel_allowance'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['medical_allowance'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($s['special_allowance'] ?? 0, 2) ?></td>
                                <td><?= (float)($s['pf_percent'] ?? 0) ?>%</td>
                                <td>₹<?= number_format($s['tax_deduction'] ?? 0, 2) ?></td>
                                <td><?= htmlspecialchars($s['effective_from'] ?? '') ?></td>
                                <td><span class="badge bg-<?= ($s['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($s['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/salary/structures/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
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
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/structures/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-plus me-1"></i>New Salary Structure</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users ?? [] as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Basic Salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="0" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">HRA</label><input type="number" step="0.01" name="hra" class="form-control" value="0"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">DA</label><input type="number" step="0.01" name="da" class="form-control" value="0"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Travel Allowance</label><input type="number" step="0.01" name="travel_allowance" class="form-control" value="0"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Medical Allowance</label><input type="number" step="0.01" name="medical_allowance" class="form-control" value="0"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Special Allowance</label><input type="number" step="0.01" name="special_allowance" class="form-control" value="0"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">PF (%)</label><input type="number" step="0.01" name="pf_percent" class="form-control" value="12"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Tax Deduction</label><input type="number" step="0.01" name="tax_deduction" class="form-control" value="0"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Effective From</label><input type="date" name="effective_from" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
