<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-coins me-2"></i>Salary Management</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/salary/report" class="btn btn-outline-primary"><i class="fas fa-chart-bar me-1"></i>Reports</a>
            <a href="<?= BASE_URL ?>/admin/salary/payroll-integration" class="btn btn-outline-info"><i class="fas fa-link me-1"></i>Payroll Integration</a>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-success text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="fw-light mb-1">Paid This Month</h6><h3 class="mb-0">₹<?= number_format($total_paid ?? 0, 2) ?></h3></div>
                        <div><i class="fas fa-check-circle fa-3x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-warning text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="fw-light mb-1">Pending Payments</h6><h3 class="mb-0"><?= (int)($pending_count ?? 0) ?></h3></div>
                        <div><i class="fas fa-clock fa-3x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-info text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="fw-light mb-1">Active users</h6><h3 class="mb-0"><?= (int)($employee_count ?? 0) ?></h3></div>
                        <div><i class="fas fa-users fa-3x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-danger text-white">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="fw-light mb-1">Pending Amount</h6><h3 class="mb-0">₹<?= number_format($pending_amount ?? 0, 2) ?></h3></div>
                        <div><i class="fas fa-exclamation-triangle fa-3x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Salary Trend</h5></div>
                <div class="card-body aps-cp-card-body">
                    <canvas id="salaryChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/admin/salary/structures" class="btn btn-outline-primary"><i class="fas fa-layer-group me-1"></i>Salary Structures</a>
                        <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-outline-success"><i class="fas fa-money-bill-wave me-1"></i>Payments</a>
                        <a href="<?= BASE_URL ?>/admin/salary/payments/create" class="btn btn-outline-info"><i class="fas fa-plus-circle me-1"></i>New Payment</a>
                        <a href="<?= BASE_URL ?>/admin/salary/contracts" class="btn btn-outline-warning"><i class="fas fa-file-signature me-1"></i>Contracts</a>
                        <a href="<?= BASE_URL ?>/admin/salary/tracker" class="btn btn-outline-secondary"><i class="fas fa-tachometer-alt me-1"></i>Tracker</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Payments</h5>
            <a href="<?= BASE_URL ?>/admin/salary/payments" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>#</th><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Date</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($recent_payments ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No payments yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_payments as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['employee_name'] ?? '') ?></strong></td>
                                <td>₹<?= number_format($p['gross_salary'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($p['total_deductions'] ?? 0, 2) ?></td>
                                <td><strong>₹<?= number_format($p['net_salary'] ?? 0, 2) ?></strong></td>
                                <td><?= htmlspecialchars($p['payment_date'] ?? '') ?></td>
                                <td><span class="badge bg-<?= match($p['status']??'pending') { 'paid'=>'success', 'pending'=>'warning', 'cancelled'=>'danger', default=>'secondary' } ?>"><?= ucfirst($p['status'] ?? 'pending') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    fetch('<?= BASE_URL ?>/admin/salary/stats').then(r=>r.json()).then(d=>{
        if(d.success && d.data){
            var labels = d.data.map(x=>x.label);
            .catch(err => console.error('Request failed:', err));
            var values = d.data.map(x=>parseFloat(x.total));
            new Chart(document.getElementById('salaryChart'), {
                type:'line',
                data:{labels:labels, datasets:[{label:'Salary Paid (₹)', data:values, borderColor:'#0d6efd', backgroundColor:'rgba(13,110,253,0.1)', fill:true, tension:0.4}]},
                options:{responsive:true, plugins:{legend:{display:false}}}
            });
        }
    }).catch(()=>{});
});
</script>
