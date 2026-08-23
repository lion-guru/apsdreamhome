<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-database me-2"></i>Salary Records</h1>
        <div class="btn-group">
            <?php if (!empty($months ?? [])): ?>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-calendar me-1"></i>Jump to Month</button>
                <ul class="dropdown-menu style-82023">
                    <?php foreach ($months as $m): ?>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/salary/records/<?= $m['year'] ?>/<?= $m['month'] ?>"><?= date('F', mktime(0,0,0,$m['month'],1)) ?> <?= $m['year'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark"><tr>
                        <th>#</th><th>Employee</th><th>Month</th><th>Year</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>Gross</th><th>Net</th><th>Date</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($records ?? [])): ?>
                            <tr><td colspan="11" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No records found</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><strong><?= htmlspecialchars($r['employee_name'] ?? '') ?></strong></td>
                                <td><?= date('F', mktime(0,0,0,$r['month'] ?? 1,1)) ?></td>
                                <td><?= $r['year'] ?? '' ?></td>
                                <td>₹<?= number_format($r['basic_pay'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($r['allowances'] ?? 0, 2) ?></td>
                                <td class="text-danger">₹<?= number_format($r['deductions'] ?? 0, 2) ?></td>
                                <td>₹<?= number_format($r['gross_pay'] ?? 0, 2) ?></td>
                                <td><strong>₹<?= number_format($r['net_pay'] ?? 0, 2) ?></strong></td>
                                <td><?= htmlspecialchars($r['payment_date'] ?? '') ?></td>
                                <td><span class="badge bg-<?= match($r['status']??'pending') { 'paid'=>'success', 'pending'=>'warning', 'cancelled'=>'danger', default=>'secondary' } ?>"><?= ucfirst($r['status'] ?? 'pending') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
