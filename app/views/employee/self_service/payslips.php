<?php
/**
 * Payslip History
*/

$page_title = $page_title ?? 'Payslip History';
$payslips = $payslips ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-money-bill me-2"></i>Payslip History</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($payslips)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-money-bill fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Payslips Found</h5>
                        <p class="text-muted">Your payslips will appear here once payroll is processed</p>
                    </div>
                    <?php else: ?>
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select id="yearFilter" class="form-select">
                                <option value="">All Years</option>
                                <?php 
                                $years = array_unique(array_column($payslips, 'period_year'));
                                rsort($years);
                                foreach ($years as $y): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All</option>
                                <option value="paid">Paid</option>
                                <option value="approved">Approved</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3><?= count($payslips) ?></h3>
                                    <small>Total Payslips</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <?php $paid = array_filter($payslips, fn($p) => ($p['status']??'')==='paid'); ?>
                                    <h3><?= count($paid) ?></h3>
                                    <small>Paid</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <?php $totalNet = array_sum(array_column($payslips, 'net_salary')); ?>
                                    <h3>₹<?= number_format($totalNet, 0) ?></h3>
                                    <small>Total Net Pay</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <?php $avgNet = count($payslips) ? $totalNet / count($payslips) : 0; ?>
                                    <h3>₹<?= number_format($avgNet, 0) ?></h3>
                                    <small>Average Monthly</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payslips Table -->
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="payslipsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>Basic</th>
                                            <th>HRA</th>
                                            <th>Allowances</th>
                                            <th>Gross</th>
                                            <th>Deductions</th>
                                            <th class="text-success">Net Pay</th>
                                            <th>Status</th>
                                            <th>Paid Date</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payslips as $ps): ?>
                                        <tr data-year="<?= $ps['period_year'] ?>" data-status="<?= $ps['status'] ?? 'draft' ?>">
                                            <td>
                                                <strong><?= date('F Y', mktime(0,0,0, $ps['period_month'], 1, $ps['period_year'])) ?></strong>
                                            </td>
                                            <td>₹<?= number_format($ps['basic_salary'] ?? 0, 2) ?></td>
                                            <td>₹<?= number_format($ps['hra'] ?? 0, 2) ?></td>
                                            <td>₹<?= number_format($ps['allowances'] ?? 0, 2) ?></td>
                                            <td class="fw-bold">₹<?= number_format($ps['basic_salary'] + $ps['hra'] + $ps['allowances'], 2) ?></td>
                                            <td>₹<?= number_format($ps['deductions'] ?? 0, 2) ?></td>
                                            <td class="text-success fw-bold">₹<?= number_format($ps['net_salary'] ?? 0, 2) ?></td>
                                            <td>
                                                <?php $st = $ps['status'] ?? 'draft'; ?>
                                                <span class="badge bg-<?= $st === 'paid' ? 'success' : ($st === 'approved' ? 'info' : ($st === 'draft' ? 'secondary' : 'warning')) ?>">
                                                    <?= ucfirst($st) ?>
                                                </span>
                                            </td>
                                            <td><?= $ps['paid_date'] ? date('d M Y', strtotime($ps['paid_date'])) : '-' ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>/employee/self-service/payslip/download/<?= $ps['id'] ?>" 
                                                        class="btn btn-outline-primary" title="Download PDF">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-info" 
                                                        onclick="viewPayslipDetails(<?= htmlspecialchars(json_encode($ps)) ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Detail Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-money-bill me-2"></i>Payslip Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payslipModalBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modalDownloadBtn" class="btn btn-warning" target="_blank">
                    <i class="fas fa-download me-1"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
#payslipsTable tbody tr { cursor: pointer; }
#payslipsTable tbody tr:hover { background-color: #f8f9fa; }
</style>

<script>
document.getElementById('yearFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const year = document.getElementById('yearFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('#payslipsTable tbody tr').forEach(row => {
        const rowYear = row.dataset.year;
        const rowStatus = row.dataset.status;
        
        const yearMatch = !year || rowYear == year;
        const statusMatch = !status || rowStatus === status;
        
        row.style.display = (yearMatch && statusMatch) ? '' : 'none';
    });
}

function viewPayslipDetails(ps) {
    const modal = new bootstrap.Modal(document.getElementById('payslipModal'));
    const gross = (ps.basic_salary || 0) + (ps.hra || 0) + (ps.allowances || 0);
    
    document.getElementById('payslipModalBody').innerHTML = `
        <div class="row mb-3">
            <div class="col-6"><strong>Month:</strong></div>
            <div class="col-6">${new Date(ps.period_year, ps.period_month - 1).toLocaleString('default', {month: 'long', year: 'numeric'})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Basic Salary:</strong></div>
            <div class="col-6">₹${Number(ps.basic_salary || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>HRA:</strong></div>
            <div class="col-6">₹${Number(ps.hra || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Allowances:</strong></div>
            <div class="col-6">₹${Number(ps.allowances || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-6"><strong>Gross Salary:</strong></div>
            <div class="col-6 text-success fw-bold">₹${Number(gross).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Deductions:</strong></div>
            <div class="col-6 text-danger">₹${Number(ps.deductions || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <hr>
        <div class="row mb-3">
            <div class="col-6"><strong>Net Pay:</strong></div>
            <div class="col-6 text-success fw-bold fs-5">₹${Number(ps.net_salary || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6"><span class="badge bg-${ps.status === 'paid' ? 'success' : (ps.status === 'approved' ? 'info' : 'secondary')}">${ps.status}</span></div>
        </div>
        <div class="row mb-3">
            <div class="col-6"><strong>Paid Date:</strong></div>
            <div class="col-6">${ps.paid_date ? new Date(ps.paid_date).toLocaleDateString() : 'Not Paid'}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Payment Method:</strong></div>
            <div class="col-6">${ps.payment_method || '-'}</div>
        </div>
        ${ps.transaction_id ? `<div class="row"><div class="col-6"><strong>Transaction ID:</strong></div><div class="col-6">${ps.transaction_id}</div></div>` : ''}
        ${ps.remarks ? `<div class="row mt-3"><div class="col-12"><strong>Remarks:</strong><br><small class="text-muted">${ps.remarks}</small></div></div>` : ''}
    `;
    
    document.getElementById('modalDownloadBtn').href = `<?= BASE_URL ?>/employee/self-service/payslip/download/${ps.id}`;
    modal.show();
}
</script>