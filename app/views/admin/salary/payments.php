<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-money-check-alt text-primary me-2"></i>Salary Payments</h1>
        <div>
            <button class="btn btn-outline-primary me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#bulkModal">
                <i class="fas fa-layer-group me-1"></i> Bulk Process
            </button>
            <a href="<?= BASE_URL ?>/admin/salary/payments/create" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50 me-1"></i> New Payment
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filter Payments</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold text-uppercase">Payment Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="paid" <?= ($filter_status ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= ($filter_status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="cancelled" <?= ($filter_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold text-uppercase">Employee</label>
                    <select name="employee_id" class="form-select select2-filter">
                        <option value="">All Employees</option>
                        <?php foreach ($users ?? [] as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= ($filter_employee ?? 0) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
                <?php if (!empty($filter_status) || !empty($filter_employee)): ?>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-light border w-100">Clear</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="paymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Payment ID</th>
                            <th>Employee</th>
                            <th>Month/Year</th>
                            <th class="text-success">Gross Amount</th>
                            <th class="text-danger">Deductions</th>
                            <th class="text-primary">Net Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td>#<?= $p['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-info text-white rounded-circle me-2 d-flex align-items-center justify-content-center" class="style-68946">
                                            <?= strtoupper(substr(htmlspecialchars($p['employee_name'] ?? 'E'), 0, 1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($p['employee_name'] ?? '') ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $monthName = date('F', mktime(0, 0, 0, $p['payment_month'] ?? 1, 10));
                                        echo $monthName . ' ' . ($p['payment_year'] ?? '');
                                    ?>
                                </td>
                                <td>₹<?= number_format($p['gross_amount'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($p['deduction_amount'] ?? 0, 2) ?></td>
                                <td class="text-primary fw-bold">₹<?= number_format($p['net_amount'] ?? 0, 2) ?></td>
                                <td><?= date('d M Y', strtotime($p['payment_date'] ?? 'now')) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucwords(str_replace('_',' ', $p['payment_method'] ?? 'bank_transfer')) ?>
                                    </span>
                                    <?php if(!empty($p['transaction_id'])): ?>
                                        <div class="small text-muted mt-1">Txn: <?= htmlspecialchars($p['transaction_id']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $statusClass = match($p['payment_status'] ?? 'pending') { 
                                            'paid' => 'success', 
                                            'pending' => 'warning', 
                                            'cancelled' => 'danger', 
                                            default => 'secondary' 
                                        };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle rounded-pill px-3">
                                        <?= ucfirst($p['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/salary/payments/view/<?= $p['id'] ?>" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i> View
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

<!-- Bulk Process Modal -->
<div class="modal fade" id="bulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="post" action="<?= BASE_URL ?>/admin/salary/payments/bulk">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="bulkModalLabel"><i class="fas fa-layer-group me-2"></i>Bulk Process Salaries</h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-info-subtle bg-info-subtle text-info-emphasis">
                        <i class="fas fa-info-circle me-2"></i> This will automatically generate pending payment records for all employees who have an <strong>Active Salary Structure</strong>, but haven't been paid for the selected month.
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Salary Month</label>
                            <select name="month" class="form-select form-select-lg" required>
                                <?php for ($m=1;$m<=12;$m++): ?>
                                <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small text-uppercase fw-bold">Salary Year</label>
                            <select name="year" class="form-select form-select-lg" required>
                                <?php for ($y=date('Y')-1;$y<=date('Y')+1;$y++): ?>
                                <option value="<?= $y ?>" <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-play me-2"></i>Generate Payments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if($.fn.DataTable) {
        $('#paymentsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search payments...",
                emptyTable: "No payments found for the selected criteria."
            }
        });
    }

    // Initialize Select2
    if($.fn.select2) {
        $('.select2-filter').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
});
</script>
