<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-layer-group text-primary me-2"></i>Salary Structures</h1>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> New Structure
        </button>
    </div>

    <?php if (isset($edit_structure)): ?>
    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header py-3 bg-warning text-white d-flex align-items-center">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-edit me-2"></i>Edit Structure #<?= $edit_structure['id'] ?> - <?= htmlspecialchars($edit_structure['employee_name'] ?? '') ?></h6>
        </div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/structures/update/<?= $edit_structure['id'] ?>" class="salary-calc-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                
                <h5 class="text-success mb-3 border-bottom pb-2">Earnings</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Basic Salary <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="basic_salary" class="form-control calc-earning" value="<?= $edit_structure['basic_salary'] ?? 0 ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">HRA</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="hra" class="form-control calc-earning" value="<?= $edit_structure['hra'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Conveyance</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="conveyance" class="form-control calc-earning" value="<?= $edit_structure['conveyance'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Medical Allowance</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="medical_allowance" class="form-control calc-earning" value="<?= $edit_structure['medical_allowance'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Special Allowance</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="special_allowance" class="form-control calc-earning" value="<?= $edit_structure['special_allowance'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Other Allowances</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="other_allowances" class="form-control calc-earning" value="<?= $edit_structure['other_allowances'] ?? 0 ?>">
                        </div>
                    </div>
                </div>

                <h5 class="text-danger mt-4 mb-3 border-bottom pb-2">Deductions</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Employee PF</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="pf_employee" class="form-control calc-deduction" value="<?= $edit_structure['pf_employee'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">TDS (Tax)</label>
                        <div class="input-group">
                            <span class="input-group-text">â‚¹</span>
                            <input type="number" step="0.01" name="tds" class="form-control calc-deduction" value="<?= $edit_structure['tds'] ?? 0 ?>">
                        </div>
                    </div>
                </div>

                <div class="row mt-4 mb-4 bg-light p-3 rounded">
                    <div class="col-md-4">
                        <h5 class="text-muted">Gross Salary</h5>
                        <h3 class="text-success" id="edit_gross_display">â‚¹<?= number_format($edit_structure['gross_salary'] ?? 0, 2) ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-muted">Total Deductions</h5>
                        <h3 class="text-danger" id="edit_deduction_display">â‚¹<?= number_format($edit_structure['total_deductions'] ?? 0, 2) ?></h3>
                    </div>
                    <div class="col-md-4 border-start">
                        <h5 class="text-muted">Net Payable</h5>
                        <h3 class="text-primary fw-bold" id="edit_net_display">â‚¹<?= number_format($edit_structure['net_salary'] ?? 0, 2) ?></h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Effective Date</label>
                        <input type="date" name="effective_date" class="form-control" value="<?= $edit_structure['effective_date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($edit_structure['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($edit_structure['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <hr>
                <div class="d-flex justify-content-end">
                    <a href="<?= BASE_URL ?>/admin/salary/structures" class="btn btn-secondary me-2"><i class="fas fa-times me-1"></i>Cancel</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Update Structure</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Active Employee Structures</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="structuresTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Basic</th>
                            <th>HRA</th>
                            <th>Conv.</th>
                            <th>Med+Spl+Oth</th>
                            <th class="text-success">Gross</th>
                            <th class="text-danger">Deductions</th>
                            <th class="text-primary">Net Salary</th>
                            <th>Effective</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($structures)): ?>
                            <?php foreach ($structures as $s): ?>
                            <tr>
                                <td>#<?= $s['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" class="style-68946">
                                            <?= strtoupper(substr(htmlspecialchars($s['employee_name'] ?? 'U'), 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($s['employee_name'] ?? '') ?></strong>
                                            <div class="small text-muted"><?= htmlspecialchars($s['employee_email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>â‚¹<?= number_format($s['basic_salary'] ?? 0, 2) ?></td>
                                <td>â‚¹<?= number_format($s['hra'] ?? 0, 2) ?></td>
                                <td>â‚¹<?= number_format($s['conveyance'] ?? 0, 2) ?></td>
                                <?php 
                                    $other_earnings = ($s['medical_allowance'] ?? 0) + ($s['special_allowance'] ?? 0) + ($s['other_allowances'] ?? 0); 
                                ?>
                                <td>â‚¹<?= number_format($other_earnings, 2) ?></td>
                                <td class="text-success fw-bold">â‚¹<?= number_format($s['gross_salary'] ?? 0, 2) ?></td>
                                <td class="text-danger fw-bold">â‚¹<?= number_format($s['total_deductions'] ?? 0, 2) ?></td>
                                <td class="text-primary fw-bold">â‚¹<?= number_format($s['net_salary'] ?? 0, 2) ?></td>
                                <td><?= date('d M Y', strtotime($s['effective_date'] ?? 'now')) ?></td>
                                <td>
                                    <?php if(($s['status'] ?? 'inactive') === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/salary/structures/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit Structure">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
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

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/structures/store" class="salary-calc-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="createModalLabel"><i class="fas fa-plus-circle me-2"></i>New Salary Structure</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <label class="form-label font-weight-bold text-primary">Select Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select select2-modal" required class="style-13113">
                                <option value="">Search and select an employee...</option>
                                <?php foreach ($users ?? [] as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-arrow-up me-2"></i>Earnings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Basic Salary <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">â‚¹</span>
                                            <input type="number" step="0.01" name="basic_salary" class="form-control form-control-lg calc-earning" value="0" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">HRA</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">â‚¹</span>
                                                <input type="number" step="0.01" name="hra" class="form-control calc-earning" value="0">
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Conveyance</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">â‚¹</span>
                                                <input type="number" step="0.01" name="conveyance" class="form-control calc-earning" value="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4 mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Medical</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">â‚¹</span>
                                                <input type="number" step="0.01" name="medical_allowance" class="form-control calc-earning" value="0">
                                            </div>
                                        </div>
                                        <div class="col-4 mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Special</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">â‚¹</span>
                                                <input type="number" step="0.01" name="special_allowance" class="form-control calc-earning" value="0">
                                            </div>
                                        </div>
                                        <div class="col-4 mb-3">
                                            <label class="form-label text-muted small text-uppercase fw-bold">Other</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">â‚¹</span>
                                                <input type="number" step="0.01" name="other_allowances" class="form-control calc-earning" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-arrow-down me-2"></i>Deductions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small text-uppercase fw-bold">Employee PF</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">â‚¹</span>
                                            <input type="number" step="0.01" name="pf_employee" class="form-control calc-deduction" value="0">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label text-muted small text-uppercase fw-bold">TDS (Tax)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">â‚¹</span>
                                            <input type="number" step="0.01" name="tds" class="form-control calc-deduction" value="0">
                                        </div>
                                    </div>
                                    
                                    <div class="p-3 bg-light rounded border">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted fw-bold">Gross Salary:</span>
                                            <span class="text-success fw-bold" id="create_gross_display">â‚¹0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                            <span class="text-muted fw-bold">Total Deductions:</span>
                                            <span class="text-danger fw-bold" id="create_deduction_display">â‚¹0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between pt-2">
                                            <span class="text-primary fw-bold fs-5">Net Payable:</span>
                                            <span class="text-primary fw-bold fs-5" id="create_net_display">â‚¹0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-body row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small text-uppercase fw-bold">Effective Date <span class="text-danger">*</span></label>
                                <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-4 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-check-circle me-2"></i>Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 & DataTables Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if($.fn.DataTable) {
        $('#structuresTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search structures..."
            }
        });
    }

    // Initialize Select2 in Modal
    if($.fn.select2) {
        $('.select2-modal').select2({
            dropdownParent: $('#createModal'),
            theme: 'bootstrap-5'
        });
    }

    // Live Calculation Logic
    function formatCurrency(num) {
        return 'â‚¹' + parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function attachCalculator(formElement, prefix) {
        const earningInputs = formElement.querySelectorAll('.calc-earning');
        const deductionInputs = formElement.querySelectorAll('.calc-deduction');
        const grossDisplay = document.getElementById(`${prefix}_gross_display`);
        const dedDisplay = document.getElementById(`${prefix}_deduction_display`);
        const netDisplay = document.getElementById(`${prefix}_net_display`);

        function calculate() {
            let gross = 0;
            let deds = 0;

            earningInputs.forEach(input => {
                gross += parseFloat(input.value) || 0;
            });

            deductionInputs.forEach(input => {
                deds += parseFloat(input.value) || 0;
            });

            let net = gross - deds;

            if (grossDisplay) grossDisplay.textContent = formatCurrency(gross);
            if (dedDisplay) dedDisplay.textContent = formatCurrency(deds);
            if (netDisplay) netDisplay.textContent = formatCurrency(net);
        }

        earningInputs.forEach(input => input.addEventListener('input', calculate));
        deductionInputs.forEach(input => input.addEventListener('input', calculate));
    }

    // Attach to create modal
    const createForm = document.querySelector('#createModal form');
    if (createForm) attachCalculator(createForm, 'create');

    // Attach to edit form
    const editForm = document.querySelector('form.salary-calc-form:not(#createModal form)');
    if (editForm) attachCalculator(editForm, 'edit');
});
</script>
